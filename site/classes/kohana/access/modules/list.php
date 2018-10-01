<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Access modules list holder
 * @package 	Access
 * @author 		Stanislav U. Alkimovich
 * @date 		2013-08-08
 *
 **/

class Kohana_Access_Modules_List implements Iterator
{
	// modules names array
	protected $modules = array( );

	/** ITERATOR IMPLEMENTATION **/
    public function rewind()
    {
        reset($this->modules);
    }

    public function current()
    {
        $key = key($this->modules);

        return $this->get($key);
    }

    public function key()
    {
        $key = key($this->modules);
        return $key;
    }

    public function next()
    {
        $value = next($this->modules);
        return $value;
    }

    public function valid()
    {
        $key = key($this->modules);
        return ($key !== NULL && $key !== FALSE);
    }


	/**
	 * Object constructor
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
		if (is_array($args) && count($args) > 0)
		{
			$this->$var = $args[0];

			return $this;
		}

		return $this->$var;
	}

	/** Add module to list
	 *
	 * @param 	string		module name
	 * @return 	void
	 */
	public function add($module)
	{
		$module = strtolower($module);

		if ( ! isset($this->modules[$module]))
		{
			$this->modules[$module] = NULL;
		}
	}

	/** Get module from list
	 *
	 * @param 	string		module name
	 * @return 	Access_Module
	 */
	public function get($module)
	{
		$module = strtolower($module);

		if (array_key_exists($module, $this->modules))
		{
			if ( ! isset($this->modules[$module]))
			{
				if ( ! class_exists($module))
				{
					throw new Access_Process_Exception('Class ":class" does not exist.', array(':class' => $module));
				}

				$class = new $module;

				if ( ! $class instanceof Access_Module)
				{
					throw new Access_Process_Exception('Class ":class" must be an Access_Module subclass.', array(':class' => $module));
				}

				$this->modules[$module] = $class;
			}

			return $this->modules[$module];
		}
		else
		{
			throw new Access_Process_Exception('Module ":module" does not register.', array(':module' => $module));
		}
	}
}