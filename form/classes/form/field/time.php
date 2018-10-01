<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Time field description
 * @package 	Form
 * @author 		Stanislav U. Alkimovich
 * @date 		2014-01-24
 *
 **/

class Form_Field_Time extends Form_Field
{
	// input type
	public $view = 'time';

	public function on_attach( )
	{
		$this->placeholder('00:00');
		$this->mask('99:99');
		$this->rule('time');
	}
	
// 	public function __construct($name)
// 	{
// 		parent::__construct($name);

		/* :TODO: attach jquery ui */
// 		InclStream::instance( )->jqueryui( );
// 		InclStream::instance( )->add('form.time.init.js', FALSE, -1);
// 	}
}