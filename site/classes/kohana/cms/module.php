<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Base class for modules custom privileges definition
 * @package 	CMS
 * @author 		Stanislav U. Alkimovich
 * @date 		2013-06-20
 *
 **/

abstract class Kohana_CMS_Module
{
	// access module name
	protected $name;

	// parent common CMS_Privilege (for object oriented privileges only)
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

		throw new CMS_Exception('Property ":prop" does not exists', array(':prop' => $var));
	}


	/** Add access template
	 *
	 * @param 	string		name
	 * @return 	CMS_Template
	 */
	public function menu($name)
	{
		if ( ! isset(CMS::$menu[$name]))
		{
			CMS::$menu[$name] = new CMS_Menu($name);
		}

		return CMS::$menu[$name]->module($this);
	}
}