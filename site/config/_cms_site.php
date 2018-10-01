<?php defined('SYSPATH') or die('No direct script access.');
return array(
	// название модуля
	'name' => 'Страница',
	// псевдоним модуля для ссылки
	'link_header' => 'contents',
	// ссылки на страницы админки и их название
	'menu' => array(
		'Администрирование' => array(
			'config' => array(
				'name' 	=> 'Настройки',
				'access' 	=> array(
					'var' => 'site_config'
				),
			),
			'access_log' => array(
				'name' 	=> 'Журнал доступа',
				'href' 	=> '/access_log',
				'access' 	=> array(
					'var' => 'access_log_view',
				),
				'ext'	=> 	TRUE,
			),
		),
		'Страница' => array(
			'create' 	=> array(
				'name' => 'Создать',
				'access' 	=> array(
					'var' => 'add',
				),
				'add_url' 	=> 1,
			),
	// 		'map' 		=> 'Карта сайта',
			'meta' 		=> array(
				'name' 		=> 'Метаданные',
				'add_url' 	=> 1,
				'access' 	=> array(
					'var' => 'edit_meta',
				),
			),
// 			'properties' 	=> array(
// 				'name' => 'Свойства',
// 				'access' 	=> array(
// 					'var' => 'edit',
// 				),
// 				'add_url' 	=> 1,
// 			),
			'access' => array(
				'name' => 'Доступ',
				'access' 	=> array(
					'var' => 'access',
				),
				'add_url' 	=> 1,
			),
			'delete' 	=> array(
				'name' => 'Удалить',
				'confirm' => 1,
				'add_url' 	=> 1,
				'access' 	=> array(
					'var' => 'delete',
				),
				'check_removable' => TRUE,
			),
		),
	),
	// файл css
	'css_file' => 'cms_site.css',
	// файл js-инициализации
// 	'js_init_file' => 'cms_site.init.js',
	'js_init_file' => '',
	// прочие js-файлы
	'js_files' => array(
	),
	'session_replacement_var' => 'include_replacements',
);