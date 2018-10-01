<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Json file representation
 * @package 	Files
 * @author 		Stanislav U. Alkimovich
 * @date 		2014-07-07
 *
 **/

class Kohana_File_Menu extends File_Regular
{
	/** This method calls when driver is initializing for concrete File object
	 *
	 * Load config
	 *
	 * @param 	Kohana_File
	 * @return 	Kohana_File
	 */
	public function before(Kohana_File $file)
	{
		if ( ! acl('file_read', $file))
		{
			throw new Access_Exception;
		}
		
		if ($file->exists( ))
		{
			// define content and config
			$this->_capture($file);
			
			/* add standard kohana check of SYSPATH existance */
			if ( ! $this->_has_header($file))
			{
				$this->_write($file);
			}
		}
		
		return $file;
	}
	
	/** Check existance of header
	 *
	 * @param 	Kohana_File
	 * @return 	boolean			TRUE if header exists
	 */
	protected function _has_header(Kohana_File $file)
	{
		$f = fopen($file->path( ), 'r');
		$result = (strpos(fgets($f), '<?php defined(\'SYSPATH\')') !== FALSE);
		fclose($f);
	
		return $result;
	}
	
	/** algorythm of menu file creation
	 *
	 * @param   Kohana_File parent Kohana_File instance
	 * @return 	Kohana_File
	 */
	protected function _create(Kohana_File $file)
	{
		$file->config = array( );

		// write defaults
		$this->_write($file);
		
		return $file;
	}
	
	/** Write header and content
	 *
	 * @param 	Kohana_File
	 * @param 	string		content
	 * @param 	array		page's config
	 * @return 	boolean		TRUE on success
	 */
	protected function _write(Kohana_File $file)
	{
		$text = '<?php defined(\'SYSPATH\') or die(\'No direct script access.\');';
		
		if (is_array($file->config) && count($file->config) > 0)
		{
			/* write config */
			$text .= "\r\n\r\n\rreturn ".var_export($file->config, TRUE).";\r\n\r\n";
		}
		
		// write changes
		file_put_contents($file->path( ), $text);
	}
	
	/** Execute the file, fetch config of page, capture file output, save and return it.
	 *
	 * @param 	Kohana_File
	 * @return 	string
	 */
	protected function _capture(Kohana_File $file)
	{
		if ( ! $file->exists( ))
		{
			return NULL;
		}
	
		if ( ! $file->loaded( ))
		{
			$file->config = Kohana::load($file->path( ));
			
			if ( ! is_array($file->config))
			{
				$file->config = array( );
			}
			
			$file->loaded(TRUE);
		}
		
		return $file->config;
    }

	/** Add items to menu according to specified config
	 *
	 * @param 	array		config
	 * @param 	Menu_Html	menu instance
	 * @return 	Menu_html
	 */
    protected function _render_level($data, Menu_Html $menu)
    {
    
		foreach ($data AS &$line)
		{
			if (isset($line['items']))
			{
				$this->_render_level($line['items'], ($submenu = $menu->submenu($line['text'], $line['href'])));

				if (isset($line['css']))
				{
					$submenu->parent( )->css($line['css']);
				}
			}
			else
			{
				$child = $menu->child($line['text'], $line['href']);
				
				if (isset($line['css']))
				{
					$child->css($line['css']);
				}
			}
			
		}
		
		return $menu;
    }
    
    
	/** Get file name by menu ID
	 *
	 * @param 	string	ID
	 * @return 	string	filename
	 */
    public static function get_file_name($id)
    {
		return '.menu.'.$id.'.php';
    }
    
	/** 
	 * Finds menu of specified type in specified page directory or in parents;
	 * Returns File object for menu
	 *
	 * @param 	Page	file
	 * @param 	string	ID
	 * @return 	Kohana_File
	 */
	public static function get_file(Kohana_Page $file, $id)
	{
		$filename = File_Menu::get_file_name($id);
		
		// find file
		$menu_file = $file->child($filename);
		
		if ( ! $menu_file->exists( ) && ! $file->is_root( ))
		{
			// load menu from parent
			$menu_file = File_Menu::get_file($file->parent_page( ), $id);
		}
		
		if ( ! $menu_file->exists( ))
		{
// 				throw new File_Exception('Cannot find menu file :file', array(':file' => $filename));
			$menu_file->create(FALSE);
		}
		
		return $menu_file;
	}
    
	/** Get view file name
	 *
	 * @param 	Kohana_File
	 * @return 	string
	 */
    protected function _get_view_name(Kohana_File $file)
    {
		return preg_replace('/(^\.|\.php)/', '', $file->name( ));
    }
    
    /** Prepare menu 
	 *
	 * @param   Kohana_File		parent Kohana_File instance
	 * @return 	Kohana_File		menu file
	 */
	public function prepare(Kohana_File $file)
	{
		$file->menu_obj = Menu_Html::factory( )
							->template($this->_get_view_name($file));
			
		// add structure to menu
		try
		{
			$file->menu_obj = $this->_render_level($file->config, $file->menu_obj);
		}
		catch (Exception $e)
		{
			throw new File_Exception($e->getMessage( ));
		}
		
		return $file;
	}
	
	/**
	 * 
	 * @param	Kohana_File $file
	 * @return	string
	 */
	public function to_string(Kohana_File $file)
	{
		return $file->menu_obj;
	}
	
	/**
	 * Returns querystring contains levels description
	 * @param array $levels
	 * @return string
	 */
	protected function _level_query_string($levels)
	{
		return count($levels) > 0 ? '?'.ltrim(implode('&levels[]=', array_merge(array(NULL), $levels)), '&') : '?';
	}
	
	public function get_menu_selection($menu, $depth = 99, $levels = array())
	{
		$out = count($levels) == 0 ? array('' => __u('root item')) : array();
		
		$i = 0;
		foreach ($menu AS $item)
		{
			$current_level = $levels;
			$current_level[] = $i++;
			
			$out[implode('|',$current_level)] = str_repeat('&nbsp;', (count($current_level)+1)*5).'&nbsp;|---&nbsp;'.$item['text'];
			
			if (isset($item['items']) && $depth > count($current_level))
			{
				$out = array_merge($out, $this->get_menu_selection($item['items'], $depth, $current_level));
			}
		}
		
		return $out;
	}
	
	/** Gallery controls
	 *
	 * @param	Kohana_File	source
	 * @return 	void
	 */
	public function action_remove_item(Kohana_File $file)
	{
		if ( ! acl('file_write', $file))
		{
			throw new Access_Exception;
		}
		
		$levels = $file->request->query('levels');
		
		// multiple ids support
		$ids = explode(Site::ID_SEPARATOR, $file->request->query('id'));
		
		$current_level = &$file->config;
		
		if (isset($levels) && count($levels) > 0)
		{
			foreach ($levels AS $level)
			{
				if ( ! isset($current_level[$level]['items']))
				{
					$current_level[$level]['items'] = array();
				}

				$current_level = &$current_level[$level]['items'];
			}	
		}
			
		foreach ($ids AS $id)
		{
			if (isset($current_level[$id]))
			{
				unset($current_level[$id]);
			}
		}
			
		$this->_write($file);
			
		$body = Request::factory($file->action('manage').'?'.http_build_query($file->request->query()))->execute( )->body( );
		
		return Basic::json_safe_encode(array(
			'body' => $body->body,
		));		
	}
	
	/**
	 * Manage menu
	 * 
	 * @param Kohana_File $file
	 * @return void
	 */
	public function action_manage(Kohana_File $file)
	{
		if ( ! acl('file_write', $file))
		{
			throw new Access_Exception;
		}
		
		$levels = $file->request->query('levels');
		
		if ( ! isset($levels))
		{
			$levels = array();
		}
		
		$level_data = $file->config;
		
		if (isset($levels) && count($levels) > 0)
		{
			foreach ($levels AS $level)
			{
				if (isset($level_data[$level]))
				{
					$level_data = isset($level_data[$level]['items']) ? $level_data[$level]['items'] : array();
				}
				else
				{
					throw new File_Exception(__('Cannot find level :level for menu :menu', array(':level' => implode('.', $levels),':menu' => $file->name())));
				}
			}
		}
		
		$menu_options = $this->get_menu_selection($file->config);
		$lower_level_href = $this->_level_query_string($levels);
		$current_level_key = implode('|',$levels);
		
		$form = Form::factory();
		$form
			->use_activator(FALSE)
			->message(__u('all changes has been saved successfuly').'.')
			->hide_on_success(TRUE);
//		'text' => string 'Информация' (length=20)
//      'href' => string '/info/' (length=6)
//      'css' => string 'fa fa-book' (length=10)
//      'items' => 
		$table = Html::factory('table')->header(array(
			__('text'),
			__('URL'),
			__('CSS classes'),
		//	__('parent item'),
			__('count of children'),
		));
		
		
		$menu = Menu_Context::factory( );
			
		$menu
			->child(__('open'), $file->action('manage').$lower_level_href.'&levels[]=:level', 'edit')
				->dbl( )		
			->child(__('delete'), $file->action('remove_item').$lower_level_href.'&id=:level', 'delete')
				->multiple( )
				->confirm( )
				->ajax( );
		
		$menu->render( );
		
		if (count($levels) > 0)
		{
			$table
				->line(array(
					Html::factory('anchor')->text('['.__('go level up').']')->href($file->action('manage').$this->_level_query_string(array_slice($levels, 0, -1))),
				));
		}
		
		// table body
		foreach ($level_data AS $i => $item)
		{
			$table
				->line(array(
					$form->field('text', NULL, 'elements['.$i.'][text]')->value(isset($item['text']) ? $item['text'] : ''),
					$form->field('text', NULL, 'elements['.$i.'][href]')->value(isset($item['href']) ? $item['href'] : ''),
					$form->field('text', NULL, 'elements['.$i.'][css]')->value(isset($item['css']) ? $item['css'] : ''),
					//$form->field('chosen', NULL, 'elements['.$i.'][parent]')->disabled(TRUE)->options($menu_options)->value($current_level_key),
					(__(':count элемент:(|а|ов)', array(':count' => isset($item['items']) ? count($item['items']) : 0))).$form->field('hidden', NULL, 'elements['.$i.'][items]')->value(isset($item['items']) ? Basic::json_safe_encode($item['items']) : '{}'),
				))
				->classes($menu->context(
					array(
						'level' => $i,
					)				
				));
		}
		
		$i = isset($i) ? $i+1 : 0;
		
		$table
			->line(array(
				$form->field('text', NULL, 'elements['.$i.'][text]')->placeholder(__u('new menu item')),
				$form->field('text', NULL, 'elements['.$i.'][href]')->placeholder(__u('hyperreference')),
				$form->field('text', NULL, 'elements['.$i.'][css]')->placeholder(__u('CSS classes')),
				//$form->field('chosen', NULL, 'elements['.$i.'][parent]')->disabled(TRUE)->options($menu_options)->value($current_level_key),
				$form->field('hidden', NULL, 'elements['.$i.'][items]'),
			));
		
		
		$form->render($table->render('cms-table').$form->field('submit', __('save')));

		$file->template->header = __u('menu manager');
		$file->template->body = $form->html();
		
		/** SAVE CHANGES **/
		if ($form->sent())
		{
			$data = $form->result()->as_array();

			$removing_items = array();
			
			foreach ($data['elements'] AS $key => &$element)
			{
				if (isset($element['items']))
				{
					$element['items'] = Basic::json_safe_decode($element['items']);
				}
				else	
				{
					$element['items'] = array();
				}

				if (empty($element['text']) || $element['text'] == '')
				{
					$removing_items[] = $key;
				}
			}
				
			foreach ($removing_items AS $key)
			{
				unset($data['elements'][$key]);
			}
			
			/** write the changes **/
			$current_level = &$file->config;
			foreach ($levels AS $level)
			{
				if ( ! isset($current_level[$level]['items']))
				{
					$current_level[$level]['items'] = array();
				}

				$current_level = &$current_level[$level]['items'];
			}	
			
			$current_level = $data['elements'];
			
			$this->_write($file);
			
			$this->action_manage($file);
		}
	}
}