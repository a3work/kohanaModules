<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Orders controller
 * @package 	Shop
 * @author 		A. St.
 * @date 		10.06.2015
 *
 **/

class Kohana_Controller_Cms_Orders extends Controller_Cms
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
			->text(__u('orders'))
			->child(__u('list'), Route::url('cms.common', array('controller' => 'Orders')))->css('cms-list')
			->child(__u('ordered goods'), Route::url('cms.common', array('controller' => 'Orders_Items')))->css('cms-list');
// 			->child(__u('add'), Route::url('cms.common', array('controller' => 'Orders', 'action' => 'add')))->css('cms-add');
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
		$orm = ORM::factory('order')
			// switch on pagination
			->page( )
			->order_by('ctime', 'desc');
			
				
		/* filter settings begins */
		$filter = Form::factory('filter')
					->always_show(TRUE)
					->filter_name('Filter')
					->field('chosen', __('client'), 'user_id')->empty_option( )->options(ORM::factory('account')->where('is_system', '=', 0)->select_values('username'))
					->field('text', __('номер заказа'), 'query')->min_length(1)
					->field('text', __('e-mail'), 'email')->min_length(1)
					->field('text', __('phone'), 'phone')->min_length(1)
					->field('text', __('comment'), 'comment')->min_length(1)
					->field('submit', 'Найти')
					->callback('user_id', function($value, $orm) {
						if ($value == '') return;
						$orm
							->where('user_id', '=', $value);

					}, array('orm' => $orm))
					->callback('query', function($value, $orm) {
						
						if ($value == '') return;
						$orm
							->where('id', 'LIKE', DB::expr('"%'.$value.'%"'));

					}, array('orm' => $orm))
					->callback('email', function($value, $orm) {
						if ($value == '') return;
						$orm
							->where('email', 'LIKE', DB::expr('"%'.$value.'%"'));

					}, array('orm' => $orm))
					->callback('phone', function($value, $orm) {
						if ($value == '') return;
						$orm
							->where('phone', 'LIKE', DB::expr('"%'.$value.'%"'));

					}, array('orm' => $orm))
					->callback('comment', function($value, $orm) {
						if ($value == '') return;
						$orm
							->where('comment', 'LIKE', DB::expr('"%'.$value.'%"'));

					}, array('orm' => $orm))
					->render( );
					
		$this->template->right = $filter;
		/* filter settings ends */
		
		
		
		/* context menu declaration begins */
		$menu = Menu_Context::factory( )->id('orders');
		
		$menu
			->child(__('edit'), Route::url('cms.common', array('controller' => 'Orders', 'mode'=>CMS::VIEW_MODE_FULL, 'action'=>'edit', 'id'=>':id')), 'edit')
				->dbl( )		
			->child(__('delete'), Route::url('cms.common', array('controller' => 'Orders', 'mode'=>CMS::VIEW_MODE_FULL, 'action'=>'delete', 'id'=>':id')), 'delete')
				->multiple( )
				->confirm( )
				->ajax( );
			
		$menu->render( );
		/* context menu declaration ends */
		
		
		
		
		
		/* data table begins */
		// set up table header 
		$table = Html::factory('table')->header(array(
			__('#'),
			__('date'),
			__('email'),
			__('phone'),
			__('comment'),
		));
		
		// table body
		foreach ($orm->find_all( ) AS $item)
		{
			$table
				->line(array(
					$item->id,
					Date::format($item->ctime, Date::FORMAT_FULL),
					$item->user_id != 0 ? Html::factory('anchor')->href(Route::url('user_manage', array('id' => $item->user->id)))->text($item->user->email) : $item->email,
					$item->user_id != 0 ? Html::factory('anchor')->href(Route::url('user_attr', array('id' => $item->user->id)))->text($item->user->attributes->phone) : $item->phone,
					$item->comment,
				))
				->classes($menu->context(
					array(
						'id' => $item->id,
					)				
				));
		}
		
		$this->template->body = $orm->pagination( ).$table->render('cms-table').$orm->pagination( );
		/* data table ends */
		
		$this->template->header = __u('orders');
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
			$orm = ORM::factory('order', $id);
			
			if ( ! $orm->loaded( ))
			{
				throw new Shop_Exception('Cannot load order #:id', array(':id' => $id));
			}
			
			$defaults = $orm->as_array( );
			$defaults['ctime'] = Date::format($defaults['ctime'], Date::FORMAT_FULL);
			
			$form->defaults($defaults);
		}
		else
		{
			$orm = ORM::factory('order');
		}
		
		
		$columns = $orm->table_columns( );
				
		$form
			->message(__u('data has been saved successfuly'))
			->field('text', __('date'), 'ctime')->disabled(TRUE)->not_empty( )
			->field('text', __('e-mail'), 'email')->disabled(TRUE)->not_empty( )
			->field('text', __('phone'), 'phone')->disabled(TRUE)->not_empty( )
			->field('textarea', __('comment'), 'comment')->message('комментарий виден клиенту')->not_empty( )
			->field('submit', __('save'));
				
		$form->render( );
		
		if ($form->sent( ))
		{
			$data = $form->result( )->as_array( );
			$orm->values($data)->save( );
		}
		
		$details = Request::factory(Route::url('cms.common', array('controller' => 'orders_items', 'action' => 'index'), array('order_id' => $orm->id)))->execute( )->body( )->body;
	
		$this->template->parent = __u('orders');
		$this->template->parent_href = Route::url('cms.common', array('controller' => 'Orders'));
		$this->template->header = __u('#').$orm->id;
		$this->template->body = $form.'<br><h3>'.__u('ordered goods').'</h3>'.$details;	
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
			$orm = ORM::factory('order', $id);
			
			if ($orm->loaded( ))
			{
				$orm->delete( );
			}
		}
		
		$body = Request::factory(Route::url('cms.common', array('controller' => 'Orders')))->execute( )->body( );
		
		$this->response->body(Basic::json_safe_encode(array(
			'body' => $body->body,
		)));
	}	
	
	
	/** Action: add
	 *  add new template
	 *
	 * @return void
	 */
	public function action_add( )
	{
		$this->action_edit( );
	
		$this->template->header = __u('new order');
	}	
}