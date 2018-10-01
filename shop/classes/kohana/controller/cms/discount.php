<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Discount controller
 * @package 	Shop
 * @author 		A. St.
 * @date 		
 *
 **/

class Kohana_Controller_Cms_Discount extends Controller_Cms
{
	/** reloaded Kohana Controller::before 
	 * 
	 * @return void
	 */
	public function before( )
	{
		parent::before( );
		
		if ( ! acl('discount_browse'))
		{
			throw new Access_Exception( );
		}
		
		$this->_left_menu = Cms_Submenu::factory( );
		$this->_left_menu
			->text(__u('discounts'))
			->child(__u('list'), Route::url('cms.common', array('controller' => 'Discount')))->css('cms-list')
			->child(__u('add'), Route::url('cms.common', array('controller' => 'Discount', 'action' => 'add')))->css('cms-add');
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
		$orm = ORM::factory('discount')
			// switch on pagination
			->page( );
			
		$pricelists_select = array();
		$pricelists_orm = ORM::factory('pricelist');

		foreach ($pricelists_orm->find_all( ) AS $pricelist)
		{
			$pricelists_select[$pricelist->id] = 
				$pricelist->supplier->name
				.', '.$pricelist->logo
				.($pricelist->dtime > 0
				? __(', :dtime :(день|дня|дней)', array(':dtime' => $pricelist->dtime))
				: __('in stock'))
				.($pricelist->comment != '' ? ', '.$pricelist->comment : '');
		}
			
		
		
		$suppliers_select = array( );
		$suppliers_orm = ORM::factory('supplier');
		
		foreach ($suppliers_orm->find_all( ) AS $suppliers_item)
		{
			$suppliers_select[$suppliers_item->id] =
				$suppliers_item->name;
		}
					
		
		$users_select = array( );
		$users_orm = ORM::factory('account')
						->with('attributes')
						->where('is_system', '=', 0);
		foreach ($users_orm->find_all( ) AS $users_item)
		{
			$users_select[$users_item->id] = $users_item->username;
		}
		
		
		/* filter settings begins */
		$filter = Form::factory('filter')
					->always_show(TRUE)
					->filter_name('Filter')
					->field('chosen', __('user'), 'user_id')->empty_option( )
						->options($users_select)
					->field('chosen', __('supplier'), 'supplier_id')->empty_option( )
						->options($suppliers_select)
					->field('chosen', __('pricelist'), 'pricelist_id')->empty_option( )
						->options($pricelists_select)
					->field('submit', 'Найти')
					->callback('user_id', function($value, $orm) {
						if ($value != '')
						{
							$orm
								->where('user_id', '=', $value);
						}
					}, array('orm' => $orm))
					->callback('supplier_id', function($value, $orm) {
						if ($value != '')
						{
							$orm
								->where('supplier_id', '=', $value);
						}
					}, array('orm' => $orm))
					->callback('pricelist_id', function($value, $orm) {
						if ($value != '')
						{
							$orm
								->where('pricelist_id', '=', $value);
						}

					}, array('orm' => $orm))
					->render( );
					
		$this->template->right = $filter;
		/* filter settings ends */
		
		
		
		/* context menu declaration begins */
		$menu = Menu_Context::factory( )->id('discount');
		
		$menu
			->child(__('delete'), Route::url('cms.common', array('controller' => 'Discount', 'mode'=>CMS::VIEW_MODE_FULL, 'action'=>'delete', 'id'=>':id')), 'delete')
				->multiple( )
				->confirm( )
				->ajax( );
			
		$menu->render( );
		/* context menu declaration ends */
		
		
		
		
		
		/* data table begins */
		// set up table header 
		$table = Html::factory('table')->header(array(
			__('#'),
			__('user'),
			__('supplier'),
			__('pricelist'),
			__('discount value'),
		));
		
		// table body
		$res = $orm->find_all( );
		foreach ($res AS $item)
		{
			$table
				->line(array(
					$item->id,
					$item->user->username && $item->user->username != 'guest' ? Html::factory('anchor')->text($item->user->username)->href(Route::url('user_manage', array('id' => $item->user->id))) : '&mdash;',
					$item->supplier->name ? Html::factory('anchor')->text($item->supplier->name)->href(Route::url('cms_suppliers', array('action' => 'edit', 'id' => $item->supplier->id))) : '&mdash;',
					$item->pricelist->logo ? Html::factory('anchor')->text($item->pricelist->logo)->href(Route::url('cms.common', array('controller' => 'Pricelist', 'action' => 'edit', 'id' => $item->pricelist->id))) : '&mdash;',
					Editor::factory('Editor_Element_Digit', 'discount', 'value')->wrap_if(acl('shop_discount_manage'))->id($item->id)->wrap($item->value).'%',
				))
				->classes($menu->context(
					array(
						'id' => $item->id,
					)				
				));
		}
		
		
		$this->template->body = $orm->pagination( ).$table->render( ).$orm->pagination( );
		
		if ($this->request->param('mode') == 'simple')
		{
			$this->template->body = Html::factory('anchor')->text(__('add discount'))->classes(array('cms-add'))->href(Route::url('cms.common', array('controller' => 'Discount', 'action' => 'add'), $this->request->query( ))).'<br>'.$this->template->body;
		}
		
		/* data table ends */
		
		$this->template->header = __u('discounts');
	}	

	/** Action: edit
	 *  Edit template
	 *
	 * @return void
	 */
	public function action_add( )
	{
		$user_id = (int) $this->request->query('user_id');
		$id = (int) $this->request->param('id');
		
		/* start of fetching values for inputs */
		$pricelists_select = array();
		$pricelists_orm = ORM::factory('pricelist');
		foreach ($pricelists_orm->find_all( ) AS $pricelist)
		{
			$pricelists_select[$pricelist->id] = 
				$pricelist->supplier->name
				.', '.$pricelist->logo
				.($pricelist->dtime > 0
				? __(', :dtime :(день|дня|дней)', array(':dtime' => $pricelist->dtime))
				: __('in stock'))
				.($pricelist->comment != '' ? ', '.$pricelist->comment : '');
		}
			
		
		
		$suppliers_select = array( );
		$suppliers_orm = ORM::factory('supplier');
		foreach ($suppliers_orm->find_all( ) AS $suppliers_item)
		{
			$suppliers_select[$suppliers_item->id] = $suppliers_item->name;
		}
		

		$users_select = array( );
		$users_orm = ORM::factory('account')
						->with('attributes')
						->where('is_system', '=', 0);
		foreach ($users_orm->find_all( ) AS $users_item)
		{
			$users_select[$users_item->id] = $users_item->username;
		}
		/* end of fetching values for inputs */
		
		
		$form = Form::factory( )
				->show_on_success(TRUE)
				->clear_on_success(FALSE);
		
		// defaults
		$total_discount = TRUE;
		$discount_type = 0;
		
		if ($id != 0)
		{
			$orm = ORM::factory('discount', $id);
			
			if ( ! $orm->loaded( ))
			{
				throw new Shop_Exception('Cannot load discount #:id', array(':id' => $id));
			}
			
			$form->defaults($orm->as_array( ));
			
			$total_discount = isset($orm->user_id) && isset($orm->supplier_id) && isset($orm->pricelist_id);
			
			if ($orm->supplier_id != 0)
			{
				$discount_type = 1;
				
			}
			elseif ($orm->pricelist_id != 0)
			{
				$discount_type = 2;
			}
		}
		else
		{
			$orm = ORM::factory('discount');
		}
		
		
		$columns = $orm->table_columns( );
				
		$form
			->message(__u('data has been saved successfuly'))
			->field('checkbox', __('discount for guests'), 'guest_discount')->selected($orm->user_id == 0 && empty($user_id))->rel('guest_discount')->checked( )
			->field('chosen', __u('user'), 'user_id')->value((int) $user_id)->empty_option( )->options($users_select)->not_empty( )->beh('!guest_discount')->action('show')
			->field('radio')->value($discount_type)->options(array(__('on all goods'), __('on goods of supplier').'...', __('on goods from pricelist').'...'))->rel('discount_supplier')->equals(1)->rel('discount_pricelist')->equals(2)
			->field('chosen', __u('supplier'), 'supplier_id')->not_empty( )->empty_option( )->options($suppliers_select)->beh('discount_supplier')->action('show')
			->field('chosen', __u('pricelist'), 'pricelist_id')->not_empty( )->empty_option( )->options($pricelists_select)->beh('discount_pricelist')->action('show')
				/* :TODO: date range for discount */
// 			->field('date', __u('start date'), 'start')
// 			->field('date', __u('end date'), 'end')
			->field('text', __u('discount value'), 'discount')
				->value((string) $orm->value)
				->unit('%')
				->not_empty( )
				->rule('numeric')
				->max_length(2)
			->field('submit', __('save'));
				
		$form->render( );
		
		if ($form->sent( ))
		{
			$data = $form->result( )->as_array( );
		
			if (empty($data['user_id']))
			{
				$data['user_id'] = 0;
			}
			if (empty($data['supplier_id']))
			{
				$data['supplier_id'] = 0;
			}
			if (empty($data['pricelist_id']))
			{
				$data['pricelist_id'] = 0;
			}
				
			$data['value'] = $data['discount'];
			$orm->values($data)->save( );
		}
	
		$this->template->parent = __u('discounts');
		$this->template->parent_href = Route::url('cms.common', array('controller' => 'Discount'), $this->request->query( ));
		$this->template->header = __u('new discount');
// 		$this->template->header = __('#').$orm->id;
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
			$orm = ORM::factory('discount', $id);
			
			if ($orm->loaded( ))
			{
				$orm->delete( );
			}
		}
		
		$body = Request::factory(str_replace(URL::site(NULL, 'http'), '', $this->request->referrer( )))->execute( )->body( );
		
		$this->response->body(Basic::json_safe_encode(array(
			'body' => $body->body,
		)));
	}	
}