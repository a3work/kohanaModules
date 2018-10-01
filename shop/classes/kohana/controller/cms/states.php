<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		States controller
 * @package 	Shop
 * @author 		A.St.
 * @date 		10.06.2015
 *
 **/

class Kohana_Controller_Cms_States extends Controller_Cms
{
	/** reloaded Kohana Controller::before 
	 * 
	 * @return void
	 */
	public function before( )
	{
		parent::before( );
		
		if ( ! acl('shop_states_viewing'))
		{
			throw new Access_Exception( );
		}
		
		$this->_left_menu = Cms_Submenu::factory( );
		$this->_left_menu
			->text(__u('states'))
			->child(__u('list'), Route::url('cms.common', array('controller' => 'States')))->css('cms-list')
			->child(__u('add'), Route::url('cms.common', array('controller' => 'States', 'action' => 'add')))->css('cms-add');
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
		$orm = ORM::factory('state')
			// switch on pagination
			->page( )
			->order_by('position');
			
			
			
				
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
		$menu = Menu_Context::factory( )->id('states');
		
		$menu
			->child(__('edit'), Route::url('cms.common', array('controller' => 'States', 'mode'=>CMS::VIEW_MODE_FULL, 'action'=>'edit', 'id'=>':id')), 'edit')
				->dbl( )		
			->child(__('delete'), Route::url('cms.common', array('controller' => 'States', 'mode'=>CMS::VIEW_MODE_FULL, 'action'=>'delete', 'id'=>':id')), 'delete')
				->multiple( )
				->confirm( )
				->ajax( );
			
		$menu->render( );
		/* context menu declaration ends */
		
		
		
		
		
		/* data table begins */
		// set up table header 
		$table = Html::factory('table')->header(array(
			__('#'),
			__('name'),
			__('label'),
			__('description'),
			__('position'),
		));
		
		// table body
		foreach ($orm->find_all( ) AS $item)
		{
			$table
				->line(array(
					$item->id,
					array(
						$item->name,
						(boolean) $item->is_initial ? __('start state') : '',
					),
					$item->label,
					$item->descr,
					Editor::factory('Editor_Element_Text', 'state', 'position')->id($item->id)->wrap($item->position),
				))
				->classes($menu->context(
					array(
						'id' => $item->id,
					)				
				));
		}
		
		$this->template->body = $orm->pagination( ).$table->render( ).$orm->pagination( );
		/* data table ends */
		
		$this->template->header = __u('states');
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
			$orm = ORM::factory('state', $id);
			
			if ( ! $orm->loaded( ))
			{
				throw new Shop_Exception('Cannot load state #:id', array(':id' => $id));
			}
			
			$defaults = $orm->as_array( );
			$form->defaults($defaults);
		}
		else
		{
			$orm = ORM::factory('state');
		}
		
		
		$columns = $orm->table_columns( );
				
		$form
			->message(__u('data has been saved successfuly'))
			->field('checkbox', __('start state'), 'is_initial')
			->field('text', __('name'), 'name')->not_empty( )
			->field('text', __('label'), 'label')
			->field('textarea', __('descr'), 'descr')
			->field('text', __('sort key'), 'position')->value(1000)->max_length(9)
			->field('submit', __('save'));
				
		$form->render( );
		
		if ($form->sent( ))
		{
			$data = $form->result( )->as_array( );
			$data['is_initial'] = (int) isset($data['is_initial']);
			$orm->values($data)->save( );
		}
	
		$this->template->parent = __u('states');
		$this->template->parent_href = Route::url('cms.common', array('controller' => 'States'));
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
			$orm = ORM::factory('state', $id);
			
			if ($orm->loaded( ))
			{
				$orm->delete( );
			}
		}
		
		$body = Request::factory(Route::url('cms.common', array('controller' => 'States')))->execute( )->body( );
		
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
	
		$this->template->header = __u('new state');
	}	
}