<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Valid rule wrapper
 * @package 	Form
 * @author 		Stanislav U. Alkimovich
 * @date 		2013-06-24
 *
 **/

class Form_Rule_Color extends Form_Rule
{
	/** Get validation rule js
	 *
	 * @return string
	 */
	public function js()
	{
		return ".match(/^#(?[0-9a-f]{3}){1,2}$/i);";
	}
}