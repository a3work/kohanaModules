<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Valid rule wrapper
 * @package 	Form
 * @author 		Stanislav U. Alkimovich
 * @date 		2013-06-24
 *
 **/

class Form_Rule_Matches extends Form_Rule
{
	// list of arguments
	public $args = array(
		'obj' => ':validation',
		// check field
		'field' => '',
		// sample field
		'match' => '',
		// label of sample field
		'match_name' => '',
	);

	/** add js mask to field
	 *
	 * @return void
	 */
	protected function field_mod()
	{
		$this->args(array('match_name' => $this->field( )->form( )->fields($this->args('match'))->label( )));
	}
	
	/** Get validation rule js
	 *
	 * @return string
	 */
	public function js()
	{
		return ".match(function(val){return val==\$.v.getValue(\$('[name=\"". $this->field( )->form( )->fields($this->args('match'))->name( )."\"]'))});";
	}
}