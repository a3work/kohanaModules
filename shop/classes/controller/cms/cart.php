<?php defined('SYSPATH') or die('No direct script access.');

class Controller_Cms_Cart extends Kohana_Controller_Cms_Cart {

	/** submit method example
	* 
	* save data
	* 
	* @return	int		new order number
	*/
	protected function _submit($uid)
	{
		if (count(Cms_Cart::instance( )) == 0)
		{
			$this->request->redirect(Url::base( ));
		}
	
		/* get new order id */
		$orm = ORM::factory('order')->order_by('id', 'desc')->limit(1)->find( );
		
		if ($orm->loaded( ))
		{
			$new_order_id = $orm->order_id + 1;
		}
		else
		{
			$new_order_id = 1;
		}
		
		$orm->clear( );
		
		
		foreach (Cms_Cart::instance( ) AS $item)
		{
			$orm->values(array(
				'user_id'		=> User::get( )->id,
				'order_id'		=> $new_order_id,
				'is_external'	=> (int) ! (isset($item->id) && $item->id != 0),
				'code'			=> $item->code,
				'producer'		=> $item->producer,
				'descr'			=> $item->descr,
				'price'			=> $item->price,
				'quan'			=> $item->quan,
				'supplier'		=> $item->supplier,
				'dtime'			=> date("Y-m-d", time( ) + $item->dtime),
				'status'		=> Site::config($this->_config)->begin_status,
			))->save( );
			
			$orm->clear( );
		}
		
		return $new_order_id;
	}
 

    /** Load data by specified id from database or other source
	 *
	 * @param	mixed	data ID
	 * 
	 * @return	mixed
	 */
    protected function _data($id)
    {
		$data = Session::instance( )->get(Controller_Cms_Goods::SESS_VAR_RESULT);
		
		if (isset($data[$id]))
		{
			return $data[$id];
		}
		
		return NULL;
    }
}
