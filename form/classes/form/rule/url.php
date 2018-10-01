<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Valid rule wrapper
 * @package 	Form
 * @author 		Stanislav U. Alkimovich
 * @date 		2013-06-24
 *
 **/

class Form_Rule_Url extends Form_Rule
{
	// list of arguments
	public $args = array(
		'obj' => ':value',
	);

	/** Get validation rule js
	 *
	 * @return string
	 */
	public function js()
	{
		return ".match('url');";
	}
}