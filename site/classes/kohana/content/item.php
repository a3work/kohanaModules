<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Site content items manipulator
 *
 * @package    Kohana/Site
 * @author     A.St.
 *
 *
 */
class Kohana_Content_Item
{
    protected $_data = null;

    protected $meta;

    public function __construct( )
    {
    }

	public function __call($prop, $param)
	{
		return $this->$prop;
	}

	/**
	 * Выдаём метаданные
	 *
	 * @return string
	 */
	public function meta( )
	{
		if ( ! isset($this->meta))
		{
			if ( ! isset($this->title) || ! isset($this->descr) || ! isset($this->kw))
			{
				$this->error = 'Undefined meta';

				return $this;
			}

			$this->meta = View::factory(Site::config('site')->view_name_meta, array(
				'title' 	=> $this->title != '' ? $this->title : Site::config( )->default_title,
				'descr' 	=> $this->descr != '' ? $this->descr : Site::config( )->default_descr,
				'kw' 		=> $this->kw != '' ? $this->kw : Site::config( )->default_kw,
			));
		}

		return $this->meta;
	}

	/**
	 * Выдаём заголовок страницы
	 *
	 * @return string
	 */
	public function title( )
	{
		return $this->title != '' ? $this->title : Site::config( )->default_title;
	}

	/**
	 * Выдаём description
	 *
	 * @return string
	 */
	public function descr( )
	{
		return $this->descr != '' ? $this->descr : Site::config( )->default_descr;
	}

	/**
	 * Выдаём keywords
	 *
	 * @return string
	 */
	public function kw( )
	{
		return $this->kw != '' ? $this->kw : Site::config( )->default_kw;
	}

	/**
	 * Выдаём шаблон
	 *
	 * @return string
	 */
	public function view( )
	{
		return $this->view != '' ? $this->view : Site::config('site')->view_name_main;
	}

	/**
	 * Get header
	 *
	 * @return string
	 */
	public function header( )
	{
		return Editor::factory('Content_Editor_Header')->id($this->id( ))->wrap($this->header);
	}

	/**
	 * Get body
	 *
	 * @return string
	 */
	public function body( )
	{
		return Editor::factory('Content_Editor_Body')->id($this->id( ))->wrap($this->body);
	}

	/**
	 * Get body
	 *
	 * @return string
	 */
	public function side( )
	{
		return Editor::factory('Content_Editor_Side')->id($this->id( ))->wrap($this->side);
	}

	/**
	 * Get date
	 *
	 * @return string
	public function created( )
	{
		return Editor::factory('Content_Editor_Body')->id($this->id( ))->wrap($this->header);
	}
	 */
}
