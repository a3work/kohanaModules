<?php defined('SYSPATH') or die('No direct script access.');

class Kohana_Model_Order_Item extends ORM
{
    // current state model
    protected $_state;

	protected $_table_name = 'shop_orders_items';

	protected $_belongs_to = array(
		'order' => array('model' => 'order', 'foreign_key' => 'order_id'),
		'state' => array('model' => 'state', 'foreign_key' => 'state_id'),
		'goods_item' => array('model' => 'goods', 'foreign_key' => 'goods_id'),
	);
	
    protected $_has_many = array(
        'movement'  => array('model' => 'movement', 'foreign_key' => 'item_id'),
    );

	/** clear pricelist by ID
	 *
	 * @param 	integer	pricelist ID
	 * @return 	this
	 */
	public function clear_pricelist($id)
	{
		DB::delete($this->_table_name)->where('pricelist_id', '=', $id)->execute( );
		
		return $this;
	}
    
    
	/** copy item movement for another
	 *
	 * @param 	Model_Order_item	destination ORM
	 * @return 	Model_Order_item this
	 */
    public function copy_movement(Model_Order_item $dst)
    {
		// copy main state
		$dst->state_id = $this->state_id;
		$dst->save( );
    
		$orm = ORM::factory('movement');
		
		// copy states of source item
		foreach ($this->movement->find_all( ) AS $item)
		{
			$orm
				->clear( )
				->values(array_merge(
					$item->as_array( ),
					array(
						'item_id' => $dst->id,
					)
				))
				->save( );
		}
		
		return $this;
    }
    
    
	/** Replace item or p/n
	 *
     * available parameters:
	 *  "code"				*	part number
	 *  "producer"			*	make
	 *  "descr"					description
	 *  "dtime"				*	delivery time
	 *  "price"				*	income price
	 *  "price_m"			*	client price (no discount)
	 *  "supplier"			*	supplier's logo
	 *  "supplier"			*	supplier's id
	 * 
	 * @param 	mixed		(array)	parameters of replacement OR (string) new p/n
	 * @param 	boolean		hide replacement
	 * @return 	ORM			replacement
	 */
	public function replace($data, $comment = '', $hide = FALSE)
	{
        if ( ! $this->_loaded)
        {
            return FALSE;
        }
        
		// :KLUDGE: check values of manadatory fields 
		if ( ! isset($data['code']) || ! isset($data['producer_logo']))
		{
			throw new Esp_Exception('Cannot replace item: mandatory values "code" and "make" don\'t set.');
		}
	
		$pricelist_id = isset($data['pricelist_id']) ? $data['pricelist_id'] : $this->pricelist_id;
		$supplier_id = isset($data['supplier_id']) ? $data['supplier_id'] : $this->supplier_id;
	
		// add new item
		$orm = ORM::factory('order_item')
			->values(array(
// 				'user_id'		=> $this->user_id,
				'order_id'		=> $this->order_id,
				'replaceable_id'=> $this->id,
				'quantity'		=> $this->quantity,
				'code'			=> $data['code'],
				'producer'		=> isset($data['producer']) ? $data['producer'] : $this->producer,
				'producer_logo'	=> $data['producer_logo'] ? $data['producer_logo'] : $this->producer_logo,
				'descr'			=> isset($data['descr']) ? $data['descr'] : $this->descr,
				'dtime'			=> isset($data["dtime"]) ? date("Y-m-d H:i:s", time( ) + $data["dtime"]*24*60*60) : $this->dtime,
				'price'			=> isset($data['price']) ? $data['price_m'] : $this->price,
// 				'price_client'	=> isset($data['price']) ? ($data['price_m'] * (1 - Pricing::discount($this->user_id))) : $this->price_client,
				'price_client'	=> isset($data['price'])
								   ? ORM::factory('discount')->calc($data['price_m'], $pricelist_id, $supplier_id, $this->order->user_id)
								   : $this->price_client,
				'price_buy'		=> isset($data['price']) ? $data['price'] : $this->price_buy,
				'supplier'		=> isset($data['supplier']) ? $data['supplier'] : $this->supplier,
				'supplier_id'	=> $supplier_id,
				'pricelist_id'	=> $pricelist_id,
				'hide_change'	=> $hide,
			))
			->save( );
			
		// duplicate movement history
		$this->copy_movement($orm);
		
		// set "replaced" state for current item
		$this->state('replacement', $comment);

        return $orm;
	}
	
	/** split item
	 *
	 * @param 	integer			part size
	 * @return 	Model_Order_item		new part
	 */
	public function split($quantity)
	{
		// fetch current data
		$data = $this->as_array( );
		
		if ($quantity >= $data['quantity'])
		{
			throw new Esp_Exception(
				'Cannot split item: quantity :quan >= :current_quan',
				array(':quan' => $quantity, ':current_quan' => $quantity)
			);
		}
		
		// set quantity and id for new item
		$data['comment'] = __('Отделено от #'.Html::factory('anchor')->text($data['id'])->href(Route::url('cms_orders', array(), array('query' => 'id:'.$data['id']))));
		$data['id'] = NULL;
		$data['quantity'] = $quantity;
		
		// add new item
		try
		{
			$orm = ORM::factory('order_item')->values($data)->save( );
		}
		catch (Exception $e)
		{
			throw new Esp_Exception($e->getMessage( ));
		}
		
		// edit current quantity
		$this->quantity -= $quantity;
		$this->save( );
		
		// duplicate movement history
		$this->copy_movement($orm);
		
		return $orm;
	}
	
    
    /** Add new state for current order or get current state
     *
     * @param   string  state label
     * @param   string  comment
     * @param   string  link to file
     * @return  mixed
     */
    public function state($label = NULL, $comment = NULL, $file = NULL)
    {
        if ( ! $this->_loaded)
        {
            return FALSE;
        }
        
        if (empty($label))
        {
            if (empty($this->_state))
            {
                $this->_state = $this
                                ->movement
                                ->order_by('id', 'desc')
                                ->find( );
            }
            
            return $this->_state;
        }
    
        // load state by label
        $state = State::instance( )->get($label);

        if ($state->name === NULL)
        {
            throw new Kohana_Exception('State ":state" not found', array(':state' => $label));
        }

		// add new movement item
		$this->_state =	$this
						->movement
						->values(array(
							'item_id'	=> $this->id,
							'state_id'	=> $state->id,
							'comment'	=> $comment,
							'file'   	=> $file,
							'username'	=> User::get( )->login,
						))
						->save( );

		// set new state to goods
		$this->state_id = $state->id;
		$this->save( );
						
		return TRUE;
    }        
	
	
	/** 
	 * get active items (according to state)
	 *
	 * @return 	this
	 */
	public function get_active( )
	{
		if ( ! $this->_loaded)
		{
			$this
			->with('state')
			->where('state.is_final', '=', 0);
		}
		
		return $this;
	}
}