<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Engine of breadcrumbs 
 * @package 	Site
 * @author 		Stanislav U. Alkimovich <a3.work@gmail.com>
 * @date 		2014-11-21
 *
 **/

class Kohana_Breadcrumbs {

	/**
	 * @var Kohana_Breadcrumbs
	 */
	protected static $_instance;
	
	/**
	 * @var array		items stack
	 */
	protected $_items = array( );

	/**
	 * @var string		HTML output
	 */
	protected $_html;	
	
	/**
	 * Singleton getter
	 * 
	 * @return Kohana_CLI
	 */
	public static function instance( )
	{
		if ( ! isset(self::$_instance))
		{
			self::$_instance = new Breadcrumbs;
		}
		
		return self::$_instance;
	}

	/** Object constructor
	 *
	 * @return void
	 */
	public function __construct( )
	{

	}

	/**
	 * Standart setter/getter
	 *
	 * @param string 	variable name
	 * @param array		parameters
	 *
	 * @return mixed
	 */
	public function __call($var, $args = NULL)
	{
		if (is_array($args) && count($args) > 0)
		{
			$this->$var = $args[0];

			return $this;
		}

		return $this->$var;
	}

	/** Remove breadcrumbs item for specified position 
	 *
	 * @param 	integer	position for removing
	 * @return 	Breadcrumbs
	 */
	public function remove($index = NULL)
	{
		if (empty($index))
		{
			$index = count($this->_items) - 1;
		}
	
		unset($this->_items[$index]);
		
		return $this;
	}

	/** Add element to breadcrumbs
	 *
	 * @param 	string	href
	 * @param 	string	text
	 * @param 	integer	position for insert
	 * @return 	Breadcrumbs
	 */
	public function add($href, $text = NULL, $position = NULL)
	{
		$item = Html::factory('anchor')
				->href($href);
		
		if ($text !== '')
		{
			$item->text($text);
		}
	
		if (isset($position))
		{
			array_splice($this->_items, $position, 0, array($item));
		}
		else
		{
			$this->_items[] = $item;
		}
		
		return $this;
	}
	
	/** Render breadcrumbs
	 *
	 * @param 	mixed			(string) template name or (View) template object
	 * @return 	string			HTML
	 * @throws	Site_Exception
	 */
	public function render($template = NULL)
	{
		/* define template */
		if (isset($template))
		{
			if (is_string($template))
			{
				$template = View::factory($template);
			}
			elseif ( ! $template instanceOf View)
			{
				throw new Site_Exception('Breadcrumbs template must be a View instance.');
			}
		}
		else
		{
			$template = View::factory(Site::config('site')->view_breadcrumbs);
		}
	
		/* bind and render */
		if (empty($this->_html))
		{
			$this->_html = $template->bind('items', $this->_items)->render( );
		}
		
		return $this->_html;
	}
	
	/** String representation of breadcrumbs
	 *
	 * @return 	string
	 */
	public function __toString()
	{
		try
		{
			return $this->render( );
		}
		catch (Exception $e)
		{
			return $e->getMessage( );
		}
	}
}