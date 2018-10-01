<?php defined('SYSPATH') or die('No direct script access.');

class Kohana_Controller_Cms_Cron extends Controller_Cms {

	public function before()
	{
		parent::before( );

		if ( ! Cli::check( ))
		{
			if ( ! acl('cron_task_manage'))
			{
				throw new HTTP_Exception_403;
			}
		}
		else
		{
			// switch off auto render for cli scripts
			$this->auto_render = FALSE;
		}
		
		InclStream::instance( )->add('cron.init.js');
		
		$this->template->left =
			Cms_Submenu::factory( )
				->text(__u('task scheduler'))
				->child(__('tasks list'), Route::url('cron_manage'))->css('cms-list')
				->child(__('add task'), Route::url('cron_manage', array('action' => 'add')))->css('cms-add')
				->render( );
				
		$this->template->right = Controller_Cms::info(array(
										__u('current website time') => date('Y-m-d H:i:s'),
									));
	}

	/** Orders list
	 *
	 * @return void
	 */
	public function action_index( )
	{
		$this->template->header = __u('tasks list');
		
		$orm = ORM::factory('Cron_Tab');
		
		$out = '';
		
		$menu = Menu_Context::factory( )->id(Basic::get_hash(__CLASS__));

		$menu 
			->child(__('edit', array(':name' => '')), Route::url('cron_manage', array('action'=>'edit', 'id'=>':id')))
			->child(__('enable/disable', array(':name' => '')), Route::url('cron_manage', array('action'=>'activate', 'id'=>':id')))
				->ajax( )
				->confirm( )
			->child(__('execute', array(':name' => '')), Route::url('cron_manage', array('action'=>'exec', 'id'=>':id')), 'exec_task', 'Cms.action')
				->confirm( )
			->child(__('delete', array(':name' => '')), Route::url('cron_manage', array('action'=>'delete', 'id'=>':id')), 'delete_task', 'Cms.action')
				->multiple( )
				->confirm( );
				
// 		var_dump($menu->id( ));
		$menu->render( );
		
		$rules 				= Site::config('cron')->rules;
// 		var_dump($menu->id( ));
		
		$table = Html::factory('table')->header(array(
			__('#'),
			array(__u('label'), __u('description')),
			__u('timetable'),
			array(__u('next execution time'), __u('executions count')),
			array(__u('task'), __u('parameters')),
		));
		
		foreach ($orm->find_all( ) AS $item)
		{
			$table
				->line(array(
					$item->id,
					array($item->label, $item->descr),
					isset($rules[$item->rule]) ? $rules[$item->rule] : $item->rule,
					(boolean) $item->is_active
					 ? array(Date::format($item->next_execution, Date::FORMAT_DAYTIME), $item->count)
					 : array('', __('disabled')),
					array($item->action, $item->param),
				))
				->href(Route::url('cron_manage', array('action' => 'edit', 'id' => $item->id)))
				->classes($menu->context(array(
					'id' => $item->id,
				)));
		}
// 		var_dump($_SERVER);
		$this->template->body = __um('cron', 'install', array(':command' => '* * * * * /usr/bin/php '.DOCROOT.'index.php --uri='.Route::url('cron'))).$orm->pagination( ).$table->render( ).$orm->pagination( );
	}

	/** Switch task activity
	 *
	 * @return 	void
	 */
	public function action_activate( )
	{
		if ( ! $this->request->is_ajax( ))
		{
			throw new Access_Exception( );
		}
		
		$this->auto_render = FALSE;
		
		// multiple ids support
		$ids = explode(Site::ID_SEPARATOR, $this->request->param('id'));
		
		$orm = ORM::factory('cron_tab', $ids[0]);
		
		if ($orm->loaded( ))
		{
			$orm->is_active = (boolean) $orm->is_active ? 0 : 1;
			$orm->save( );
			
			Cron::install($orm->label);
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
		
		$orm	= ORM::factory('cron_tab');
		$form	= Form::factory( );
		$rules 				= Site::config('cron')->rules;

		$rules_val			= $rules;
		$rules_val['']		= '';
		$rules_val['time']	=  __('choose time').'...';
		$rules_val['custom']=  __('set rule').'...';
		
		$rule_time = $rule = $rule_custom = '';
		
		if ($id != 0)
		{
			$orm->where('id', '=', $id)->find( );
			
			if ( ! $orm->loaded( ))
			{
				throw new Kohana_Exception('Cannot load task #:id', array(':id' => $id));
			}
		
			$rule_custom = $orm->rule;
		
			if ( ! isset($rules[$orm->rule]))
			{
				if (preg_match("/^\d{1,2} \d{1,2} \* \* \*$/", $orm->rule))
				{
					$rule_time_arr = explode(' ', $orm->rule);
					
					$rule_time_arr[1] = (int) $rule_time_arr[1];
					$rule_time_arr[0] = (int) $rule_time_arr[0];
					
					$rule_time =
						($rule_time_arr[1] > 9 ? $rule_time_arr[1] : '0'.$rule_time_arr[1])
						.':'.
						($rule_time_arr[0] > 9 ? $rule_time_arr[0] : '0'.$rule_time_arr[0]);
						
					$rule = 'time';
				}
				else
				{
					$rule = 'custom';
				}
			}
			else
			{
				$rule = $orm->rule;
			}
			
			$form->defaults(array(
				'is_active'		=> $orm->is_active,
				'label'			=> $orm->label,
				'descr'			=> $orm->descr,
				'rule'			=> $rule,
				'rule_time'		=> $rule_time, 
				'rule_custom'	=> $rule_custom, 
				'action'		=> $orm->action,
				'param'			=> $orm->param,
			));

			$form
				->message(__u('data saved'))
				->clear_on_success(FALSE);
		}
		else
		{
			$form
				->message(Site::redirect(Route::url('cron_manage'), __u('task has been added').'.', 'self'))
				->show_on_success(FALSE);
		}
	
		$form = $form
				->field('checkbox', __u('enable task'), 'is_active')->value(1)
				->field('text', __u('label'), 'label')->not_empty( )->max_length(30)
				->field('text', __u('description'), 'descr')
				->field('select', __u('timetable'), 'rule')->not_empty( )->options($rules_val)->rel('custom_time')->not_empty( )->equals('time')->rel('custom_rule')->equals('custom')->rel('not_empty')->not_empty( )
				->field('time', __u('execution time'), 'rule_time')->not_empty( )->value($rule_time)->beh('not_empty && custom_time')->action('show')
				->field('text', __u('rule'), 'rule_custom')->placeholder('*/2 */4 * * 1-5')->not_empty( )->value($rule_custom)->beh('not_empty && custom_rule')->action('show')
				->field('text', __u('action'), 'action')->placeholder('/my/action/url')->not_empty( )
				->field('text', __u('parameters'), 'param')->placeholder('foo=bar&foo1=bar1')
				->field('submit', __u('save'))
				->render( );
				
		if ($form->sent( ))
		{
			if ($form->rule( ) == 'time')
			{
				list($hours, $minutes) = explode(':', $form->rule_time( ));
				$hours = (int) $hours;
				$minutes = (int) $minutes;
				$rule = "$minutes $hours * * *";
			}
			elseif ($form->rule( ) == 'custom')
			{
				$rule = $form->rule_custom( );
			}
			else
			{
				$rule = $form->rule( );
			}
			
			$orm->values(array_merge(
				$form->as_array( ),
				array(
					'rule' => $rule,
				)
			))->save( );
			
			Cron::install( );
		}
	
		$this->template->parent = __u('task scheduler');
		$this->template->parent_href = Route::url('cron_manage');
		$this->template->header = __u(':name: task properties', array(':name' => $orm->label));
		$this->template->body = $form;
	}	

	/** Action: delete
	 *  Delete template
	 *
	 * @return void
	 */
	public function action_delete( )
	{
		$id = (int) $this->request->param('id');
	
		if ( ! $this->request->is_ajax( ))
		{
			throw new Access_Exception( );
		}
		
		$this->auto_render = FALSE;
		
		$orm = ORM::factory('cron_tab', $id)->delete( );
		
		$body = Request::factory(Route::url('cron_manage', array('mode' => CMS::VIEW_MODE_FULL)))->execute( )->body( );
		
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
		
		$this->template->parent = __u('task scheduler');
		$this->template->parent_href = Route::url('cron_manage');
		$this->template->header = __u('new task');
		$this->template->body = Request::factory(Route::url('cron_manage', array('action' => 'edit', 'id' => 0)))->execute( )->body( )->body;
	}	
	
	/** execute task
	 *
	 * @return void
	 */
	public function action_exec( )
	{
		$id = (int) $this->request->param('id');
	
		if ( ! $this->request->is_ajax( ))
		{
			throw new Access_Exception( );
		}
		
		$this->auto_render = FALSE;
		
		$orm = ORM::factory('cron_tab', $id);
	
		// execute task
		Cron::exec($orm);
			
		$body = Request::factory(Route::url('cron_manage', array('mode' => CMS::VIEW_MODE_FULL)))->execute( )->body( );
		
		$this->response->body(Basic::json_safe_encode(array(
			'body' => $body->body,
		)));

	}	

	
}



