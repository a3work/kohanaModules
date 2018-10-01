<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Page content files collection
 * @package 	Files
 * @author 		Stanislav U. Alkimovich
 * @date 		2014-06-12
 *
 **/

class Kohana_File_Page extends File_Directory
{
	/** Find files in current directory
	 *
     * @param   Kohana_File		parent Kohana_File instance
	 * @param 	string			pattern
	 * @return 	Collection
	 */	
	public function find(Kohana_File $file, $pattern)
	{
		return Page_Collection::factory($file->path( ).$pattern);
	}	
	
	/** Generate breadcrumbs for current page
	 *
	 * @param	Kohana_File
	 * @return 	void
	 */
	public function breadcrumbs(Kohana_File $file)
	{
		if ( ! $file->is_root( ))
		{
			$file->parent_page( )->breadcrumbs( );
		}
		
		Breadcrumbs::instance( )->add($file->url( ), $file->attr('name'));
	}

	/** Generate menu
	 *
	 * @param 	Kohana_File
	 * @param 	string			menu name
	 * @return 	string			menu HTML code
	 */
	public function menu(Kohana_File $file, $name)
	{
		if ( ! isset($file->page_menu))
		{
			$file->page_menu = array( );
		}
		
		if ( ! isset($file->page_menu[$name]))
		{
			$menu_file = File_Menu::get_file($file, $name);

			// prepare menu and save it to list
			$file->page_menu[$name] = $menu_file->prepare( );
		}
		
		return $file->page_menu[$name];
	}
	
	/** This method calls when driver is initializing for concrete File object
	 *
	 * @param 	Kohana_File
	 * @return 	void
	 */
	public function before(Kohana_File $file)
	{
		
	
	}
	
	/** algorythm of file creation
	 *
	 * @param   Kohana_File parent Kohana_File instance
	 * @param	string	default page text
	 * @param	string	language ID
	 * @return 	void
	 */
	protected function _create(Kohana_File $file, $text = '', $language = NULL)
	{
		// create main part
		File::factory(
						$file->path( )
						.DIRECTORY_SEPARATOR
						.Page::main_part($language)
					)->create( );
	}
	
	/** Read and return attributes
	 *
	 * @param   Kohana_File parent Kohana_File instance
	 * @return 	array
	 */
	public function attr(Kohana_File $file, $rules = NULL, $value = NULL)
	{
		if ( ! $file->exists( ))
		{
			return;
		}

		if ( ! acl('file_read', $file))
		{
			throw new Access_Exception;
		}
	
		if (isset($rules))
		{
			if (is_array($rules))
			{
				$file->page_main_part->config(array_merge($file->page_main_part->config( ), $rules));
			}
			else
			{
				if (isset($value))
				{
					$file->page_main_part->config(array_merge($file->page_main_part->config( ), array($rules => $value)));
				}
				
				$config = $file->page_main_part->config( );
				
				return isset($config[$rules]) ? $config[$rules] : '';
			}
		}
	
		return $file->page_main_part->config( );
	}
	
	/** Remove from server
	 *
	 * @param	Kohana_File	source
	 * @return 	void
	 */
	public function remove(Kohana_File $file)
	{
		if ($file->exists( ) === FALSE)
		{
			return;
		}
		
		if ( ! acl('file_write', $file->dir( )))
		{
			throw new Access_Exception;
		}
		
		if ($file->is_root( ))
		{
			throw new File_Exception('Cannot remove index page.');
		}
		
		$count_access = 0;
		
		foreach ($file AS $child)
		{
			if ($child->driver_obj( ) instanceOf File_Access)
			{
				$count_access ++;
				if (count($file->content) > $count_access)
				{
					continue;
				}
				else
				{
					break;
				}
			}

			// remove child
			$child->remove( );
		}

		// get directory
		$dir = File::factory($file->path( ));
		
		// remove directory if not contains files
		if (count($dir->content( )) <= 1)
		{
			$dir->remove( );
		}
		
		// remove Kohana_File object from instances stack
		$file->overload( );
		
		// drop file parameters
		$file->init( );
	}
	
	/** 
	 * Add custom attributes of page to specified form
	 * 
	 * @return void
	 */
	protected function _custom_attr_form(&$form) {
	}
	
	/** 
	 * Process values of custom attributes
	 * 
	 * @return void
	 */
	protected function _custom_attr_result(&$result, &$page) {
	}

	/** Add new page
	 *
	 * @param	Kohana_File	source
	 * @return 	void
	 */
	public function action_add(Kohana_File $file)
	{
		if ( ! acl('file_write', $file))
		{
			throw new Access_Exception( );
		}
		
		$form = Form::factory( );
		$form 
			->show_on_success(FALSE)
			->use_activator(FALSE)
			->message(__u('page has been created successfuly'))
			->field('text', __u('header'), 'header')->not_empty( )->rule('page_exists')
			->field('text', __u('alias'), 'alias')
			->field('checkbox', __u('extra options'), 'extra')->hidden(TRUE)->rel('extra_on')->checked( )
			/* :TODO: make pages shortcuts */
// 				->field('text', __u('href'), 'href')->beh('extra_on')->action('show')
			->field('textarea', __u('title'), 'title')->beh('extra_on')->action('show')
			->field('textarea', __u('description'), 'descr')->beh('extra_on')->action('show')
			->field('textarea', __u('keywords'), 'kw')->beh('extra_on')->action('show')
			->field('hidden', NULL, 'parent')->value($file->path(FALSE))
			->field('submit', __u('add page'));
		
		$this->_custom_attr_form($form);
		
		$form = $form
				->render( );

		$message = '';

		if ($form->sent( ))
		{
			$result = $form->result( )->as_array( );
			
			$child = Page::factory(rtrim($result['parent'], DIRECTORY_SEPARATOR).DIRECTORY_SEPARATOR.File::filter_url($result['alias'] != '' ? $result['alias'] : $result['header']))->create( );
			
			$this->_custom_attr_result($result, $child);
			
			$child->attr('name', $result['header']);
			
			if (isset($result['title']))
			{
				$child->attr('title', $result['title']);
			}
			
			if (isset($result['descr']))
			{
				$child->attr('description', $result['descr']);
			}
			
			if (isset($result['kw']))
			{
				$child->attr('keywords', $result['kw']);
			}
			
			$message =   
							'<br>'.Html::factory('anchor')->href($child->url( ))->target('_blank')->text(__('go to the new page ":page"', array(':page' => $child->name( ))))
						.'<br>'.Html::factory('anchor')->href($file->action('add'))->text(__('create an other page'))
						.'<br>'.Html::factory('anchor')->href($child->action('add'))->text(__('create child page for ":page"', array(':page' => $child->name( ))));
						
		}
				
		$file->template->header	= __u('new page');
		$file->template->body = $form.$message;
	}
	
	/**
	 * List of children pages
	 * 
	 * @param Kohana_File $file
	 */
	public function action_list(Kohana_Page $file) {
		
		$menu = Menu_Context::factory( )->id('text_list');
		
		$menu
			->child(__('edit'), 'NULL' /*Route::url('cms_bechamel', array('mode'=>CMS::VIEW_MODE_FULL, 'action'=>'edit', 'id'=>':id'))*/, 'edit')
				->dbl( )		
//			->child(__('publish'), Route::url('cms_bechamel', array('mode'=>CMS::VIEW_MODE_FULL, 'action'=>'publish', 'id'=>':id')), 'publish')
//				->multiple( )
//				->ajax( )
//			->child(__('unpublish'), Route::url('cms_bechamel', array('mode'=>CMS::VIEW_MODE_FULL, 'action'=>'unpublish', 'id'=>':id')), 'unpublish')
//				->multiple( )
//				->ajax( )
			->child(__('delete'), 'NULL' /*Route::url('cms_bechamel', array('mode'=>CMS::VIEW_MODE_FULL, 'action'=>'delete', 'id'=>':id'))*/, 'delete')
				->confirm( );
		$menu->render();
		
		$table = Html::factory('table')->header(array(
			__('name'),
			__('size'),
			__('ptime'),
		));
		
		foreach ($file->find('*') AS $child_page) {
			
			$table
				->line(array(
					$child_page->name(),
					$child_page->size(),
					'ptime',
				))
				->classes($menu->context(
					array(
						'id' => $child_page->name(),
//					),
//					array(
//						($item->publish_time == '0000-00-00 00:00:00' ? 'unpublish' : 'publish'),
//						((boolean) $item->is_archived ? 'add_to_archive' : 'remove_from_archive'),
					)
				));
		}
		
		$file->template->body = $table->render('cms-table');
	}
	
	/** attributes form builder
	 * 
	 * @param	Kohana_File $file
	 * @return Form_Base	
	 */
	protected function _attributes_form(Kohana_File $file)
	{
		$form = Form::factory( )
				->show_on_success(TRUE)
				->clear_on_success(FALSE)
				->message(__u('attributes of page has been saved successfuly').'.');
		foreach ($file->attr( ) AS $key=>$value)
		{
			if (is_string($value))
			{
				$form->field('textarea', $key, $key)->value($value);
			}
		}
		
		$form
			->field('submit', __u('save'));
		
		return $form;
	}
	
	/** Process attributes form
	 * 
	 * @param	Kohana_File $file
	 * @param	Form_Base $form
	 */
	protected function _attributes_form_result(Kohana_File $file, &$form)
	{
		// save config
		$file->attr(array_intersect_key($form->result( )->as_array( ), $file->attr( )));
	}
	
	
	/** Setting up attributes
	 *
	 * @param 	Kohana_File
	 * @return 	void
	 */
	public function action_attributes(Kohana_File $file)
	{
		$form = $this->_attributes_form($file);
		$form->render();
		
		if ($form->sent( ))
		{
			$this->_attributes_form_result($file, $form);
		}

		if ($file->request->param('mode') == CMS::VIEW_MODE_FULL)
		{
			$file->template->parent = $file->is_root( ) ? __u('main page') : $file->name( );
			$file->template->parent_href = $file->url( );
		}
		
		$file->template->header = __u('list of attributes').'.';
		$file->template->body = $form;
	}
	
	/** Page removing action
	 *
	 * @param	Kohana_File	source
	 * @return 	void
	 */
	public function action_remove(Kohana_File $file)
	{
		$file->remove( );
		
		$orm = ORM::factory('file')
			->where('filename', '=', $file->path(FALSE));
			
		$result = $orm->find_all();
		
		// remove all database variables
		foreach ($result AS $db_record)
		{
			$db_record->delete();
		}
		
		$file->template->header = __u('page has been removed successfuly').'.';
		$file->template->body = __u('page has been removed successfuly').'.<br>'.Html::factory('anchor')->href($file->dir( )->url( ))->text(__('go to parent page'));
	}
}