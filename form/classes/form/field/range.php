<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Range field
 * @package 	Form
 * @author 		A. St.
 * @date 		2015-05-03
 * @use			jRange plugin by Nitin Hayaran (http://nitinhayaran.github.io/jRange/demo/)
 *
 **/

class Form_Field_Range extends Form_Field
{
	// input type
	public $view = 'range';
	
	/**
	 * @var array		settings
	 */
	protected $_settings = array( );
	
	public function __construct($name)
	{
		parent::__construct($name);

		InclStream::instance( )->add('jquery.range-min.js', FALSE, 1);
		InclStream::instance( )->add('jquery.range.css', FALSE, 1);
	}
}