<?php defined('SYSPATH') or die('No direct script access.');

class Kohana_Model_Order extends ORM {

    protected $_table_name = 'shop_orders';
    
	protected $_has_many = array(
		'item' => array('model' => 'order_item', 'foreign_key' => 'order_id'),
	);

	protected $_belongs_to = array(
		'user' => array('model' => 'account', 'foreign_key' => 'user_id'),
		'account' => array('model' => 'account', 'foreign_key' => 'user_id'),
		'payment' => array('model' => 'payment', 'foreign_key' => 'payment_id'),
		'email' => array('model' => 'email_messages', 'foreign_key' => 'email_id'),
	);

	/** 
	 * Overload ORM::delete: delete items of order when delete order
	 *
	 * @param 	TYPE	VAR_DESCR
	 * @return 	RETURN
	 */
    public function delete( )
    {
		if ( ! $this->_loaded)
			throw new Kohana_Exception('Cannot delete :model model because it is not loaded.', array(':model' => $this->_object_name));

		DB::delete($this->item->table_name( ))->where('order_id', '=', $this->id)->execute( );
			
		parent::delete();
    }
//    /** fetch active ordered items
//      * 
//      * @return   void
//      */
//     protected function _build_limitation( )
//     {
//         // fetch active items
//         $this->where($this->_object_name.'.is_removed', '=', 0);
//         
//         // fetch already ordered items
//         $this
// 			->and_where_open( )
// 			->or_where($this->_object_name.'.user_id', '!=', 0)
// 			->or_where($this->_object_name.'.state_id', '!=', 0)
// 			->and_where_close( );
//     }
    	/** 
	 * Fetch total price of order
	 *
	 * @return 	int
	 */
    public function price( )
    {
		$result = 0;
		
    		if ($this->_loaded)
    		{
			foreach ($this->item->find_all() AS $item)
			{
				$result += $item->price;
			}
    		}
		
		return $result;
    }
}
