<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Base class for custom access templates
 * @package 	Access
 * @author 		Stanislav U. Alkimovich
 * @date 		2013-08-07
 *
 **/
/** :TODO: **/
class Kohana_Access_Template
{
	// template name
	protected $name;

	// link to parent Access_Module
	protected $module;

	// privileges list
	protected $privileges;

	/** Object constructor
	 *
	 * @param	string			template name
	 * @param	Access_Module	link to parent module
	 * @return 	void
	 */
	public function __construct($name)
	{
		$this->name($name);
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

	/** Attach Access_Privilege to current obj
	 *
	 * @param 	string		Access_Privilege name
	 * @return 	void
	 */
	public function attach($name)
	{
		if ($this->module( )->privileges($name) !== NULL)
		{
			$this->privileges($name, $this->module( )->privileges($name));

			return $this;
		}
		else
		{
			throw new Access_Process_Exception('Cannot find privilege ":priv" in module ":class"', array(':priv' => $name, ':class' => get_class($this->module( ))));
		}
	}
}