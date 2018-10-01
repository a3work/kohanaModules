<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Valid rule wrapper
 * @package 	Form
 * @author 		Stanislav U. Alkimovich
 * @date 		2013-06-24
 *
 **/

class Form_Rule_Max_Length extends Form_Rule
{
	// list of arguments
	public $args = array(
		'obj' => ':value',
		'length' => NULL,
	);

	protected function field_mod()
	{
		if ($this->field( )->form( )->modify_input_length( ) && $this->args('length') <= 32)
		{
			$this->field( )->length($this->args('length'));

			$class = $this->id( );

			InclStream::instance( )->write("input[name=\"".$this->field( )->name( )."\"] {width:".($this->args('length') >= 6 ? $this->args('length') * Site::config('form')->sign_width : $this->args('length') * floor(Site::config('form')->sign_width * 2))."px !important}\n", 'css');

			$this->field( )->classes(NULL, $class);
		}
	}

	/** Get validation rule js
	 *
	 * @return string
	 */
	public function js()
	{
		return ".maxLength(".$this->args('length').");";
	}
}