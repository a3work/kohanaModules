<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Base class for custom CMS menu chapter
 * @package 	CMS
 * @author 		Stanislav U. Alkimovich
 * @date 		2013-08-07
 *
 **/

class Kohana_CMS_Menu
{
	// template name
	protected $name;

	// link to parent CMS_Module
	protected $module;

	// items list
	protected $items;

	/** Object constructor
	 *
	 * @param	string			template name
	 * @param	CMS_Module	link to parent module
	 * @return 	void
	 */
	public function __construct($name)
	{
		$this->name($name);
	}

	/** Standart setter/getter
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

	/** Add CMS_Menu_Item and attach to current obj
	 *
	 * @param 	string		name
	 * @param 	string		URL
	 * @return 	this
	 */
	public function item($name, $url)
	{
		if ( ! isset($this->items[$name]))
		{
			$this->items[$name] = new CMS_Menu_Item($name, $url);
		}

		return $this->items[$name];
	}
}