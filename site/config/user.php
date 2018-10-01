<?php defined('SYSPATH') or die('No direct access allowed.');

return array
(
	/** настройки входа в систему **/
	// название переменной в сессии с данными пользователя
	'session_user_data' => 'user_data',

	// переменная cookie-склада, хранящая данные автологина
	'cookie_remember_var' => 'auto_login',

	// метод хэширования
	'hash_method'  => 'sha1',

	// ключ для хэширования
	'hash_key'     => '73468',

	// флаг автологина в сессии
	'session_auto_login_flag' => HtmlDump::$_AUTO_LOGIN_VAR,

	// зарезервированные слова, не используемые в качестве логина
	'reserved_words' => array(
		'root',
		'guest',
		'all'
	),

	'session_auth_users' => 'auth_users',

	/** настройки объектов для ACL **/
	// administration account name
	'root_name' => 'root',
	
	// guest account name
	'guest_name' => 'guest',
	
	// cli account name
	'cli_name' => 'guest',
	
	// admin id
	'root_id' => 1,
	
	// guest id
	'guest_id' => 0,
	
	// роли юзеров, имеющих право изменять объекты
	'acl_roles' => array(
		'site_map' 	=> 'editor',
// 		'file_files'	=> 'file_manager',
	),

);