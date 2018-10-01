<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Accounts and Groups management
 * @package 	User
 * @author 		Stanislav U. Alkimovich
 * @date 		2013-07-31
 *
 **/

class Kohana_Controller_Cms_User extends Controller_Cms
{
	// type of object: account or group
	protected $type;

	public function before( )
	{
		parent::before( );

		$this->type = $this->request->param('list');
	}

	/** head page **/
	public function action_index( )
	{
		if ( ! acl('user_manage'))
		{
			throw new Access_Exception(__u('permission denied'));
		}

		$orm = ORM::factory('account');

		if ( ! User::check(Site::config('user')->root_name))
		{
			$orm->where('is_system', '=', 0);
		}

		$count = array(
			'users' 	=> $orm->where('is_group', '=', 0)->count_all( ),
		);
/*
		if ( ! User::check(Site::config('user')->root_name))
		{
			$orm->where('is_system', '=', 0);
		}
*/
		$count['groups'] 	= $orm->where('is_group', '=', 1)->count_all( );

		$this->template->header = __u('users');
		$this->template->body = View::factory('user.index', array('count' => $count));
	}

	/** Show accounts / groups list
	 */
	public function action_list( )
	{
		if ( ! acl('user_manage'))
		{
			throw new Access_Exception(__u('permission denied'));
		}

		// get data
		$data = ORM::factory('account');

		switch ($this->type)
		{
			case 'accounts':
				$data->where('is_group', '=', '0');
				break;
			case 'groups':
				$data->where('is_group', '=', '1');
				break;
		}

		// allow view and edit system users from root only
		if ( ! User::check(Site::config('user')->root_name))
		{
			$data->where('is_system', '=', 0);
		}

		if ($this->type == 'accounts')
		{
			$filter = Form::factory('filter')
					->always_show(TRUE)
					->filter_name('Filter')
					->field('text', __('query'), 'query')->min_length(3)
					->field('chosen', __('sort by'), 'sort_by')->options(array(__('by register time, descending'), __('by quantity of logins, descending'), __('by last login time, descending')))
					->field('submit', 'Найти')
					->callback('query', function($value, $orm) {
						if ($value != '')
						{
							$orm
								->and_where_open()
								->where('username', 'LIKE', DB::expr('"%'.$value.'%"'))
								->or_where('email', 'LIKE', DB::expr('"%'.$value.'%"'))
								->or_where('last_login_ip', 'LIKE', DB::expr('"%'.$value.'%"'))
								->and_where_close();
						}

					}, array('orm' => $data))
					->callback('sort_by', function($value, $orm) {
						if ($value != '' && count($value))
						{
							switch ($value)
							{
								case 0:
									$orm->order_by('ctime', 'desc');
									break;
								case 1:
									$orm->order_by('logins', 'desc');
									break;
								case 2:
									$orm->order_by('last_login', 'desc');
									break;
								default:
							}
						}

					}, array('orm' => $data));
					
					
			$this->template->right = $filter->render( );
					
			$menu = Menu_Context::factory( )->id('text_list');
		
			$menu
				->child(__('edit'), Route::url('user_manage', array('list'=>$this->type, 'id'=>':id')), 'edit')
					->dbl( )		
				->child(__('attributes'), Route::url('user_attr', array('list'=>$this->type, 'id'=>':id')), 'attr')
				->child(__('permissions'), Route::url('user_access', array('list'=>$this->type, 'id'=>':id')), 'access')
				->child(__('remove'), Route::url('user_delete', array('list'=>$this->type, 'id'=>':id')), 'delete')
					->confirm( );
					/*
					
				->child(__('translate'), Route::url('cms_bechamel', array('mode'=>CMS::VIEW_MODE_FULL, 'action'=>'translate', 'id'=>':id')), 'translate')
					->dbl( )		
				->child(__('publish'), Route::url('cms_bechamel', array('mode'=>CMS::VIEW_MODE_FULL, 'action'=>'publish', 'id'=>':id')), 'publish')
					->multiple( )
					->ajax( )
				->child(__('unpublish'), Route::url('cms_bechamel', array('mode'=>CMS::VIEW_MODE_FULL, 'action'=>'unpublish', 'id'=>':id')), 'unpublish')
					->multiple( )
					->ajax( )
				->child(__('preview'), Route::url('cms_bechamel', array('action' => 'preview', 'id' => ':id')), 'preview')
					->window('popup')
				->child(__('add to archive'), Route::url('cms_bechamel', array('mode'=>CMS::VIEW_MODE_FULL, 'action'=>'add_to_archive', 'id'=>':id')), 'add_to_archive')
					->multiple( )
					->ajax( )
				->child(__('remove from archive'), Route::url('cms_bechamel', array('mode'=>CMS::VIEW_MODE_FULL, 'action'=>'remove_from_archive', 'id'=>':id')), 'remove_from_archive')
					->multiple( )
					->ajax( )
				->child(__('delete'), Route::url('cms_bechamel', array('mode'=>CMS::VIEW_MODE_FULL, 'action'=>'delete', 'id'=>':id')), 'delete')
					->confirm( );
			*/
			$menu->render( );
			
			$table = HTML::factory('table');
			$table->header(array(
				__('username'),
				__('email'),
				__('groups'),
				__('label'),
				__('register time'),
				__('last login time'),
				__('logins quantity'),
				__('last login IP'),
			));
			
			foreach ($data->find_all( ) AS $item)
			{
				$table->line(array(
					$item->username,
					$item->email,
					$item->groups,
					$item->label,
					Date::format($item->ctime, Date::FORMAT_FULL),
					Date::format($item->last_login, Date::FORMAT_FULL),
					$item->logins,
					$item->last_login_ip,
				))
				->classes($menu->context(
					array(
						'id' => $item->id,
					)
				));
			}
			
			$this->template->body = $table->render('cms-table');
		}
		else
		{
			$data = $data->order_by('username')->find_all( );
			
			// flags
			$flag_edit = acl('user_manage');
			$flag_attr = $this->type != 'groups' && acl('user_manage');
			$flag_acc = acl('user_access');
			$flag_del = acl('user_manage');

			// generate interface
			$out = '';
			foreach ($data AS $item)
			{
				$flags = array(
					'edit' => $flag_edit,
					'attr' => $flag_attr,
					'acc' 	=> $item->username != Site::config('user')->root_name && $flag_acc,
					'del' 	=> ! (boolean) $item->is_system && ! (boolean) $item->is_hold && $flag_del,
				);

				$out .= View::factory('user.list.item', array(
					'username' 	=> $item->username,
					'label'		=> $item->label,
					'list'		=> $this->type,
					'id'		=> $item->id,
					'flags'		=> $flags,
				))->render( );
			}

			$this->template->body = View::factory('user.list', array(
				'list'		=> $this->type,
				'add_txt'	=> $this->type == 'accounts' ? __('add account') : __('add group'),
				'body' 		=> $out,
			));
			
		}

		$this->template->header	= $this->type == 'accounts' ? __u('accounts') : __u('user groups');
		$this->template->parent = __u('users');
		$this->template->parent_href = Route::url('user_default');
		
		
	}

	/** Add / edit account or group data **/
	public function action_manage( )
	{
		if ( ! acl('user_manage'))
		{
			throw new Access_Exception(__u('permission denied'));
		}

		// get id
		$id = $this->request->param('id');

		$form = Form::factory('u');

		// load user data
		if ($id != 0)
		{
			$user = ORM::factory('account')
					-> select(array(DB::expr('account.username = account.email'), 'email_as_uname'))
					-> where('id', '=', $id);
			if ( ! User::check(Site::config('user')->root_name))
			{
				$user->where('is_system', '=', 0);
			}

			$user = $user->find( );

			if ( ! $user->loaded( ))
			{
				throw new User_Exception('Cannot load user #:id', array(':id' => $id));
			}

			unset($user->password);

			$form->defaults($user);
		}

		$form
			-> show_on_success(FALSE)
			-> handler(array($this, 'save'), $id)
			-> message($id == 0 ? (($this->type == 'accounts' ? __u('account') : __u('group')).__(' has been added successfuly')).'.'.' <a href="'.Route::url('user_list', array('list'=>$this->type)).'">'.__('return').'</a>' : __u('data saved').'. '.'<a href="'.Route::url('user_manage', array('list'=>$this->type, 'id'=>$id)).'">'.__('back').' '.__('to settings').'</a>');

		if ($this->type == 'accounts')
		{
			if ($id != 0)
			{
				$form
					-> field('text', __u('login'), 'username')->disabled(TRUE)->not_empty( )->regex('/^[^\d]/', 'login can not start at number')->rule('username_not_reserved')->rule('username_unique', array('current' => $user->username))
					-> field('text', __u('e-mail'), 'email')->not_empty( )->rule('email')->rule('email_unique', array('current' => $user->email))
					-> field('checkbox', __u('change password'), 'change_pass')->selected(FALSE)->rel('ch_pass')->checked( )
					-> field('checkbox', __u('generate password'), 'gen_pass')->selected(TRUE)->beh('ch_pass')->action('show')->rel('gen_pass')->checked( )
					-> field('password', __u('password'), 'password')->not_empty( )->beh('ch_pass && !gen_pass')->action('show')
					-> field('password', __u('password again'), 'confirm')->not_empty( )->matches('password')->beh('ch_pass && !gen_pass')->action('show')
					-> field('checkbox', __u('send account data to specified email'), 'send_to_email')->beh('ch_pass');
			}
			else
			{
				$form
					-> field('text', __u('e-mail'), 'email')->not_empty( )->rule('email')->rule('email_exists')
					-> field('checkbox', __u('use e-mail as login'), 'email_as_uname')->selected(TRUE)->rel('email_as_login')->checked( )
					-> field('text', __u('login'), 'username')->not_empty( )->regex('/^[^\d]/', 'login can not start at number')->beh('email_as_login')->action('hide')->rule('username_not_reserved')->rule('username_exists')
					-> field('checkbox', __u('generate password'), 'gen_pass')->selected(TRUE)->rel('gen_pass')->checked( )
					-> field('password', __u('password'), 'password')->not_empty( )->beh('gen_pass')->action('hide')
					-> field('password', __u('password again'), 'confirm')->not_empty( )->matches('password')->beh('gen_pass')->action('hide')
					-> field('checkbox', __u('send account data to specified email'), 'send_to_email');

				if (acl('user_access'))
				{
					Access::init( );

					$templates = array_keys(Access::$templates);

					$form
						-> field('checkbox_group', __u('roles'), 'access')->options(array_combine($templates, $templates));
				}
			}

			$form
				-> field('checkbox_group', '<a href=\''.Route::url('user_manage', array('list'=>'groups', 'id'=>0)).'\' class=\'cms-add\' title=\''.__('add group').'\' target=\'_blank\'>'.__u('groups').'</a>', 'groups')->options(ORM::factory('group')->get_list( ))->filter_in(array($this, 'groups_filter_in'))->filter_out(array($this, 'groups_filter_out'))
				-> field('text', __u('account label'), 'label')->after('send_to_email');
		}
		else
		{
			$form
				-> field('text', __u('group name'), 'username')->not_empty( )->regex('/^[^\d]/', 'group name can not start at number')
				-> field('text', __u('account label'), 'label');
			
			if (User::check(Site::config('user')->root_name))
			{
				$form
					->field('checkbox', __u('hold'), 'is_hold')->value(1);
			}
		}

		$form
			-> field('submit', $id == 0 ? __u('add') : __u('save'));

		// render and execute handler
		$form->render( );

		if ($id == 0)
		{
			$this->template->header = $this->type == 'accounts' ? __u('new account') : __u('new group');
		}
		else
		{
			$this->template->header = $user->username.': '.__('settings');
		}

		// render control buttons
		$buttons = '';
		if ($id != 0)
		{
			$buttons = View::factory('user.item.ctrl.wrap', array('body' =>
							View::factory('user.item.ctrl', array(
								'flags' => array(
									'edit' => acl('user_manage'),
									'attr' => $this->type != 'groups' && acl('user_manage'),
									'acc' 	=> $user->username != Site::config('user')->root_name && acl('user_access'),
									'del' 	=> ! (boolean) $user->is_system &&  ! (boolean) $user->is_hold && acl('user_manage'),
								),
								'list' 	=> $this->type,
								'id' 	=> $id,
							)
						)));
		}


		$this->template->parent = $this->type == 'accounts' ? __u('accounts') : __u('user groups');
		$this->template->parent_href = Route::url('user_list', array('list' => $this->type));

// 		$this->template->body = $buttons.$form;
		// :WARNING: arr::factory works slow as concat
		$this->template->body = Arr::factory($buttons, $form);
	}

	/** MySQL set type input filter
	 *
	 * @param 	array
	 * @return 	string
	 */
	public function groups_filter_in($value)
	{
		return explode(',', $value);
	}

	/** MySQL set type output filter
	 *
	 * @param 	array
	 * @return 	string
	 */
	public function groups_filter_out($value)
	{
		return implode(',', $value);
	}

	/** Save account data or create account
	 *
	 * @param 	object		data
	 * @param 	integer		user_id
	 * @return	boolean
	 */
	public function save($data, $id = NULL)
	{
		if ( ! acl('user_manage'))
		{
			throw new Access_Exception(__u('permission denied'));
		}

		User::save($data, $id, $this->type);
		// сохраняем аттрибуты пользователя, если они есть
// 		ORM::factory('personal_attributes')->where('user_id', '=', $user->id)->find( )->values(array_merge(array('user_id'=>$user->id), $form_data))->save( );
	}

	/** Delete account or group **/
	public function action_delete( )
	{
		if ( ! acl('user_manage'))
		{
			throw new Access_Exception(__u('permission denied'));
		}

		$id = $this->request->param('id');

		// clear personal privileges of user
		ORM::factory('Access_Rule')->clear_rules($id);
		$user = ORM::factory('account', $id);

		if ( ! $user->loaded( ))
		{
			throw new User_Exception('Cannot load user #:id', array(':id' => $id));
		}

		if ((boolean) $user->is_system || (boolean) $user->is_hold)
		{
			throw new User_Exception('Bad request.');
		}

		$this->template->header = $user->username;
		$this->template->parent = (boolean) $user->is_group ? __u('user groups') : __u('accounts');
		$this->template->parent_href = Route::url('user_list', array('list' => $this->type));

		$this->template->body = ((boolean) $user->is_group ? __u('group has been deleted') : __u('user has been deleted')).'. '.'<a href="'.$this->template->parent_href.'">'.__('return').'</a>';

		$user->delete( );
	}

	/** User attributes **/
	public function action_attr( )
	{
		if ( ! acl('user_manage'))
		{
			throw new Access_Exception(__u('permission denied'));
		}

		$id = $this->request->param('id');

		if ($this->type == 'groups')
		{
			throw new User_Exception('Cannot load attributes of group');
		}

		$user = ORM::factory('account', $id);
		if ( ! $user->loaded( ))
		{
			throw new User_Exception('Cannot load user #:id', array(':id' => $id));
		}

		if ((boolean) $user->is_system && ! User::check(Site::config('user')->root_name))
		{
			throw new User_Exception(__u('permission denied'));
		}

		$attributes = $user->attributes;

		$this->template->header = $user->username.': '.__('attributes');
		$this->template->parent = $user->username.': '.__('settings');
		$this->template->parent_href = Route::url('user_manage', array('list' => $this->type, 'id' => $id));

		$buttons = '';
		if ($id != 0)
		{
			$buttons = View::factory('user.item.ctrl.wrap', array('body' =>
							View::factory('user.item.ctrl', array(
								'flags' => array(
									'edit' => acl('user_manage'),
									'attr' => $this->type != 'groups' && acl('user_manage'),
									'acc' 	=> $user->username != Site::config('user')->root_name && acl('user_access'),
									'del' 	=> ! (boolean) $user->is_system && ! (boolean) $user->is_hold && acl('user_manage'),
								),
								'list' 	=> $this->type,
								'id' 	=> $id,
							)
						)->render( )));
		}

		$attributes->form( )->show_on_success(FALSE)->message(__u('data saved').'. '.'<a href="'.Route::url('user_attr', array('list'=>$this->type, 'id'=>$id)).'">'.__('back').' '.__('to attributes').'</a>')->render( );

		$this->template->body = Arr::factory($buttons, $attributes->form( ));
	}

	/** User privileges **/
	public function action_access( )
	{
		if ( ! acl('user_manage'))
		{
			throw new Access_Exception(__u('permission denied'));
		}


		$id = $this->request->param('id');

		$user = ORM::factory('account', $id);
		if ( ! $user->loaded( ))
		{
			throw new User_Exception('Cannot load user #:id', array(':id' => $id));
		}

		if ((boolean) $user->is_system && ! User::check(Site::config('user')->root_name))
		{
			throw new User_Exception(__u('permission denied'));
		}

		
		$this->template->header = $user->username.': '.__('access');
		$this->template->parent = $user->username.': '.__('settings');
		$this->template->parent_href = Route::url('user_manage', array('list' => $this->type, 'id' => $id));

		$buttons = '';
		if ($id != 0)
		{
			$buttons = View::factory('user.item.ctrl.wrap', array('body' =>
							View::factory('user.item.ctrl', array(
								'flags' => array(
									'edit' => acl('user_manage'),
									'attr' => $this->type != 'groups' && acl('user_manage'),
									'acc' 	=> $user->username != Site::config('user')->root_name && acl('user_access'),
									'del' 	=> ! (boolean) $user->is_system && ! (boolean) $user->is_hold && acl('user_manage'),
								),
								'list' 	=> $this->type,
								'id' 	=> $id,
							)
						)->render( )));
		}

		$this->template->body = $buttons.
								Request::factory(Route::get('access_user')->uri(array('user_id' => $id)))->execute( )->body( )->body;
	}

	/** User logout **/
	public function action_logout( )
	{
		User::instance( )->logout( );

		// переадресовываем назад
		$this->request->redirect($this->request->referrer( ));
	}
	
	/** 
	 * Login to the specified user
	 *
	 * @return 	void
	 */
	public function action_enter()
	{
		if ( ! acl('user_enter'))
		{
			throw new Access_Exception(__u('permission denied'));
		}
		
		$username = $this->request->query('username');
		$current_user = User::get()->username;
		
		User::instance()->logout();
		User::instance()->login_on_logout($current_user);
		
		User::instance()->force_login($username);
		
		$this->request->redirect('/');
	}
	
}
