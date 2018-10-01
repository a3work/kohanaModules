<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Submit field description
 * @package 	Form
 * @author 		Stanislav U. Alkimovich
 * @date 		2013-07-08
 *
 **/

class Form_Field_Submit extends Form_Field
{
	// input type
	public $view = 'submit';

	// don't add to result
	public $is_private = TRUE;

	/** change name 
	 * 
	 * @param	Form_Base 	$link
	 * @return 	void
	 */
	public function on_before_attach(Form_Base $link)
	{
		try
		{
			$submit_field = $link->fields('submit');
		}
		catch (Form_Exception $e)
		{
			$submit_field = NULL;
		}
	
		if ($this->is_name_generated( ) === TRUE && $submit_field === NULL)
		{
			$this->name('submit');
		}
	}
	
	/** Label and header setter
	 * 	Checkbox has't label
	 *
	 * Setter:
	 * @param string
	 * @return this
	 *
	 * Getter:
	 * @return string
	 */
	public function label($label = NULL)
	{
		if (isset($label))
		{
			$this->header = $label;
		}

		return NULL;
	}
}