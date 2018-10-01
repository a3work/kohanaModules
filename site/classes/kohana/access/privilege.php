<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Access privilege properties wrapper
 * @package 	Access
 * @author 		Stanislav U. Alkimovich
 * @date 		2013-07-16
 *
 **/

class Kohana_Access_Privilege
{
	// privilege name
	protected $name;

	// privilege label
	protected $label;

	// allow this action from guest
	protected $allow_guest = FALSE;

	// is_object
	protected $objected = FALSE;

	// link to module
	protected $module;

	// default privilege flag
	protected $defaults = FALSE;

	// hidden privilege flag: only root user can set it
	protected $hidden = FALSE;

	/** Object constructor
	 *
	 * @param string			name / label
	 * @param Access_Module		parent Access_Module
	 * @return void
	 */
	public function __construct($name, Access_Module $module)
	{
		$this->name($name);
		$this->label($name);
		$this->module($module);
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

	/** Object serialization
	 *
	 * @return string
	 */
	public function __toString( )
	{
		return $this->label( );
	}

	/** Set up this privilege as parent (switch) of object privileges
	 *
	 * @return this
	 */
	public function obj_parent( )
	{
		if ($this->objected( ))
		{
			throw new Access_Exception('Cannot set object privilege ":name" as parent.', array(':name' => $this->name( )));
		}

		$this->module( )->parent($this);

		return $this;
	}

	/** Add this to list of defaults privileges for new users (for non-obj privilege) or new object
	 *
	 * @return this
	 */
	public function defaults( )
	{
		if ($this->objected( ))
		{
			$this->module( )->privileges_obj_def($this->name( ), $this);
		}
		else
		{
			Access_Module::privileges_def($this->name( ), $this);
		}

		$this->defaults = TRUE;

		return $this;
	}
}