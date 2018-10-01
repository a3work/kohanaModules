<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Pricing helper
 * @package 	Shop
 * @author 		A.St <a3.work@gmail.com>
 * @date 		03.01.14
 *
 **/

class Kohana_Pricing
{
	// discounts array
	protected static $_discounts = array( );

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

	
	/** Get price for current user
	 *
	 * @param 	TYPE	VAR_DESCR
	 * @return 	RETURN
	 */
}