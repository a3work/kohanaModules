<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Form fields shortcuts
 * @package 	Migomdengi/Parser
 * @author 		Stanislav U. Alkimovich
 * @date 		2013-06-15
 *
 **/

abstract class Kohana_Form_Field_Shortcut
{
	abstract function field( );

	/** text field shortcut
	 *
	 * @param string 	label
	 * @param string 	variable
	 * @return this
	 */
	public function text($label = NULL, $var = NULL)
	{
		return $this->field('text', $label, $var);
	}
}