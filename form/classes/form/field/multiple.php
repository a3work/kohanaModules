<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Multiple select description
 * @package 	Form
 * @author 		Stanislav U. Alkimovich
 * @date 		2013-07-08
 *
 **/

class Form_Field_Multiple extends Form_Field
{
	// input type
	public $view = 'multiple';
	public $view_opt = 'option';

	// multiple flag
	public $multiple = TRUE;

	public $extendable = TRUE;
}