<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Valid rule wrapper
 * @package 	Form
 * @author 		Stanislav U. Alkimovich
 * @date 		2013-06-24
 *
 **/

class Form_Rule_IP extends Form_Rule
{
	// list of arguments
	public $args = array(
		'obj' => ':value',
		'allow_private' => FALSE,
	);

	/** Get validation rule js
	 *
	 * @return string
	 */
	public function js()
	{
		return ".match(/^\d{1,3}\.\d{1,3}.\d{1,3}.\d{1,3}$/);";
	}
}