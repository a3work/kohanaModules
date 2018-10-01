<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Order items
 * @package 	Shop
 * @author 		A. St.
 * @date 		10.06.2015
 *
 **/

class Kohana_Controller_Cms_Orders_Items extends Controller_Cms
{
	/** reloaded Kohana Controller::before 
	 * 
	 * @return void
	 */
	public function before( )
	{
		parent::before( );
		
		if ( ! acl('shop_orders_viewing'))
		{
			throw new Access_Exception( );
		}
		
		$this->_left_menu = Cms_Submenu::factory( );
		$this->_left_menu
			->text(__u('ordered goods'))
			->child(__u('list'), Route::url('cms.common', array('controller' => 'Orders_Items')))->css('cms-list')
			->child(__u('orders table'), Route::url('cms.common', array('controller' => 'Orders')))->css('cms-list');
	}

	/** reloaded Kohana Controller::after 
	 * 
	 * @return void
	 */
	public function after( )
	{
		$this->template->left = $this->_left_menu->render( );
	
		parent::after( );
	}

	/** Action: index
	 *  list
	 *
	 * @return void
	 */
	public function action_index( )
	{
		// init orm
		$orm = ORM::factory('order_item')
			// switch on pagination
			->page( );
			
			
			
				
		/* filter settings begins */
		$filter = Form::factory('filter')
					->always_show(TRUE)
					->filter_name('Filter')
					->field('text', __('order'), 'order_id')->min_length(1)->max_length(9)
					->field('submit', 'Найти')
					->callback('order_id', function($value, $orm) {
						
						$orm
							->where('order_id', '=', $value);

					}, array('orm' => $orm))
					->render( );
					
		$this->template->right = $filter;
		/* filter settings ends */
		
		
		
		/* context menu declaration begins */
		$menu = Menu_Context::factory( );
		
		$menu
			->child(__('edit'), Route::url('cms.common', array('controller' => 'Orders_Items', 'mode'=>CMS::VIEW_MODE_FULL, 'action'=>'edit', 'id'=>':id')), 'edit')
				->dbl( );
		$submenu = $menu
			->submenu(__('set status'), NULL, 'set_status')
				->multiple( );
				
		foreach (ORM::factory('state')->order_by('position')->select_values( ) AS $value)
		{
			$submenu
				->child($value->name, Route::url('cms.common', array('controller' => 'Orders_Items', 'mode'=>CMS::VIEW_MODE_FULL, 'action'=>'set_status', 'id'=>':id'), array('state_id' => $value->id)), 'set_status_'.$value->id)
					->multiple( )
					->ajax( );
		}
			
			
		$menu
			->child(__('delete'), Route::url('cms.common', array('controller' => 'Orders_Items', 'mode'=>CMS::VIEW_MODE_FULL, 'action'=>'delete', 'id'=>':id')), 'delete')
				->multiple( )
				->confirm( )
				->ajax( );
			
		$menu->render( );
		/* context menu declaration ends */
		
		
		
		
		
		/* data table begins */
		// set up table header 
		$table = Html::factory('table')->header(array(
			__('#'),
			__('order'),
			__('state'),
			__('supplier'),
			__('pricelist'),
			__('description'),
			__('quantity'),
			__('price'),
			__('comment'),
			__('date'),
		));
		
		// table body
		foreach ($orm->find_all( ) AS $item)
		{
			$table
				->line(array(
					$item->id,
					Html::factory('anchor')->href(Route::url('cms.common', array('controller' => 'orders', 'action' => 'edit', 'id' => $item->order_id)))->text($item->order_id),
					
					$item->state->name,
					
					Html::factory('anchor')->href(
						Route::url('cms.common', array('controller' => 'suppliers', 'action' => 'edit', 'id' => $item->supplier_id))
					)->text($item->supplier_name),
					
					Html::factory('anchor')->href(
						Route::url('cms.common', array('controller' => 'pricelists', 'action' => 'edit', 'id' => $item->pricelist_id))
					)->text($item->pricelist_name),
					$item->descr,
					$item->quantity,
					Model_Goods::format_price($item->price, $item->currency),
					$item->comment,
					Date::format($item->ctime, Date::FORMAT_DATE),
				))
				->classes($menu->context(
					array(
						'id' => $item->id,
					)				
				));
		}
		
		$this->template->parent = __u('orders table');
		$this->template->parent_href = Route::url('cms.common', array('controller' => 'Orders'));
		$this->template->body = $orm->pagination( ).$table->render('cms-table').$orm->pagination( );
		/* data table ends */
		
		$this->template->header = __u('ordered goods');
	}	

	/** 
	 * set order status
	 *
	 * @return 	void
	 */
	public function action_set_status( )
	{
		if ( ! $this->request->is_ajax( ))
		{
			throw new Access_Exception( );
		}
		
		$this->auto_render = FALSE;
		
		// multiple ids support
		$ids = explode(Site::ID_SEPARATOR, $this->request->param('id'));
		$state_id = (int) $this->request->query('state_id');
		
		foreach ($ids AS $id)
		{
			$orm = ORM::factory('order_item', $id);
			$orm->state_id = $state_id;
			$orm->save( );			
		}
		
		
		if ($this->request->referrer( ) !== NULL)
		{
			$body = Request::factory(str_replace(URL::site(NULL, 'http'), '', $this->request->referrer( )))->execute( )->body( );
			
			$this->response->body(Basic::json_safe_encode(array(
				'body' => $body->body,
			)));
		}
	}
	
	/** Action: edit
	 *  Edit template
	 *
	 * @return void
	 */
	public function action_edit( )
	{
		$id = (int) $this->request->param('id');

		$form = Form::factory( )
				->show_on_success(TRUE)
				->clear_on_success(FALSE);
		
		if ($id != 0)
		{
			$orm = ORM::factory('order_item', $id);
			
			if ( ! $orm->loaded( ))
			{
				throw new Shop_Exception('Cannot load text #:id', array(':id' => $id));
			}
			
			$defaults = $orm->as_array( );
			$form->defaults($defaults);
		}
		else
		{
			$orm = ORM::factory('order_item');
		}
		
		
		$columns = $orm->table_columns( );

		$form
			->message(__u('data has been saved successfuly'))
			->field('select', __('status'), 'state_id')->options(ORM::factory('state')->order_by('position')->select_values( ))
			->field('textarea', __('description'), 'descr')
			->field('text', __('quantity'), 'quantity')->not_empty( )->rule('numeric')
			->field('text', __('price').', '.$orm->currency, 'price')->not_empty( )->rule('numeric')
			->field('text', __('comment'), 'comment')->message('комментарий виден пользователю')
			->field('submit', __('save'));
				
		$form->render( );
		
		if ($form->sent( ))
		{
			$data = $form->result( )->as_array( );
			$orm->values($data)->save( );
		}
	
		$this->template->parent = __u('ordered goods');
		$this->template->parent_href = Route::url('cms.common', array('controller' => 'Orders_Items'));
		$this->template->header = __u(':name', array(':name' => $orm->descr));
		$this->template->body = $form;	
	}	

	/** Action: delete
	 *  Delete template
	 *
	 * @return void
	 */
	public function action_delete( )
	{
		if ( ! $this->request->is_ajax( ))
		{
			throw new Access_Exception( );
		}
		
		$this->auto_render = FALSE;
		
		// multiple ids support
		$ids = explode(Site::ID_SEPARATOR, $this->request->param('id'));
		
		foreach ($ids AS $id)
		{
			$orm = ORM::factory('order_item', $id);
			
			if ($orm->loaded( ))
			{
				$orm->delete( );
			}
		}
		
		$body = Request::factory(Route::url('cms.common', array('controller' => 'Orders_Items')))->execute( )->body( );
		
		$this->response->body(Basic::json_safe_encode(array(
			'body' => $body->body,
		)));
	}	
}