<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Checkbox group field description
 * @package 	Form
 * @author 		Stanislav U. Alkimovich
 * @date 		2013-07-08
 *
 **/

class Form_Field_Checkbox_Group extends Form_Field
{
	// views
	public $view = 'checkbox_group';
	public $view_opt = 'checkbox_group_item';
	public $view_optgroup = 'checkbox_optgroup';

		// multiple flag
	public $multiple = TRUE;

	public $extendable = TRUE;

	// default selected value (if selected does not use -- must set to true for correct form processing in "return_changes" mode)
	public $selected = TRUE;
}