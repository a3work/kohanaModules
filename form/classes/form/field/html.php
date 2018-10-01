<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		external html
 * @package 	Form
 * @author 		A. St.
 * @date 		2015-06-01
 *
 **/

class Form_Field_Html extends Form_Field
{
	// input type
	public $view = 'html';
	
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