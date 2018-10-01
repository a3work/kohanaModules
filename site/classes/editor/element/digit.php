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

class Editor_Element_Digit extends Editor_Base
{
	// form label
	protected $label = 'cms_input';
	
	/**
	 * @var string		unit for field
	 */
	protected $unit = '';

	/**
	 * @var integer		length for field
	 */
	protected $length = 2;

	/**
	 * Render form
	 *
	 * @return 	string HTML / array values
	 */
	protected function form( )
	{
		$form = Form::factory($this->label( ))->class('editor-element-text');
		$input = $form->field('text', Editor_Base::VALUE_KEY, Editor_Base::VALUE_KEY)
					->unit($this->unit( ))
					->mask(str_repeat('9', $this->length( )));
		$input->value($this->data( ));
		$form = $form->render($input.$form->field('submit', __('save')));

		if ($form->sent( ))
		{
			return $form->result( )->as_array( );
		}

		return $form;
	}
}