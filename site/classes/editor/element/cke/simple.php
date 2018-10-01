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

class Editor_Element_CKE_Simple extends Editor_Base
{
	// form label
	protected $label = 'cms_cke_simple';

	/**
	 * Render form
	 *
	 * @return 	string HTML / array values
	 */
	protected function form( )
	{
		$form = Form::factory()->use_activator(FALSE)->field('editor_simple', NULL, 'data')->value($this->data( ))->render( );

		if ($form->sent( ))
		{
			return $form->as_array( );
		}

		return $form;
	}
}