<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		User basic authorization v2
 * @package 	User
 * @author 		Stanislav U. Alkimovich
 * @date 		2013-07-16
 *
 * :TODO: rework attachment of user to groups
 **/

class Kohana_User
{
	// array of recent credentials 
	public static $credentials = array();
	
	// delimiter of id and groups id
	const SEPARATOR = ',';

	// instance of class
	protected static $instance;

	// comma separated authorized users id
	protected $_authorized;
	
	/**
	 * Last authorized user
	 * 
	 * @var Kohana_Model_Account 
	 */
	protected $_last_auth_user = NULL;

	/** Singleton factory
	 *
	 * @return object
	 */
	public static function instance( )
	{
		if ( ! isset(self::$instance))
		{
			self::$instance = new User;
		}

		return self::$instance;
	}

	/** Standart setter/getter
	 *
	 * @param string 	variable name
	 * @param array		parameters
	 *
	 * @return mixed
	 */
	public function __call($var, $args = NULL)
	{
		if (is_array($args) && count($args) > 0)
		{
			$this->$var = $args[0];

			return $this;
		}

		return $this->$var;
	}


	/** check, is current user guest
	 *
	 * @return boolean
	 */
	public function is_guest( )
	{
		return ! $this->check( );
	}


	/** Check user authorization
	 *	must use checking privileges instead of checking authorization
	 *
	 * @param string 	login
	 * @return boolean
	 */
	public static function check($login = NULL)
	{
		if (Kohana::$is_cli)
		{
			return TRUE;
		}

		// get session data
		$user = User::get( );

		if ( ! isset($user))
		{
			return FALSE;
		}

		if (isset($login))
		{
			return $user->login == $login;
		}
		else
		{
			return $user->login !== NULL;
		}
	}

	/** Get authorized user data
	 *
	 * to get user data call:
	 * 		// get user "masha" data
	 * 		User::instance( )->get("masha");
	 * 		User::get("masha");		// shortcut
	 *
	 * 		// get first authorized user data
	 * 		User::instance( )->get( );		// shortcut in a similar
	 *
	 * 		// get id
	 * 		User::get( )->id;
	 *
	 *
	 *
	 * @param string 	login
	 * @return object
	 */
	public static function get($login = NULL)
	{
// 		if ( ! isset($this))
// 		{
// 			return self::instance( )->get($login);
// 		}

		$data = Session::instance( )->get(Site::config('user')->session_user_data);

		if ( ! isset($data))
		{
            $data = array(
                'id'        => 0,
                'login'     => NULL,
                'email'     => NULL,
                'groups'    => array(0),
            );
		}

		$data['username'] = &$data['login'];
		
		return (object) $data;
	}
	
	/** Load root account
	 *
	 * @return 	Model_Account
	 */
	public function root()
	{
		return User::load(Site::config('user')->root_name);
	}

	/** Load root account
	 *
	 * @return 	Model_Account
	 */
	public static function guest()
	{
		return User::load(Site::config('user')->guest_name);
	}
	
	/**
	 * Returns last authorized user
	 * @return Kohana_Model_Account
	 */
	public function last_auth_user()
	{
		return $this->_last_auth_user;
	}
	
	/**
	 * Process login data, check and return result
	 * @param string	$login
	 * @param string	$password
	 * @return boolean	login result
	 */
	public function process_login($login, $password)
	{
		if (isset(User::$credentials[$login]))
		{
			if (isset(User::$credentials[$login][$password]))
			{
				return User::$credentials[$login][$password];
			}
		}
		else
		{
			User::$credentials[$login] = array();
		}
		
		// get data from DB
		$this->_last_auth_user = ORM::factory('account')
			-> where('username', '=', $login)
			-> find( );
		
		
		return (User::$credentials[$login][$password] = ($this->_last_auth_user->password === User::hash($password)));
	}
	
	/** Authorization using password
	 *
	 * @param string 	login
	 * @param string 	password
	 * @param boolean	store in cookie store
	 * @return boolean
	 */
	public function login($login, $password, $remember = TRUE)
	{
			
		if ($this->process_login($login, $password))
		{
			// store auth data
			if ($remember === TRUE)
			{
				$this->remember($login);
			}

			// завершаем вход
			return $this->finish($this->_last_auth_user);
		}
		else
		{
			return FALSE;
		}
	}
	
	
	/**
	 * Attempt to authorize user using the social networks accounts
	 * 
	 * @return boolean
	 */
	public function oauth_login($type)
	{
		$config = (array) Site::config('oauth');
	
		$class = 'SocialAuther\Adapter\\' . ucfirst($type);
		$adapter = new $class(Site::config('oauth')->providers[$type]);

// 		$adapter = new SocialAuther\Adapter\Vk($config['providers'][$type]);
		$auther = new SocialAuther\SocialAuther($adapter);
		
		if ($auther->authenticate())
		{
			if (($social_id = $auther->getSocialId()) === NULL)
			{
				return FALSE;
			}
		
			$login = $type.'_'.$auther->getSocialId();
			
			$userdata = array(
				'username' => $login,
				'label' => '',
				'groups' => '',
				'email' => $auther->getEmail(),
				'name' =>  $auther->getName(),
				'social_type' => $type,
				'social_id' => $auther->getName(),
				'social_page' => $auther->getSocialPage(),
				'gender' => $auther->getSex(),
				'birthday' => $auther->getBirthday(),
			);
		
			$this->_last_auth_user = ORM::factory('account')
				-> where('username', '=', $login)
				-> find( );
				
			if ($this->_last_auth_user->loaded() === FALSE && $auther->getEmail() != '')
			{
				$this->_last_auth_user = ORM::factory('account')
					->where('email', '=', $auther->getEmail())
					->find();
					
				if ($this->_last_auth_user->loaded() === TRUE)
				{
					$userdata['username'] = $this->_last_auth_user->username;
				}
			}
				
// 			if (!is_null($auther->getAvatar()))
// 				echo '<img src="' . $auther->getAvatar() . '" />'; echo "<br />";
			if ($this->_last_auth_user->loaded() === FALSE)
			{
				$userdata['access'] = array('Клиент' => 'Клиент');
			}

			User::save((object) $userdata, $this->_last_auth_user->id);
			
			return User::instance()->force_login($userdata['username']);
		}
		
		return FALSE;
	}

	/** Store auth data in cookie_store
	 *
	 * @param string 	login
	 * @return void
	 */
	public function remember($login)
	{
		// save
		Cookie::store(Site::config('user')->cookie_remember_var, $login);
	}

	/** Hash password
	 *
	 * @param string 	password
	 * @return string 	hashed password
	 */
	public static function hash($password)
	{
		return hash_hmac(Site::config('user')->hash_method, $password, Site::config('user')->hash_key);
	}

	/** Finish authorization process
	 *
	 * @param 	Model_Account		login
	 * @param 	boolean			    force login flag
	 * @return 	boolean
	 */
	public function finish(Model_Account $current_user, $force = FALSE)
	{
		if ($current_user->username != Site::config('user')->root_name && ! acl('access_login', NULL, $current_user->id))
		{
			throw new Access_Exception( );
		}

		// fetch data
		$user_data = Session::instance( )->get(Site::config('user')->session_user_data);

		if (isset($user_data))
		{
			throw new User_Exception('User is already authorized.');
		}

		$logins_count = $current_user->logins+1;
		
		$user_data = array(
			'id' 		=> $current_user->id,
			'login' 	=> $current_user->username,
			'email' 	=> $current_user->email,
			'logins'	=> $logins_count,
			'groups'	=> array_unique(array_map('trim', explode(Kohana_User::SEPARATOR, trim($current_user->groups.Kohana_User::SEPARATOR.Site::config('user')->guest_id, Kohana_User::SEPARATOR)))),
		);

		// обновляем количество посещений
		$current_user->logins = $logins_count;

		// устанавливаем дату последнего посещения
		$current_user->last_login = time();

		if (isset($_SERVER['REMOTE_ADDR']))
		{
			// устанавливаем ip последнего посещения
			$current_user->last_login_ip = $_SERVER['REMOTE_ADDR'];
		}

		// сохраняем данные
		$current_user->update();
		
		// выключаем htmldump
		HtmlDump::instance( )->off( );

		// сохраняем данные в сессии
		Session::instance( )->set(Site::config('user')->session_user_data, $user_data);
		
		// отписываем в лог
		if ($current_user->id != 0)
		{
			Kohana::$log->add(
				Log::INFO,
				'User :username logged in.',
				array(':username' => $current_user->username),
				NULL,
				array('logo' => 'login', 'external_id_0' => $current_user->id)
			);
		}

		return TRUE;
	}

	/** Get comma separated authorized users id -- for usage with sql "IN" statement
	 *
     * @param   boolean return as array
	 * @return 	string
	 */
	public function authorized($as_array = FALSE)
	{
        if ( ! isset($this->_authorized))
        {
        
            $data = $this->get( );

            $this->_authorized = array_unique(array_merge(array($data->id), $data->groups));
        }
        
        // get authorized users
        if ($as_array === TRUE)
        {
            return $this->_authorized;
        }

        return implode(Kohana_User::SEPARATOR, $this->_authorized);
	}

	/** Set comma separated authorized users id -- for usage with sql "IN" statement
	 *
	 * @return 	string
	 */
// 	public function set_authorized( )
// 	{
// 		// authorized users
// 		$users = Session::instance( )->get(Site::config('user')->session_user_data);
// 
// 		// skip current values
// 		$this->authorized = NULL;
// 		Session::instance( )->delete(Site::config('user')->session_auth_users);
// 
// 		if ($users !== NULL)
// 		{
// 			// auth string
// 			$auth = explode(Kohana_User::SEPARATOR, $this->authorized( ));
// 
//             // skip links
//             if (is_integer($key))
//             {
//                 continue;
//             }
// 
//             if ($user['groups'] != '')
//             {
//                 $arg = array_map('trim', explode(Kohana_User::SEPARATOR, $user['groups']));
//             }
//             else
//             {
//                 $arg = array( );
//             }
// 
//             // increase key according to user priority
//             $current_key = 1000 * $user['priority'];
// 
//             $arg[$current_key] = $user['id'];
// 
//             // merge existent users and new
//             $auth = array_merge($auth, $arg);
// 
// 			krsort($auth);
// 
// 			//save
// 			$this->authorized = implode(Kohana_User::SEPARATOR, $auth);
// 
// 			Session::instance( )->set(Site::config('user')->session_auth_users, $this->authorized);
// 		}
// 	}

	/** Get user by group id
	 *
	 * @param 	integer 	group id
	 * @return 	mixed		user data or FALSE if user exist not
	 */
// 	public function id_by_group($group_id)
// 	{
// 		$users = Session::instance( )->get(Site::config('user')->session_user_data);
// 
// 		if (isset($users))
// 		{
// 			foreach ($users AS $user)
// 			{
// 				if (array_search($group_id, explode(Kohana_User::SEPARATOR, $user['groups'])) !== FALSE)
// 				{
// 					return (object) $user;
// 				}
// 			}
// 		}
// 
// 		return FALSE;
// 	}
// 
	/** Force authorization
	 *
	 * @param string	login
	 * @return boolean
	 */
	public function force_login($login)
	{
		// достаём данные пользователя из БД
		$user = ORM::factory('account')
			-> where('username', '=', $login)
			-> find( );

		if ($user->loaded( ))
		{
			return $this->finish($user, TRUE);
		}

		// default
		return FALSE;
	}

	/** Logout: clear session and cookies
	 *
	 *	@return void
	 */
	public function logout($login = NULL)
	{
// 		if (isset($login))
// 		{
// 			$user_data = Session::instance( )->get(Site::config('user')->session_user_data);
// 
// 			// remove data from cookie store
// 			$cookie_data = Cookie::store(Site::config('user')->cookie_remember_var);
// 
// 			if (isset($user_data[$login]))
// 			{
// 				unset($user_data[$login]);
// 			}
// 
// 			if (isset($cookie_data[$login]))
// 			{
// 				unset($cookie_data[$login]);
// 			}
// 
// 			if (count($user_data) > 0)
// 			{
// 				// remove session data
// 				Session::instance( )->set(Site::config('user')->session_user_data, $user_data);
// 			}
// 			else
// 			{
// 				// remove session data
// 				Session::instance( )->delete(Site::config('user')->session_user_data);
// 				// switch on htmldump
// 				HtmlDump::instance( )->on( );
// 			}
// 
// 			if (count($cookie_data) > 0)
// 			{
// 				// remove data from cookie store
// 				Cookie::store(Site::config('user')->cookie_remember_var, $cookie_data);
// 			}
// 			else
// 			{
// 				// remove data from cookie store
// 				Cookie::store_clear(Site::config('user')->cookie_remember_var);
// 			}
// 		}
// 		else
// 		{
        // remove session data
        Session::instance( )->delete(Site::config('user')->session_user_data);

        // remove data from cookie store
        Cookie::store_clear(Site::config('user')->cookie_remember_var);

        // switch on htmldump
        HtmlDump::instance( )->on( );
        
        $username = Session::instance( )->get('login_on_logout');
        
        if (isset($username))
        {
			User::instance()->force_login($username);
			
			Session::instance( )->delete('login_on_logout');
        }
// 		}
// 
// 		$this->set_authorized( );
	}

	/** Authorization using cookie store data
	 *
	 * @return boolean
	 */
	public function auto_login( )
	{
		if (Kohana::$is_cli)
		{
			return;
		}

		// получаем флаг автологина из сессии
		$session_auto_login_flag = Session::instance( )->get(Site::config('user')->session_auto_login_flag);

		// если выставлен -- не входим
		if (isset($session_auto_login_flag) || $this->check( ))
		{
			return;
		}

		// устанавливаем флаг автологина
		Session::instance( )->set(Site::config('user')->session_auto_login_flag, 1);

		// получаем данные из куки-склада
		$login = Cookie::store(Site::config('user')->cookie_remember_var);

		if (isset($login))
		{
			// достаём данные пользователя из БД
			$user = ORM::factory('account')
				-> where('username', '=', $login)
				-> find( );

			if ($user->loaded( ))
			{
				return $this->finish($user);
			}
		}

		return FALSE;
	}


	/** Get/set user attributes
	 *
	 * @param	string	Login
	 *
	 * @return	ORM
	 */
	public static function attr($user = NULL)
	{
		$id = $user ? User::get($user)->id : User::get()->id;
		$orm = ORM::factory('account', $id)->attributes;

		if ( ! $orm->loaded( ))
		{
			return NULL;
		}

		return $orm;
	}
	
	/** Save account data or create account
	 *
	 * @param 	object		data
	 * @param 	integer		user_id
	 * @param 	string		type: account or group
	 * @return 	mixed		account ORM on success or FALSE
	 */
	public static function save($data, $id = NULL, $type = 'accounts')
	{
		if ($id == 0)
		{
			$id = NULL;
		}
		
		if (isset($data->email_as_uname) && ! isset($id))
		{
			$data->username = $data->email;
		}

		// generate password if queried
		if (( ! isset($id) || isset($data->change_pass)) && isset($data->gen_pass) && $data->gen_pass)
		{
			$data->password = mt_rand(100000000, 999999999);
		}

		$user = ORM::factory('account', $id);

		if (isset($id) && ! $user->loaded( ))
		{
			throw new User_Exception('Cannot save data: User_Accounts.ID #:id does not exist.', array(':id' => $id));
		}

		if ((boolean) $user->is_system && ! User::check(Site::config('user')->root_name))
		{
			throw new User_Exception(__u('permission denied'));
		}
		
		try
		{
			// initialize access modules
			Access::init( );

			if ($type == 'accounts')
			{
				if (is_array($data->groups))
				{
					$data->groups = implode(Kohana_User::SEPARATOR, $data->groups);
				}
			
				// save account data
				$values = array(
					'email' 		=> $data->email,
					'label' 		=> isset($data->label) ? $data->label : NULL,
					'groups'		=> $data->groups,
				);
				
				if (isset($data->username))
				{
					$values['username'] = $data->username;
				}

				if ( (! isset($id) || isset($data->change_pass)) && isset($data->password) )
				{
					$values['password'] = $data->password;
				}

				$user->values($values)->save( );
				$user->reload( );

				if (( ! isset($id) || isset($data->change_pass)) && isset($data->send_to_email))
				{

					if ( ! isset($id))
					{
						$user_mail_subj = __('New account on :url', array(':url' => IDN::decodeIDN(URL::domain( ))));
						$user_mail_body = View::factory('user.mail.created', array(
							'username' => $user->username,
							'password' => $data->password,
						));
					}
					else
					{
						$user_mail_subj = __('Password changed on :url', array(':url' => IDN::decodeIDN(URL::domain( ))));
						$user_mail_body = View::factory('user.mail.pass.changed', array(
							'username' => $user->username,
							'password' => $data->password,
						));
						
					}
					
					// send email
					Email::factory( )
						->from(Site::config( )->email_from)
						->to($data->email)
						->subj($user_mail_subj)
						->text($user_mail_body->render( ))
						->save( );
				}

				if ( ! isset($id))
				{
					// add attributes for new user
					$attribute = ORM::factory('attributes')->values(array('user_id' => $user->id))->save( );

					/* Drop permissions of new user to defaults according to selected roles */
					// clear personal privileges of user
					ORM::factory('Access_Rule')->clear_rules($user->id);

					// save new permissions
					ORM::factory('Access_Rule')->save_rules($user->id, array(
						'access_login'
					));
					
					// отписываем в лог
					Kohana::$log->add(
						Log::INFO,
						'User :username has been registered.',
						array(':username' => $user->username),
						NULL,
						array('logo' => 'reg', 'external_id_0' => $user->id)
					);
				}
				
				// save attributes
				$attr_orm = ORM::factory('attributes')
					->where('user_id', '=', $user->id)
					->find( );
				
				if ($attr_orm->loaded( ))
				{
					$attr_orm
						->values(array_merge(array('user_id'=>$user->id), array_diff_key((array) $data, $user->as_array( ))))
						->save( );
				}
			}
			else
			{
				// save account data
				$user
					-> values(array(
						'username' 		=> $data->username,
						'label' 		=> $data->label,
						'is_group' 		=> 1,
						'is_hold'		=> (int) isset($data->is_hold),
					))
					-> save( );
			}
			
			return $user;

		}
		catch (Exception $e)
		{
			if (IN_PRODUCTION)
			{
				throw $e;
			}
		
			return FALSE;
		}
	}
	
	
	/** Load account by username
	 * 
	 * @param	mixed	(string) username or (integer) id
	 * @return	Model_Account OR NULL
	 */
	public static function load($username)
	{
		if (is_integer($username))
		{
			$orm = ORM::factory('account', $username);
		}
		else
		{
			$orm = ORM::factory('account')->where('username', '=', $username)->find( );
		}
		
		if ($orm->loaded( ))
		{
			return $orm;
		}
		
		return NULL;
	}
	
	/** Check root authorization
	 * 
	 * @return	boolean
	 */
	public static function is_root( )
	{
		return User::check(Site::config('user')->root_name);
	}
	
    /** Get users list, optional filtered by group name
     * 
     * @param   mixed   (string) group name or (integer) group id
     * @return  ORM
     */
    public static function get_list( )
    {
        $args = func_get_args( );

        $orm = ORM::factory('account');
        
        foreach ($args AS $arg)
        {
            if ( ! is_integer($arg))
            {
                $arg = ORM::factory('account')
                        ->where('username', '=', $arg)
                        ->find( );
                
                if ( ! $arg->loaded( ))
                {
                    // skip current group
                    continue;
                }
                
                $arg = $arg->id;
            }
            
            $orm->where('groups', 'REGEXP', '[[:<:]]'.$arg.'[[:>:]]');
        }
        
        return $orm;
    }

    
	/** 
	 * set username for login when logout
	 *
	 * @param 	string	username
	 * @return 	boolean
	 */
	public function login_on_logout($username)
	{
		Session::instance( )->set('login_on_logout', $username);
		
		return TRUE;
	}
}
