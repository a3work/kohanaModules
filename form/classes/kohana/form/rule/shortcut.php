<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Parser search result's data marker
 * @package 	Migomdengi/Parser
 * @author 		Stanislav U. Alkimovich
 * @date 		2013-05-22
 *
 **/

class Parser_Result_Item
{

	/**
	 * Object constructor
	 *
	 * @param string href to webdoc
	 * @param string title of webdoc
	 * @param string common information: author, date
	 * @param string short text for examination
	 */
	public function __construct($href, $title, $info, $preview = '')
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
		}

		return $this->$var;
	}
}