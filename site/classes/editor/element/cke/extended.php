<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Editor: simple CKEditor
 * @category	Common
 * @package 	Editor
 * @author 		Stanislav U. Alkimovich
 * @date 		2013-06-03
 *
 **/

class Editor_Element_CKE_Extended extends Editor_Base
{
	// form label
	protected $label = 'cms_cke_extended';

	/**
	 * Render form
	 *
	 * @return 	string HTML / array values
	 */
	protected function form( )
	{
		$form = Form::factory()->use_activator(FALSE)->show_on_success(TRUE)->field('editor_extended', NULL, 'data')->value($this->data( ))->render( );

		if ($form->sent( ))
		{
			return $form->as_array( );
		}

		return $form;
	}
}