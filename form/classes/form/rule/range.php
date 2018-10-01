<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Valid rule wrapper
 * @package 	Form
 * @author 		Stanislav U. Alkimovich
 * @date 		2013-06-24
 *
 **/

class Form_Rule_Range extends Form_Rule
{
	// list of arguments
	public $args = array(
		'obj' => ':value',
		// sample field
		'min' => NULL,
		// check field
		'max' => NULL,
	);

	/** Get validation rule js
	 *
	 * @return string
	 */
	public function js()
	{
		return ".range(".$this->args('min').", ".$this->args('max').");";
	}
}