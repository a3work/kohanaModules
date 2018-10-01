<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Valid rule wrapper
 * @package 	Form
 * @author 		A. St.
 * @date 		2015-04-27
 *
 **/

class Form_Rule_Unique extends Form_Rule
{
	// list of arguments
	public $args = array(
		'obj' => ':value',
		'model' => '',
		'field' => '',
	);

	/** validate data
	 *
	 * @param	string	value
	 * @param	string	model name
	 * @param	string	model
	 * @return	boolean
	 */
	public static function exec($value, $model, $field)
	{
		return (ORM::factory($model)->where($field, '=', $value)->find( )->loaded( ) === FALSE);
	}
}