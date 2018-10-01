<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Cron cms module
 * @package 	Cron
 * @author 		Stanislav U. Alkimovich
 * @date 		2013-12-05
 *
 **/

class CMS_Cron extends CMS_Module
{
	/** Object constructor
	 *
	 * @return void
	 */
	public function __construct( )
	{
		$menu = $this->menu(__('site'));

		if (acl('cron_task_manage'))
		{
			$menu->item(__('task scheduler'), Route::url('cron_manage'));
		}
	}
}