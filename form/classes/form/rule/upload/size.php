<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Valid rule wrapper
 * @package 	Form
 * @author 		Stanislav U. Alkimovich
 * @date 		2013-06-24
 *
 **/

class Form_Rule_Upload_Size extends Form_Rule
{
	// list of arguments
	public $args = array(
		'obj' 	=> ':value',
		'size' 	=> '1M',
	);

	// error message
	protected $message = ':field filesize can\'t exceed :param1.';

	public static function exec($obj, $size)
	{
		return Upload::size($obj, $size);
	}
}