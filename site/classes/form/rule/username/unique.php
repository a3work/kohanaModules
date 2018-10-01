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
	// list of arguments
	public $args = array(
		'obj' => ':value',
	);

	public static function exec($value, $current)
	{
		return ($value == $current || ORM::factory('account')->where('username', '=', $value)->count_all( ) == 0);
	}
}