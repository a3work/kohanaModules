<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Site menu items manipulator
 *
 * @package    Kohana/Site
 * @author     A.St.
 *
 *
 */
class Kohana_Menu_Item
{
	public function __construct( )
	{

	}

	public function __call($prop, $param)
	{
		return $this->$prop;
	}

	/**
	 * Определяем шаблон
	 */
	public function template( )
	{
		return $this->template != '' ? $this->template : Site::config('site')->view_name_menu_item;
	}

	/**
	 * Записываем данные в шаблон
	 */
	public function render( )
	{
		return View::factory($this->template( ), array(
			'header' 	=> $this->header( ),
			'href'		=> $this->href( )
		));
	}
}
