<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Suppliers management
 * @package 	Shop
 * @author 		A. St.
 * @date 		05.01.14
 *
 **/

class Kohana_Controller_Cms_Suppliers extends Controller_Cms
{
	
	/** reloaded Kohana Controller::before 
	 * 
	 * @return void
	 */
	public function before( )
	{
		parent::before( );
		
		if ( ! acl('shop_suppliers_viewing'))
		{
			throw new Access_Exception('Permission denied');
		}
		
		$menu = Cms_Submenu::factory( )
			->text(__u('suppliers'))
			->child(__u('list'), Route::url('cms.common', array('controller' => 'Suppliers')))->css('cms-list')
			->child(__u('add'),Route::url('cms.common', array('controller' => 'Suppliers', 'action' => 'add')))->css('cms-add')
			->child(__u('files templates'), Route::url('cms.common', array('controller' => 'Templates')))->css('cms-newspaper')
			->render( );
			
		$this->template->left = $menu;
	}

	/** Action: index
	 *  list of suppliers
	 *
	 * @return void
	 */
	public function action_index( )
	{
		$orm = ORM::factory('supplier');

		if (acl('shop_suppliers_manage'))
		{
			$menu = Menu_Context::factory()->id('suppliers');
			$menu 
				->child(__u('edit', array(':name' => '')), Route::url('cms.common', array('controller' => 'Suppliers', 'action'=>'edit', 'id'=>':id')))
                    ->dbl( )
				->child(__u('delete', array(':name' => '')), Route::url('cms.common', array('controller' => 'Suppliers', 'action'=>'delete', 'id'=>':id')))
					->confirm( )
				->render( );
		}
				
		$table = Html::factory('table')->header(array(
			__u('#'),
			__u('name'),
			__u('change date'),
		));
		
		foreach ($orm->find_all( ) AS $item)
		{
			$line = $table
				->line(array(
					$item->id,
					$item->name,
					Date::format($item->ctime, DATE::FORMAT_DAY),
				));
				
			if (acl('shop_suppliers_manage'))
			{
				$line
					->classes($menu->context(
						array(
							'id' => $item->id,
						)
					));
			}

		}

		$this->template->header = __u('suppliers');
		$this->template->body = $orm->pagination( ).$table->render( ).$orm->pagination( );
	}	

	/** Action: edit
	 *  Edit template
	 *
	 * @return void
	 */
	public function action_edit( )
	{
		if ( ! acl('shop_suppliers_manage'))
		{
			throw new Access_Exception('Permission denied');
		}
		
		$id = (int) $this->request->param('id');
		
		$form = Form::factory( );
		$markup = 0;
		
		if ($id != 0)
		{
			$orm = ORM::factory('supplier', $id);
			
			if ( ! $orm->loaded( ))
			{
				throw new HTTP_Exception_404('Cannot load supplier :id', array(':id' => $id));
			}
			
			$defaults = $orm->as_array( );
			$defaults['file_template'] = array( );
			
			foreach ($orm->templates->find_all( ) AS $template)
			{
				$defaults['file_template']/*[]*/ = $template->id;
			}
			
			$defaults['load_prices'] = $defaults['send_orders'] = $defaults['trace_state'] = FALSE;
			
			if (count($defaults['file_template']))
			{
				$defaults['load_prices'] = TRUE;
			}
			
			if ($defaults['class_order'] != '')
			{
				$defaults['send_orders'] = TRUE;
			}
			
			if ($defaults['class_trace'] != '')
			{
				$defaults['trace_state'] = TRUE;
			}

			if ($defaults['class_acceptance'] != '')
			{
				$defaults['use_acceptance'] = TRUE;
			}
			
			
			$form
				->message(__u('data has been saved successfuly').'.')
				->clear_on_success(FALSE)
				->defaults($defaults);
		}
		else
		{
			$form
				->message(__u('supplier added').'.')
				->show_on_success(FALSE);
				
			$orm = ORM::factory('supplier');
		}

		
		$templates = ORM::factory('template')
						->select(array('id', 'key'))
						->select(array('name', 'header'))
						->find_all( );
// 		
		$form
			/* :TODO: AJAX CHOSEN

				https://github.com/ksykulev/chosen-ajax-addition
			*/
// 			->field('')
// 			->field('select', __u('supplier'), 'supplier')->options($accounts)
			->field('text', __u('name'), 'name')->not_empty( )->max_length(22)
			->field('text', __u('value of discount'), 'discount')
				->message(__('применяется для вычисления цен закупки'))
				->unit('%')
				->value(0)
				->not_empty()
				->max_length(3)
			->field('text', __u('margin'), 'markup')->value((int) $orm->markup->value)
				->rule('numeric')
				->unit('%')
				->not_empty( )
				->max_length(3)
			->field('checkbox', __u('load prices'), 'load_prices')->rel('load_prices')->checked( )
			->field('chosen',
						__u('file template'),
						'file_template')
					->empty_option( )
					->options($templates)
					/* :TODO:  multiple file templates */
					->multiple(FALSE)
					->placeholder(__('set file template'))
					->not_empty(__u('set file template please'))
					->beh('load_prices')
					->action('show')
			->field('submit', __u('save'))
			->render( );
		
		if ($form->sent( ))
		{
			$values = $form->result( )->as_array( );
			
			if ( ! isset($values['send_orders']))
			{
				$values['class_order'] = '';
			}

			if ( ! isset($values['trace_state']))
			{
				$values['class_trace'] = '';
			}
			
			$orm->values($values)->save( );
			
			// add templates
			$orm->remove('templates');
			if (isset($values['load_prices']))
			{
				$orm->add('templates', $form->result( )->file_template( ));
			}

			if ($orm->markup->value != $values['markup'])
			{
				$orm->markup->supplier_id = $orm->id;
				$orm->markup->value = $values['markup'];
				$orm->markup->save( );
			}
			
			if ($id == 0)
			{
				Kohana::$log->add(
					Log::INFO,
					"supplier :name has been created (#:id)",
					array(
						':id'	=> $orm->id,
						':name'	=> $form->result( )->name( ),
					)
				);
			}
			else
			{
				Kohana::$log->add(
					Log::INFO,
					"data of supplier #:id :name has been changed",
					array(
						":id" 	=> $id,
						":name"	=> $orm->name,
					)
				);
			}
		}
		
		$this->template->parent = __u('suppliers');
		$this->template->parent_href = Route::url('cms.common', array('controller' => 'Suppliers'));
		$this->template->header = $orm->name;
		$this->template->body = $form;
	}	

	/** Action: delete
	 *  Delete template
	 *
	 * @return void
	 */
	public function action_delete( )
	{
		if ( ! acl('shop_suppliers_manage'))
		{
			throw new Access_Exception('Permission denied');
		}
		
		$id = (int) $this->request->param('id');
		
		$form = Form::factory( );
		
		if ($id != 0)
		{
			$orm = ORM::factory('supplier', $id);
			
			if ( ! $orm->loaded( ))
			{
				throw new HTTP_Exception_404('Cannot load supplier :id', array(':id' => $id));
			}
		}
		else
		{
			throw new HTTP_Exception_403('Empty ID');
		}
		
		$this->template->header = __u('delete supplier :name', array(':name' => $orm->name));

		Kohana::$log->add(
			Log::INFO,
			"supplier #:id :name has been removed",
			array(
				":id" => $id,
				":name" => $orm->name,
			)
		);

		$orm->delete( );
		
		$this->template->parent = __u('suppliers');
		$this->template->parent_href = Route::url('cms.common', array('controller' => 'Suppliers'));
		$this->template->body = __u('supplier has been deleted').'.<br>'.Html::factory('anchor')->href(Route::url('cms.common', array('controller' => 'Suppliers')))->text(__('return to list')).'.';
	}	
	
	
	/** Action: add
	 *  add new template
	 *
	 * @return void
	 */
	public function action_add( )
	{
		
		$this->template->parent = __u('suppliers');
		$this->template->parent_href = Route::url('cms.common', array('controller' => 'Suppliers'));
		$this->template->header = __u('new supplier');
		$this->template->body = Request::factory(Route::url('cms.common', array('controller' => 'Suppliers', 'action' => 'edit')))->execute( )->body( )->body;
	}	

}