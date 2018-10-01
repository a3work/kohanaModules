<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Basic site contents and menu handlers
 *
 * @package    Kohana/Site
 * @category   Controllers
 * @author     A.St.
 *
 * :TODO:
 * 1. необходимо дописать возможность создания страниц с произвольными адресами (столбец href таблицы site_map)
 * 2. переделать путь до текущей страницы на массив с ключами = map_id
 *
 *
 *
ALTER TABLE `site_contents` ADD `body_ru_RU` LONGTEXT NOT NULL DEFAULT '';
ALTER TABLE `site_contents` ADD FULLTEXT (`body_ru_RU`);
UPDATE `site_contents` AS c JOIN `site_texts` AS t ON (t.id = c.text_id) SET c.body_ru_RU = t.ru_RU;
ALTER TABLE `site_contents` DROP `text_id`;
DROP TABLE `site_texts`;

ALTER TABLE `site_contents` ADD `side_ru_RU` TEXT NOT NULL DEFAULT '';
ALTER TABLE `site_contents` ADD `title_ru_RU` VARCHAR( 1024 ) NOT NULL DEFAULT '',
ADD `descr_ru_RU` VARCHAR( 1024 ) NOT NULL DEFAULT '',
ADD `kw_ru_RU` VARCHAR( 1024 ) NOT NULL DEFAULT '';

ALTER TABLE `site_map` ADD `uri` VARCHAR( 4096 ) NOT NULL AFTER `parent` ,
ADD `uri_hash` VARCHAR( 32 ) NOT NULL AFTER `uri` ,
ADD `address` VARCHAR( 255 ) NOT NULL AFTER `uri_hash`;
ALTER TABLE `site_map` CHANGE `uri` `uri` VARCHAR( 4096 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT 'uri страницы, определяется в момент изменения данных страницы',
CHANGE `uri_hash` `uri_hash` VARCHAR( 32 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT 'хэш uri страницы, определяется в момент изменения данных страницы',
CHANGE `address` `address` VARCHAR( 255 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL COMMENT 'адрес страницы, разделитель -- запятые (0,15,244,357), определяется в момент изменения данных страницы';
ALTER TABLE `site_map` ADD `label` VARCHAR( 40 ) NOT NULL DEFAULT '' AFTER `alias`;

 *
 */
class Kohana_Site
{
    // Текущий uri
    protected static $current_uri;
    // Кэш ссылок
    protected static $href_cache = array( );

    // Конфиг
    public static $config;
    // Язык
    public static $lang;
    // Путь до текущей страницы
//     public static $path;
    // Данные меню
    public static $menu_data;
    // Конфиги
    public static $configs;

    // Массив директорий шаблонов
    public static $templates;
    // Ключ кэша
    private static $cache_group = 'Kohana_Site';

    // геттер конфига сайта
    public static function config($group = null)
    {
		if ( ! isset($group))
		{
			$group = Site::config('site')->common_config_group;
		}
		
//		if (Kohana::$caching)
//		{
//			self::$configs = Cache::instance()->get('config');
//		}
		
		if ( ! isset(self::$configs[$group]))
		{
		
			self::$configs[$group] = Kohana::$config->load($group);
			
//			if (Kohana::$caching)
//			{
//				Cache::instance()->set_with_tags('config', self::$configs, NULL, array('config'));
//			}
		}
		
		return self::$configs[$group];
    }

    /**
     * Ищем родителей страницы, кэшируем, возвращаем массив родителей
     *
     * :DEPRECATED:
     *
     * @param mixed (int ID карты сайта| string адрес страницы | object элемент карты сайта)
     * @return array
     */
    public static function get_parents($map_id)
    {
		return Basic::get_parents('site_map', $map_id, Site::config('site')->cache_tag_map.$map_id, Site::config('site')->cache_lifetime);

// 		if (Kohana::$caching)
// 		{
// 			$cache_key = Basic::get_hash(__FILE__.__CLASS__.'get_parents'.$map_id);
// 			$cache_data = Cache::instance(Site::config('site')->cache_driver)->get($cache_key);
// 			if ($cache_data != NULL)
// 			{
// 				return $cache_data;
// 			}
// 		}
//
// 		$map = ORM::factory('site_map', $map_id);
// 		if ($map->loaded( ))
// 		{
// 			if ($map->parent == 0)
// 			{
// 				$out = array(0);
// 			}
// 			else
// 			{
// 				$out = array_merge(self::get_parents($map->parent), array((integer) $map->parent));
// 			}
// 		}
// 		else
// 		{
// 			$out = array( );
// 		}
//
// 		if (Kohana::$caching)
// 		{
// 			Cache::instance(Site::config('site')->cache_driver)->set($cache_key, $out, );
// 			Cache::set_tag($cache_key, );
// 		}
// 		return $out;
    }

    /**
     * Возвращаем map_id заданной страницы
     *:DEPRECATED:
     * @param string адрес страницы
     * @return string
     */
	public static function get_map_id($current_uri)
	{
		$out = self::get_path($current_uri);
		if ( ! isset($out->map_id))
		{
			//:KLUDGE:
			$uri = Basic::get_hash('/'.trim($current_uri, '/'));
			$orm = ORM::factory('site_map')->where('uri_hash', '=', $uri)->find( );
			if ($orm->loaded( ))
			{
				return $orm->id;
			}
		}

		return $out->map_id;
	}


    // возвращаем массив пути и map_id указанной страницы относительно морды :DEPRECATED:
    public static function get_path($current_uri)
    {
		/*:KLUDGE:*/
		// пятнадцать прыжков по кэшу ради получения одного и того же
		// надо определяться, что иметь на входе -- строку (адрес) или число (map_id)
		if (is_integer($current_uri))
		{
			$out = self::get_path(self::get_href($current_uri));
		}
		else
		{
			// массив пути;
			$path = array( );
			// id карты сайта
			$map_id = $parent_id = NULL;
			// ссылка на страницу
			$href = '';
			// флаг явно заданного пути
			$is_specified = FALSE;
			// если запрошен индекс
			if ($current_uri == '')
			{
				// ищем страницу, отмеченную флагом "индекс"
				$map = ORM::factory('site_map', array('is_index'=>1));
				$map_id = $map->id;
				// записываем в путь
				$path[$map->parent] = array(
					'map_id' 	=> $map->id,
					'name'		=> $map->name,
					'href'		=> self::get_href($map),
				);
			}
			else
			{
				// ищем на карте сайта страницу с явно заданным URL, совпадающим с запрашиваемым
				$map = ORM::factory('site_map')
					-> where('href', '=', trim($current_uri, '/'))
					-> find( )
					-> as_array( );
				if (isset($map['id']))
				{
					$map_id = $map['id'];
					// записываем в путь
					$path[$map['parent']] = array(
						'map_id' 	=> $map['id'],
						'name'		=> $map['name'],
						'href'		=> $map['href'],
					);
					// путь задан явно, отмечаем
					$is_specified = TRUE;
				}
				// если совпадений не найдено
				// пробуем найти по названиям и псевдонимам страниц
				else
				{
					// разбиваем url на странички
					$pages_array = explode('/', $current_uri);
	// 				print_r($pages_array);

					// получаем map_id страницы с index = 1
					// инициализируем $map_id
					$map_id = array(0, ORM::factory('site_map')->where('is_index', '=', 1)->find( )->id);
					$parent_id = 0;

					foreach ($pages_array AS $page)
					{
						// декодируем параметр url
						$page = urldecode($page);

						// ищем соответствие
						$map_mod = ORM::factory('site_map');
						if ($page == '')
						{
							$map_mod->and_where_open( )
								->and_where('alias','=','')
								->and_where('is_index','=','1')
								->and_where_close( );
						}
						else
						{
							$map_mod->or_where_open( )
								->or_where('alias','=',$page)
								->or_where('name','=',$page)
								->or_where_close( );
						}
						// если заданы "корни" сайта
						if (is_array($map_id))
						{
							$map_mod->and_where_open( )
								->and_where('parent', 'IN', DB::expr('(\''.implode('\', \'', $map_id).'\')'))
								->and_where_close( );
						}
						else
						// если задан один родитель
						{
							$map_mod->and_where_open( )
								->and_where('parent', '=', $map_id)
								->and_where_close( );
						}
						$map_item = $map_mod->find( );

						// сохраняем id предка
						if ($map_id != 0 && ! is_array($map_id))
						{
							$parent_id = $map_id;
						}
						$map_id = $map_item->id;
						$href = self::get_href($map_item);
						// записываем в путь
						$path[$map_item->parent] = array(
							'map_id' 	=> $map_id,
							'name'		=> $map_item->name == '' ? $page : $map_item->name,
							'href'		=> $href,
						);
					}
	// 				print_r($path);
	// 				echo "<br>\n\n";
				}
			}
			$out = (object) array(
				'map_id' 			=> $map_id,
				'parent_id' 		=> $parent_id,
				'path' 			=> $path,
				'is_specified' 	=> $is_specified,
			);
		}
		return $out;
	}

    // сеттер языка :DEPRECATED:
    public static function set_language($lang)
    {
		self::$lang = $lang;
    }

    // геттер языка :DEPRECATED:
    public static function get_language( )
    {
		if ( ! isset(self::$lang))
		{
			self::$lang = Site::config('site')->default_language;
		}
		return self::$lang;
    }

	// Возвращаем ссылку на страницу
	// @argument item идентификатор карты сайта или объект карты сайта
	public static function get_href($item, $internal = FALSE)
	{
		if ( ! is_object($item) && $item == 0)
		{
			return URL::base( );
		}
		// если запрос по id
		if ( ! is_object($item))
		{
			// получаем объект map
			$item = ORM::factory('site_map', $item);
		}
		// если для страницы задана жёсткая ссылка
		// и запрашивается именно эта страница, а не её потомок
		if ($item->href > '' && ! $internal)
		{
			return URL::base() . $item->href;
		}
		// если кэш не существует
		if ( ! isset(self::$href_cache))
		{
			self::$href_cache = array( );
		}
		// вытаскиваем ссылку из кэша
		if (isset(self::$href_cache[$item->id]))
		{
			return self::$href_cache[$item->id];
		}
		/* конструируем ссылку */
		// если запрошена морда
		if ($item->is_index == 1)
		{
			$href = URL::base();
		}
		else
		{
			// если страница в корне сайта
			if ($item->parent == 0)
			{
				$href = URL::base( );
			}
			else
			{
				// если адрес страницы-родителя есть в кэше
				if (isset(self::$href_cache[$item->parent]))
				{
					// грузим из кэша
					$href = self::$href_cache[$item->parent];
				}
				else
				{
					// получаем ссылку на страницу-родителя
					$href = self::get_href($item->parent, TRUE);
				}
			}
// 			if ($item->login == 1 && $item->parent == 0)
// 			{
// // 				$href .= 'private/';
// 			}
			$href .= self::urlencode($item->alias != '' ? $item->alias : $item->name). '/';
		}

		// записываем ссылку в кэш
		self::$href_cache[$item->id] = $href;

		return $href;
    }

	/**
	 * Возвращаем дублёра адресной строки
	 *
	 * @return array
	 */
	public static function get_address( )
	{
		$path = (array) self::get_path(self::get_current_uri( ))->path;
		array_unshift($path, array('name' => Site::config()->common_display_name, 'href' => URL::base( )));
		$result = array( );
		$href = '';

		foreach ($path AS &$path_item)
		{
			$current_href = trim($path_item['href'], '/');
			$href .= $current_href .'/';
			if ($current_href == '')
			{
				$path_item['href'] = $href;
			}
		} unset($path_item);

		return $path;
	}

	/** Return redirect message with timer and js
	 *
	 * @param 	url			redirect url
	 * @param 	string		message
	 * @param	array		{
	 * 							'obj': 	'parent',	// redirect object: this window, parent window
	 * 							'time': 3,			// delay time, seconds
								'close': true,		// close this window or not
								'target': '_self',	// redirect href target
	 * 						}
	 * @return 	View
	 */
	public static function redirect($url = NULL, $message = '', $options = NULL)
	{
		// options templates
		if (is_string($options))
		{
			switch ($options)
			{
				case "self":
					$options = array(
						'time'	=> 3,
						'obj'	=> 'self',
						'close'	=> FALSE,
						'target'=> '_self',
					);
					
				break;
				default:
					unset($options);
			}
		}
	
		// defaults
		if ( ! isset($options))
		{
			$options = array();
		}
		if ( ! isset($options['time']))
		{
			$options['time'] = 3;
		}
		if ( ! isset($options['obj']))
		{
			$options['obj'] = 'parent';
		}
		if ( ! isset($options['close']))
		{
			$options['close'] = TRUE;
		}
		if ( ! isset($options['target']))
		{
			$options['target'] = '_self';
		}

		$command = '';
		switch ($options['obj'])
		{
			case 'self':
				$command = 'location.href = "'.$url.'";';
				break;
			case 'iframe':
				$command = strpos($_SERVER['REQUEST_URI'], 'iframe') !== FALSE ? 'window.parent.current_iframe.instance( ).src = window.parent.current_iframe.modifySrc(Delay.href) + "?"+ (rnd = Math.random( )) +"=" + rnd;' : 'location.href = Delay.href;';
				break;
			case 'iframe_close':
				$command = strpos($_SERVER['REQUEST_URI'], 'iframe') !== FALSE ? 'window.parent.current_iframe.remove( );window.parent.Shadow.hide( );' : 'location.href = "'.URL::base( ).'";';
				break;
			default:
//for iframe: 				$command = 'window.parent.location.href = Delay.href;';
				if (isset($url))
				{
					$command = 'if (window.opener) window.opener.location.href = "'.$url.'";';
				}
		}

		$elapse_message = __u('you will be redirected in');
		$is_close = FALSE;

		if ($options['close'])
		{
			$command .= 'window.close( );';
			$elapse_message = __u('window will be closed in');
			$is_close = TRUE;
		}

		return View::factory('site.redirect', array(
			"elapse_message" 	=> $elapse_message,
			"message" 			=> $message,
			"url"				=> $url,
			"time" 				=> $options['time'],
			"command"			=> $command,
			'target'			=> $options['target'],
			'is_close'			=> $is_close,
		));
	}

	/** Add text to spoiler
	 *
	 * @param 	string	text to wrap
	 * @param	boolean	show after loading
	 * @param	string	name
	 * @return 	string
	 */
	public static function spoiler($body = '', $show = FALSE, $name = '')
	{
		InclStream::instance( )->add('common.js');
		InclStream::instance( )->add('common.css');
		
		return View::factory('site.spoiler', array(
			'body' => $body,
			'show' => $show,
			'name' => $name,
		))->render( );
	}
	
	/** Show data in tabs
	 * 
	 * @param 	array 	data
	 * @param	string	css class name
	 * @return	string	html output
	 */
	public static function tabs($data, $class = NULL)
	{
		if (empty($class))
		{
			$class = Site::DEFAULT_TABS_CSS;
		}
		
		InclStream::instance( )->add('common.js');
		InclStream::instance( )->add('common.css');

		InclStream::instance( )->write('new tabs("'.$class.'");');
		
		return View::factory('site.tabs', array(
			'data'	=> $data,
			'class' => $class,
		));
		
	}
	
	/**
	 * Set the rollback point for restore in future
	 * 
	 * @param string $class
	 * @param string $method
	 * @param array $args
	 */	
	public static function set_rollback_point($class, $method, $args = array())
	{
		Session::instance()->set('rollback_data', array(
			'class' => $class,
			'method' => $method,
			'args' => $args,
		));
	}
	
	public static function clear_rollback_point()
	{
		Session::instance()->delete('rollback_data');
	}
	
	
	public static function rollback()
	{
		$data = Session::instance()->get('rollback_data');
		
		if (!isset($data))
		{
			throw new Site_Exception('Nothing to rollback.');
		}
		
		try
		{
			$class = new ReflectionClass($data['class']);
			$instance = $class->newInstance();
			$result = call_user_func_array(array($instance, $data['method']), $data['args']);
			
			return $result;
		}
		catch (Exception $e)
		{
			throw $e;
//			throw new Kohana_Exception('Cannot rollback the state.');
		}
		
		Site::clear_rollback_point();
	}
}
