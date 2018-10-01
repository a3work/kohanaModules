<?php
/**
 *
 * @name		Window constructor
 * @package 	Site
 * @author 		Stanislav U. Alkimovich <a3.work@gmail.com>
 * @date 		20.07.14
 *
 **/
class Kohana_Window {

	/**
	 * @var string		window js-creator name (will be declarated in site.win.{$this->_config}.js and named site_win_{$this->_config})
	 */
	protected $_config;
	
	/**
	 * @var array		window attributes
	 *					available:
	 *						(int) width -- window width
	 *						(int) height -- window height
	 *						(boolean) draggable
	 *						(boolean) resizable
	 *						(boolean) maximizable
	 *						(boolean) minimizable
	 *						(boolean) showModal
	 *						(boolean) bookmarkable
	 *						@see http://fstoke.me/jquery/window/
	 */
	protected $_attributes = array( );
	
	
	const WIN_WRAP_FILE 	= 'site.window.wrap';
	const WIN_CONF_FILE 	= 'site.win.*.js';
	const WIN_CONF_DEFAULT	= 'common';

	/** Window factory
	 *
	 * @return	void
	 */
	public static function factory( )
	{
		$classname = get_called_class( );
		
		$reflection = new ReflectionClass($classname);
		
		return $reflection->newInstanceArgs(func_get_args( ));
	}
	
	/** Window constructor
	 *
	 * @param 	string	config name
	 * @param 	array	array of attributes
	 * @return 	void
	 **/
	public function __construct($config = NULL, $attributes = array( ))
	{
		// save attributes
		$this->_attributes = $attributes;
	
		if (isset($config))
		{
			if (is_array($config))
			{
				$this->_attributes = $config;
			}
			else
			{
				$this->_config	= $config;
			}
		}
		
		// include js library and css
		InclStream::instance( )->jqueryui( );
		InclStream::instance( )->add('jquery.window.min.js');
		InclStream::instance( )->add('jquery.window.css');
		
		// generate code
		$this->_code( );
	}
	
	/** Return window js code
	 *
	 * @return 	string
	 **/
	public function code( )
	{
		return $this->_code( );
	}
	
	/** Bind click handler for specified selector
	 *
	 * @param 	string	jquery selector
	 * @return 	this
	 **/
	public function bind($id)
	{
		$wrap_file = Kohana::find_file('media/js', Kohana_Window::WIN_WRAP_FILE, 'js');
		
		if ( ! $wrap_file)
		{
			throw new Exception('Cannot find wrapper :file', array(':file' => Kohana_Window::WIN_WRAP_FILE.'.js'));
		}
		
		/* load wrapper */
		ob_start( );
		
		$code = $this->_code( );
		
		// write id and code
		include $wrap_file;
		
		$out = ob_get_contents( );
		ob_end_clean( );
		
		// write js
		InclStream::instance( )->write($out);
		
		return $this;
	}

	/** Get window call string
	 *
	 * @param	string	window url
	 * @param	string	window title
	 * @param	string	caller object js var
	 * @return 	string
	 */
	public function call($title = 'title', $url = 'href', $obj = 'obj')
	{
		$options = str_replace('"', "'", Basic::json_safe_encode($this->_attributes));
		return $this->_code( ).'(obj,\''.$title.'\', href, '.$options.');';
	}
	
	
	/** Generate javascript window code
	 *
	 * @return 	RETURN
	 **/
	protected function _code( )
	{
		if ( ! isset($this->_code))
		{
			$config = $this->_config;
		
			// load defaults
			if (empty($config))
			{
				$config = Kohana_Window::WIN_CONF_DEFAULT;
			}
		
			// fetch config filename
			if (strpos($config, 'js') === FALSE)
			{
				// attach preset config
				$config = str_replace('*', $config, Kohana_Window::WIN_CONF_FILE);
			}

			// attach window config
			InclStream::instance( )->add($config);
			
			// write funcname
			$this->_code = str_replace(array('.js', '.'), array('', '_'), $config);
		}
		
		return $this->_code;
	}
}