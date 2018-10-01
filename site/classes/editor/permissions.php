<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Editor basic permissions check
 * @category	Common
 * @package 	Editor
 * @author 		Stanislav U. Alkimovich
 * @date 		2013-06-01
 *
 **/

abstract class Editor_Permissions
{
	// check permission result
	protected $check_permissions;

	/**
	 * Basic common permissions check
	 *
	 * @return boolean
	 */
	protected function check_permissions( )
	{
		if ( ! isset($this->check_permissions))
		{
			$this->check_permissions = TRUE;
		}

		return $this->check_permissions;
	}
}