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

class Editor_Element_Text extends Editor_Base
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
		$form = Form::factory($this->label( ))->class('editor-element-text');
		$input = $form->field('text', Editor_Base::VALUE_KEY, Editor_Base::VALUE_KEY);
		$input->value($this->data( ));
		$form = $form->render($input.$form->field('submit', __('save')));

		if ($form->sent( ))
		{
			return $form->result( )->as_array( );
		}

		return $form;
	}
}