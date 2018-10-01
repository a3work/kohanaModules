<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Form CAPTCHA
 *
 * @package    Kohana/Form
 * @category   Controllers
 * @author     	 A.St.
 */
 
 class Controller_Captcha extends Controller
 {
	/**
	 * Вывод каптчи для определённой формы
	 *
	 * @return void
	 */
	 public function action_index( )
	 {
		$captcha = new Captcha( );

		$this->response->headers('content-type',  'image/jpeg');
		
		$captcha->publish( );
	 }
 }