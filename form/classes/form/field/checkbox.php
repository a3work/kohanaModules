<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Checkbox field description
 * @package 	Form
 * @author 		Stanislav U. Alkimovich
 * @date 		2013-07-08
 *
 **/

class Form_Field_Checkbox extends Form_Field
{
	// default value
	public $value = 1;
	public $selected = FALSE;

	// input type
// 	public $wrapper = 'form_unit_w_lab';
	public $view = 'checkbox';


// 	protected function defaults( )
// 	{
// 		$this->selected((boolean) $this->selected( ));
// // 		echo 'checkbox: ';
// // 		var_dump((boolean) $this->name);
// // 		var_dump($this->selected( ));
// // 		echo "\n------------------\n";
// // 		if ( ! $this->selected)
// // 		{
// // 			$this->value = NULL;
// // 		}
// 	}

	/** get actual element value for validation
	 * 	for checkbox = value( ) if selected == true
	 *
	 * @return mixed
	 */
	public function val( )
	{
		// get value
		$this->value( );

		return 	  $this->selected( )
				? $this->value( )
				: NULL;
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

	/** Save value in Form_Base::data for external usage
	 *
	 * @return void
	 */
	protected function publish( )
	{
		// save checked values only
		if ($this->selected( ))
		{
			// write to output data array
			$this->form( )->result( )->add($this);
		}
	}

	/** value setter behavior if fetch NULL value (param not found in income values arrya)
	 *
	 * @return void
	 */
	public function null_behavior( )
	{
		$this->selected(FALSE);
	}
}