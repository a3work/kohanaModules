<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Valid rule wrapper
 * @package 	Form
 * @author 		Stanislav U. Alkimovich
 * @date 		2013-08-03
 *
 **/

class Form_Rule_Username_Not_Reserved extends Form_Rule
{
	// list of arguments
	public $args = array(
		'obj' => ':value',
	);

	// error message
	protected $message = ':field must not contain reserved words.';

	public static function exec($value)
	{
		return ! in_array($value, Site::config('user')->reserved_words);
	}
}