<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Valid rule wrapper
 * @package 	Form
 * @author 		Stanislav U. Alkimovich
 * @date 		2013-11-13
 *
 **/

class Form_Rule_Mask extends Form_Rule
{
	// list of arguments
	public $args = array(
		'obj' => ':value',
		'mask' => '',
	);

	/** Get validation rule js
	 *
	 * @return string
	 */
	public function js()
	{
		return ".match(".Form_Rule_Mask::get_regexp($this->args['mask']).");";
	}

	/** convert mask to regexp
	 *
	 * @return string
	 */
	public static function get_regexp($mask)
	{
		$replacements = array(
			'a' => '[A-Za-zА-Яа-я]',
			'9' => '\d',
			'*' => '[A-Za-zА-Яа-я0-9]',
			'(' => '\(',
			')' => '\)',
			'[' => '\[',
			']' => '\]',
			'{' => '\{',
			'}' => '\}',
			'+' => '\+',
		);

		return "/^".str_replace(array_keys($replacements), $replacements, $mask)."$/";
	}
	
	/** validate data
	 *
	 * @return string
	 */
	public static function exec($obj, $mask)
	{
		$regexp = self::get_regexp($mask);

		return preg_match($regexp, $obj);
	}
	
	/** add js mask to field
	 *
	 * @return void
	 */
	protected function field_mod()
	{
// 		if ($this->field( )->form( )->modify_input_length( ) && $this->args('length') <= 32)
// 		{
// 			$this->field( )->length($this->args('length'));

// 		}
	
		$class = $this->id( );

		InclStream::instance( )->add('jquery.maskedinput.min.js');
		InclStream::instance( )->write("$('.$class input').mask('".$this->args('mask')."',{placeholder:' '});", 'js');
		
		$this->field( )->classes(NULL, $class);
		
		// add max-lentgth
		$this->field( )->max_length(strlen($this->args('mask')));
		
		// add placeholder
		if ($this->field( )->placeholder( ) === NULL)
		{
			$this->field( )->placeholder($this->args('mask'));
		}
	}	
}