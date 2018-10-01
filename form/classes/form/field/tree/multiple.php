<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Multiple tree field description
 * @package 	Form
 * @author 		Stanislav U. Alkimovich
 * @date 		2013-07-08
 *
 **/

class Form_Field_Tree_Multiple extends Form_Field
{
	// input type
	public $type = 'tree_multiple';

	// multiple flag
	public $multiple = TRUE;

	public $extendable = TRUE;
}