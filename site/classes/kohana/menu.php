<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Common menu item object
 * @package 	Menu
 * @author 		Stanislav U. Alkimovich
 * @date 		2013-11-19
 *
 * :TODO: may be: destruct child objects after render
 **/

abstract class Kohana_Menu
{
	// Menu item id settings
	const ID_LENGTH = 4;
	const ID_PREFIX = 'm';

	// count of objects
	public static $count = 0;
	
	// Menu item id
	protected $_id;
	
	// link to parent menu obj
	protected $_parent;
	
	// link to representative obj
	protected $_view;
	
	// rendered html
	protected $_view_html;
	
	// link to representative obj
	protected $_key;
	
	// custom item data
	protected $_data;
	
	// loaded flag
	protected $_is_loaded = FALSE;
	
	/* common menu item properties */
	protected $_text;
	protected $_href = '';
	protected $_is_selected = FALSE;
	
	// js action
	protected $_action;
	
	// js action arguments
	protected $_action_args = '[]';
	
	// hash of view string
	protected $_hash;
	
	// list of children
	protected $_list = array( );
	
	// link to last appended item
	protected $_last_item;

// 	/**
// 	 * @var array	instances
// 	 */
// 	public static $_instances = array( );
// 	
	/** Get new representative object
	 *
	 * @return 	Menu
	 */
	abstract protected function _init_view( );
	
	/** Render current Menu view
	 *
	 * @param	mixed	current view
	 * @return mixed
	 */
	abstract protected function _render($view);
	
	/** Render root view
	 *
	 * @param	mixed	current view
	 * @return string
	 */
	abstract protected function _render_root($view);
	
	/** Menu item factory
	 *
	 * @param	string	classname
	 * @param	array	parameters of controller
	 * @return	void
	 */
	public static function factory($classname = NULL, $parameters = array( ))
	{
		if ( ! isset($classname))
		{
			$classname = get_called_class( );
			
			$reflection = new ReflectionClass($classname);
			
			return $reflection->newInstanceArgs($parameters);
		}
		elseif (is_integer($classname))		// backward compatibility with old class Menu 
		{
			$obj = new Menu_Html( );
			
			/* :TODO: database source type */
// 			return $obj->source('db')->id($classname);
			return $obj;
		}
		else
		{
			if (strpos(mb_strtolower($classname), 'menu_') === FALSE)
			{
				$classname = 'menu_'.$classname;
			}
			
			if (class_exists($classname))
			{
				$reflection = new ReflectionClass($classname);
				
				$obj = $reflection->newInstanceArgs($parameters);
				
				if ( ! $obj instanceof Menu)
				{
					throw new Menu_Exception("Class :menu not extends Menu.", array(':menu' => $classname));
				}
			}
			else
			{
				throw new Menu_Exception("Cannot find :menu.", array(':menu' => $classname));
			}
			
			return $obj;
		}
	}
	
	/** Return Menu item id
	 *
	 * @return	mixed
	 */
	public function id($id = NULL)
	{
		if (isset($id))
		{
			if (is_string($id))
			{
				$id = substr(Basic::get_hash($id), 0, Kohana_Menu::ID_LENGTH);
			}
		
			$this->_id(Kohana_Menu::ID_PREFIX.$id);
			
			return $this;
		}
		
		return $this->_id( );
	}
	
	/** Load menu by DB.id
	 * :TODO:
	 * @param	integer	id
	 * @return 	Menu
	 */
// 	public static function load($id, $parent = NULL, $parent_id = NULL)
// 	{
// 		// create menu
// 		$menu = Menu::factory( );
// 	
// 		// load data from DB
// 		$data = ORM::factory('site_menu')->data($id, $parent_id);
// 
// 		foreach ($data AS $item)
// 		{
// 			
// 		}
// 		
// 		return $menu;
// 	}

	/** Object constructor
	 *
	 * @param	string	text of menu item
	 * @param	string	href
	 * @param	boolean	is selected 
	 * @return void
	 */
	public function __construct($text = NULL, $href = NULL, $key = NULL, $action = NULL, boolean $selected = NULL)
	{
		if (isset($text))
		{
			$this->_text($text);
		}

		if (isset($href))
		{
			$this->_href($href);
		}

		if (isset($key))
		{
			$this->_key($key);
		}

		if (isset($action))
		{
			$this->_action($action);
		}

		if (isset($selected))
		{
			$this->_is_selected((boolean) $selected);
		}
		
		$this->_id(Kohana_Menu::ID_PREFIX.(++ Kohana_Menu::$count));
		
		$this->_last_item = $this;
	}
	
	/** Check parent Menu_item existence
	 * 
	 * @return	boolean
	 */
	protected function _is_root()
	{
		return $this->_parent == NULL;
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
	
	/**
	 * convert object to string
	 *
	 * @return string
	 */
	public function __toString( )
	{
		return $this->render(TRUE);
	}
	
	/** Initialize and get view obj
	 *
	 * @param	mixed	view
	 * @return 	Menu
	 */
	protected function _view($view = NULL)
	{
		if (isset($view))
		{
			$this->_view = $view;
			
			return $this;
		}
		
		if ( ! isset($this->_view))
		{
			$this->_view = $this->_init_view( );
		}
		
		return $this->_view;
	}
	
	/** append Menu obj to another Menu obj
	 *
	 * @param	Menu
	 * @return 	Menu	recipient menu obj
	 */
	public function append(Menu $menu)
	{
		$key = count($this->_list);
	
		// add to list
		$this->_list[$key] = $menu;
		
		// last insert item
		$this->_last_item = &$this->_list[$key];
		
		// set parent
		$menu->parent($this);
		
		return $this;
	}

	/** Set parent Menu obj for current menu item or get parent obj 
	 *
	 * @param	Menu	Menu item or count of parent
	 * @return 	Menu	current (recipient) menu obj
	 */
	public function parent($menu = NULL)
	{
		if (is_object($menu) && $menu instanceof Menu)
		{
			$this->_parent($menu);
			
			return $this;
		}
		else
		{
			return $this->_parent( );
		}
	}
	
	/** Return N-th parent of Item
	 *
	 * @param 	integer level
	 * @return 	Menu
	 */
	public function parents($level = 0)
	{
		$obj = $this;
	
		for ($i = 0; $i < $level; $i ++)
		{
			$obj = $obj->parent( );
			
			if ($obj === NULL)
			{
				throw new Menu_Exception('Parent of level :level exists not.', array(':level' => $level));
			}
		}
		
		return $obj;
	}
	
	/** Create child menu item
	 *
	 * All parameters pass to controller
	 * 
	 * @return 	Menu	current Menu obj
	 */
	public function child( )
	{
		// create menu item
		$obj = call_user_func_array(array(get_class($this), 'factory'), array(NULL, func_get_args( )));
		
		// append it to current menu item
		$this->append($obj);
	
		// return child
		return $this;
	}
	
	/** Create child menu item and switch to it
	 *
	 * All parameters pass to controller
	 *
	 * @return 	Menu	new child Menu obj
	 */
	public function submenu( )
	{
		// create menu item
		$obj = call_user_func_array(array(get_class($this), 'factory'), array(NULL, func_get_args( )));
		
		// append it to current menu item
		$this->append($obj);
	
		// return child
		return $obj;
	}
	
	/** Create sibling menu item and switch to it
	 *
	 * All parameters pass to controller
	 *
	 * @return 	Menu	new sibling Menu obj
	 */
	public function sibling( )
	{
		// create menu item
		$obj = call_user_func_array(array(get_class($this), 'factory'), array(NULL, func_get_args( )));
		
		if ($this->_parent( ) === NULL)
		{
			throw new Menu_Exception('Cannot create siblings: parent item exists not.');
		}
		
		// append it to current menu item
		$this->_parent( )->append($obj);
	
		// return child
		return $obj;
	}
	
	/** Create a sibling of parent menu item and switch to it
	 *
	 * All parameters pass to controller
	 *
	 * @return 	Menu	new Menu obj
	 */
	public function uncle( )
	{
		if ($this->_parent( ) === NULL)
		{
			throw new Menu_Exception('Cannot create uncle: parent item exists not.');
		}
		
		// create menu item
		return call_user_func_array(array($this->_parent( ), 'child'), func_get_args( ));
	}
	
	/** Get children of current Menu obj
	 *
	 * @return 	array
	 */
	public function children( )
	{
		return $this->_list( );
	}
	
	/** Set or get text of menu item to View obj
	 *
	 * @param	string	text
	 * @return 	Menu
	 */
	public function text($text = NULL)
	{
		if ($this->_last_item( ) === NULL)
		{
			return $this;
		}
		
		return $this->_last_item( )->_text($text);
	}

	/** Set or get href to View obj
	 *
	 * @param	string	href
	 * @return 	Menu
	 */
	public function href($href = NULL)
	{
		if ($this->_last_item( ) === NULL)
		{
			return $this;
		}
		
		return $this->_last_item( )->_href($href);
	}
	
	/** Add is_selected flag to View obj
	 *
	 * @param	boolean	flag "selected"
	 * @return 	Menu
	 */
	public function is_selected($is_selected = NULL)
	{
		if ($this->_last_item( ) === NULL)
		{
			return $this;
		}
		
		return $this->_last_item( )->_is_selected($is_selected);
	}
	
	/** Set javascript action for current item
	 *
	 * @param	string	js function name
	 * @param	array	js function arguments
	 * @return	string or this obj
	 */
	public function action($action = NULL, $arguments = array( ))
	{
		if ($this->_last_item( ) === NULL)
		{
			return $this;
		}

		$this->_last_item( )->_action($action);
		$this->_last_item( )->_action_args(Basic::json_safe_encode($arguments));

		return $this;
	}
	
	/** Set default action for background execution of specified controller
	 *
	 * @return	string or this obj
	 */
	public function action_bg( )
	{
		return $this->action('Cms.action');
	}
	
	/** Use ajax mode
	 * :TODO: make possible usage for non-context menus
	 * @return 	this
	 **/
	public function ajax( )
	{
		if ($this->_last_item( ) === NULL)
		{
			return $this;
		}
		
		$this->_last_item( )->action_bg( );
		
		return $this;
	}
	
	/** Render view
	 *
	 * @param	boolean	search menu root and render it
	 * @return 	string
	 */
	public function render($search_root = TRUE)
	{
		// go to root if queried child rendering 
		if ($search_root && $this->_parent( ) !== NULL)
		{
			return $this->_parent( )->render( );
		}
	
		// render root item if current Menu object hasn't parent
		if ($this->_is_root( ))
		{
			// set up main ID
			if ($this->id( ) === NULL)
			{
				$debug = debug_backtrace( );
				$this->id(serialize($debug[0]));
			}

			// render view
			$this->_view_html($this->_render_root($this->_view( )));
		}
		else
		{
			// render view
			$this->_view_html($this->_render($this->_view( )));
		}
		
		// check as loaded
		$this->_is_loaded(TRUE);
		
 		return $this->_view_html( );
	}
	
	/** Check loading of current View object
	 *
	 * @return 	boolean
	 */
	public function loaded( )
	{
		return $this->_is_loaded( );
	}
	
	/** Set flag for menu root
	 *
	 * @param 	mixed	key
	 * @param	mixed	value
	 * 
	 */
	public function _root_data($key, $value/* = NULL*/)
	{
// 		if (empty($value))
// 		{
// 			if ($this->parent( ) === NULL && isset($this->_data[$key]))
// 			{
// 				return $this->_data[$key];
// 			}
// 			else
// 			{
// 				return NULL;
// 			}
// 		}
	
		if ($this->parent( ) === NULL)
		{
			$this->_data[$key] = $value;
		}
		else
		{
			$this->parent( )->_root_data($key, $value);
		}
	}
	
	
	/** Separate ids string
	 *
	 * @param 	string	ids
	 * @return 	array
	 */
	public static function ids($string)
	{
		return explode(Site::ID_SEPARATOR, $string);
	}
}