<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Valid rule wrapper
 * @package 	Form
 * @author 		Stanislav U. Alkimovich
 * @date 		2013-06-24
 *
 **/

class Form_Rule_Username_Unique extends Form_Rule
{
	protected $message = 'user is already exists';
	
	// list of arguments
	public $args = array(
		'obj' 		=> ':value',
	);

	public static function exec($username)
	{
		User::instance()->process_login($username, false);
		
		return User::instance()->last_auth_user() === NULL || User::instance()->last_auth_user()->loaded() === FALSE;
	}
}