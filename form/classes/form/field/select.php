<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Select description
 * @package 	Form
 * @author 		Stanislav U. Alkimovich
 * @date 		2013-07-08
 *
 **/

class Form_Field_Select extends Form_Field
{
	// input type
	public $view = 'select';
	public $view_opt = 'option';

	public $extendable = TRUE;
}