<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Valid rule wrapper
 * @package 	Form
 * @author 		Stanislav U. Alkimovich
 * @date 		2013-06-24
 *
 **/

class Form_Rule_Credit_Card extends Form_Rule
{
	// list of arguments
	public $args = array(
		// типы кредитных карт
		'obj' => ':value',
		'type' => NULL,
	);

	/** Get validation rule js
	 *
	 * @return string
	 */
	public function js()
	{
		return ".match('credit_card');";
	}
}