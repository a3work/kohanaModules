<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Valid rule wrapper
 * @package 	Form
 * @author 		Stanislav U. Alkimovich
 * @date 		2013-06-24
 *
 **/

class Form_Rule_Upload_Image extends Form_Rule
{
	// list of arguments
	public $args = array(
		'obj' 			=> ':value',
		'max_width' 	=> 1900,
		'max_height' 	=> 1080,
	);

	public static function exec($obj, $max_width, $max_height)
	{
		return Upload::image($obj, $max_width, $max_height);
	}
}