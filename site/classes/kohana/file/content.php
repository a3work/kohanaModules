<?php defined('SYSPATH') or die('No direct script access.');
/**
 *
 * @name		Page part representation
 * @package 	Files
 * @author 		Stanislav U. Alkimovich
 * @date 		2014-07-07
 *
 **/
class Kohana_File_Content extends File_Regular
{
	/**
	 * @const string	autosave extension
	 */
	const EXTENSION = ".php";
	
	/** This method calls when driver is initializing for concrete File object
	 *
	 * Check existence of mandatory block of code and write data if need
	 *
	 * @param 	Kohana_File
	 * @return 	Kohana_File
	 */
	public function before(Kohana_File $file)
	{
		if ($file->exists( ))
		{
			$file->content_main_part = $file->name( ) == Page::main_part( );

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
	
	/** Get config for current file
	 *
	 * @param 	Kohana_File
	 * @return 	array
	 */
	public function config(Kohana_File $file, $config = NULL)
	{
		if ( ! $file->exists( ))
		{
			return NULL;
		}
	
		if (isset($config))
		{
			// write config
			$this->_write($file, NULL, $config);
		}
	
		return $file->content_config;
	}
	
	/** Check existance of header
	 *
	 * @param 	Kohana_File
	 * @return 	boolean			TRUE if header exists
	 */
	protected function _has_header(Kohana_File $file)
	{
		return preg_match('/^<\?php defined\(\'SYSPATH\'\)/', file_get_contents($file->path( )));
	}
	
	/** Write header and content
	 *
	 * @param 	Kohana_File
	 * @param 	string		content
	 * @param 	array		page's config
	 * @return 	boolean		TRUE on success
	 */
	protected function _write(Kohana_File $file, $content = NULL, $config = NULL)
	{
		$text = '<?php defined(\'SYSPATH\') or die(\'No direct script access.\');';
		
		if ($file->content_main_part)
		{
			if (is_array($config) && count($config) > 0)
			{
				$file->content_config = array_merge($file->content_config, $config);
			}
		
			/* write config */
			$text .= "\r\n\r\n\$config = ".var_export($file->content_config, TRUE).";\r\n\r\n";
		}
		
		$text .= "?>\r\n";
	
		// get current content, remove old header
		$text .= isset($content)
				 ? $content
				 : preg_replace('/^<\?(.*)(?<!\?>)\?>(?:\r|\n)*/s', '', file_get_contents($file->path( )));
		
		// write changes
		file_put_contents($file->path( ), $text);
		
		if (function_exists("opcache_get_status"))
		{
			$opcache_config = opcache_get_status();
			
			if (isset($opcache_config['opcache_enabled']) && (bool) $opcache_config['opcache_enabled']) {
				opcache_invalidate($file->path());
			}
		}
	}
	
	
	/** Replace stubs and return text for saving
	 *
	 * @param 	Kohana_File
	 * @param 	string		text
	 * @return 	string
	 */
	protected function _make_source(Kohana_File $file, $text)
	{
		/* :TODO: convert all stubs to PHP code */
		return $text;
	}

	/** Replace PHP-code of current text to stubs and return text for CKEditor
	 *
	 * @param 	Kohana_File
	 * @return 	string
	 */
	protected function _make_editable(Kohana_File $file)
	{
		/* :TODO: convert all php to non-editable areas for CKEditor */
		return $this->_capture($file);
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
			// Capture the view output
			ob_start( );
		
			try
			{
				// Load the view within the current scope
				include $file->path( );
			}
			catch (Exception $e)
			{
				// Delete the output buffer
				ob_end_clean( );
		
				// Re-throw the exception
				throw $e;
			}
			
			/* Define config for main part*/
			if ($file->content_main_part === TRUE)
			{
				// merge default config and current
				$file->content_config = (isset($config))
										? array_merge(Site::config('site')->default_page_config, $config)
										: Site::config('site')->default_page_config;
			}
			
			// Get the captured output and close the buffer
			$file->content = ob_get_contents( );
			ob_end_clean( );
			
			$file->loaded(TRUE);
		}
		
		return $file->content;
    }
	
	/** Create autosave file 
	 *
	 * @param 	Kohana_File		file
	 * @return 	Kohana_File		new autosave file
	 */	
	public function autosave(Kohana_File $file)
	{
		// copy current html file to autosave
		$autosave =
			$file->dir( )->child(
				str_replace('.', File::SEPARATOR, $file->name( ))
				.File::SEPARATOR
				.date('Ymd'.File::SEPARATOR.'His')
				.Kohana_File_Content_Autosave::EXTENSION
			)
			->driver('File_Content_Autosave')
			->create( );
		
		return $autosave;
	}

	/** Content setter / getter
	 *
	 * @param 	Kohana_File	
	 * @param 	string		text for saving
	 * @param	string		insert mode
	 * @return 	string
	 */	
	public function content(Kohana_File $file, $text = NULL, $mode = NULL)
	{
		// capture content, load config if need
		$this->_capture($file);
	
		/* if writing allowed */
		if (acl('file_write', $file))
		{
			// if saving queried
			// run as setter
			if ($text !== NULL)
			{
				// write text to file
				$this->_write($file, $this->_make_source($file, $text));
				
				// write event to log
				Kohana::$log->add(
					Log::INFO,
					"File :file has been modified.",
					array(
						':file' => $file->path( ),
					)
				);
			}
			// run as getter in either case
			else
			{
				InclStream::instance( )->add('editor/ckeditor.js', NULL, 10);
				InclStream::instance( )->add('form.cke.js');

				$excluded = array( );

				$id = Basic::get_hash($file->path( ), 'md5', 8);
				
// 				var_dump($file->action('save'));
				
				$menu = Menu_Context::factory( );
				$menu
						// add menu item and href to handler
						->child(__('edit'), $file->action('save'))
							->dbl( )
							// add inline CKEditor to context (call CKE.inline(obj, key, opt, href) -- see MODPATH/menu/classes/kohana/menu/context.php)
							// use autosave, set autosave action
							->action('CKE.inline', array(
								'useAutoSave' => Site::config('site')->content_use_autosave,
								'autoSaveHandler' => $file->action('autosave'),
							));
						// :TODO: edit in new window
// 						->child(__('edit in new window'), $file->action('edit'))
// 							->window('editor');
							
				if (Site::config('site')->content_use_autosave === TRUE)
				{
					$submenu = $menu->submenu(__('restore'), NULL, 'restore');
					
					// find autosave files
					$autosaves = $file->dir( )->find(
									str_replace('.', File::SEPARATOR, $file->name( ))
									.File::SEPARATOR
									.'*'
									.Kohana_File_Content_Autosave::EXTENSION
								);
					
					
					if (count($autosaves) > 0)
					{
						InclStream::instance( )->add('files.js');
					
						foreach ($autosaves AS $extra_file)
						{
							preg_match('/(\d{4})(\d{2})(\d{2})'.File::SEPARATOR.'(\d{2})(\d{2})(\d{2})/', $extra_file->name( ), $matches);
						
							$submenu->child(
								"{$matches[1]}-{$matches[2]}-{$matches[3]} {$matches[4]}:{$matches[5]}:{$matches[6]}",
								$file->action('restore', array('sav' => $extra_file->path(FALSE)))
							)
							->action('Files.restore');
	// 						->confirm( );
						}
					}
					else
					{
						$excluded[] = 'restore';
					}
				}	
				
				$menu->render( );

				return Cms::wrap($this->_make_editable($file), array($menu->context(NULL, $excluded)), $id);
			}
		}
		/* read file and get data */
		else
		{
			return $this->_capture($file);
		}
	}
	
	/** Action: restore file from sav file
	 *
	 * @param   Kohana_File parent Kohana_File instance
	 * @return 	JSON
	 */
	public function action_restore(Kohana_File $file)
	{
		if (Site::config('site')->content_use_autosave === FALSE)
		{
			throw new File_Exception('Content autosaving is disabled.');
		}
				
		// load autosave
		$autosave = File::factory($_GET['sav']);
		
		// check existence
		if ( ! $autosave->exists( ))
		{
			throw new File_Exception('Cannot load autosave :file', array(':file' => $_GET['sav']));
		}

		// check compatibility
		if ( ! $autosave->check($file))
		{
			throw new File_Exception(
				'Autosave file :autosave is not version of :file',
				array(
					':autosave' => $_GET['sav'],
					':file' => $file->path( ),
				)
			);
		}
		
		$content = $autosave->content( );
		
		// copy autosave
		$this->_write($file, $content);
		
		// remove autosave
		$autosave->remove( );
		
		// return new fragment text
		return	Basic::json_safe_encode(array(
					'text' => $content,
				));
	}
	
	/** Action: save file handler
	 *
	 * @param   Kohana_File parent Kohana_File instance
	 * @return 	array
	 */
	public function action_save(Kohana_File $file)
	{
		$file->content($_POST['text']);
		
		return 0;
	}
	
	/** Action: autosave file handler
	 *
	 * @param   Kohana_File parent Kohana_File instance
	 * @return 	array
	 */
	public function action_autosave(Kohana_File $file)
	{
		// if current session autosave exists
		if (isset($_POST['filename']))
		{
			// load already created file
			$autosave = File::factory($_POST['filename']);
		}
		else
		{
			// create new autosave file (ru_body.html => ru_body_html_20140722095423.sav)
			$autosave = $file->autosave( );
		}
		
		if ( ! acl('file_read', $autosave) || ! acl('file_write', $autosave))
		{
			throw new Access_Exception;
		}
		
		// save text
		$autosave->content($_POST['text']);
		
		return Basic::json_safe_encode(array(
					'filename' 	=> $autosave->path(TRUE),
					'status'	=> 0,
				));
	}
	
	/** Action: edit file
	 *
	 * @param   Kohana_File parent Kohana_File instance
	 * @return 	array
	 */
	public function action_edit(Kohana_File $file)
	{
		if ( ! acl('file_write', $file))
		{
			throw new Access_Exception;
		}
		
		InclStream::instance( )->add('cms.page.js');
		
		$form = Form::factory( )->clear_on_success(FALSE);
		$body = $form->render(
					(string) $form
								->field('editor_basic', __('text'), 'text')
								->value(file_get_contents($file->path( )))
								->settings(array(
									'show_save_btn' => TRUE,
									'maximize'		=> TRUE,
								))
				);
		
		if ($form->sent( ))
		{
			$file->content($file, $form->result( )->text( ));
// 			$body = Site::redirect($file->url( ));
		}
		
		return array(
			'body' => $body,
// 			'window_message' => 
		);
	}
}