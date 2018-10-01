<?php defined('SYSPATH') or die('No direct script access.');

class Kohana_Model_Goods extends ORM {

    protected $_table_name = 'shop_goods';
    
    // current state model
    protected $_state;

    protected $_belongs_to = array(
        'supplier'  => array('model' => 'supplier', 'foreign_key' => 'supplier_id'),
        'pricelist'  => array('model' => 'pricelist', 'foreign_key' => 'pricelist_id'),
        'category' => array('model' => 'goods_category', 'foreign_key' => 'category_id')
    );

	/**
	 * @const integer	flag: use simple view for display currency
	 */
	const CURRENCY_SIMPLE = 1;

	/**
	 * @const integer	flag: use regular view for display currency
	 */
	const CURRENCY_REGULAR = 2;

	/**
	 * Returns hyperreference to the item details page
	 *
	 * @return	string
	 */
	public function get_href() {
		if ($this->_loaded === FALSE)
		{
			return;
		}
	
		return Route::url('goods', array('category' =>  $this->category->symcode, 'id' => $this->symcode));
	}
    
	/** Reload update function: add searchbox
	 *
	 * @param	Validation
	 * @return 	this
	 */
	public function update(Validation $validation = NULL)
	{
		// generate symbolic code
		$this->symcode = $this->id.Basic::tr(' '.$this->name);
	
		return parent::update($validation);
	}

    
    
    
    /** Devide goods: extract specified quantity from totals
     *
     * @param   integer count for extraction
     * @return  new line
     **/
    public function extract($count)
    {
    
    }
     
    /** fetch unordered and ordered for stores only
     * 
     * @return   void
     */
    protected function _build_limitation( )
    {
//         $this->where($this->_object_name.'.user_id', 'IN', DB::expr('(0, '.implode(',', User::stores( )).')'));
    }
     
    /** add limitation and build query
     * 
     * @param   integer type
     * @return  void
     */
    protected function _build($type)
    {
        $this->_build_limitation( );
    
        parent::_build($type);
    }
    
    /** Overload method "delete"
     *  Set up is_removed flag while item delete
     *
     * @return ORM
     */
//     public function delete( )
//     {
//         if ( ! $this->_loaded)
//             throw new Kohana_Exception('Cannot delete :model model because it is not loaded.', array(':model' => $this->_object_name));
//     
//         // Use primary key value
//         $id = $this->pk();
//     
//         // Delete the object
//         DB::update($this->_table_name)
//             ->set(array('is_removed' => 1))
//             ->where($this->_primary_key, '=', $id)
//             ->execute($this->_db);
//     
//         return $this->clear();
//     }

	/** clear pricelist by ID
	 *
	 * :DEPRECATED: moved to Kohana_Model_Order_Item
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
	 * :DEPRECATED: moved to Kohana_Model_Order_Item
	 * 
	 * @param 	Model_Goods	destination ORM
	 * @return 	Model_Goods this
	 */
    public function copy_movement(Model_Goods $dst)
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
	 * :DEPRECATED: moved to Kohana_Model_Order_Item
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
	
		// add new item
		$orm = ORM::factory('goods')
			->values(array(
				'user_id'		=> $this->user_id,
				'order_id'		=> $this->order_id,
				'replaceable_id'=> $this->id,
				'quantity'		=> $this->quantity,
				'code'			=> $data['code'],
				'producer'		=> isset($data['producer']) ? $data['producer'] : $this->producer,
				'producer_logo'	=> $data['producer_logo'] ? $data['producer_logo'] : $this->producer_logo,
				'descr'			=> isset($data['descr']) ? $data['descr'] : $this->descr,
				'dtime'			=> isset($data["dtime"]) ? date("Y-m-d H:i:s", time( ) + $data["dtime"]*24*60*60) : $this->dtime,
				'price'			=> isset($data['price']) ? $data['price_m'] : $this->price,
				'price_client'	=> isset($data['price']) ? ($data['price_m'] * (1 - Pricing::discount($this->user_id))) : $this->price_client,
				'price_buy'		=> isset($data['price']) ? $data['price'] : $this->price_buy,
				'supplier'		=> isset($data['supplier']) ? $data['supplier'] : $this->supplier,
				'supplier_id'	=> isset($data['supplier_id']) ? $data['supplier_id'] : $this->supplier_id,
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
	 * :DEPRECATED: moved to Kohana_Model_Order_Item
	 * 
	 * @param 	integer			part size
	 * @return 	Model_Goods		new part
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
		$data['id'] = NULL;
		$data['quantity'] = $quantity;
		
		// add new item
		try
		{
			$orm = ORM::factory('goods')->values($data)->save( );
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
	 * :DEPRECATED: moved to Kohana_Model_Order_Item
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
    
	/** Calc price for specified user
	 *
	 * @param	integer		user_id
	 * @return 	float
	 */
	public function client_price($uid = NULL)
	{
		if ( ! $this->_loaded)
		{
			return 0;
		}
	
		$markup_price = ORM::factory('markup')->calc($this->price, $this->pricelist_id, $this->supplier_id);
		$discount_price = ORM::factory('discount')->calc($markup_price, $this->pricelist_id, $this->supplier_id, $uid);
		
		return $discount_price;
	}
	
	/** Calc price for specified user and format it
	 *
	 * @param 	integer		user_id
	 * @return 	string
	 */
	public function client_price_formatted($uid = NULL)
	{
		return Model_Goods::format_price($this->client_price($uid), $this->currency);
	}

	/** Format price
	 *
	 * @param	float
	 * @param	string	currency code (RUR, USD etc.)
	 * @return	string
	 */
	public static function format_price($price, $currency = NULL, $decimal = 2, $currency_view = Model_Goods::CURRENCY_REGULAR)
	{
		switch ($currency)
		{
			case 'RUR':
// 				$currency = '&nbsp;<span class="rub">p</span>';
				$currency = Model_Goods::CURRENCY_REGULAR == 1 ? '&nbsp;р' : '&nbsp;<span class="r"></span>';
			break;
			case 'USD':
				$currency = '$';
			break;
			case 'EUR':
				$currency = '&nbsp;&euro;';
			break;
			default:
				$currency = '';
		}
	
		$price = (float) $price;
	
		return str_replace(' ', '&nbsp;', number_format($price, $decimal, ',', $price > 10000 ? ' ' : '')).$currency;
	}

	/** Normalize goods code
	*
	* @param        string  Input code
	*
	* @return       string  Normalized code
	*/
	public static function code_norm($code)
	{
		return (preg_replace('/[\W_]+/', '', mb_strtolower($code)));
	}    
}
