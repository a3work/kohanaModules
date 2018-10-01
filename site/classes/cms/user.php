<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		User cms module
 * @package 	User
 * @author 		Stanislav U. Alkimovich
 * @date 		2013-08-16
 *
 **/

class CMS_User extends CMS_Module
{
	/** Object constructor
	 *
	 * @return void
	 */
	public function __construct( )
	{
		$menu = $this->menu(__('users'));

		if (acl(__('user_manage')))
		{
			$menu->item(__('accounts'), Route::url('user_list', array('list' => 'accounts')));
			$menu->item(__('user groups'), Route::url('user_list', array('list' => 'groups')));
		}
	}
}