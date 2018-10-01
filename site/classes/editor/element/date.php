<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Editor: float values input
 * @category	Common
 * @package 	Editor
 * @author 		A.St.
 * @date 		2013-05-22
 *
 **/

class Editor_Element_Date extends Editor_Base
{
	// form label
	protected $label = 'cms_date';

	/**
	 * Render form
	 *
	 * @return 	string HTML / array values
	 */
	protected function form( )
	{
// 		$data = number_format($this->data( ), 2, '.', '');

		Form::add_data($this->label( ), array('data' => $this->data( )));

		$html = Form::render($this->label( ));

		$result = Form::get_data($this->label( ));

		if (is_array($result))
		{
			return $result;
		}

		return $html;
	}
}