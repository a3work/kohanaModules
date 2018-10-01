<?php defined('SYSPATH') or die('No direct script access.');

class Kohana_Cms_Site implements Cms_Module {
	private static $config;

	/** Реализуемые методы **/
	public static function get_config( )
	{
		if ( ! isset(self::$config))
		{
			self::$config = Kohana::$config->load('cms_site');
		}
		return self::$config;
	}

	public static function get_menu( )
	{
		$menu = self::get_config( )->menu;
		$out = array( );

		$current_map_id = Site::get_map_id(Site::get_current_uri( ));

		foreach ($menu AS $name => $menu_item)
		{
			$subout = array( );
			foreach ($menu_item AS $link=>$item)
			{
				if (isset($item['check_removable']) && $item['check_removable'] && ! self::check_removable($current_map_id))
				{
					continue;
				}

				$options = array( );
				if (isset($item['access']['check_children']) && $item['access']['check_children'])
				{
					$options = array('with_children' => 1);
				}
				// если правило доступа задано и не выполняется (в случае, если нужно или не нужно проверять правило относительно текущей страницы)
				if (isset($item['access'])
					&&
					! Object_Access::check('site_map', $current_map_id, $item['access']['var'], $options)
					&&
					! Access::check('site_master_edit')
				)
				{
					continue;
				}


				if (isset($item['href']))
				{
					$ref = $item['href'];
				}
				else
				{
					$ref = URL::base( ). CMS::get_link_header( ) .'/'. self::get_link_header( ).'/'.$link;
				}

				// конструируем ссылки на страницы админки
				$subout[$ref] = $item;
			}

			// если вложенных элементов нет -- пропускаем
			if (count($subout) == 0)
			{
				continue;
			}

			$out[$name] = $subout;
		}
		return $out;

	}

	public static function get_name( )
	{
		return self::get_config( )->name;
	}

	public static function get_link_header( )
	{
		return self::get_config( )->link_header;
	}

	public static function get_css( )
	{
		$file = self::get_config( )->css_file;
		return isset($file) && $file != '' ? $file : '';
	}

	public static function get_init_js( )
	{
		$file = self::get_config( )->js_init_file;
		return isset($file) && $file != '' ? $file : '';
	}

	public static function get_js( )
	{
		$file = self::get_config( )->js_files;
		return isset($file) && is_array($file) ? $file : '';
	}

	/** Приватные методы **/

	/**
	 * Проверяем возможность удаления страницы
	 *
	 * @param integer ID карты сайта
	 * @return boolean
	 */
	private static function check_removable($map_id)
	{
		$result = FALSE;
		$map_item = ORM::factory('site_map', $map_id);
		if ($map_item->loaded( ))
		{
			$result = (boolean) $map_item->is_removable;
		}

		return $result;
	}

	// обработчик создания страницы
	private static function handler_edit($id, $data)
	{
		if ( ! Access::check('site_master_edit') && ! Object_Access::check('site_map', $id, 'edit'))
		{
			return CMS::get_delayed_redirect('У вас нет прав управления страницами в выбранном разделе.<br>Окно будет закрыто ', '', 'iframe_close');
		}

		// создаём элемент карты сайта
		$map = ORM::factory('site_map', $id);

		if ( ! $map->loaded( ))
		{
			return CMS::get_delayed_redirect('Ошибка загрузки данных.<br>Окно будет закрыто ', '', 'iframe_close');
		}

		$map
			-> values(array(
					'name' 	=> $data['header'],
					'alias' 	=> $data['alias'],
					'parent' 	=> $data['parent'],
					'href' 		=> $data['ref'],
				))
			-> save( );



		return CMS::get_delayed_redirect('Информация обновлена.<br>Вы будете перемещены ', Site::get_href($map));
	}


	// обработчик создания страницы
	private static function handler_create($data)
	{
		if ( ! Access::check('site_master_edit') && ! Object_Access::check('site_map', $data['parent'], 'add'))
		{
			return CMS::get_delayed_redirect('У вас нет прав управления страницами в выбранном разделе.<br>Окно будет закрыто ', '', 'iframe_close');
		}

		if ( ! isset($data['parent']))
		{
			$data['parent'] = 0;
		}

		// создаём элемент карты сайта
		$map = ORM::factory('site_map')
			-> values(array(
					'name' 	=> $data['header'],
					'alias' 	=> $data['alias'],
					'parent' 	=> $data['parent'],
					'href' 		=> $data['ref'],
				))
			-> save( );

		/* create uri, uri_hash and address */
		$href = Site::get_href($map->id);
		$parent = $data['parent'];
		$address = array($map->id);
		$item = $map;
		while ($item->parent > -1)
		{
			HtmlDump::instance( )->clean($item->uri_hash);
			array_unshift($address, $item->parent);
			$item = ORM::factory('site_map', $item->parent);
		}
		$address = implode(',', $address);
		$uri = '/'.trim($href, '/');
		$map->values(array(
			'uri'	=> $uri,
			'uri_hash' => Basic::get_hash($uri),
			'address' => $address,
		))
		->save( );

		if (strpos($href, 'news') !== FALSE || strpos($href, 'faq') !== FALSE)
		{
			$redirect_url = Site::get_href($map->parent);
		}
		else
		{
			$redirect_url = Site::get_href($map);
		}

		// создаём контент
		$contents = ORM::factory('site_contents')
			-> values(array(
					'map_id'					=> $map->id,
					Site::get_language( ) 	=> $data['header'],
				))
			-> save( );

		// создаём пункт меню, если просили
		if (isset($data['create_menu']))
		{
			// заглушка
			$data['create_menu'] = 1;

			$menu = ORM::factory('site_menu_item')
			-> values(array(
					'contents_id'=> $contents->id,
					'map_id'		=> $map->id,
					'menu_id'	=> $data['create_menu'],
				))
			-> save( );
		}

		$actions_list = array_keys(ORM::factory('personal_object')->where('model', '=', 'site_map')->find( )->get_action_list( ));
		$creator = Object_Access::get_match('site_map', $data['parent'], 'add');

		// добавляем шаблонное правило доступа на чтение
		Cms_Object_Access::handler_modify('site_map', $map->id, array(
			$creator['type'].'s' 	=> $creator['id'],
			'actions' 				=> $actions_list,
			'negation' 				=> 0,
			'with_children' 		=> 1,
		));

		$username = ORM::factory('personal_user', $creator['id'])->username;

		// write message to access log
		Access_Log::instance( )->write("Пользователь <b>$username</b> добавил страницу <a href='$redirect_url'>$redirect_url</a>");

		return CMS::get_delayed_redirect('Страница создана.<br>Вы будете перемещены ', $redirect_url);
	}

	// обработчик удаления страницы
	private static function handler_delete($map_id)
	{
		if ( ! Access::check('site_master_edit') && ! Object_Access::check('site_map', $map_id, 'delete'))
		{
			return CMS::get_delayed_redirect('У вас нет права удаления текущей страницы.<br>Окно будет закрыто ', '', 'iframe_close');
		}

		// отмечаем страницу как удалённую
		$map_item = ORM::factory('site_map')
			-> where('id', '=', $map_id)
			-> where('is_removable', '=', 1)
			-> find( );

		if ($map_item->loaded( ))
		{
			$item = $map_item;
			while ($item->parent > -1)
			{
				HtmlDump::instance( )->clean($item->uri_hash);

				$item = ORM::factory('site_map', $item->parent);
			}

			$redirect_url = Site::get_href($map_item->parent);
			$map_item
				-> values(array(
						'is_deleted' => 1
					))
				-> update( );

			// удаляем кэш
			Cache::delete_tag(Site::config('site')->cache_tag_map.$map_id);
			Cache::delete_tag(Site::config('personal')->obj_address_tag.'site_map'.$map_id);

			// удаляем правила просмотра страницы
			$map_item->delete_rights( );

			return CMS::get_delayed_redirect('Страница удалена.<br>Вы будете перемещены ', $redirect_url);
		}
		else
		{
			return CMS::get_delayed_redirect('Невозможно удалить страницу.<br>Вы будете перемещены ');
		}
	}

	private static function handler_meta($data, $map_id)
	{
		if ( ! Object_Access::check('site_map', $map_id, 'edit_meta') && ! Access::check('site_master_edit'))
		{
			return CMS::get_delayed_redirect('У вас нет права редактирования метаданных этой страницы.<br>Окно будет закрыто ', '', 'iframe_close');
		}

		// находим id данных
		$title = ORM::factory('site_contents')
			-> where('map_id', '=', $map_id)
			-> where('label', '=', Site::config('site')->label->title)
			-> find( );
		if ($data[Site::config('site')->label->title] > '')
		{
			// если данные есть, обновляем
			// иначе -- добавляем
			if (isset($title->id) && $title->id != 0)
			{
				$title = ORM::factory('site_contents', $title->id)
					-> values(array(
						Site::get_language( ) => $data[Site::config('site')->label->title],
					))
					-> save( );
			}
			else
			{
				$title = ORM::factory('site_contents')
					-> where('map_id', '=', $map_id)
					-> values(array(
						Site::get_language( ) => $data[Site::config('site')->label->title],
						'label' => Site::config('site')->label->title,
						'map_id' => $map_id,
					))
					-> save( );
			}
		}
		elseif (isset($title->id) && $title->id != 0)
		{
			$title = ORM::factory('site_contents', $title->id)
				-> delete( );
		}


		// находим id данных
		$description = ORM::factory('site_contents')
			-> where('map_id', '=', $map_id)
			-> where('label', '=', Site::config('site')->label->description)
			-> find( );
		if ($data[Site::config('site')->label->description] > '')
		{
			// если данные есть, обновляем
			// иначе -- добавляем
			if (isset($description->id) && $description->id != 0)
			{
				$description = ORM::factory('site_contents', $description->id)
					-> values(array(
						Site::get_language( ) => $data[Site::config('site')->label->description],
					))
					-> save( );
			}
			else
			{
				$description = ORM::factory('site_contents')
					-> where('map_id', '=', $map_id)
					-> values(array(
						Site::get_language( ) => $data[Site::config('site')->label->description],
						'label' => Site::config('site')->label->description,
						'map_id' => $map_id,
					))
					-> save( );
			}
		}
		elseif (isset($description->id) && $description->id != 0)
		{
			$description = ORM::factory('site_contents', $description->id)
				-> delete( );
		}

		// находим id данных
		$keywords = ORM::factory('site_contents')
			-> where('map_id', '=', $map_id)
			-> where('label', '=', Site::config('site')->label->keywords)
			-> find( );
		if ($data[Site::config('site')->label->keywords] > '')
		{
			// если данные есть, обновляем
			// иначе -- добавляем
			if (isset($keywords->id) && $keywords->id != 0)
			{
				$keywords = ORM::factory('site_contents', $keywords->id)
					-> values(array(
						Site::get_language( ) => $data[Site::config('site')->label->keywords],
					))
					-> save( );
			}
			else
			{
				$keywords = ORM::factory('site_contents')
					-> where('map_id', '=', $map_id)
					-> values(array(
						Site::get_language( ) => $data[Site::config('site')->label->keywords],
						'label' => Site::config('site')->label->keywords,
						'map_id' => $map_id,
					))
					-> save( );
			}
		}
		elseif (isset($keywords->id) && $keywords->id != 0)
		{
			$keywords = ORM::factory('site_contents', $keywords->id)
				-> delete( );
		}

		// удаляем кэш
		Cache::delete_tag(Site::config('site')->cache_tag_map.$map_id);
		Cache::delete_tag(Site::config('personal')->obj_address_tag.'site_map'.$map_id);

		return CMS::get_delayed_redirect('Метаданные сохранены.<br>Вы будете перемещены ', Site::get_href($map_id));
	}

	/** Публичные методы  **/
	public static function map( )
	{
		return 'hello';
	}

	public static function properties($param)
	{
		$path = self::get_processed_page($param);

		if ( ! Object_Access::check('site_map', $path->map_id, 'edit') && ! Access::check('site_master_edit'))
		{
			return CMS::get_delayed_redirect('У вас нет права редактирования свойств текущей страницы.<br>Окно будет закрыто ', '', 'iframe_close');
		}

		// получаем данные страницы
		$page = ORM::factory('site_map', $path->map_id);

		if ( ! $page->loaded( ))
		{
			return CMS::get_delayed_redirect('Ошибка получения свойств текущей страницы.<br>Окно будет закрыто ', '', 'iframe_close');
		}

		// выбираем форму с добавлением в меню или без него
		// в зависимости от прав доступа
		$form_label = 'contents_add_without_rights';
/*
		// получаем разделы, в которых можно создавать
		$root_chapters = Access::get_actions_root('site_map', 'add');

		if (count($root_chapters))
		{
			// составляем дерево из нескольких
			$map_tree = array( );

			foreach ($root_chapters AS $root_chapters_item)
			{
				$map_tree = array_merge($map_tree, Site::get_map_tree($root_chapters_item, TRUE, $page->id));
			}
		}
		else
		{
			return CMS::get_delayed_redirect('Не определены разделы для создания страниц.<br>Окно будет закрыто ', '', 'iframe_close');
		}

		// получаем массив правил доступа
		$viewing_templates = Site::config('cms_personal')->add_page_templates;

		$templates = array( );
		foreach ($viewing_templates AS $template)
		{
			$template_orm = ORM::factory('personal_rights_template')->where('name', '=', $template)->find( );

			if ($template_orm->loaded( ))
			{
				$templates[] = array(
					'value' 	=> $template_orm->name,
					'header' 	=> $template_orm->{Site::get_language( )},
				);
			}
		}

		Form::options($form_label, array(
			'parent' 			=> $map_tree,
			'access_mode' 	=> $templates,
		));
*/
		Form::add_data($form_label, array(
			'header'			=> $page->name,
			'alias'				=> $page->alias,
			'ref'				=> $page->href,
			'parent'			=> $page->parent,
		));
// 			return Request::factory(Route::get('contents_by_id')->uri(array('view' => 'contents', 'id_type' => 'map', 'id' =>'2', 'type'=>'body')))->execute( )->body( );
		$data = Form::render($form_label);

		// получение данных
		$form_data = Form::get_data($form_label);
		if (is_array($form_data))
		{
			return self::handler_edit($page->id, $form_data);
		}
		else
		{
			return $data;
		}
	}

	// создаём страницу
	public static function create($param)
	{
		$path = self::get_processed_page($param);

		if ( ! Object_Access::check('site_map', $path->map_id, 'add') && ! Access::check('site_master_edit'))
		{
			return CMS::get_delayed_redirect('У вас нет права создания страниц в текущем разделе.<br>Окно будет закрыто ', '', 'iframe_close');
		}

// 		var_dump(Cms_Object_Access::handler_modify('site_map', 0, array(
// 			'roles' 					=> 4,
// 			'actions' 					=> array(1),
// 			'negation' 				=> 0,
// 			'with_children' 			=> 1,
// 		)));

		// выбираем форму с добавлением в меню или без него
		// в зависимости от прав доступа
		$form_label = /*Access::check('site_control_menu') ? 'contents_add_with_menu' : */'contents_add';

		// получаем корневые разделы
// 		$parent = Object_Access::get_tree('site_map', 'add');

// 		if (count($parent) == 0)
// 		{
// 			return CMS::get_delayed_redirect('Не определены разделы для создания страниц.<br>Окно будет закрыто ', '', 'iframe_close');
// 		}

// 		Form::options($form_label, array(
// 			'parent' 	=> $parent,
// 		));

		Form::add_data($form_label, array(
			'parent' => $path->map_id,
		));
// 		*/
		$orm = ORM::factory('site_map', $path->map_id);
// 			return Request::factory(Route::get('contents_by_id')->uri(array('view' => 'contents', 'id_type' => 'map', 'id' =>'2', 'type'=>'body')))->execute( )->body( );
		$data = '<h2>Новая страница</h2><p>Страница создаётся в разделе &laquo;'.$orm->name.'&raquo;</p>'.Form::render($form_label);

		// получение данных
		$form_data = Form::get_data($form_label);
		if (is_array($form_data))
		{
			return self::handler_create($form_data);
		}
		else
		{
			return $data;
		}
	}


	/**
	 * Возвращаем страницу, с которой вызвана функция админки
	 *
	 * @param array 	параметры адресной строки
	 * @return object
	 */
	private static function get_processed_page($param)
	{
		// если параметры не заданы, берём на удаление страницу из HTTP_REFERER
		if (count($param) == 0 && isset($_SERVER['HTTP_REFERER']))
		{
			$current_url = parse_url($_SERVER['HTTP_REFERER']);
			$current_uri = trim(str_replace(URL::base( ), '', $current_url['path']), '/');
		}
		elseif (count($param) != 0)
		{
			$current_uri = implode('/', $param);
		}

		return Site::get_path($current_uri);
	}

	// удаляем страницу
	public static function delete($param)
	{
		$path = self::get_processed_page($param);

		if (isset($path->map_id))
		{
			return self::handler_delete($path->map_id);
		}
		else
		{
			throw new HTTP_Exception_404;
		}
	}

	// редактируем метаданные
	public static function meta($param)
	{
		$current_uri = '';
		if (count($param) != 0)
		{
			$current_uri = implode('/', $param);
		}
		$path = Site::get_path($current_uri);

		if ( ! Access::check('site_master_edit') && ! Object_Access::check('site_map', $path->map_id, 'edit_meta'))
		{
			return CMS::get_delayed_redirect('У вас нет прав редактирования метаданных этой страницы.<br>Окно будет закрыто ', '', 'iframe_close');
		}

		if (isset($path->map_id))
		{
			// определяем столбец с данными
			$lang = Site::get_language( );
			// массив входных данных
			$data = array(
				'title' 				=> '',
				'description' 	=> '',
				'keywords' 		=> '',
			);
			// пытаемся найти метаданные страницы
			$title = ORM::factory('site_contents')
				-> where('map_id', '=', $path->map_id)
				-> where('label', '=', Site::config('site')->label->title)
				-> find( );
			if (isset($title->id))
			{
				$data[Site::config('site')->label->title] = $title->$lang;
			}
			$description = ORM::factory('site_contents')
				-> where('map_id', '=', $path->map_id)
				-> where('label', '=', Site::config('site')->label->description)
				-> find( );
			if (isset($description->id))
			{
				$data[Site::config('site')->label->description] = $description->$lang;
			}
			$keywords = ORM::factory('site_contents')
				-> where('map_id', '=', $path->map_id)
				-> where('label', '=', Site::config('site')->label->keywords)
				-> find( );
			if (isset($keywords->id))
			{
				$data[Site::config('site')->label->keywords] = $keywords->$lang;
			}
			Form::add_data('contents_meta', $data);
			$out = '<h1>Метаданные</h1>'.Request::factory(Route::get('form_by_id')->uri(array('id_type' => 'label', 'id' =>'contents_meta')))->execute( )->body( );

			// получение данных
			$form_data = Form::get_data('contents_meta');
			if (is_array($form_data))
			{
				return self::handler_meta($form_data, $path->map_id);
			}
			else
			{
				return $out;
			}

		}
		else
		{
			throw new HTTP_Exception_404;
		}

	}

	// сохраняем отредактированные данные
	public static function save( )
	{
		$id = Security::xss_clean($_POST['id']);
		$type = Security::xss_clean($_POST['type']);
		$data = Security::xss_clean($_POST['data']);
		$data_for_save = self::replace_includes($data, $id);

		$contents_orm = ORM::factory('site_contents', $id);

		/*
			Проверяем права доступа
			Если нет доступа, возвращаем неизменённый текст
		*/
		if ( ! Access::check('site_master_edit') && ! Object_Access::check('site_map', $contents_orm->map_id, 'edit'))
		{
			switch ($type)
			{
				case 'header':
					return $contents_orm->{Site::get_language( )};
				case 'body':
					return $contents_orm->{'body_'.Site::get_language( )};
				case 'side':
					return $contents_orm->{'side_'.Site::get_language( )};
			}
		}

		// удаляем кэш
		$console = Cache::delete_tag(Site::config('site')->cache_tag_contents.$id);
		$console = Cache::delete_tag(Site::config('site')->cache_tag_map.$contents_orm->map_id);

		switch ($type)
		{
			case 'header':
				$contents_orm
					-> values(array(
						Site::get_language( ) => strip_tags($data_for_save),
					))
					-> save( );

				// удаляем кэш
				$console = Cache::delete_tag(Site::config('site')->cache_tag_contents.$id);
// 					ORM::factory('site_contents', $id)
// 						{Site::get_language( )}->$data
// 						->save
				break;

			case 'body':
				$var = 'body_'.Site::get_language( );
				$contents_orm
					-> values(array(
						$var => $data_for_save,
					))
					-> save( );

				break;

			case 'side':
				$var = 'side_'.Site::get_language( );
				$contents_orm
					-> values(array(
						$var => $data_for_save,
					))
					-> save( );

				break;
		}

		return self::replace_includes($data, $id, 'display');
	}


	// ищем в тексте ранее заменённый php-код
	// и меняем на исходный
	private static function replace_includes($data, $id, $type = 'save')
	{
		$cms_include_replacements = Session::instance( )->get(self::get_config( )->session_replacement_var);
		if (isset($cms_include_replacements) && isset($cms_include_replacements[$id]))
		{
			foreach ($cms_include_replacements[$id] AS $class => $replacements)
			{
				$regexp = "/<div class=\"([^\"]+)$class\">[^<]+<\/div>/";
				preg_match_all($regexp, $data, $matches);
 				$data = str_replace($matches[0], $replacements[$type], $data);
			}
		}
		return $data;
	}
	// ищем в тексте php-код
	// не в админке исполняем
	// в админке -- пишем сообщение
	private static function get_includes($data, $id)
	{
		preg_match_all('/<\?=((.+)(?=\|\|)\|\|(.+))(?=\?>)\?>/', $data, $matches, PREG_SET_ORDER);
		if (count($matches) != 0)
		{
			$cms_include_replacements = Session::instance( )->get(self::get_config( )->session_replacement_var);
			if ( ! isset($cms_include_replacements))
			{
				$cms_include_replacements = array( );
			}
			$cms_include_replacements[$id] = array( );
			foreach ($matches AS &$match)
			{
				if (class_exists('CMS') && CMS::logged_in( ))
				{
					$class = 'cmsreadonly'. mt_rand(10000, 99999);
					$replacement = View::factory('cms_readonly', array('contents' => $match[3], 'class' => $class));
					$data = str_replace($match[0], $replacement, $data);
					// записываем в сессию данные для обратной замены при сохранении
					$cms_include_replacements[$id][$class] = array(
						'save' => $match[0],
						'display' 	=> (string) $replacement,
					);
				}
				else
				{
					eval('$gtw='.$match[2].';');
					$data = str_replace($match[0], $gtw, $data);
					unset($gtw);
				}
			} unset($match);
			Session::instance( )->set(self::get_config( )->session_replacement_var, $cms_include_replacements);
		}
		return $data;
	}

	// обёртываем текст в тэги-маркеры админки
	// показываем подключённый неизменяемый контент
	// или заглушки с соответствующими надписями
	public static function get_wrapper($data, $id, $type, $label  = NULL, $is_editable = TRUE)
	{
		// проверка прав на действия в разделе
		$access_edit_text = Access::check('site_master_edit') || Object_Access::check('site_map', ORM::factory('site_contents', $id)->map_id, 'edit');

		// ищем подключаемый контент
		$data = self::get_includes($data, $id);
		if (class_exists('CMS') && $access_edit_text && $is_editable && Cookie::store(Site::config('cms')->edit_switch_var) == 'checked')
		{
			if ( ! isset($label))
			{
				$label = Site::config('site')->label->default;
			}
			if (($type != 'header' || $type == 'header' && $data != '') && ! in_array($label, array(
				Site::config('site')->label->title,
				Site::config('site')->label->description,
				Site::config('site')->label->keywords,
			)))
			{
				return "<span class='cms-editable cms-$id-$type'>". $data ."</span>";
			}
			else
			{
				return $data;
			}
		}
		else
		{
			return $data;
		}
	}

	public static function config( )
	{
		if ( ! Access::check('site_config'))
		{
			return CMS::get_delayed_redirect('У вас нет права редактирования настроек.<br>Окно будет закрыто ', '', 'iframe_close');
		}

		$out = '';

		$config = ORM::factory('site_config') -> order_by('position') -> find_all( );
		// генерируем форму для каждого значения
		$saved = FALSE;
		foreach ($config AS $config_item)
		{
			Form::add_data('common_config_form', array('key'=>$config_item->config_key, 'group'=>$config_item->group_name, 'value'=>Site::config($config_item->group_name)->{$config_item->config_key}));
			$form = Request::factory(Route::get('form_by_id')->uri(array('id_type' => 'label', 'id' =>'common_config_form')))->execute( )->body( );
			$form_data = Form::get_data('common_config_form');
			if (is_array($form_data) && ! $saved)
			{
				Site::config($form_data['group'])->set($form_data['key'], $form_data['value']);
				Form::add_data('common_config_form', array('key'=>$config_item->config_key, 'group'=>$config_item->group_name, 'value'=>Site::config($config_item->group_name)->{$config_item->config_key}));
				$form = Request::factory(Route::get('form_by_id')->uri(array('id_type' => 'label', 'id' =>'common_config_form')))->execute( )->body( );
				$saved = TRUE;
			}
			$out .= '<div class="cms-settings-item"><span>'.$config_item->label .'</span>'. $form .'</div>';
		}
		return '<h2>Настройки</h2><div class="cms-settings-wrapper">'.$out.'</div>';
	}

	/**
	 * Интерфейс для работы с правами доступа к объектам
	 *
	 * @param array 	массив параметров адресной строки
	 * @return string 	html
	 */
	public static function access($param)
	{
		$path = self::get_processed_page($param);

		if (/*Access::check('site_control_access') && (*/Access::check('site_master_edit') || Object_Access::check('site_map', $path->map_id, 'access')/*)*/)
		{
			return Cms_Object_Access::get_list(array('site_map', $path->map_id));
		}
		else
		{
			return CMS::get_delayed_redirect('Страница не доступна.<br>Окно будет закрыто ', '', 'iframe_close');
		}
	}
}