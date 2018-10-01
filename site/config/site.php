<?php defined('SYSPATH') or die('No direct access allowed.');

return array
(
	// переменная для хранения страницы, с которой осуществляется переадресация
	'referrer_page_var' => 'referrer',

	// заголовки рутов
	'route_headers' => (object) array(
		'menu' 			=> 'sitemenu',
		'contents' 	=> 'contents',
		'json'			=> 'json',
		'private'		=> 'private',
		'meta'			=> 'meta',
	),
	// заголовки рутов модулей, использующих модуль Site
	'foreign_route_headers' => (object) array(
		'form'			=> 'form',
	),
	// дефолтный язык сайта
	'default_language' => 'ru',
	// кэшировать id карты сайта
	'cache_map_id' => TRUE,
	// дефолтная метка
	'label'   => (object) array(
		'default' 		=> '',
		'title'			=> 'title',
		'description'	=> 'descr',
		'keywords'	=> 'kw',
	),
	// допустимые типы контента
	'allow_content_types' => array(
		'header',
		'body',
		'side',
		'created',
	),
	// ключевое слово ссылки на внешний контроллер
	'external_func_keyword' => ':FUNC:',
	// дефолтная метка главного меню сайта
	'main_menu_label' => 'main',
	// таблица общего изменяемого конфига сайта
	'common_config_table' => 'site_config',
	// группа общего конфига
	'common_config_group' => 'main',
	// максимальная глубина вложенности для страниц
	'max_depth' => 4,
	// разделитель пути страницы
	'address_separator' => ',',

	/** Настройки кэширования **/
	'cache_driver' => 'file',
	// время хранения кэшированных данных
	'cache_lifetime' => 1209600, // 2 недели
	// тэг карты сайта
	'cache_tag_map' => 'site_map_',
	// тэг контента
	'cache_tag_contents' => 'site_contents_',
// 	// тэг пути
// 	'cache_tag_path' => 'site_path_',
// 	// тэг списка родителей
// 	'cache_tag_parents' => 'site_parents_',

	/** Настройки куки-хранилища **/
	// имя куки-ключа
	'cookie_store_name' => 'cookie_store_5137',
	// время жизни куки
	'cookie_store_lifetime' => 2592000, 		// -- 30 дней
	// дефолтное время жизни переменной склада
	'cookie_store_default_expire' => 2592000,
	// список переменных, доступных для смены через ajax
	'cookie_store_available_vars' => array(
		'browser_interface',
		'interface',
	),

	/** Имена шаблонов **/
	// основной шаблон
	'view_name_main' => 'main',
	// шаблон мета-тэгов
	'view_name_meta' => 'meta',
	// основной шаблон элемента меню
	'view_name_menu_item' => 'main_menu_item',
	// template of breadcrumbs
	'view_breadcrumbs' => 'breadcrumbs',
	// default template of page
	'view_page_default' => 'body',
		

	/** CLI **/
	'cli_command' => '/usr/bin/php',
	'cli_final_states' => array(
		'dead',
		'canceled',
		'done',
		'error',
	),
	
	/** Files **/
	// FS mode for new files
	'new_file_mode' => 0664,
	// FS mode for new directories
	'new_dir_mode' => 0775,
	// default page config
	'default_page_config' => array(
		'template' 		=> NULL,
		'name' 			=> '',
		'title' 		=> '',
		'description' 	=> '',
		'keywords' 		=> '',
	),
	// default upload 
	'upload_dir' => 'upload',
	
	// autosaving for content's files
	'content_use_autosave' => FALSE,
);