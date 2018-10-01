<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Valid rule wrapper
 * @package 	Form
 * @author 		Stanislav U. Alkimovich
 * @date 		2013-06-24
 *
 **/

class Form_Rule_User_Activate extends Form_Rule
{
	public static function exec($value)
	{
		return (bool) ORM::factory('personal_user')-> where('username', '=', $value)->where('LOGO','!=','')-> count_all( );
	}
}