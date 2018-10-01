<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Pagination common functions
 * @package 	Site
 * @author 		Stanislav U. Alkimovich
 * @date 		2013-10-21
 *
 **/

class Kohana_Pagination
{
	protected static $_defaults;

	/** Return default values for pagination variables
	 *
	 * @return	object
	 */
	public static function defaults( )
	{
		if ( ! isset(self::$_defaults))
		{
			if ( ! isset($rows_per_page))
			{
				$rows_per_page = Pagination::ROWS_PER_PAGE;
			}

			if ( ! isset($page))
			{
				$page = (int) Request::current( )->query(Pagination::PAGE_QUERY_VAR);
				$page = $page <= 0 ? 1 : $page;
			}

			self::$_defaults = (object) array(
				'rows_per_page' => $rows_per_page,
				'page' => $page,
			);
		}

		return self::$_defaults;
	}

	/** Show navigation
	 *
	 * @param	integer		max_value
	 * @param	integer		number of rows per page
	 * @param	integer		page number
	 * @return	View
	 */
	public static function show($max, $rows_per_page = NULL, $page = NULL)
	{
		InclStream::instance( )->add('pagination.css');

		if ( ! isset($rows_per_page))
		{
			$rows_per_page = Pagination::defaults( )->rows_per_page;
		}

		if ( ! isset($page))
		{
			$page = Pagination::defaults( )->page;
		}

		return View::factory('site.pagination', array(
			'max' 			=> $max,
			'rows_per_page'	=> $rows_per_page,
			'page' 			=> $page,
		));
	}
	
	/** 
	 * Get link to next page
	 *
	 * @return 	void
	 */
	public static function next()
	{
		if ( ! isset($page))
		{
			$page = (int) Request::current( )->query(Pagination::PAGE_QUERY_VAR);
			$page = $page <= 0 ? 1 : $page;
		}
		
		return Request::current( )->url( ).'?'.http_build_query(array_merge(Request::current( )->query( ), array(Pagination::PAGE_QUERY_VAR => $page+1)));
	}
}