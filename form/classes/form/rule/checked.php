<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Valid rule wrapper
 * @package 	Form
 * @author 		Stanislav U. Alkimovich
 * @date 		2013-06-24
 *
 **/

class Form_Rule_Checked extends Form_Rule
{
	// list of arguments
	public $args = array(
		// required value
		'obj' => ':value',
	);

	public static function exec( )
	{
		$args = func_get_args( );

		$result = ! in_array($args[0], array(NULL, FALSE, '', array(), Basic::get_hash('')), TRUE);

		return $result;
	}

	/** Get validation rule js
	 *
	 * @return string
	 */
	public function js()
	{
		return ".checked();";
	}
}