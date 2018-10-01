<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		File browser
 * @package 	Site
 * @author 		A. St.
 * @date 		05.02.2017
 *
 **/

class Kohana_Controller_Cms_Filebrowser extends Controller_Cms
{
	/** extension filter
	 * @var array
	 */
	protected $ext_filter;

	/** reloaded Kohana Controller::before 
	 * 
	 * @return void
	 */
	public function before( )
	{
		parent::before( );
		
		if ( ! acl('files_browse'))
		{
			throw new Access_Exception( );
		}
		
		$path = $this->request->query('file');
		$this->ext_filter = trim($this->request->query('ext_filter'));
		$this->ext_filter = $this->ext_filter != '' ? explode('|', $this->ext_filter) : array();
		$create_if_not_exists = (int) $this->request->query('create_new');
		$file = ff($path);
		
		if ($file->exists() === FALSE)
		{
			if ($create_if_not_exists)
			{
				$file->driver("File_Directory");
				$file->create();
			}
			else
			{
				throw new HTTP_Exception_404();
			}
		}
		
		$this->_left_menu = Cms_Submenu::factory( )->id('Filebrowser');
		$this->_left_menu
			->text(__u('File browser'))
			->child(__u('list'), Route::url('cms.common', array('controller' => 'Filebrowser')))->css('cms-list')
// 			->child(__u('upload'), Route::url('cms.common', array('controller' => 'Filebrowser', 'action' => 'upload'), array('file' => $file->path(FALSE))))->css('cms-add')
				//->window()
				;
	}

	/** reloaded Kohana Controller::after 
	 * 
	 * @return void
	 */
	public function after( )
	{
		$this->template->left = $this->_left_menu->render( );
	
		parent::after( );
	}
	
	/** Action: index
	 *  list
	 * 
	 * @param	Menu_Context	external menu
	 * @return void
	 */
	public function action_index(Menu_Context $menu = NULL)
	{
		$chroot_mode = $this->request->query("chroot");
		$show_hidden = (int) $this->request->query("show_hidden");
		
		$use_ext_filter = count($this->ext_filter) > 0;
	
		/* filter settings begins */
		$filter = Form::factory('filter')
					->always_show(TRUE)
					->filter_name('Filter')
					->field('text', __('query'), 'query')->min_length(3)
					->field('submit', 'Найти')
					->callback('query', function($value, $orm) {
						

					}, array('orm' => ''))
					->render( );
					
		$this->template->right = $filter;
		/* filter settings ends */
		
		/* context menu declaration begins */
		$query_params = $this->request->query();
		
		if ( ! isset($menu))
		{
			$menu = Menu_Context::factory( )->id('Filebrowser');
			$query_params['file'] = ':file';
			$menu
				->child(__('open'), Route::url('cms.common', array('controller' => 'Filebrowser'), $query_params), 'open')
					->dbl( );
				
			if ($this->_mode == CMS::VIEW_MODE_FULL)
			{
				$menu
					->child(__('rename'), Route::url('cms.common', array('controller' => 'Filebrowser', 'mode'=>CMS::VIEW_MODE_SIMPLE, 'action'=>'rename'), array('file'=>':file')), 'rename')
					->window('popup');
			}
			else
			{
				$menu
					->child(__('rename'), Route::url('cms.common', array('controller' => 'Filebrowser', 'mode'=>CMS::VIEW_MODE_SIMPLE, 'action'=>'rename'), array('file'=>':file', 'back_url' => $this->backurl)), 'rename');
			}
			
			$menu
				->child(__('download'), Route::url('cms.common', array('controller' => 'Filebrowser', 'mode'=>CMS::VIEW_MODE_SIMPLE, 'action'=>'download'), array('file'=>':file')), 'download')
				->child(__('delete'), Route::url('cms.common', array('controller' => 'Filebrowser', 'mode'=>CMS::VIEW_MODE_FULL, 'action'=>'delete'), array('file'=>':file')), 'delete')
// 					->multiple( )
					->confirm( )
					->ajax( );
		}

		$menu->render( );
		/* context menu declaration ends */
		
		/* data table begins */
		// set up table header 
		$table = Html::factory('table')->header(array(
			__('name'),
			__('type'),
			__('size'),
 			__('modified'),
		))
		->_empty_message(__u('directory is empty').'.');
		
		$path = $this->request->query('file');
		
		$file = ff($path);
		
		/* uploading form request begins */
		$upload_form_request = Request::factory(
			Route::url('cms.common',
				array(
					'controller' => 'Filebrowser',
					'action' => 'upload',
					'mode' => CMS::VIEW_MODE_SIMPLE
				),
				array(
					'file' => $file->path(FALSE),
					'ext_filter' => implode('|', $this->ext_filter),
				)
			)
		)->execute();
		
		$upload_form = $upload_form_request->body()->body;
		/* uploading form request ends */
		
		if ($file->is_dir())
		{
			if (!$file->is_root() && (empty($chroot_mode) || $chroot_mode === 1 || $chroot_mode != $file->path(FALSE)))
			{
				$table
					->line(array(
						'..',
						'',
						'',
						'',
					))
					->classes($menu->context(
						array(
							'file' => $file->dir()->path(FALSE),
						),
						array(
							'download'
						)
					));
			}
		
			
			// table body
			foreach ($file AS $item)
			{
				if ($item->name()[0] == '_' && !$show_hidden)
				{
					continue;
				}
				
				if ($use_ext_filter && !in_array($item->ext(), $this->ext_filter))
				{
					continue;
				}
			
				$table
					->line(array(
						$item->name(),
						$item->is_dir() ? __("directory") : $item->mime_type(),
						$item->size(),
 						Date::format($item->mtime(), Date::FORMAT_RFC3339),
	//					$item->,
					))
					->classes($menu->context(
						array(
							'file' => $item->path(FALSE),
						),
						array(
							$item->is_dir() ? 'download' : 'open',
						)
					));
			}
		}
		// :TODO: file viewer
		else
		{
			$query_params['file'] = $file->dir()->path(TRUE);
			$this->request->redirect(Route::url('cms.common', array('controller' => 'Filebrowser'), $query_params));
		}
		
		$this->template->body = Site::spoiler($upload_form, !$table->rows_count() || $upload_form->sent( ) !== NULL, 'форму загрузки').$table->render('cms-table');
		/* data table ends */
		
		$this->template->header = $file->path(FALSE);
	}	

	
	
	/** Action: edit
	 *  Edit template
	 *
	 * @return void
	 */
	public function action_edit( )
	{
		$id = (int) $this->request->param('id');

		$form = Form::factory( )
				->show_on_success(TRUE)
				->clear_on_success(FALSE);
		
		if ($id != 0)
		{
			$orm = ORM::factory('', $id);
			
			if ( ! $orm->loaded( ))
			{
				throw new File_Exception('Cannot load text #:id', array(':id' => $id));
			}
			
			$defaults = $orm->as_array( );
			$form->defaults($defaults);
		}
		else
		{
			$orm = ORM::factory('');
		}
		
		
		$columns = $orm->table_columns( );
				
		$form
			->message(__u('data has been saved successfuly'))
			->field('text', __('full name'), '')->not_empty( )
			->field('submit', __('save'));
				
		$form->render( );
		
		if ($form->sent( ))
		{
			$data = $form->result( )->as_array( );
			$orm->values($data)->save( );
		}
	
		$this->template->parent = __u('File browser');
		$this->template->parent_href = Route::url('cms.common', array('controller' => 'Filebrowser'));
//		$this->template->header = __u('New file', array(':name' => $orm->));
		$this->template->body = $form;	
	}	

	/** Action: rename file
	 *
	 * @return void
	 */
	public function action_rename( )
	{
		$message = '';
		$file = ff($this->request->query('file'));
		$back_url = base64_decode($this->request->query('back_url'));

		$form = Form::factory( )
				->show_on_success(TRUE)
				->clear_on_success(FALSE);
		
		$form->defaults(array(
			'name' => $file->name(),
		));
		
		$form
			->message(__u('files has been renamed successfuly'))
			->field('text', __('new name of file'), 'name')->not_empty()
			->field('submit', __('rename'));
				
		$form->render( );
		
		if ($form->sent( ))
		{
			$result = $form->result()->as_array();
			
			try {
				$file->move($file->dir(), $result['name']);
				
				if ($back_url)
				{
					$this->request->redirect($back_url);
				}
			
			} catch (Exception $e) {
				$message = $e->getMessage();
			}
		}
	
// 		$this->template->parent = __u('file browser');
// 		$this->template->parent_href = Route::url('cms.common', array('controller' => 'Filebrowser'), array('file' => $upload_dir->path(FALSE)));
		$this->template->header = __u('file renaming');
		$this->template->body = $message.$form;	
	}
	
	/** Action: download
	 *  Download file
	 *
	 * @return void
	 */
	public function action_download( )
	{
		$this->auto_render = FALSE;
		$this->auto_load = FALSE;
		
		// multiple ids support
		$filename = $this->request->query('file');
		
		$file = ff($filename);
		
		if ($file->exists( ))
		{
			$file->download( );
		}
	}	
	
	
	/** Action: upload file
	 *
	 * @return void
	 */
	public function action_upload( )
	{
		$upload_dir = ff($this->request->query('file'));

		$form = Form::factory( )
				->show_on_success(TRUE)
				->clear_on_success(FALSE);
		
		$form
			->message(__u('files has been uploaded successfuly'));
		$upload_field = $form
			->field('file_multiple', __('files for uploading'), 'newfile')->not_empty()
				->upload_dir($upload_dir->path());
				
		if (count($this->ext_filter) > 0)
		{
			$upload_field->rule('upload_type', array('allowed' 	=> Basic::json_safe_encode($this->ext_filter)));
		}
		
		$form
			->field('submit', __('upload'));
				
		$form->render( );
		
		if ($form->sent( ))
		{
			
		}
	
		$this->template->parent = __u('File browser');
		$this->template->parent_href = Route::url('cms.common', array('controller' => 'Filebrowser'), array('file' => $upload_dir->path(FALSE)));
		$this->template->header = __u('File uploading');
		$this->template->body = $form;	
	}
	
	/** Action: delete
	 *  Delete template
	 *
	 * @return void
	 */
	public function action_delete( )
	{
		if ( ! $this->request->is_ajax( ))
		{
			throw new Access_Exception( );
		}
		
		$this->auto_render = FALSE;
		
		// multiple ids support
// 		$ids = explode(Site::ID_SEPARATOR, $this->request->param('id'));
		$filename = $this->request->query('file');
		
// 		foreach ($ids AS $id)
// 		{
		$file = ff($filename);
		
		if ($file->exists( ))
		{
			$file->remove( );
		}
// 		}
		
		$body = Request::factory(str_replace(URL::site(NULL, 'http'), '', $this->request->referrer( )))->execute( )->body( );
		
		$this->response->body(Basic::json_safe_encode(array(
			'body' => $body->body,
		)));
	}	
	
	
	/** Action: add
	 *  add new template
	 *
	 * @return void
	 */
	public function action_add( )
	{
		$this->action_edit( );
	
		$this->template->header = __u('New file');
	}

	/** 
	 * select category and call specified method
	 *
	 * @return 	void
	 */
	public function action_select( )
	{
		$id = $this->request->param('id');
		$handler = $this->request->query('h');
		
		$menu = Menu_Context::factory( )->id('Filebrowser');
		
		$child = $menu
			->child(__('select'), $handler.'?id='.$id, 'select')
				->dbl( )
				->ajax( );

		if ((boolean) $this->request->multiple)
		{
			$child->multiple();
		}
		
		$this->action_index($menu);
	}
}