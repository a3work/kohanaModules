<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Valid rule wrapper
 * @package 	Form
 * @author 		Stanislav U. Alkimovich
 * @date 		2013-06-24
 *
 **/

class Form_Rule_Divisible extends Form_Rule
{
	protected $message = ':field must be contained :param1 without remainder';

	// list of arguments
	public $args = array(
		'obj' => ':value',
		'number' => 1,
	);
	
	/** Get validation rule js
	 *
	 * @return string
	 */
	public function js()
	{
		/* :TODO: */
		return ".match('number');";
	}
	
	/** validate data
	 *
	 * @return string
	 */
	public static function exec($obj, $number)
	{
		return $obj % $number == 0;
	}
	
	
}