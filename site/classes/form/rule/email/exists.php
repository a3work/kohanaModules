<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Valid rule wrapper
 * @package 	Form
 * @author 		Stanislav U. Alkimovich
 * @date 		2013-06-24
 *
 **/

class Form_Rule_Email_Exists extends Form_Rule
{
	// list of arguments
	public $args = array(
		'obj' 	=> ':value',
		'valid' => ':validation',
	);

	protected $message = ":field is already exist.";

	public static function exec($value, $validation)
	{
		$result = (ORM::factory('account')->where('email', '=', $value)-> count_all( ) == 0);

		if (isset($validation['email_as_uname']))
		{
			$result = $result && ! in_array($value, Site::config('user')->reserved_words) && (ORM::factory('account')-> where('username', '=', $value)-> count_all( ) == 0);
		}

		return $result;
	}
}