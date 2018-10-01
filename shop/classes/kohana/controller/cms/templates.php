<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Income and outgoing files templates management
 * @package 	Shop
 * @author 		A. St. <a3.work@gmail.com>
 * @date 		05.01.14
 *
 **/

class Kohana_Controller_Cms_Templates extends Controller_Cms
{
	const DEFAULT_SEPARATOR = ';';
	const DEFAULT_DELIMITER = '"';
	
	/** reloaded Kohana Controller::before 
	 * 
	 * @return void
	 */
	public function before( )
	{
		parent::before( );
		
		if ( ! acl('templates_browse'))
		{
			throw new Access_Exception('Permission denied');
		}
		
		$menu = Cms_Submenu::factory( )
			->text(__u('files templates'))
			->child(__u('list'), Route::url('cms.common', array('controller' => 'Templates')))->css('cms-list');
		
		if (acl('templates_manage'))
		{
			$menu
				->child(__u('add'), Route::url('cms.common', array('controller' => 'Templates', 'action' => 'add')))->css('cms-add');
				
		}
		
		$menu
			->render( );
		
		$this->template->parent = __u('suppliers');
		$this->template->parent_href = Route::url('cms.common', array('controller' => 'Suppliers'));
		$this->template->left = $menu;
	}

	/** Action: index
	 *  list of templates
	 *
	 * @return void
	 */
	public function action_index( )
	{
		$orm = ORM::factory('template')
				->page( );
				
		if (acl('templates_manage'))
		{
			$menu = Menu_Context::factory()->id('templates');
			$menu 
				->child(__u('edit', array(':name' => '')), Route::url('cms.common', array('controller' => 'Templates', 'action'=>'edit', 'id'=>':id')))
				->child(__u('delete', array(':name' => '')), Route::url('cms.common', array('controller' => 'Templates', 'action'=>'delete', 'id'=>':id')), 'del_template', 'Cms.action')
					->multiple( )
					->confirm( )
				->render( );
		}
				
		$table = Html::factory('table')->header(array(
			__u('#'),
			__u('name'),
			array(__u('type'), __u('regular expression')),
			__u('creation date'),
		));
		
		foreach ($orm->find_all( ) AS $item)
		{
			$line = $table
				->line(array(
					$item->id,
					$item->name,
					array($item->regexp, $item->type),
// 					$item->producer.' '.$item->code.' '.$item->descr.' '.$item->price.' '.$item->quan.' '.$item->dtime.' ',
					Date::format($item->ctime, DATE::FORMAT_DAY),
				));
				
			if (acl('templates_manage'))
			{
				$line
					->href(Route::url('cms.common', array('controller' => 'Templates', 'action' => 'edit', 'id' => $item->id)))
					->classes($menu->context(
						array(
							'id' => $item->id,
						)
					));
			}

				
		}
		

		
		$this->template->header = __u('files templates');
		$this->template->body = $orm->pagination( ).$table->render( ).$orm->pagination( );
	}	

	/** Action: edit
	 *  Edit template
	 *
	 * @return void
	 */
	public function action_edit( )
	{
		if ( ! acl('templates_manage'))
		{
			throw new Access_Exception('Permission denied');
		}
		
		$id = (int) $this->request->param('id');
		
		$form = Form::factory( );
		
		if ($id != 0)
		{
			$orm = ORM::factory('template', $id);
			
			if ( ! $orm->loaded( ))
			{
				throw new HTTP_Exception_404('Cannot load template :id', array(':id' => $id));
			}

			$defaults = $orm->as_array( );
			
			if ($defaults['descr'] != '' || $defaults['quan'] != '' || $defaults['dtime'] != '' || $defaults['currency'] != '' || $defaults['logo'] != '')
			{
				$defaults['extra'] = TRUE;
			}
			
			$form
				->message(__u('data has been saved successfuly').'.')
				->clear_on_success(FALSE)
				->defaults($defaults);
		}
		else
		{
			$form
				->message(__u('template added').'.')
				->show_on_success(FALSE);
				
			$orm = ORM::factory('template');
		}

		$columns = $orm->table_columns( );
		
		$form
			->field('radio', __u('type'), 'type')
				->options($columns['type']['options'], TRUE)
				->value($columns['type']['options'][0])
				->not_empty( )
				->rel('type_txt')->equals('txt/csv')
			->field('text', __u('name'), 'name')->message(__('используется при выборе шаблона'))->not_empty( )->max_length(50)
			->field('text', __u('producer column'), 'producer')->message(__('здесь и далее -- номер колонки, отсчёт начинается с единицы'))->not_empty( )->max_length(2)->rule('digit')
			->field('text', __u('code column'), 'code')->not_empty( )->max_length(2)->rule('digit')
			->field('text', __u('price column'), 'price')->not_empty( )->max_length(2)->rule('digit')
			->field('text', __u('separator'), 'separator')
				->placeholder(Controller_Cms_Templates::DEFAULT_SEPARATOR)
				->value(Controller_Cms_Templates::DEFAULT_SEPARATOR)
				->not_empty( )
				->max_length(2)
				->beh('type_txt')
				->action('show')
			->field('text', __u('delimiter'), 'delimiter')
				->placeholder(Controller_Cms_Templates::DEFAULT_DELIMITER)
				->max_length(2)
				->beh('type_txt')
				->action('show')
			->field('text', __u('begin row'), 'begin_line')->not_empty( )->max_length(2)
				->beh(' ! type_txt')
				->action('show')
			
			->field('checkbox', __u('set LOGO for pricelist'))->selected($orm->logo_global != '')->hidden(TRUE)
				->rel('global_logo')->checked( )
			->field('text', __u('LOGO'), 'logo_global')
				->not_empty( )
				->max_length(4)
				->beh('global_logo')->action('show')
				
			->field('checkbox', __u('use global currency for all prices in pricelist'))->selected($orm->currency_global != '')->hidden(TRUE)->rel('global_currency')->checked( )
			->field('text', __u('code of global currency'), 'currency_global')
				->message(__u('use global code of currency: RUR, USD, EUR etc.'))
				->value('RUR')
				->placeholder('RUR')
				->not_empty( )
				->max_length(3)
				->beh('global_currency')->action('show')
			->field('checkbox', __u('set extra columns'))->hidden(TRUE)->rel('extra_columns')->checked( )
			->field('text', __u('description column'), 'descr')->max_length(2)->rule('digit')->beh('extra_columns')->action('show')
			->field('text', __u('quantity column'), 'quan')->max_length(2)->rule('digit')->beh('extra_columns')->action('show')
			->field('text', __u('delivery time column'), 'dtime')->max_length(2)->rule('digit')->beh('extra_columns')->action('show')
			->field('text', __u('LOGO column'), 'logo')->max_length(2)->rule('digit')->beh('extra_columns && !global_logo')->action('show')
			->field('text', __u('currency column'), 'currency')->max_length(2)->rule('digit')->beh('extra_columns && !global_currency')->action('show')
			
			->field('submit', __u('save'))
			->render( );
		
		if ($form->sent( ))
		{
			$data = $form->result( )->as_array( );
			
// 			$data['separator'] = Html::entities()
			$orm->values($data)->save( );
		}
	
		$this->template->parent = __u('files templates');
		$this->template->parent_href = Route::url('cms.common', array('controller' => 'Templates'));
		$this->template->header = __u('edit file template :name', array(':name' => $orm->name));
		$this->template->body = __u('Заполните форму описания шаблона файла.<br>Внимание! Нумерация колонок шаблона начинается с единицы.').$form;
	}	

	/** Action: delete
	 *  Delete template
	 *
	 * @return void
	 */
	public function action_delete( )
	{
		if ( ! $this->request->is_ajax( ) ||  ! acl('templates_manage'))
		{
			throw new Access_Exception( );
		}
		
		$this->auto_render = FALSE;

		$ids = explode(Site::ID_SEPARATOR, $this->request->param('id'));
		
		foreach ($ids AS $id)
		{
			if ($id != 0)
			{
				$orm = ORM::factory('template', $id);
				
				if ( ! $orm->loaded( ))
				{
					throw new HTTP_Exception_404('Cannot load template :id', array(':id' => $id));
				}
			}
			else
			{
				throw new HTTP_Exception_403('Empty ID');
			}
			
			$orm->delete( );
		}
		
		$body = Request::factory(Route::url('cms.common', array('controller' => 'Templates', 'mode' => CMS::VIEW_MODE_FULL)))->execute( )->body( );
		
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
		$this->template->parent = __u('files templates');
		$this->template->parent_href = Route::url('cms.common', array('controller' => 'Templates'));
		$this->template->header = __u('new file template');
		$this->template->body = Request::factory(Route::url('cms.common', array('controller' => 'Templates', 'action' => 'edit')))->execute( )->body( )->body;
	}	

}