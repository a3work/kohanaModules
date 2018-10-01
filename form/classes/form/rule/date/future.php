<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Valid rule wrapper
 * @package 	Form
 * @author 		Stanislav U. Alkimovich
 * @date 		2013-06-24
 *
 **/

class Form_Rule_Date_Future extends Form_Rule
{
	protected $message = ':field must be a future date';
	
	// list of arguments
	public $args = array(
		'obj' => ':value',
	);
	/** Get validation rule js
	 *
	 * @return string
	 */
	public function js()
	{
// 		:TODO:
		return ".match(/^([012]\d|30|31)\.([01]\d)\.\d{1,4}$/);ALERT('TODO Rule_Date_Future');";
	}
	
	/** validate data
	 *
	 * @return string
	 */
	public static function exec($obj)
	{
		$obj = explode('-', $obj);
		return mktime(0, 0, 0, $obj[1], $obj[2], $obj[0]) > time();
	}
}