<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Form element validation rule constructor and superclass
 * @package 	Form
 * @author 		Stanislav U. Alkimovich
 * @date 		2013-06-21
 *
 * To create custom validation rule must declarate static function exec in specified rule class
 **/

abstract class Kohana_Form_Rule
{
	// link to parent Form_Field object
	public $field;

	public $can_extends = TRUE;

	// list of arguments
	public $args = array(
		'obj' => ':value'
	);

	// validate function name
	protected $name;

	// call func name
	protected $func;

	// rule id
	protected $id;

	// specified error message
	protected $message;

	// rules total
	protected static $count = 0;

	/** Get validation rule js
	 *
	 * @example : '.require( );'
	 *
	 * @return string
	 */
	public function js( )
	{
	}

	/** Search Form_Rule subclass and create instance
	 *
	 * @param string rule name
	 * @param Form_Field parent field
	 * @return Form_Rule
	 */
	public static function factory($rule)
	{
		$classname = "Form_Rule_{$rule}";

		// check class existence
		if (class_exists($classname))
		{
			$obj = new $classname;

			// check type
			if ( ! $obj instanceof Form_Rule)
			{

				throw new Form_Exception("Class '{$classname}' not extends Form_Rule.");
			}

			$obj->id(++self::$count);
		}
		else
		{
			throw new Form_Exception("Cannot find '{$classname}' class.");
		}

		return $obj;
	}

	/**
	 * Object constructor
	 */
	public function __construct( )
	{
		$callname = strtolower(str_replace('Form_Rule_', '', get_class($this )));

		$this->name($callname);

		if ($this->func( ) === NULL)
		{
			// check built-in valid method existence
//			if (method_exists('Valid', $callname))
			if (method_exists(get_class($this), 'exec'))
			{
				$this->func(get_class($this)."::exec");
			}
			elseif (method_exists('Valid', $callname))
			{
				$this->func($callname);
			}
			else
			{
				throw new Form_Exception(__('Cannot load rule :rule', array(':rule' => $callname)));
			}
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

			return $this;
		}

		return $this->$var;
	}

	/** Process code after field attachment
	 *
	 * @return void
	 */
	protected function field_mod( )
	{
	}

	/** Attach this rule to specified Form_Field object or get link to it
	 *
	 * @param mixed (Form_Field) object
	 * @return this
	 *
	 * Getter:
	 * @return Form_Base
	 */
	public function field(Form_Field $link = NULL)
	{
		if (isset($link))
		{
			$this->field = $link;

			// modify field params if need
			$this->field_mod( );

			return $this;
		}
		else
		{
			return $this->field;
		}
	}

	/** Merge external and built-in arguments or get it
	 *
	 * Setter:
	 * @param array
	 * @return this
	 *
	 * Getter:
	 * @param string	argument name
	 * @return string
	 *
	 * without args
	 * @return array
	 */
	public function args($args = NULL)
	{
		if (isset($args) && is_array($args))
		{
			$this->args = array_merge($this->args, $args);

			return $this;
		}
		elseif(is_string($args))
		{
			return $this->args[$args];
		}
		else
		{
			return $this->args;
		}
	}
}