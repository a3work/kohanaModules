<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Form element validation rule
 * @package 	Form
 * @author 		Stanislav U. Alkimovich
 * @date 		2013-06-26
 *
 **/

class Kohana_Form_Relation
{
	// rule label
	protected $label;

	// attached field
	protected $field;

	// rules list
	protected $rules;

	// checking result
	protected $result;

	// array of classes
	public $classes = array( );

	// js expression
	protected $js_expr;

	/** Object constructor
	 *
	 * @param string 	form name
	 * @return void
	 */
	public function __construct($label)
	{
		$this->label($label);
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

	/** Add relation rule to rules list
	 *
	 * @param string 		name
	 * @param string 		id
	 * @param array	 		args
	 * @return Form_Field 	attached field
	 */
	public function rule($name, $id, $args = NULL)
	{
		if ( ! isset($this->rules[$name]))
		{
			$this->rules[$name] = Form_Rule::factory($name)->field($this->field( ));

			$this->id($id);

			if (isset($args))
			{
				$this->rules[$name]->args($args);
			}
		}

		return $this->field( );
	}
}