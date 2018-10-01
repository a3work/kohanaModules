<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Editor: simple text input element
 * @category	Common
 * @package 	Editor
 * @author 		A.St.
 * @date 		2013-05-22
 *
 **/

class Editor_Element_Textarea extends Editor_Base
{
	// form label
	protected $label = 'cms_input';

	/**
	 * Render form
	 *
	 * @return 	string HTML / array values
	 */
	protected function form( )
	{
		$form = Form::factory($this->label( ))->class('editor-element-textarea');
		$textarea = $form->field('textarea', Editor_Base::VALUE_KEY, Editor_Base::VALUE_KEY);
		$textarea->value($this->data( ));
		$form = $form->render($textarea.$form->field('submit', __('save')));

		if ($form->sent( ))
		{
			return $form->result( )->as_array( );
		}

		return $form;
	}
}