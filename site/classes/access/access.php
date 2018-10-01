<?php

class Access_Access extends Access_Module
{
	public function __construct( )
	{
		// Module name
		$this->name('Доступ пользователей');

		// Module privileges
		$this->add('access_login')->hidden(TRUE)->defaults( )->label('Возможность входа под учётной записью.');
	}
}