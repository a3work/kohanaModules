<?php defined('SYSPATH') or die('No direct script access.');

class Kohana_Controller_Main extends Controller
{
	/**
	 * @const string	mobile device flag
	 */
	const IS_MOBILE_FLAG = 'is_mobile';
	
	/**
	 * @const string	name of content view variable
	 */
	const VIEW_CONTENT_VAR = 'content';

	/**
	 * @var string	namespace for views
	 */
	protected $_view_namespace = '';

	/**
	 * @var string	header view filename
	 */
	protected $_view_header = 'header';
	
	/**
	 * @var string	footer view filename
	 */
	protected $_view_footer = 'footer';

	/**
	 * @var boolean		check access rules and load page data from storage
	 */
	protected $_auto_load = TRUE;

	/**
	 * @var boolean		automatic rendering switch (TRUE by default for non-CLI requests) 
	 */
	protected $_auto_render = TRUE;
	
	/**
	 * @var boolean 	detect mobile devices and redirect to mobile version
	 */
	protected $_detect_mobile = FALSE;

	/**
	 * @var boolean 	use breadcrumbs
	 */
	protected $_use_breadcrumbs = TRUE;

	/**
	 * @var boolean 	use breadcrumbs
	 */
	protected $_process_menu = TRUE;

	/**
	 * @var boolean 	throw errors HTTP-404 if page not exists
	 */
	protected $_throw_errors = TRUE;

	/**
	 * @var array		list of menu types
	 */
	protected $_menu_types = array('main');	

	/**
	 * @var array		list of menu objects
	 */
	protected $_menu = array( );	
	
	/**
	 * @var boolean 	is current page index or not
	 */
	protected $_is_index = FALSE;
	
	/**
	 * @var boolean 	is current device mobile
	 */
	protected $_is_mobile = FALSE;
	
	/**
	 * @var string 		current URI
	 */
	protected $_uri;
	
	/**
	 * @var string 		basic page URI for custom contollers
	 */
	protected $_basic_uri;
	
	/**
     * @var Exception	current exception object
     */
    protected $_error;
	
	/**
     * @var File		current page object
     */
	protected $_file;
	
	/**
     * @var File		basic page object for custom controllers
     */
	protected $_basic_file;
	
	/**
	 * @var array		current page content
	 */
	protected $_content = array( );	
	
	/**
	 * @var string		current page settings (page template, metadata...)
	 */
	protected $_settings = array( );
	
	/** AUTHORIZATION **/
	/**
	 * @var boolean		use authorization
	 */
	protected $_use_auth = TRUE;
	
	/**
	 * @var Form_Base		auth form instance
	 */
	protected $_auth_form;
	
	/**
	 * @var string		redirect URI for auth action
	 */
	protected $_auth_redirect_uri;
	
	/**
	 * Initialize common page components: content, settings, breadcrumbs, menus
	 * 
	 * @param Page $file
	 */
	protected function _init($file)
	{
		// save content
		$this->_content = $file->text( );

		// save settings
		$this->_settings = $file->attr( );

		// set index flag
		$this->_is_index = $file->is_root( );

		/* render breadcrumbs */
		if ($this->_use_breadcrumbs && $this->request->is_initial( ))
		{
			$file->breadcrumbs( );
		}

		/* render menus */
		if ($this->_process_menu)
		{
			$menu_list = array( );

			foreach ($this->_menu_types AS &$menu)
			{
				$menu_list[$menu] = $file->menu($menu);
			}

			$this->_menu = $menu_list;
		}
	}
	
	/**
	 * fetch page content, metadata and menu
	 */
    public function before( )
    {
		parent::before( );
		
		// set namespace for views
		View::_namespace($this->_view_namespace);

		if (defined("ALLOW_DEVELOPER_ONLY") && ALLOW_DEVELOPER_ONLY && strpos($_SERVER['REMOTE_ADDR'], '192.168.') === FALSE && ( ! defined("DEVELOPER_IP") || $_SERVER['REMOTE_ADDR'] != DEVELOPER_IP) && ( ! defined("DEVELOPER_IP_2") || $_SERVER['REMOTE_ADDR'] != DEVELOPER_IP_2))
		{
			throw new HTTP_Exception_403;
		}
		
        // $this->uri -- force loading
        if ($this->_uri === NULL)
        {
            if (($route_name = Route::name($this->request->route( ))) == 'default')
            {
                // load content
                $this->_uri = $this->request->url( );
            }
            elseif ($this->_auto_load)
            {
                $this->_uri = Request::current( )->url( );
            }
        }
        
        

        // switch off autorender for cli or internal requests
		if (Cli::check( ))
		{
			$this->_auto_render = FALSE;
		}
        
		if ($this->_detect_mobile)
		{
			if (strpos(URL::base('http'), '//m.') !== FALSE)
			{
				$this->_is_mobile = TRUE;
				$this->template->versions = array(
					'full' 		=> str_replace('http://m.', 'http://', URL::base('http')),
					'mobile' 	=> URL::base('http'),
				);
			}
			else
			{
				$this->template->versions = array(
					'full' 		=> URL::base('http'),
					'mobile' 	=> str_replace('http://', 'http://m.', URL::base('http')),
				);
			}
			
			$is_mobile = Cookie::get(Kohana_Controller_Main::IS_MOBILE_FLAG);

			if ($this->_is_mobile && MDetect::instance( )->isMobile( ) && ! isset($is_mobile))
			{
				Cookie::set(Kohana_Controller_Main::IS_MOBILE_FLAG, 1, 0);
			}

			if ( ! $this->_is_mobile && MDetect::instance( )->isMobile( ) && ! isset($is_mobile))
			{
				$m_domain = str_replace('//', '//m.', str_replace('www.', '', URL::site(Request::detect_uri( ), 'http')));
				$this->request->redirect($m_domain);
			}			
		}

		/* load default settins */
		$this->_settings = Site::config('site')->default_page_config;
		
		/* create empty menu list */
		$this->_menu = array_fill_keys($this->_menu_types, NULL);
		
		// body
		$this->_content = array('body' => '');
		
        if ($this->_auto_load === TRUE)
        {
			try
			{
				try
				{
					$this->_file = File::factory($this->_uri);

					if ($this->_file->exists( ) === FALSE)
					{
						throw new HTTP_Exception_404;
					}

					// if queried file
					if ($this->_file->is_dir( ) === FALSE)
					{
						/* for initial requests start downloading */
						if ($this->request->is_initial( ))
						{
							$this->_auto_load = FALSE;
							$this->_auto_render = FALSE;

							$this->_file->download( );
						}
					}
					// if queried directory (page)
					else
					{
						$this->_file = Page::factory($this->_uri);

						// throw 404 if file not found
						if ( ! $this->_file->exists( ))
						{
							throw new HTTP_Exception_404;
						}

						$this->_init($this->_file);
					}
				}
				catch (HTTP_Exception_404 $e)
				{
					// check existence of basic URL and load it
					if ($this->_basic_uri)
					{
						$this->_file = Page::factory($this->_uri);
						$this->_basic_file = Page::factory($this->_basic_uri);

						// throw 404 if file not found
						if ( ! $this->_basic_file->exists( ))
						{
							throw new HTTP_Exception_404;
						}

						$this->_init($this->_basic_file);
					}
					else
					{
						// throw 404 if file not found
						throw new HTTP_Exception_404;
					}
				}
			}
			catch (Access_Exception $e)
			{
				throw $e;
// 				throw new HTTP_Exception_403;
			}
			catch (Exception $e)
			{
				$this->_error = $e;
			}
			
			if ($this->_use_auth && $this->request->is_initial( ) && ! User::check( ))
			{
				$this->_auth_form = Form::factory('auth')
					/** :TODO: switch on activator iss733 **/
					->use_activator(FALSE)
					->show_on_success(TRUE);
					
				$this->_auth_form
					->field('text', __('Логин'), 'username')
						->placeholder(__('Ваш логин или e-mail'))
						->not_empty( )
					->field('password', __('Пароль'), 'password')
						->placeholder(__('Ваш пароль'))
						->not_empty( )
						->rule('auth', NULL, __('Указанная Вами комбинация логина и пароля не зарегистрирована в системе.<br>Вы можете <a href="'.Route::url('recovery').'">восстановить</a> пароль. '))
					->field('hidden', NULL, 'remember')->value('1')
					->field('submit', __('Войти'));
			}
        }
        
        // generate metadata
        
		// init header
		$this->_view_header = View::factory($this->_view_header, array(
			'admin_menu'	=> CMS::instance( )->menu( ),
			'settings'		=> &$this->_settings,
			'breadcrumbs'	=> Breadcrumbs::instance( ),
			'menu'			=> &$this->_menu,
			'is_index'		=> &$this->_is_index,
			'auth'			=> $this->_auth_form,
			'page'			=> $this->_file,
		));
		
		// init footer
		$this->_view_footer = View::factory($this->_view_footer, array(
			'menu'			=> &$this->_menu,
			'breadcrumbs'	=> $this->_view_header->breadcrumbs,
			'is_index'		=> &$this->_is_index,
			'auth'			=> $this->_view_header->auth,
			'page'			=> $this->_file,
		));
		
		// set default page template name
		if ( ! isset($this->_settings['template']) || $this->_settings['template'] == '')
		{
			$this->_settings['template'] = Site::config('site')->view_page_default;
		}
		
		// init body view object and write content
		$this->_view_body = View::factory($this->_settings['template'])->set($this->_content);
		
		// add breadcrumbs to body
		$this->_view_body->breadcrumbs = $this->_view_header->breadcrumbs;
		$this->_view_body->auth = $this->_view_header->auth;
		
		// add link to page object
		$this->_view_body->page = $this->_file;
		
		// add link to menu
		$this->_view_body->bind('menu', $this->_menu);
	}
	
    /**
     * default action
     */
    public function action_index( )
    {
    }

	/**
	 * Check output of authorization form and authorize user
	 * 
	 * @return void
	 */
	protected function _authorize()
	{
		/* authorize user if queried */
		if ($this->_use_auth && $this->_auth_form && ! User::check( ))
		{
			$auth_result = $this->_auth_form->render( );
		
			if ($this->_auth_form->sent( ))
			{
			
				// в $status помещаем результат функции login
				$status = User::instance( )->login($auth_result->username, $auth_result->password, isset($auth_result->remember));

				if ($status === TRUE)
				{
					$redirect_uri = Session::instance( )->get(Site::config('site')->referrer_page_var);

					// redirect to specified page or refresh current
					if ( ! isset($this->auth_redirect_uri) || $this->auth_redirect_uri == '')
					{
						$this->auth_redirect_uri = $this->request->uri( );
					
						$query_string = http_build_query($this->request->query( ));
						
						if ($query_string != '')
						{
							$this->auth_redirect_uri .= '?'.$query_string;
						}
					}
					
					return $this->request->redirect($this->auth_redirect_uri);
				}
				else
				{
					$message = $status;
				}
			}
		}		
	}
	
    public function after( )
    {
		$this->_authorize();
    
		// throw content error
        if ($this->_auto_load && isset($this->_error))
        {
            throw $this->_error;
        }
    
		// render view if used auto_render
        if ($this->_auto_render === TRUE)
        {
			if ( ! $this->request->is_initial( ))
			{
				$this->response->body($this->_view_body, TRUE);
			}
			else
			{
				// add sitename to title
				$this->_settings['title'] .= ($this->_settings['title'] != '' ? ' - ' : '').Site::config()->common_display_name;
			
				$this->response->body("{$this->_view_header}{$this->_view_body}{$this->_view_footer}");
			}
		}

		parent::after( );
    }
}