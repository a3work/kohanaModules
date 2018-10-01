<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		client controller of orders
 * @package 	Shop
 * @author 		A. St.
 * @date 		10.06.2015
 *
 **/

class Kohana_Controller_Orders extends Controller_Main
{
	/**
	 * @var boolean		check access rules and load page data from storage
	 */
	protected $_auto_load = TRUE;

	/**
	 * @var boolean		automatic rendering switch (TRUE by default for non-CLI requests) 
	 */
	protected $_auto_render = TRUE;
	
	/**
	 * @var boolean 	use breadcrumbs
	 */
	protected $_process_menu = TRUE;

	/**
	 * @var string 		current URI
	 */
	protected $_uri;
	
	/**
	 * fetch page content, metadata and menu
	 */
    public function before( )
    {
		// define URI of basic page
		$this->_uri = Route::url('orders');
		
		parent::before( );
	}
		
	/** orders list
	 *
	 * @return 	void
	 */
	public function action_index( )
	{
		if ( ! User::check( ))
		{
			throw new HTTP_Exception_403;
		}
	
		$id = (int) $this->request->param('id');
	
		if ($id != 0)
		{
			// init orm
			$orm = ORM::factory('order')
				->where('user_id', '=', User::get( )->id)
				->where('id', '=', $id)
				->find( );
				
			if ( ! $orm->loaded( ))
			{
				throw new HTTP_Exception_404;
			}
				
			/* data table begins */
			// set up table header 
			$table = Html::factory('table')->header(array(
				__('#'),
				__('state'),
				__('description'),
				__('quantity'),
				__('price'),
				__('date'),
			));
			
			// table body
			foreach ($orm->item->find_all( ) AS $item)
			{
				$table
					->line(array(
						$item->id,
						$item->state->name,
						$item->descr,
						$item->quantity,
						Model_Goods::format_price($item->price, $item->currency),
						Date::format($item->ctime, Date::FORMAT_DATE),
						$item->comment,
					));
			}
			
			$this->_view_body->extra = View::factory('table', array('body' => $table->render('basic search-results')));
			
			Breadcrumbs::instance( )->add('', __u('order #:id', array(':id' => $id)));
			
			$this->_view_body->body = '<h1>'.__u('order #:id of :date', array(':id' => $id, ':date' => Date::format($orm->ctime))).'</h1>';
		}
		else
		{
			// init orm
			$orm = ORM::factory('order')
				->where('user_id', '=', User::get( )->id)
				// switch on pagination
				->page( )
				->order_by('ctime', 'desc');
			
			
			/* data table begins */
			// set up table header 
			$table = Html::factory('table')->header(array(
				__('#'),
				__('date'),
				__('composition of the order'),
				__('quantity'),
				__('price'),
				__('comment'),
				''
			));
			
			// table body
			foreach ($orm->find_all( ) AS $item)
			{
				$composition = array();
				$total = $quantity = 0;
				$total_currency = '';
				
				$i = 0;
				foreach (ORM::factory('order_item')->where('order_id', '=', $item->id)->find_all( ) AS $orders_item)
				{
					$quantity += $orders_item->quantity;
					$total += $orders_item->price*$orders_item->quantity;
					$item_price = Model_Goods::format_price($orders_item->price, $orders_item->currency);
					if (++$i <= 3)
					{
						$composition[] = "{$orders_item->descr} ({$orders_item}&times;{$item_price})";
					}
					$total_currency = $orders_item->currency;
				}
			
				$details_url = Route::url('orders', array('id' => $item->id));
			
				$table
					->line(array(
						$item->id,
						Date::format($item->ctime, Date::FORMAT_FULL),
						(count($composition) > 0 ? '<ul><li>'.implode('</li><li>', $composition).'</li></ul>' : '')
						.($i > 3 ? Html::factory('anchor')->href($details_url)->text(__('show all')) : ''),
						$quantity,
						Model_Goods::format_price($total, $total_currency),
						$item->comment,
						Html::factory('anchor')->href($details_url)->text(str_replace(' ', '&nbsp;', __('show details'))),
					));
			}
			/* data table ends */
			
			$this->_view_body->extra = View::factory('table', array('body' => $table->render('basic search-results'), 'pagination' => $orm->pagination( )));
		}
	}
	
	/** 
	 * details of order
	 *
	 * @return 	void
	 */
	public function action_item( )
	{
		$this->_view_body->body =  '';
	}
	
}