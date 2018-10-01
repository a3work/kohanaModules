<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Controller of goods categories
 * @package 	Shop
 * @author 		A. St.
 * @date 		
 *
 **/

class Kohana_Controller_Cms_Goods_Categories extends Controller_Cms
{
	/** reloaded Kohana Controller::before 
	 * 
	 * @return void
	 */
	public function before( )
	{
		parent::before( );
		
		if ( ! acl('shop_goods_viewing'))
		{
			throw new Access_Exception( );
		}
		
		$this->_left_menu = Cms_Submenu::factory( );
		$this->_left_menu
			->text(__u('categories of goods'))
			->child(__u('list'), Route::url('cms.common', array('controller' => 'Goods_Categories')))->css('cms-list')
			->child(__u('add'), Route::url('cms.common', array('controller' => 'Goods_Categories', 'action' => 'add')))->css('cms-add');
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
	 * @param	Menu_Context
	 * @return 	void
	 */
	public function action_index(Menu_Context $menu = NULL)
	{
		// init orm
		$orm = ORM::factory('goods_category')
			// switch on pagination
			->page( )
			->order_by('path')
			->order_by('name');
			
				
		/* filter settings begins */
		$filter = Form::factory('filter')
					->always_show(TRUE)
					->filter_name('Filter')
					->field('text', __('query'), 'query')->min_length(3)
					->field('submit', 'Найти')
					->callback('query', function($value, $orm) {
						
						$orm
							->where('name', 'LIKE', DB::expr('"%'.$value.'%"'));

					}, array('orm' => $orm))
					->render( );
					
		$this->template->right = $filter;
		/* filter settings ends */
		
		
		
		/* context menu declaration begins */
		if ( ! isset($menu))
		{
			$menu = Menu_Context::factory( )->id('Goods_Categories');
			
			$menu
				->child(__('edit'), Route::url('cms.common', array('controller' => 'Goods_Categories', 'mode'=>CMS::VIEW_MODE_FULL, 'action'=>'edit', 'id'=>':id')), 'edit')
					->dbl( )		
				->child(__('add subcategory'), Route::url('cms.common', array('controller' => 'Goods_Categories', 'mode'=>CMS::VIEW_MODE_FULL, 'action'=>'add'), array('parent_id' => ':id')), 'subcat')
				->child(__('delete'), Route::url('cms.common', array('controller' => 'Goods_Categories', 'mode'=>CMS::VIEW_MODE_FULL, 'action'=>'delete', 'id'=>':id')), 'delete')
					->multiple( )
					->confirm( )
					->ajax( );
		}
		$menu->render( );
		/* context menu declaration ends */
		
		
		
		
		
		/* data table begins */
		// set up table header 
		$table = Html::factory('table')->header(array(
			__('#'),
			__('name'),
			__('symbolic code'),
			__('title'),
			__('description'),
			__('keywords'),
			__('creation time'),
		));
		
		// table body
		foreach ($orm->find_all( ) AS $item)
		{
			$table
				->line(array(
					$item->id,
					'<span style=margin-left:'.(($item->level-1)*25).'px>'.$item->name.'</span>',
					$item->symcode,
					$item->title,
					$item->description,
					$item->keywords,
					Date::format($item->ctime),
				))
				->classes($menu->context(
					array(
						'id' => $item->id,
					)				
				));
			
			
		}
		
		$this->template->body = $orm->pagination( ).$table->render('cms-table').$orm->pagination( );
		/* data table ends */
		
		$this->template->header = __u('categories of goods');
	}	

	/** 
	 * select category and call specified method
	 *
	 * @return 	void
	 */
	public function action_select( )
	{
		$id = $this->request->param('id');
		$handler = $this->request->query('h');
		
		$menu = Menu_Context::factory( )->id('Goods_Categories');
		
		$menu
			->child(__('select'), $handler.'?id='.$id, 'select')
				->dbl( )
				->ajax( );		
		
		$this->action_index($menu);
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
			$orm = ORM::factory('goods_category', $id);
			
			if ( ! $orm->loaded( ))
			{
				throw new Shop_Exception('Cannot load category #:id', array(':id' => $id));
			}
			
			$defaults = $orm->as_array( );
			$form->defaults($defaults);
		}
		else
		{
			$orm = ORM::factory('goods_category');
		}
		
		$parent_id = $this->request->query('parent_id') !== NULL
						? (int) $this->request->query('parent_id')
						: (int) $orm->parent_id;
		
		$columns = $orm->table_columns( );
				
		
		$parents_orm = ORM::factory('goods_category')
						->order_by('path')
						->order_by('name');
		
		if ($orm->id != 0)
		{
			$parents_orm
				->where('id', '!=', $orm->id)
				->where('path', 'NOT LIKE', DB::expr("'{$orm->path}%'"));
		}
						
		$parents_res = $parents_orm->find_all();
		
		$parents = array();
		foreach ($parents_res AS $parent)
		{
			$parents[$parent->id] = str_repeat('.....', $parent->level).$parent->name;
		}
				
		$form
			->message(__u('data has been saved successfuly'))
			->field('chosen', __('parent category'), 'parent_id')
				->value($parent_id)
				->empty_option()
				->options($parents)
			->field('text', __('name'), 'name')->not_empty( )
			->field('editor_basic', __('description'), 'text')
			->field('text', __('title'), 'title')->max_length(255)
			->field('textarea', __('description'), 'description')->max_length(255)
			->field('textarea', __('keywords'), 'keywords')->max_length(255)
			->field('submit', __('save'));
				
		$form->render( );
		
		if ($form->sent( ))
		{
			$data = $form->result( )->as_array( );
			$orm->values($data)->save( );
		}
	
		$this->template->parent = __u('categories of goods');
		$this->template->parent_href = Route::url('cms.common', array('controller' => 'Goods_Categories'));
		$this->template->header = __u(':name', array(':name' => $orm->name));
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
			$orm = ORM::factory('goods_category', $id);
			
			if ($orm->loaded( ))
			{
				$orm->delete( );
			}
		}
		
		$body = Request::factory(Route::url('cms.common', array('controller' => 'Goods_Categories')))->execute( )->body( );
		
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
	
		$this->template->header = __u('new category');
	}	
}