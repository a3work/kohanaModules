<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Form element behavior action
 * @package 	Form
 * @author 		Stanislav U. Alkimovich
 * @date 		2013-06-26
 *
 **/

abstract class Kohana_Form_Action
{
	// action antipode
	protected $name;

	// action antipode
	protected $antipode;

	// action arguments
	protected $args = array( );

	// action impact to (switch off) validation:
	// FALSE 	- impact if parent condition hold
	// TRUE 	- impact if parent condition not hold
	// NULL 	- never impact to validation
	protected $valid_impact;

	// action js code
	// :TODO: move js to this class code
	// now hold it in form.actions.js
// 	protected string $js;

	/** Object constructor
	 */
	public function __construct( )
	{
		if ($this->name( ) === NULL)
		{
			$this->name(strtolower(str_replace('Form_Action_', '', get_class($this))));
		}
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
		}

		return $this->$var;
	}
}