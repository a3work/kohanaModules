<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Base class for modules custom privileges definition
 * @package 	Access
 * @author 		Stanislav U. Alkimovich
 * @date 		2013-06-20
 *
 **/

abstract class Kohana_Access_Module {

	// access module name
	protected $name;

	// privileges list
	protected $privileges = array( );

	// object privileges list
	protected $privileges_obj = array( );

	// default privileges list
	protected static $privileges_def = array( );

	// default privileges list
	protected $privileges_obj_def = array( );

	// current privilege
	protected $current;

	// parent common Access_Privilege (for object oriented privileges only)
	protected $parent;

	// is module objected
	protected $is_obj;

	/** Object factory
	 *
	 *

	/**
	 * Object constructor
	 *
	 */
	public function __construct()
	{
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
		if (property_exists($this, $var))
		{
			if (is_array($args) && count($args) > 0)
			{
				$this->$var = $args[0];

				return $this;
			}

			return $this->$var;
		}

		if ($var != 'current' && isset($this->current) && (method_exists($this->current, $var) || property_exists($this->current, $var)))
		{
			return call_user_func_array(array($this->current, $var), $args);
		}

		throw new Access_Exception('Property ":prop" does not exists', array(':prop' => $var));
	}


	/** Standart array setter / getter
	 * 	Merge external and existent privileges or get it
	 *
	 * Setter:
	 * - one argument:
	 * @param array
	 * @return this
	 *
	 * - many arguments:
	 * @param mixed		key (if null generate automatic)
	 * @param mixed		value
	 *
	 * Getter:
	 * @param string	argument name
	 * @return string
	 *
	 * without args
	 * @return array
	 */
	public function privileges($param0 = NULL, $param1 = NULL)
	{
		if (isset($param0))
		{
			// array setter mode
			if (is_array($param0))
			{
				$this->privileges = array_merge($this->privileges, $param0);

				return $this;
			}
			else
			{
				// single getter mode
				if ( ! isset($param1))
				{
					if (isset($this->privileges[$param0]))
					{
						return $this->privileges[$param0];
					}
					else
					{
						return NULL;
					}
				}
				// single setter mode
				else
				{
					$this->privileges[$param0] = $param1;
				}
			}
		}
		else
		{
			// array getter mode
			if ( ! isset($param1))
			{
				return $this->privileges;
			}
			// single setter mode with numeric key
			else
			{
				$this->privileges[] = $param1;
			}
		}

		return $this;
	}

	/** Standart array setter / getter
	 * 	Merge external and existent object privileges or get it
	 *
	 * Setter:
	 * - one argument:
	 * @param array
	 * @return this
	 *
	 * - many arguments:
	 * @param mixed		key (if null generate automatic)
	 * @param mixed		value
	 *
	 * Getter:
	 * @param string	argument name
	 * @return string
	 *
	 * without args
	 * @return array
	 */
	public function privileges_obj($param0 = NULL, $param1 = NULL)
	{
		if (isset($param0))
		{
			// array setter mode
			if (is_array($param0))
			{
				$this->privileges_obj = array_merge($this->privileges_obj, $param0);

				return $this;
			}
			else
			{
				// single getter mode
				if ( ! isset($param1))
				{
					if (isset($this->privileges_obj[$param0]))
					{
						return $this->privileges_obj[$param0];
					}
					else
					{
						return NULL;
					}
				}
				// single setter mode
				else
				{
					$this->privileges_obj[$param0] = $param1;
				}
			}
		}
		else
		{
			// array getter mode
			if ( ! isset($param1))
			{
				return $this->privileges_obj;
			}
			// single setter mode with numeric key
			else
			{
				$this->privileges_obj[] = $param1;
			}
		}

		return $this;
	}

	/** Standart array setter / getter
	 * 	Merge external and existent default privileges or get it
	 *
	 * Setter:
	 * - one argument:
	 * @param array
	 * @return this
	 *
	 * - many arguments:
	 * @param mixed		key (if null generate automatic)
	 * @param mixed		value
	 *
	 * Getter:
	 * @param string	argument name
	 * @return string
	 *
	 * without args
	 * @return array
	 */
	public static function privileges_def($param0 = NULL, $param1 = NULL)
	{
		if (isset($param0))
		{
			// array setter mode
			if (is_array($param0))
			{
				self::$privileges_def = array_merge(self::$privileges_def, $param0);

				return $this;
			}
			else
			{
				// single getter mode
				if ( ! isset($param1))
				{
					if (isset(self::$privileges_def[$param0]))
					{
						return self::$privileges_def[$param0];
					}
					else
					{
						return NULL;
					}
				}
				// single setter mode
				else
				{
					self::$privileges_def[$param0] = $param1;
				}
			}
		}
		else
		{
			// array getter mode
			if ( ! isset($param1))
			{
				return self::$privileges_def;
			}
			// single setter mode with numeric key
			else
			{
				self::$privileges_def[] = $param1;
			}
		}
	}

	/** Standart array setter / getter
	 * 	Merge external and existent default privileges or get it
	 *
	 * Setter:
	 * - one argument:
	 * @param array
	 * @return this
	 *
	 * - many arguments:
	 * @param mixed		key (if null generate automatic)
	 * @param mixed		value
	 *
	 * Getter:
	 * @param string	argument name
	 * @return string
	 *
	 * without args
	 * @return array
	 */
	public function privileges_obj_def($param0 = NULL, $param1 = NULL)
	{
		if (isset($param0))
		{
			// array setter mode
			if (is_array($param0))
			{
				$this->privileges_obj_def = array_merge($this->privileges_obj_def, $param0);

				return $this;
			}
			else
			{
				// single getter mode
				if ( ! isset($param1))
				{
					if (isset($this->privileges_obj_def[$param0]))
					{
						return $this->privileges_obj_def[$param0];
					}
					else
					{
						return NULL;
					}
				}
				// single setter mode
				else
				{
					$this->privileges_obj_def[$param0] = $param1;
				}
			}
		}
		else
		{
			// array getter mode
			if ( ! isset($param1))
			{
				return $this->privileges_obj_def;
			}
			// single setter mode with numeric key
			else
			{
				$this->privileges_obj_def[] = $param1;
			}
		}
	}

	/** Add privilege to list
	 *
	 * @param string	privilege key
	 * @param boolean 	is object
	 * @return this
	 */
	public function add($privilege_key, $is_obj = FALSE)
	{
		// create
		$privilege = new Access_Privilege($privilege_key, $this);

		// set objected flag
		$privilege->objected($is_obj);

		// add to array
		if ($is_obj)
		{
// 			if ( ! ($this instanceOf Access_Objected_Module))
// 			{
// 				throw new Access_Process_Exception('Module ":name" must implement Access_Objected_Module.', array(':name' => get_class($this)));
// 			}

			$this->privileges_obj($privilege_key, $privilege);
		}
		else
		{
			$this->privileges($privilege_key, $privilege);
		}

		// check privilege as current
		$this->current($privilege);

		return $this;
	}

	/** Add access template
	 *
	 * @param 	string		name
	 * @return 	Access_Template
	 */
	public function template($name)
	{
		if ( ! isset(Access::$templates[$name]))
		{
			Access::$templates[$name] = new Access_Template($name);
		}

		return Access::$templates[$name]->module($this);
	}

	/** Get privileges list */


	/** Current element address */
}