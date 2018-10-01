<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Valid rule wrapper
 * @package 	Form
 * @author 		Stanislav U. Alkimovich
 * @date 		2013-06-24
 *
 **/

class Form_Rule_Auth extends Form_Rule
{
	// list of arguments
	public $args = array(
		'obj' 	=> ':value',
		'valid' => ':validation',
	);

	/**
	 * Object constructor
	 */
	public function __construct( )
	{
		parent::__construct( );
		
		$this->message = __('Wrong password of account is not exist. You can <a href=":recover">recover</a> your account or <a href=":reg">register</a>.', array(':recover' => Route::url('recovery'), ':reg' => Route::url('default', array('page'=>'reg'))));
	}

	public static function exec($value, $validation)
	{
		$result = (ORM::factory('account')
					->where('username', '=', $validation['username'])
					->where('password', '=', User::hash($validation['password']))
					->count_all( ) != 0);

		return $result;
	}
}