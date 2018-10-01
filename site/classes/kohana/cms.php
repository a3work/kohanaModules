<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		CMS common methods and properties
 * @package 	CMS
 * @author 		Stanislav U. Alkimovich
 * @date 		2013-08-16
 *
 **/

class Kohana_CMS
{
	// init flag
	protected static $instance;

	// modules list object
	protected static $modules;

	// menu
	public static $menu = array( );

	/** Object constructor
	 *
	 * @return void
	 */
	public function __construct( )
	{
		// Create instances of modules
		foreach (CMS::modules( ) AS $module)
		{
			// all logic build in modules list
		}
	}

	/** Modules setter
	 *
	 * @param string		Access_Module subclass name
	 * @return void
	 */
	public static function module($param0)
	{
		if ( ! isset(self::$modules))
		{
			self::$modules = new CMS_Modules_List( );
		}

		self::$modules->add($param0);
	}

	/** Module getter
	 *
	 * @param string		Access_Module subclass name
	 * @return array
	 */
	public static function module_get($param0 = NULL)
	{
		return self::$modules->get($param0);
	}

	/** Modules list getter
	 *
	 * @return Access_Modules_List
	 */
	public static function modules()
	{
		return self::$modules;
	}

	/** Create and return CMS instance
	 *
	 * @return 	CMS
	 */
	public static function instance( )
	{
		if ( ! isset(self::$instance))
		{
			self::$instance = new CMS;
		}

		return self::$instance;
	}

	/**
	 * Standart setter/getter
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

 	/** Menu getter
	 *
	 * @return array
	 */
	public function menu( )
	{
		if (acl('cms_menu'))
		{
			if ( ! isset(Controller_Cms::$menu))
			{
				InclStream::instance( )->add('cms.incl.css');
				InclStream::instance( )->add('cms.init.js');

				$menu = CMS::$menu;
				Controller_Cms::$menu = View::factory('cms.menu', array('menu' => $menu))->render( );
			}

			return Controller_Cms::$menu;
		}
		else
		{
			return false;
		}

	}
	
	/** Wrap CMS inline element
	 *
	 * @param 	string		element
	 * @param 	string		classes
	 * @return 	View
	 */
	public static function wrap($text, $classes = array( ), $id = NULL)
	{
		if ( ! Cms::state( ))
		{
			return $text;
		}
		
		if (empty($id))
		{
			$id = Basic::get_hash($text, 'md5', 8);
		}
		
		// include cms css file
		InclStream::instance( )->add('cms.incl.css');
	
		// return view object
		return View::factory('cms.wrapper', array(
			'id' => $id,
			'text' => $text,
			'classes' => $classes,
		));
	}
	
	/** Return cms editor state
	 *
	 * @return 	boolean
	 */
	public static function state( )
	{
		return Cookie::store(Site::config('cms')->edit_switch_var) == 'checked';
	}
}