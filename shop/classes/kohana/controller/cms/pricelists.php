<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Controller of pricelists
 * @package 	Shop
 * @author 		A. St.
 * @date 		
 *
 **/

class Kohana_Controller_Cms_Pricelists extends Controller_Cms
{
	/** reloaded Kohana Controller::before 
	 * 
	 * @return void
	 */
	public function before( )
	{
		parent::before( );
		
		if ( ! acl('goods_manage'))
		{
			throw new Access_Exception( );
		}
		
		$this->_left_menu = Cms_Submenu::factory( );
		$this->_left_menu
			->text(__u('pricelists'))
			->child(__u('list'), Route::url('cms.common', array('controller' => 'Pricelists')))->css('cms-list')
			->child(__u('add'), Route::url('cms.common', array('controller' => 'Pricelists', 'action' => 'add')))->css('cms-add');
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
	
	/**
	 * generates and returns context menu object
	 * @return Menu_Context
	 */
	protected function _get_menu()
	{
		$menu = Menu_Context::factory( )->id('Pricelists');
		
		$menu
			->child(__('edit'), Route::url('cms.common', array('controller' => 'Pricelists', 'mode'=>CMS::VIEW_MODE_FULL, 'action'=>'edit', 'id'=>':id')), 'edit')
				->dbl( )		
			->child(__('import'), Route::url('cms.common', array('controller' => 'Goods', 'mode'=>CMS::VIEW_MODE_FULL, 'action'=>'import'), array('pricelist_id' => ':id')), 'import')
			->child(__('clear'), Route::url('cms.common', array('controller' => 'Pricelists', 'mode'=>CMS::VIEW_MODE_FULL, 'action'=>'clear', 'id'=>':id')), 'clear')
				->multiple( )
				->confirm( )
				->ajax( )
			->child(__('delete'), Route::url('cms.common', array('controller' => 'Pricelists', 'mode'=>CMS::VIEW_MODE_FULL, 'action'=>'delete', 'id'=>':id')), 'delete')
				->multiple( )
				->confirm( )
				->ajax( );
		
		return $menu;
	}

	/** Action: index
	 *  list
	 *
	 * @return void
	 */
	public function action_index( )
	{
		// init orm
		$orm = ORM::factory('pricelist')
			// switch on pagination
			->page( );
			
			
			
				
		/* filter settings begins */
		$filter = Form::factory('filter')
					->always_show(TRUE)
					->filter_name('Filter')
					->field('text', __('query'), 'query')->min_length(3)
					->field('submit', 'Найти')
					->callback('query', function($value, $orm) {
						
						$orm
							->and_where_open()
							->where('name', 'LIKE', DB::expr('"%'.$value.'%"'))
							->or_where('logo', 'LIKE', DB::expr('"%'.$value.'%"'))
							->or_where('comment', 'LIKE', DB::expr('"%'.$value.'%"'))
							->and_where_close();

					}, array('orm' => $orm))
					->render( );
					
		$this->template->right = $filter;
		/* filter settings ends */
		
		
		
		/* context menu declaration begins */
		$menu = $this->_get_menu();
			
		$menu->render( );
		/* context menu declaration ends */
		
		
		
		
		
		/* data table begins */
		// set up table header 
		$table = Html::factory('table')->header(array(
			__('#'),
			__('supplier'),
			__('LOGO'),
			__('name'),
			__('delivery time'),
			__('markup'),
			__('comment'),
			__('creation date'),
			__('count of goods'),
		));
		
		$suppliers = array();
		
		// table body
		foreach ($orm->find_all( ) AS $item)
		{
			if (isset($item->markup->value))
			{
				$markup = array($item->markup->value.'%', __('for pricelist'));
			}
			else
			{
				if (isset($item->supplier->markup->value))
				{
					$markup = array($item->supplier->markup->value.'%', __('for supplier'));
				}
				else
				{
					$markup = '<span class="cms-msg-fail">'.__('no markup').'</span>';
				}
			}
		
			$table
				->line(array(
					array($item->id, (bool)$item->is_internal ? __('internal') : ''),
					$item->supplier->name,
					$item->logo,
					$item->name,
					$item->dtime ? $item->dtime.__('days') : '&mdash;',
					$markup,
					$item->comment,
					Date::format($item->ctime),
					$item->count,
				))
				->classes($menu->context(
					array(
						'id' => $item->id,
					)				
				));
		}
		
		$this->template->body = $orm->pagination( ).$table->render( ).$orm->pagination( );
		/* data table ends */
		
		$this->template->header = __u('pricelists');
	}	

	/** Action: edit
	 *  Edit template
	 *
	 * @return void
	 */
	public function action_edit( )
	{
		$id = (int) $this->request->param('id');

		$suppliers_orm = ORM::factory('supplier');
		
		foreach ($suppliers_orm->find_all( ) AS $suppliers_item)
		{
			$suppliers_select[$suppliers_item->id] =
				$suppliers_item->name.', '
				.($suppliers_item->markup->value
				? 
					__('markup').' '.$suppliers_item->markup->value
					.'%'
				: __('no markup')
				);
		}
	
		$form = Form::factory( )
				->show_on_success(TRUE)
				->clear_on_success(FALSE);
			
		if ($id != 0)
		{
			$orm = ORM::factory('pricelist', $id);
			
			if ( ! $orm->loaded( ))
			{
				throw new Esp_Exception('Cannot load pricelist #:id', array(':id' => $id));
			}
			
			$form->defaults($orm->as_array( ));
		}
		else
		{
			$orm = ORM::factory('pricelist')
					->with('markup');
		}
		
		if (count($suppliers_select) > 0)
		{
			$columns = $orm->table_columns( );
					
			$form
				->message(__u('data has been saved successfuly'))
				->field('chosen', __u('supplier'), 'supplier_id')
						->options($suppliers_select)
				->field('text', __u('name'), 'name')
				->field('text', __u('LOGO'), 'logo')
					->not_empty( )
					->max_length(4)
				->field('checkbox', __u('internal pricelist'), 'is_internal')
				->field('checkbox', __u('in stock'), 'in_stock')->selected($orm->dtime == 0)
					->rel('in_stock')->checked( )
				->field('text', __u('delivery time'), 'dtime')
					->not_empty( )
					->max_length(2)
					->rule('numeric')
					->unit(__('days'))
					->beh('!in_stock')->action('show')
				->field('checkbox', __u('use markup for supplier'), 'use_supplier_markup')->selected(empty($orm->markup->value))
					->rel('supplier_markup')->checked( )
				->field('text', __u('markup for pricelist'), 'markup')->value((string) $orm->markup->value)
					->unit('%')
					->not_empty( )
					->rule('numeric')
					->max_length(3)
					->beh('!supplier_markup')->action('show')
				->field('textarea', __u('comment'), 'comment')
				->field('submit', __('save'));
					
			$form->render( );
			
			if ($form->sent( ))
			{
			
				$data = $form->result( )->as_array( );
				$data['is_internal'] = (int) isset($data['is_internal']);
				$orm->values($data)->save( );
			}
		
			$this->template->body = $form;
		}
		else
		{
			$this->template->body = __u('create a <a href=":href">supplier</a> first', array(':href' => Route::url('cms_suppliers', array('action' => 'add')))).'.';
		}
		
		$this->template->parent = __u('pricelists');
		$this->template->parent_href = Route::url('cms.common', array('controller' => 'Pricelists'));
		$this->template->header = __u(':name', array(':name' => $orm->name ? $orm->name : __('#').$orm->id));
	}	

	/** Action: clear pricelist
	 *
	 * @return 	void
	 */
	public function action_clear( )
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
			$orm = ORM::factory('pricelist', $id)->clear_goods( );
		}
		
		
		if ($this->request->referrer( ) !== NULL)
		{
			$body = Request::factory(str_replace(URL::site(NULL, 'http'), '', $this->request->referrer( )))->execute( )->body( );
			
			$this->response->body(Basic::json_safe_encode(array(
				'body' => $body->body,
			)));
		}
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
			$orm = ORM::factory('pricelist', $id);
			
			if ($orm->loaded( ))
			{
				$orm->clear_goods( );
				$orm->delete( );
			}
		}
		
		$body = Request::factory(Route::url('cms.common', array('controller' => 'Pricelists')))->execute( )->body( );
		
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
	
		$this->template->header = __u('new pricelist');
	}	
}