<?php defined('SYSPATH') or die('No direct script access.');

class Kohana_Controller_User extends Controller_Main
{
	/**
	 * @var boolean		check access rules and load page data from storage
	 */
// 	protected $_auto_load = FALSE;

	/**
	 * @var View		template object
	 */
	protected $_NAME ;
	protected $template;
	protected $_auto_load = FALSE;

	public function before( )
	{
		parent::before( );

		if ( ! $this->request->is_initial( ))
		{
			$this->_view_body->set_filename('cms.login');
			$this->_view_body->body = '';
		}
		
		// attempt to login using oauth
		$this->_oauth_login();
		
		$this->_view_body->register = $this->_view_body->recovery = '';
	}
	
	/** Publish search form
	 *
	 * @return	void
	 */
	protected function _auth_form( )
	{
		return '';
	}

	/** Publish search form
	 *
	 * @return	void
	 */
	protected function _register_form( )
	{
		return '';
	}
	
	/** Recovery form
	 *
	 * @return 	void
	 */
	protected function _recovery_form( )
	{
		return '';
	}
	
	public function action_reg_success() {
		
	}
	
	/**
	 * Attempt to login using OAuth, redirect to success page (specified in oauth config)
	 * 
	 * @return void
	 */
	protected function _oauth_login()
	{
		if ( (($provider = $this->request->param('oauth_provider')) !== NULL || ($provider = $this->request->query('provider') )) && isset(Site::config('oauth')->providers[$provider]) && $this->request->query('code') !== NULL)
		{
			$result = User::instance()->oauth_login($provider);

			if ($result)
			{
				$this->request->redirect(Site::config('oauth')->common['redirect_url']);
			}
			else
			{
				$this->request->redirect(Site::config('oauth')->common['error_url']);
			}
		}
	}
	
	/** Login form 
	 *
	 * @return	void
	 */
	public function action_login( )
	{   
		if (User::check() && !acl('user_manage'))
		{
			$this->request->redirect('/');
		}
	
		$this->_auto_load = FALSE;
		
		$form = Form::factory('login')
// 				 ->use_activator(FALSE)
				 ->field('text', 'Логин', 'username')->not_empty( )
				 ->field('password', 'Пароль', 'password')->not_empty( )
				 ->field('checkbox', 'Запомнить', 'remember')
				 ->field('submit', 'Войти')
				 ->render( );

 		$message = '';

		// если данные получены
		// запускаем обработчик
		if ($form->sent( ))
		{
			// в $status помещаем результат функции login
			$status = User::instance( )->login($form->username, $form->password, isset($form->remember));

			if ($status === TRUE)
			{
				$redirect_uri = Session::instance( )->get(Site::config('site')->referrer_page_var);

				// если существует страница для переадресации
				return $this->request->redirect( ! isset($redirect_uri) || $redirect_uri == '' ? URL::base( ) : $redirect_uri);
			}
			else
			{
				$message = $status;
			}
		}

		$this->_view_body->body .= $message.$form;
	}

	
	/** Register new user
	 *
	 * @return	void
	 */
	public function action_reg( )
	{
// 		$this->_auto_load = FALSE;
		if (User::check() && !acl('user_manage'))
		{
			$this->request->redirect('/');
		}
	
		
		$form = Form::factory('regform')
			->use_activator(FALSE)
			->show_on_success(TRUE)
			->field('text', 'E-mail', 'email')->not_empty( )
			->field('text', 'Телефон', 'phone')->mask('+7 (999) 999-99-99')
// 			->field('text', 'E-mail', 'email')->not_empty( )->rule('email')->rule('email_exists', array('email_as_uname' => TRUE))
			->field('submit', 'Регистрация')->render();
 		$message = '';
		if ($form->sent( ))
		{
			$user = ORM::factory('account')->where('username', '=', $form->email)->find();
			
			if ( ! $user->loaded())
			{
				$data = array(
							'change_pass' => TRUE,
							'gen_pass' => TRUE,
							'email_as_uname' => TRUE,
							'send_to_email' => TRUE,
							'access' => array('Клиент' => 'Клиент'),
							'label' => '',
							'groups' => ''
						);
						
				User::save((object) array_merge($form->result()->as_array(), $data));
				if (isset($form->phone) && $form->phone)
				{
					$uid = ORM::factory('account')->where('username', '=', $form->email)->find()->id;
					ORM::factory('attributes')->where('user_id', '=', $uid)->find()
					//ORM::factory('account')->where('username', '=', $form->email)->find()->attributes
							->values(array('phone' => $form->phone))->save( );
				}
				
// 				User::force_login($form->email);
				
				$message = 'Регистрация завершена. Пароль для входа на сайт выслан на '.$form->email.'.';
			}
			else
			{
				$message = 'Такой пользователь уже зарегистрирован.';
			}
		}
		
		$this->_view_body->body .= $message.$form;
	}


	/** Restore password
	 *
	 * @return void
	 */
	public function action_recovery( )
	{
		if (User::check() && !acl('user_manage'))
		{
			$this->request->redirect('/');
		}
	
// 		$this->_auto_load = FALSE;
		
		$form = Form::factory('recform')->field('text', 'E-mail', 'email')
			->not_empty( )
// 			->rule('email_exists', array('email_as_uname' => TRUE))
			->field('submit', 'Восстановить')->render();
			
		if ($form->sent( ))
		{
			$user = ORM::factory('account')->where('username', '=', $form->email)->find();
			
			if ($user->loaded())
			{
				$data = array('change_pass' => TRUE, 'gen_pass' => TRUE, 'email_as_uname' => TRUE, 'send_to_email' => TRUE, 
					'label' => $user->label, 'groups' => $user->groups);
					
				User::save((object) array_merge($form->result()->as_array(), $data), $user->id);

				$this->_view_body->body .= 'На указанный адрес выслано письмо с новым паролем.';
			}
			else
			{
				$this->_view_body->body .= 'Указанный e-mail не зарегистрирован. Вы можете пройти <a href="'.Route::url('reg').'">регистрацию</a>.';
			}
		}
		$this->_view_body->body .= $form;
	}


	/** User preferences
	 *
	 * @return void
	 */
	public function action_preferences()
	{
		if (User::check( ))
		{
			$id = User::get()->id;

			$user = ORM::factory('account', $id);
			
			if ( ! $user->loaded( ))
			{
				throw new User_Exception('Cannot load user #:id', array(':id' => $id));
			}

			$attributes = $user->attributes->find( );

			$this->_view_body->header = $user->username.': '.__('attributes');
			$this->_view_body->parent = $user->username.': '.__('settings');

			$form = $attributes->form( )->clear_on_success(FALSE)->show_on_success(TRUE)->message(__u('data saved').'.');
				
			$form->fields('phone')->mask('+7 (999) 999-99-99')->not_empty( );
			$form->field('text', __u('login'), 'username')->value($user->username)->disabled(TRUE)->before('first_name');
			$form->field('text', __u('e-mail'), 'email')->value($user->email)->not_empty( )->rule('email')->before('first_name');
			$form->field('checkbox', __u('change password'), 'change_pass')->selected(FALSE)->rel('ch_pass')->checked( )->after('phone')
				->field('checkbox', __u('generate password'), 'gen_pass')->selected(TRUE)->beh('ch_pass')->action('show')->rel('gen_pass')
					->checked( )->after('change_pass')
				->field('password', __u('password'), 'password')->not_empty( )->beh('ch_pass && !gen_pass')->action('show')
					/*->filter_out(array($this, 'hash_password'))*/->after('gen_pass')
                                ->field('password', __u('password again'), 'confirm')->not_empty( )->matches('password')->beh('ch_pass && !gen_pass')
					->action('show')->after('password');
			$form->text('<span class="red">*</span> обязательные для заполнения поля')->after('submit');
			$form->render( );
			
			if ($form->sent( ))
			{
				$data = array(
							'email_as_uname' => TRUE,
							'send_to_email' => TRUE,
							'access' => array('Клиент' => 'Клиент'),
							'label' => $user->label,
							'groups' => $user->groups,
							'username' => $user->username,
						);
						
				User::save((object) array_merge($form->result()->as_array(), $data), User::get( )->id);
				
				if (isset($form->phone) && $form->phone)
				{
					$uid = ORM::factory('account')->where('username', '=', $form->email)->find()->id;
					ORM::factory('attributes')->where('user_id', '=', $uid)->find()
					//ORM::factory('account')->where('username', '=', $form->email)->find()->attributes
						->values(array('phone' => $form->phone))->save( );
							
				}
			}
			
			$this->_view_body->body .= $form;

		}
		else
		{
			$this->request->redirect('/');
		}
	}
	
	
	public function action_oauth()
	{
		if (($provider = $this->request->param('oauth_provider')) !== NULL && isset(Site::config('oauth')->providers[$provider]))
		{
			$class = 'SocialAuther\Adapter\\' . ucfirst($provider);
			$vkAdapter = new $class(Site::config('oauth')->providers[$provider]);
    
			// создание адаптера и передача настроек
// 			$vkAdapter = new SocialAuther\Adapter\Vk();

			// передача адаптера в SocialAuther
			$auther = new SocialAuther\SocialAuther($vkAdapter);
			// аутентификация
			$this->request->redirect($auther->getAuthUrl());
		}
		else
		{
			$this->request->redirect('/');
		}
		
	}


	/** Save user data wrapper
	*
	*/
//	tatic function user_save();
}
