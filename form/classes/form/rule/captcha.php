<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Valid rule wrapper
 * @package 	Form
 * @author 		Stanislav U. Alkimovich
 * @date 		2013-06-24
 *
 **/

class Form_Rule_Captcha extends Form_Rule
{
	protected $message = ':field must be a correct CAPTCHA value';

	public static function exec($value)
	{
		return Captcha::check($value);
	}
}