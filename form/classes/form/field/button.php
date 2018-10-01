<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Submit field description
 * @package 	Form
 * @author 		Stanislav U. Alkimovich
 * @date 		2013-07-08
 *
 **/

class Form_Field_Button extends Form_Field
{
	// input type
	public $view = 'button';

	// don't add to result
	public $is_private = TRUE;

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