<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		CMS menu item
 * @package 	CMS
 * @author 		Stanislav U. Alkimovich
 * @date 		2013-06-16
 *
 **/

class Kohana_CMS_Menu_Item
{
	protected $name;
	protected $url;
	protected $window;

	// add opener href to url
	protected $add_opener = FALSE;

	// href target
	protected $target;
	
	// options
	protected $options = array( );


	/** Object constructor
	 *
	 * @return void
	 */
	public function __construct($name, $url)
	{
		$this->name($name);
		$this->url($url);
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

	/** Standart array setter / getter
	 * 	Merge external and existent options or get it
	 *
	 * Setter:
	 * - one argument:
	 * @param array
	 * @return this
	 *
	 * - many arguments:
	 * @param mixed		key (if null generate automatic)
	 * @param mixed		value
	 *
	 * Getter:
	 * @param string	argument name
	 * @return string
	 *
	 * without args
	 * @return array
	 */
	public function options($param0 = NULL, $param1 = NULL)
	{
		if (isset($param0))
		{
			// array setter mode
			if (is_array($param0))
			{
				$this->options = array_merge($this->args, $param0);

				return $this;
			}
			else
			{
				// single getter mode
				if ( ! isset($param1))
				{
					if ( ! isset($this->options[$param0]))
					{
						return NULL;
					}
					else
					{
						return $this->options[$param0];
					}
				}
				// single setter mode
				else
				{
					$this->options[$param0] = $param1;
				}
			}
		}
		else
		{
			// array getter mode
			if ( ! isset($param1))
			{
				return $this->options;
			}
			// single setter mode with numeric key
			else
			{
				$this->options[] = $param1;
			}
		}

		return $this;
	}
}