<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Valid rule wrapper
 * @package 	Form
 * @author 		Stanislav U. Alkimovich
 * @date 		2013-06-24
 *
 **/

class Form_Rule_Upload_Not_Empty extends Form_Rule
{
	protected $func = 'Upload::not_empty';

	// error message
	protected $message = ':field must not be empty';
	
	protected function field_mod()
	{
		// check attached field as "fill mandatory"
		$this->field( )->classes(NULL, 'f-not-empty');
	}

	/** Get validation rule js
	 *
	 * @return string
	 */
	public function js()
	{
		return '.require( );';
	}
}