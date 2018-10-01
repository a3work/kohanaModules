<?php defined('SYSPATH') or die('No direct script access.');


// register Access Module
Access::module('Access_User');
Access::module('Access_Access');
Access::module('Access_CMS');
Access::module('Access_Site');
Access::module('Access_Cron');
Access::module('Access_Files');

// register CMS module
CMS::module('CMS_User');
CMS::module('CMS_Site');
Cms::module('Cms_Cron');
CMS::module('Cms_Files');

// чтение конфига модуля
$config = Kohana::$config->load('site');
$allow_types = implode('|',$config->allow_content_types);

if ( ! (boolean) Kohana::$is_cli)
{
	// инициализация куки-хранилища переменных
	Cookie::store( );

	// автовход
	User::instance( )->auto_login( );
}

// инициализация изменяемого конфига
Kohana::$config->attach(new Config_Database(array(
	'instance' 		=> Kohana_Database::instance( ),
	'table_name'	=> $config->common_config_table,
)));


Route::set('cron', 'cron(/<action>(/<id>(/)))')
	->defaults(array(
		'controller'	=> 'cron',
		'action' 		=> 'index',
	));

// default cms route
Route::ext('cms.base', 'cron_manage', 'cron(/<id>)(/<action>)(/)', array(
		'id' => '\d+',
	))
	->defaults(array(
		'controller' 	=> 'cms_cron',
	));

class Crontab_Parser_Autoloader {
	public static function autoload($class)
	{
		if ($class == 'Crontab')
		{
			include_once Kohana::find_file('vendor', 'PHP-Crontab-master/Crontab.class');
		}
	}
}

// Register the autoloader
spl_autoload_register(array('Crontab_Parser_Autoloader', 'autoload'));

/**
 * Kohana extended log engine
 * @author Max <nagaychenko@gmail.com>	https://github.com/maxnag/Kohana-log
 *
Route::set('log_download', 'log/download(/<date>)', array(
		'date' => '\d{2}.\d{2}.\d{4}',
	))
	->defaults(array(
		'controller' => 'log',
		'action' => 'download',
));

Route::set('log', 'log(/<date>)', array(
		'date' => '\d{2}.\d{2}.\d{4}',
	))
	->defaults(array(
		'controller' => 'log',
		'action' => 'view',
));
*/


Route::set('login', 'login(/<oauth_provider>(/))')
    ->defaults(array(
        'controller'    => 'user',
        'action'        => 'login',
    ));
Route::set('preferences', 'preferences(/)')
    ->defaults(array(
        'controller'    => 'user',
        'action'        => 'preferences',
    ));
Route::set('register', 'reg(/)')
    ->defaults(array(
        'controller'    => 'user',
        'action'        => 'reg',
    ));

Route::set('register_success', 'reg/success(/)')
    ->defaults(array(
        'controller'    => 'user',
        'action'        => 'reg_success',
    ));

Route::set('recovery', 'recovery(/)')
    ->defaults(array(
        'controller'    => 'user',
        'action'        => 'recovery',
    ));
Route::set('logout', 'logout(/<username>(/))')
    ->defaults(array(
        'controller'    => 'cms_user',
        'action'        => 'logout',
    ));
Route::set('oauth', 'oauth(/<oauth_provider>(/))')
    ->defaults(array(
        'controller'    => 'user',
        'action'        => 'oauth',
    ));  
    
    
/* OAuth library includes */
class SocialAuther_Autoloader {
	public static function autoload($class)
	{
		if (strpos($class,  'SocialAuther') !== FALSE)
		{
			include_once Kohana::find_file('vendor', 'SocialAuther-master/lib/SocialAuther/'.str_replace('SocialAuther/', '', str_replace('\\', '/', $class) ));
		}
	}
}

// Register the autoloader
spl_autoload_register(array('SocialAuther_Autoloader', 'autoload'));
    
/* routes of inline editor engine begins */
Route::ext('cms.base', 'editor_by_id', 'editor/<settings_id>/<item_id>(/)',
	array(
		'settings_id' 	=> '\d+',
		'item_id' 		=> '\d+',
	))
	->defaults(array(
		'controller' 	=> 'editor',
		'action'     	=> 'id',
	));

Route::ext('cms.base', 'editor_by_param', 'editor/<class>/<orm>/<field>/<item_id>(/)',
	array(
		'class' => '[a-zA-Z_][a-zA-Z_0-9]+',
		'orm' 	=> '[a-zA-Z_][a-zA-Z_0-9]+',
		'field' => '[^ ]+',
		'item_id' 		=> '\d+',
	))
	->defaults(array(
		'controller' 	=> 'editor',
		'action'     	=> 'param',
	));
/* routes of inline editor engine ends */

Route::ext('cms.base', 'user_access', 'user/<list>/<id>/access(/)',
    array(
        'type'  => 'accounts|groups',
        'id'    => '\d+',
    ))
    ->defaults(array(
        'controller'    => 'cms_user',
        'action'        => 'access',
    ));

Route::ext('cms.base', 'user_delete', 'user/<list>/<id>/del(/)',
    array(
        'type'  => 'accounts|groups',
        'id'    => '\d+',
    ))
    ->defaults(array(
        'controller'    => 'cms_user',
        'action'        => 'delete',
        'list'          => 'accounts',
    ));

Route::ext('cms.base', 'user_attr', 'user/<list>/<id>/attr(/)',
    array(
        'list'  => 'accounts|groups',
        'id'    => '\d+',
    ))
    ->defaults(array(
        'controller'    => 'cms_user',
        'action'        => 'attr',
        'list'          => 'accounts',
    ));

Route::ext('cms.base', 'user_manage', 'user/<list>/<id>(/)',
    array(
        'list'  => 'accounts|groups',
        'id'    => '\d+',
    ))
    ->defaults(array(
        'controller'    => 'cms_user',
        'action'        => 'manage',
        'list'          => 'accounts',
    ));

Route::ext('cms.base', 'user_list', 'user/<list>(/)',
    array(
        'list'  => 'accounts|groups',
    ))
    ->defaults(array(
        'controller'    => 'cms_user',
        'action'        => 'list',
        'list'          => 'accounts',
    ));

Route::ext('cms.base', 'user_default', 'user(/)')
    ->defaults(array(
        'controller'    => 'cms_user',
        'action'        => 'index',
    ));

Route::ext('cms.base', 'access_user', 'access/<user_id>(/)',
    array(
        'user_id'   => '\d+',
    ))
    ->defaults(array(
        'controller'    => 'cms_access',
        'action'        => 'user',
    ));

Route::ext('cms.base', 'access_obj', 'access/<class>/<obj_id>(/)',
    array(
        'class'     => '[a-zA-Z_][a-zA-Z_0-9]+',
        'obj_id'    => '\d+',
    ))
    ->defaults(array(
        'controller'    => 'cms_access',
        'action'        => 'obj',
    ));


// /*
// Route::set('site_manage', 'pages/<action>(/)')
//     ->defaults(array(
//         'controller'    => 'site',
//         'action'        => 'add',
//     ));
// */
Route::ext('cms.base', /*set(*/'files_browse', 'browse(/<'.File::ROUTE_PATH_VAR.'>(/))',
    array(
        File::ROUTE_PATH_VAR    => '(.|\/)+',
    ))
    ->defaults(array(
        'controller'    => 'browser',
        'action'        => 'index',
    ));

Route::set('files', 'files(/<action>(/<id>(/)))')
    ->defaults(array(
        'controller'    => 'files',
        'action'        => 'download',
    ));

Route::ext('cms.base', 'log', 'log(/)')
	->defaults(array(
		'controller' => 'cms_log',
	));

// init database log storage
Kohana::$log->attach(new Log_Writer_Database);

/**
-- end log engine routes
**/

// cms gate for including non-kohana scripts
Route::set('cms_gate', 'admin/<script>', array(
		'script' => '[a-z0-9]+\.php',
	))
	-> defaults(array(
		'controller' 	=> 'cms',
		'action'		=> 'gate',
		'script' 		=> 'index.php',
	));

Route::ext('cms.base', 'filesystem', 'filesystem/<type>/<action>(/<path>)', array(
		'path' 			=> '(.|\/)+',
	))
	->defaults(array(
		'type'			=> 'file',
		'controller' 	=> 'filesystem',
	));

Route::ext('cms.base', 'site_manage', 'pages/<action>(/)')
->defaults(array(
	'controller' 	=> 'page',
	'action'		=> 'add',
));

Route::ext('cms.base', 'cms', '_<action>(/<id>(/))')->defaults(array(
		'controller'	 => 'cms',
		'action'    	 => 'index',
	));
	
Route::ext('cms.base', 'cms.common', '<controller>(/<action>(/<id>))(/)',
    array(
        'id'    => '[\d_]+',
        'controller' => '[^\/]+',
        'action' => '[^\/]+',
    ))
    -> defaults(array(
		'prefix' => 'controller_cms_',
	));

Route::set('messages', 'messages(/<id>(/<action>(/)))', array(
		'id' => '\d+',
	))
	-> defaults(array(
		'controller' 	=> 'messages',
		'action' 		=> 'index',
		'id'			=> NULL,
	));
	
Route::set('feedback', 'feedback(/)', array(
		'id' => '\d+',
	))
	-> defaults(array(
		'controller' 	=> 'messages',
		'action' 		=> 'feedback',
	));


	
// Route::set('cms', 'cms/<action>(/)')->defaults(array(
// 		'controller'	 => 'cms',
// 		'action'    	 => 'index',
// 	));
// 
// Common cms delete object routing
Route::set('cms_delete', 'admin/<page>/<id>/delete(/)', array(
		'id' => '\d+',
	))
	-> defaults(array(
		'controller' 	=> 'cms',
		'action' 		=> 'delete',
		'id'				=> NULL,
	));

/*
// Common cms forms routing
Route::set('cms_form', 'admin/<page>/(<id>/)<process>(/)', array(
		'process' 		=> 'add|edit',
		'id'				=> '\d+',
	))
	-> defaults(array(
		'controller'	 => 'cms',
		'action'		 => 'form',
		'id'				 => NULL,
	));
	*/
// cms pages routing
Route::set('cms_content', 'admin(/<view>)(/<page>(/<param>(/)))', array(
		'view' => 'ajax|iframe',
		'param' => '(.|\/)+',
	))
	-> defaults(array(
		'controller'	 => 'cms',
		'action'		 => 'index',
		'page'			 => '',
		'param'		 => NULL,
	));

// // cms modules
// Route::set('cms_common', 'admin/<module>/<method>(/<param>(/))', array(
// 		'param' => '(.|\/)+',
// 	))
// 	-> defaults(array(
// 		'controller'	 => 'cms',
// 		'action'		 => 'module',
// 		'param'		 => NULL,
// 	));


// роуты модуля
Route::set('menu', '<view>/'. $config->route_headers->menu.'(/<page>(/))',
	array(
		'view'		=> $config->route_headers->contents .'|'. $config->route_headers->json,
		'page' 		=> '(.|\/)+',
	))
	->defaults(array(
		'controller' 	=> 'site',
		'action'     	=> 'menu',
		'page'		 	=> '',
		'view'			=> $config->route_headers->contents,
	));

Route::set('meta', $config->route_headers->meta.'(/<page>(/))',
	array(
		'page' 		=> '(.|\/)+',
	))
	->defaults(array(
		'controller' 	=> 'page',
		'action'     	=> 'meta',
		'page'		 	=> '',
	));

Route::set('contents_by_id', '<view>/<id_type>/<id>(/<list>)(/<label>)(/<type>)(/)',
	array(
		'view'		=> $config->route_headers->contents .'|'. $config->route_headers->json,
		'id_type' 	=> 'map|id',
		'id' 			=> '\d+',
		'list'			=> 'list|',
		'label' 		=> '(?!('. $allow_types .'))[^/]+',
		'type' 		=> $allow_types,
	))
	->defaults(array(
		'controller'	=> 'site',
		'action'		=> 'contents',
		'view'			=> $config->route_headers->contents,
	));

Route::set('cli', 'cli/<action>(/<task>(/))',
	array(
		'task' 		=> '(.|\/)*',
	))
	->defaults(array(
		'controller' 	=> 'cli',
		'action'     	=> 'exec',
	));

Route::set('contents', '<view>(/<page>(/))',
	array(
		'view'		=> $config->route_headers->contents .'|'. $config->route_headers->json,
		'page' 		=> '(.|\/)*',
	))
	->defaults(array(
		'controller' 	=> 'page',
		'action'     	=> 'index',
		'page'		 	=> '',
		'view'			=> $config->route_headers->contents,
	));

Route::set('save_to_cookie_store', 'store_var/<var>(/)')
	->defaults(array(
		'controller' 	=> 'site',
		'action'     	=> 'store',
	));
	
	
// basic cms route
Route::set('cms.base', 'cms(/<exec_parent>)(/<mode>)(<'.Route::URI_STUB.'>)', array(
        'exec_parent' => CMS::EXEC_PARENT_FLAG,
        'mode' => CMS::VIEW_MODE_FULL.'|'.CMS::VIEW_MODE_SIMPLE,
        Route::URI_STUB => '(.|\/)*',
    ))->defaults(array(
        'controller' => 'cms',
        'action' => 'login',
        'mode' => CMS::VIEW_MODE_FULL,
    ));


Route::set('get_from_cookie_store', 'get_stored_var/<var>(/)')
	->defaults(array(
		'controller' 	=> 'site',
		'action'     	=> 'store_get',
	));

// default route for any request
Route::set('default', '(<page>)',
	array(
		'page' 		=> '(.|\/)+',
	))
	->defaults(array(
		'controller' 	=> 'main',
		'action'     	=> 'index',
		'page'		 	=> '',
	));

Site::$lang = preg_replace('/-.*$/', '', I18n::lang());