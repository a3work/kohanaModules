<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Captcha field description
 * @package 	Form
 * @author 		Stanislav U. Alkimovich
 * @date 		2013-07-08
 *
 **/

class Form_Field_Captcha extends Form_Field
{
	// list of arguments
	public $args = array(
		'obj' => ':value',
	);

	public $view = 'captcha';

	// don't save value after form submit
	public $hold_value = FALSE;
	
	public function on_attach( )
	{
		if ($this->label( ) === NULL)
		{
			$this->label(__('CAPTCHA'));
			$this->header(__('CAPTCHA'));
		}
		
		$this
			->not_empty( )
			->rule('numeric')
			->max_length(2)
			->rule('captcha');
	}

}