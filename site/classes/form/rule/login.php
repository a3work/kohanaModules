<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Valid rule wrapper
 * @package 	Form
 * @author 		Stanislav U. Alkimovich
 * @date 		2015-03-26
 *
 **/

class Form_Rule_Login extends Form_Rule
{
	public $message = ":field is used later.";

	// list of arguments
	public $args = array(
		'obj' => ':value',
	);

	/** validate data
	 *
	 * @return string
	 */
	public static function exec($obj)
	{
		$result = (ORM::factory('account')->where('username', '=', $obj)-> count_all( ) == 0);

		return $result;
	}
}