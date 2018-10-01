<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Select description
 * @package 	Form
 * @author 		Stanislav U. Alkimovich
 * @date 		2013-07-08
 *
 **/

class Form_Field_Chosen extends Form_Field
{
	// options quantity for enable search
	const DISABLE_SEARCH_THRESHOLD = 10;

	// input type
	public $view = 'chosen';
	public $view_opt = 'option';

	public $extendable = TRUE;
	
	/** on attach to form add chosen js and css to includes stream
	 *
	 * @return 	void
	 **/
	public function on_attach( )
	{
		InclStream::instance( )->add('chosen.jquery.min.js');
		InclStream::instance( )->add('chosen.ajaxaddition.jquery.js');
		InclStream::instance( )->add('chosen.init.js');
		InclStream::instance( )->add('chosen.css');
	}

}