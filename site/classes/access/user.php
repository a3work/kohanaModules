<?php

class Access_User extends Access_Module
{
	public function __construct( )
	{
		$this->name('Учётные записи');

		$this->add('user_access')->label('Управление правами доступа.');		// view popup cms menu
		$this->add('user_manage')->label('Управление учётными записями.');		// view popup cms menu
		$this->add('user_enter')->label('Вход под учётной записью клиента.');		// view popup cms menu

		$this->template('Администратор')->attach('user_manage')->attach('user_access');
	}
}