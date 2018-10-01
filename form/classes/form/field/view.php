<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Text field description
 * @package 	Form
 * @author 		Stanislav U. Alkimovich
 * @date 		2013-07-08
 *
 **/

class Form_Field_View extends Form_Field
{
	// input type
	public $view = 'view';
	
	/**
	 * @var string		view text
	 */
	protected $_text;
}