<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Textarea field description
 * @package 	Form
 * @author 		Stanislav U. Alkimovich
 * @date 		2013-07-08
 *
 **/

class Form_Field_Textarea extends Form_Field
{
	// input type
	public $view = 'textarea';
	
	public function on_attach( )
	{
		$this->classes(NULL, 'form-ta');
	}

}