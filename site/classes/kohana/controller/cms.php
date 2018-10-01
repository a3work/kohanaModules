<?php defined('SYSPATH') or die('No direct script access.');

class Kohana_Controller_Cms extends Controller_Template
{
	const AJAX_TEMPLATE_VAR_SEPARATOR = ',';
	const AJAX_TEMPLATE_VAR = 'atv';
	const VIEW_MARKER = 'cms-vw';

	public $template = 'cms';

	/**
	 * @var string		view mode (full or simple)
	 */
	protected $_mode;
	
	/**
	 * @var string		opener uri
	 */
	protected $_opener;
	
	/**
	 * @var string		menu text
	 */
	public static $menu;

	/**
	 * @var string		current backurl
	 */
	public $backurl;
	
	/** 
	 *
	 */
	public static function wrap_view_var($marker, $body)
	{
		return '<span class="'. Kohana_Controller_Cms::VIEW_MARKER .' '.$marker.'">'.$body.'</span>';
	}
	
	/** Check route parameter 
	 *  if CMS route switch exists execute parent method 
	 * 
	 * @return	boolean	TRUE = need execute parent
	 */
	protected function _exec_parent( )
	{
		// check "exec_parent" flag
		return ($this->request->param('exec_parent') == CMS::EXEC_PARENT_FLAG);
	}
	
	public function before( )
	{
		parent::before( );
		
		$this->backurl = base64_encode($this->request->uri().'?'.http_build_query($this->request->query()));
		
		InclStream::instance( )->add('cms.js');
		InclStream::instance( )->config(array(
			'refreshUri' => Route::url('cms', array('action' => 'refresh', 'id' => ':id')),
		));

		if (isset($_GET[Site::config('cms')->opener_uri_var]))
		{
			$this->opener = '/'.trim(Security::xss_clean($_GET[Site::config('cms')->opener_uri_var]), '/');
		}
		
		$this->template->menu = '';
		
		// switch CMS view mode
		if ($this->request->param('mode') !== NULL && $this->request->param('mode') == CMS::VIEW_MODE_FULL)
		{
			$this->_mode = CMS::VIEW_MODE_FULL;
		}
		
		$this->template->header = $this->template->body = '';
	}
	
	/** standart Kohana after
	 *
	 * @return	void
	 */
	public function after( )
	{
		// set full template if neec
		if (isset($this->_mode) && $this->_mode == CMS::VIEW_MODE_FULL)
		{
			// full template
			$this->template->set_filename('cms.full');
			
			$this->template->menu = CMS::instance( )->menu( );
		}
		
		// if this request is internal and need render
		if ( ! $this->request->is_initial( ) && $this->auto_render)
		{
			// switch off render and return content
			$this->auto_render = FALSE;
			
			// save view object to body
			$this->response->body($this->template, TRUE);
		}
		
		// for ajax request returns only specified template variables
		if ($this->request->is_ajax( ) && $this->auto_render && $this->request->query(Kohana_Controller_Cms::AJAX_TEMPLATE_VAR) !== NULL)
		{
			// switch off render and return JSON
			$this->auto_render = FALSE;
		
			// get template variables names
			$vars = explode(Kohana_Controller_Cms::AJAX_TEMPLATE_VAR_SEPARATOR, $this->request->query(Kohana_Controller_Cms::AJAX_TEMPLATE_VAR));
			
			if (count($vars) > 0)
			{
				$out = array( );
				
				foreach ($vars AS $var)
				{
					if (isset($this->template->$var))
					{
						$out[$var] = (string) $this->template->$var;
					}
				}
				
				$out = Basic::json_safe_decode($out);
			}

			$this->response->body($out);
		}
		
		/* mark output for ajax */
		if ($this->auto_render)
		{
			InclStream::instance( )->add('cms.view.js');
			
			$this->template->body = Kohana_Controller_Cms::wrap_view_var('body', $this->template->body);
			
			if (isset($this->template->left))
			{
				$this->template->left = Kohana_Controller_Cms::wrap_view_var('left',	$this->template->left);
			}
			
			if (isset($this->template->right))
			{
				$this->template->right = Kohana_Controller_Cms::wrap_view_var('right',	$this->template->right);
			}
		}
		
		parent::after( );
	}

	/** Common login **/
	public function action_login( )
	{
		if (User::check( ))
		{
			$this->request->redirect(URL::base( ));
		}

		$this->template->body = Request::factory(Route::get('login')->uri( ))->execute( )->body( );
	}

	/** Clear cache **/
	public function action_cache( )
	{

	}

	/** Refresh specified view-vars of main view
	 *
	 * @return 	void
	 */
	function action_refresh( )
	{
		if ( ! $this->request->is_ajax( ))
		{
			throw new Access_Exception( );
		}
		
		$this->auto_render = FALSE;
		
		// multiple ids support
		$ids = explode(Site::ID_SEPARATOR, $this->request->param('id'));
		
		if (count($ids) == 0)
		{
			$ids = array('body');
		}
		
		$body = Request::factory(str_replace(URL::site(NULL, 'http'), '', $this->request->referrer( )))->execute( )->body( );
		$out = array();
		
		foreach ($ids AS $id)
		{
			if (isset($body->$id))
			{
				$out[$id] = $body->$id;
			}
		}
		
		$this->response->body(Basic::json_safe_encode($out));
	}
	
	/** Toggle editor **/
	public function action_toggle( )
	{
		// переключаем содержимое куки-связанной переменной
		Cookie::store(Site::config('cms')->edit_switch_var, Cookie::store(Site::config('cms')->edit_switch_var) == 'checked' ? '' : 'checked');

		// URL для редиректа
		$redirect_uri = $this->request->referrer( ) == '' ? URL::base( ) : $this->request->referrer( );

		// переадресуем на реферера
		$this->request->redirect($redirect_uri);

		$this->auto_render = FALSE;
	}
	
	/** 
	 * Clear site cache
	 *
	 * @return 	void
	 */
	public function action_clear_cache( )
	{
		// Delete all cache entries in the default group
		exec('rm -rf '.Kohana::$cache_dir.'/*');
		
		if (function_exists("opcache_get_status"))
		{
			$opcache_config = opcache_get_status();
			
			if (isset($opcache_config['opcache_enabled']) && (bool) $opcache_config['opcache_enabled']) {
				opcache_reset();
			}
		}

		if (function_exists("apc_clear_cache"))
		{
			apc_clear_cache();
		}
	
		// URL для редиректа
		$redirect_uri = $this->request->referrer( ) == '' ? URL::base( ) : $this->request->referrer( );

		// переадресуем на реферера
		$this->request->redirect($redirect_uri);

		$this->auto_render = FALSE;
	}
	
	/** Show cms info module
	 *
	 * @param 	array	information
	 * @return	string
	 */
	public static function info($data)
	{
		InclStream::instance( )->add('cms.info.css');
		
		return View::factory('cms.info', array('data' => $data))->render( );
	}
}