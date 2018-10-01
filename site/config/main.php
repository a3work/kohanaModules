<?php
return array(
	/** Общие настройки */
	// общее имя сайта
	'common_display_name' => 'sh.ru',

	/** Настройки почты */
	// from-адрес электронной почты
	'email_from' => 'support@examle.com',
	// название сайта для почты
	'email_site_name' => 'examle.com',
	// почта администратора
	'email_admin' => 'admin@examle.com',
	// почта для отправки сообщений с форм
	'form_email' => 'admin@example.com',


	// Дефолтные метаданные
	'default_title'	=> "",
	'default_descr' => "",
	'default_kw'	=> "",
	
	'content_parts' => array(
        'header',
        'side',
	),
	
	/* names of special groups */
	'roles' => (object) array(
        'suppliers'     => 'Поставщики',
        'clients'       => 'Клиенты',
        'logistics'     => 'Логисты',
        'storekeepers'  => 'Кладовщики',
        'suppliers'     => 'Поставщики',
        'admins'        => 'Администраторы',
	),
);
