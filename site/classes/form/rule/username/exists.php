<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Valid rule wrapper
 * @package 	Form
 * @author 		Stanislav U. Alkimovich
 * @date 		2013-06-24
 *
 **/

class Form_Rule_Username_Exists extends Form_Rule
{
	// list of arguments
	public $args = array(
		'obj' => ':value',
	);

	// error message
	protected $message = ':field is already exist.';

	public static function exec($value)
	{
		return (ORM::factory('account')->where('username', '=', $value)->count_all( ) == 0);
	}
}