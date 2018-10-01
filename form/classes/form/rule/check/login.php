<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Valid rule wrapper
 * @package 	Form
 * @date 		2013-06-24
 *
 **/

class Form_Rule_Check_Login extends Form_Rule
{
	protected $message = 'invalid login or password';
	
	// list of arguments
	public $args = array(
		'obj' => ':validation',
		'username_field' => '',
		'password_field' => '',
	);

	public static function exec($validation, $username_field, $password_field)
	{
     return User::instance( )->process_login($validation[$username_field], $validation[$password_field]);
	}
}