<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Valid rule wrapper
 * @package 	Form
 * @author 		Stanislav U. Alkimovich
 * @date 		2013-11-13
 *
 **/

class Form_Rule_Time extends Form_Rule
{
	// list of arguments
	public $args = array(
		'obj' => ':value',
	);

	// error message
	protected $message = ':field must be a time';

	/** validate data
	 *
	 * @return string
	 */
	public static function exec($obj)
	{
		return (boolean) preg_match("/^([01][0-9]|2[0-3]):[0-5][0-9]$/", $obj);
	}
}