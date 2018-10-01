<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Valid rule wrapper
 * @package 	Form
 * @author 		Stanislav U. Alkimovich
 * @date 		2013-06-24
 *
 **/

class Form_Rule_Decimal extends Form_Rule
{
	// list of arguments
	public $args = array(
		'obj' => ':value',
		'places' => 2,
		'digit' => NULL,
	);

	/** Get validation rule js
	 *
	 * @return string
	 */
	public function js()
	{
		return ".match(/^[0-9]{".$this->args('digit')."}(?\.|,[0-9]{".$this->args('places')."})$/i);";
	}
	
	/** modify decimal point according to the locale settings
	 *
	 * @return void
	 */
	protected function field_mod()
	{
		$settings = localeconv();
		$decimal_point = $settings['decimal_point'];

		$this->field( )->filter_in(
			function($value, $decimal_point)
			{
				$val = str_replace('.', $decimal_point, $value);
				return $val;
			},
			array(
				'decimal_point'		=> $decimal_point,
			)
		);
	}
}