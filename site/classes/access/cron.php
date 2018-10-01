<?php

class Access_Cron extends Access_Module
{
	public function __construct( )
	{
		// Module name
		$this->name(__('Periodic task scheduler'));

		// Module privileges
		$this->add('cron_task_manage')->label(__('task management'));

		// Module access templates
		$this->template('Администратор')->attach('cron_task_manage');
	}
}