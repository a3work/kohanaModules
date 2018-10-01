<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Basic editor description
 * @package 	Form
 * @author 		Stanislav U. Alkimovich
 * @date 		2013-08-02
 *
 **/

class Form_Field_Editor_Source extends Form_Field
{
	// input type
	public $view = 'editor_source';
	
	protected $settings = array(
		'sourceOnly' => TRUE,
	);
}