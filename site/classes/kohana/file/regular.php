<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Basic filetype: normal file
 * @package 	Files
 * @author 		Stanislav U. Alkimovich
 * @date 		2013-10-09
 *
 **/

class Kohana_File_Regular extends File_Driver
{
	const READ_CHUNK_SIZE = 4096;

	/**
	 * @var string		ico color
	 */
	protected $_ico_color;

	/**
	 * @var string		view name
	 */
	protected $_item_template = 'files.item.regular';

	/**
	 * @var string		default file extension
	 */
	protected $_default_ext = 'txt';

	
	/** rewind Kohana_File content
	 *
	 * @param 	Kohana_File	file
	 * @return 	void
	 */
	public function rewind(Kohana_File $file)
    {
		$this->_init($file);
    }

	/** Return current worksheet
	 *
	 * @param 	Kohana_File	file
	 * @return 	Kohana_File
	 */
    public function current(Kohana_File $file)
    {
		// return current phpExcel object
		return fgets($file->txt_resource);
    }

	/** Get current key of Kohana_File content
	 *
	 * @param 	Kohana_File	file
	 * @return 	scalar
	 */
    public function key(Kohana_File $file)
    {
		// return current key
		return $file->txt_line_num;
    }

	/** Switch Kohana_File content to the next element
	 *
	 * @param 	Kohana_File	file
 	* @return 	void
	 */
    public function next(Kohana_File $file)
    {
		// increase key
		$file->txt_line_num ++;
    }

	/** Validate current content item
	 *
	 * @param 	Kohana_File	file
	 * @return 	boolean
	 */
    public function valid(Kohana_File $file)
    {
		return !feof($file->txt_resource);	
    }
 	
	/** initialize file resource
	 *
	 * @param 	Kohana_File
	 * @return 	object
	 */
	protected function _init(Kohana_File $file)
	{
		$file->txt_resource = fopen($file->path( ), 'r+b');
		$file->txt_line_num = 0;
		
		if ($file->txt_resource === FALSE)
		{
			throw new File_Exception('Cannot read file :file', array(':file' => $file->path( )));
		}
	}
	
	/** Get extension color
	 *
	 * @return 	string		html-code of color
	 */
	protected function _ico_color( )
	{
		$color = '343333';
/*
		switch ($this->_orm( )->mime)
		{
			case 'image/jpeg':
				break;
			case '':
				break;
			default:
				;
		}
	*/
		return $color;
	}

	/** algorythm of file creation
	 *
	 * @param   Kohana_File parent Kohana_File instance
	 * @return 	Kohana_File
	 */
	protected function _create(Kohana_File $file)
	{
		fclose(fopen($file->path( ), 'a'));
		
		$file->chmod( );
		
		return $file;
	}
	
	/** Get file ico
	 *
	 * :TODO: rework
	 *
	 * @param	string		route name
	 * @param	array		array of parameters
	 * @param	string		query string
	 * @param	string		presents name
	 * @return 	mixed
	 */
	public function html($route, $params = array( ), $query = '', $name = NULL)
	{
		$out = View::factory($this->_item_template( ), array(
					'name' 	=> isset($name) ? $name : $this->_orm( )->name,
					'href'	=> Route::url($route, array_merge($params, array(File::ROUTE_PATH_VAR => $this->_orm( )->uri))).$query,
				));

		return $out;
	}

	/** Download file
	 *
	 * @param   Kohana_File
	 * @return void
	 */
	public function download(Kohana_File $file, $filename = NULL)
	{
		if ( ! acl('file_read', $file))
		{
			throw new Access_Exception;
		}
		
		// $filepath – путь к файлу, который мы хотим отдать
		// $mimetype – тип отдаваемых данных (можно не менять)
// 		function func_download_file($filepath, $mimetype = 'application/octet-stream') {
		$fd = @fopen($file->path( ), 'rb'); // открываем файл на чтение в бинарном режиме

		// default range
		$range = 0;

		// detect partial downloading :TODO:
		if (isset($_SERVER['HTTP_RANGE']))
		{
			// get already downloaded part
			$range = $_SERVER['HTTP_RANGE'];
			$range = str_replace('bytes=', '', $range);
			list($range, $end) = explode('-', $range);

			if ( ! empty($range))
			{
				// go to last downloaded byte
				fseek($fd, $range);
			}

			// send HTTP 206 code
			header($_SERVER['SERVER_PROTOCOL'].' 206 Partial Content');
		}
		else
		{
			header($_SERVER['SERVER_PROTOCOL'].' 200 OK');
		}

		Request::current( )->response( )->headers('Accept-Ranges',  'bytes');
		Request::current( )->response( )->headers('Content-Length', ($file->size( ) - $range));
		Request::current( )->response( )->headers('Last-Modified', date('D, d M Y H:i:s T', filemtime($file->path( ))));
		Request::current( )->response( )->headers('Content-Type', $file->mime_type( ));
		
		$filename = empty($filename) ? str_replace(array('\'', '"'), array('\\\'', ''), $file->name( )) : $filename;
		
		Request::current( )->response( )->headers('Content-Disposition',  'attachment; name="'.$filename.'"');
		Request::current( )->response( )->headers('Content-Disposition',  'attachment; filename="'.$filename.'"');

		Request::current( )->response( )->send_headers( );

		if ($range != 0)
		{
			Request::current( )->response( )->headers('Content-Range', "bytes $range-".($file->size( ) - 1).'/'.$file->size( ));
// 			header("Content-Range: bytes $range-".($filesize - 1).'/'.$filesize);
		}

		while (!feof($fd)) {
			$buffer = fgets($fd, File_Regular::READ_CHUNK_SIZE);
			echo $buffer;
			ob_flush( );
		}

		fclose($fd);
	}

	/** Get extension color
	 *
	 * :TODO: rework
	 *
	 * @return 	string		html-code of color
	 */
	public function ico_color( )
	{
		return $this->_ico_color( );
	}


	/** Copy file
	 *
	 * @param	Kohana_File		source
	 * @param	mixed			destination folder
	 * @param	mixed			new name
	 * @param	integer			mode of collision resolution 
	 * @return	Kohana_File		file object of copied file
	 */
	public function copy(Kohana_File $file, $dest = NULL, $target = NULL, $collision_mode = File::COPY_CANCEL)
	{
		if ($file->exists( ) === FALSE)
		{
			throw new File_Exception('Cannot copy :src: source file not exists.', array(':src' => $file->path( )));
		}
		
		if ( ! acl('file_read', $file))
		{
			throw new Access_Exception('Cannot read :src -- permission denied.', array(':src' => $file->path( )));
		}
		
		// check system access for writing -- system privileges will be copied too and this script should create rewritable files
		if ( ! is_writable($file->path( )))
		{
			throw new Access_Exception('Cannot copy :src -- file must be writable.', array(':src' => $file->path( )));
		}
		
		if (empty($dest) || ! is_object($dest))
		{
			$dest = File::factory($dest);
		}
		elseif ( ! $dest instanceOf Kohana_File)
		{
			throw new File_Exception(
				'Destination must be a Kohana_File instance, :type given',
				array(':type' => gettype($dest) == 'object' ? get_class($dest) : gettype($dest))
			);
		}
	
		if ( ! $dest->exists( ))
		{
			$dest->driver('File_Directory')->create( );
		}
	
		if ($dest->is_dir( )) 
		{
			$dest = $dest->child(empty($target) ? $file->name( ) : $target);
		}
		
		if ( ! acl('file_write', $dest->dir( )))
		{
			throw new Access_Exception('Cannot copy :src to :dst -- permission denied.', array(':src' => $file->path( ), ':dst' => $dest->path( )));
		}
		
		if ($dest->exists( ))
		{
			if ($file == $dest)
			{
				throw new File_Exception('Cannot copy file :src into itself', array(':src' => $file->path( )));
			}
		
			switch ($collision_mode)
			{
				case File::COPY_REWRITE:
					break;
					
				case File::COPY_SKIP:
					return $dest;
				
				
				case File::COPY_CANCEL:
					throw new File_Exception('Cannot copy :src to :dst: destination already exists', array(':src' => $file->path( ), ':dst' => $dest->path( )));
			}
		}
		elseif ( ! $dest->dir( )->exists( ))
		{
			// create parents
			$dest->dir( )->driver('File_Directory')->create( );
		}

		try
		{
			if (copy($file->path( ), $dest->path( )) === FALSE)
			{
				throw new File_Exception('Cannot copy :src to :dst.', array(':src' => $file->path( ), ':dst' => $dest->path( )));
			}
		}
		catch (Exception $e)
		{
			throw new File_Exception('Cannot copy file: '.$e->getMessage( ));
		}
		
		// set default mode
		$dest->chmod( );

		// re-initialize destination file object
		$dest->init( );
		
		/* :TODO: WINDOWS exception for empty file copy; see http://php.net//manual/ru/function.copy.php */
		return $dest;
	}

	/** Move file
	 *
	 * @param	Kohana_File		source
	 * @param	mixed			destination
	 * @param	mixed			new name
	 * @param	integer			mode of collision resolution 
	 * @return 	Kohana_File		reloaded source
	 */
	public function move(Kohana_File $file, $dest = NULL, $target = NULL, $collision_mode = File::COPY_CANCEL)
	{
		if ($file->exists( ) === FALSE)
		{
			throw new File_Exception('Cannot move :src: source file not exists.', array(':src' => $file->path( )));
		}
		
		if ( ! acl('file_read', $file))
		{
			throw new Access_Exception('Cannot read :src -- permission denied.', array(':src' => $file->path( )));
		}
		
		// check system access for writing -- system privileges will be copied too and this script should create rewritable files
		if ( ! is_writable($file->path( )))
		{
			throw new Access_Exception('Cannot move :src -- file must be writable.', array(':src' => $file->path( )));
		}
		
		if (empty($dest) || ! is_object($dest))
		{
			$dest = File::factory($dest);
		}
		elseif ( ! $dest instanceOf Kohana_File)
		{
			throw new File_Exception(
				'Destination must be a Kohana_File instance, :type given',
				array(':type' => gettype($dest) == 'object' ? get_class($dest) : gettype($dest))
			);
		}
	
		if ( ! $dest->exists( ))
		{
			$dest->driver('File_Directory')->create( );
		}
		elseif ( ! $dest->is_dir( )) 
		{
			throw new File_Exception('Cannot move :src to :dst: destination must be a directory', array(':src' => $file->path( ), ':dst' => $dest->path( )));
		}
			
		$dest = $dest->child(empty($target) ? $file->name( ) : $target);
		
		if ( ! acl('file_write', $dest->dir( )))
		{
			throw new Access_Exception('Cannot move :src to :dst -- permission denied.', array(':src' => $file->path( ), ':dst' => $dest->path( )));
		}
		
		if ($dest->exists( ))
		{
			switch ($collision_mode)
			{
				case File::COPY_REWRITE:
					if ( ! acl('file_write', $dest))
					{
						throw new Access_Exception('Cannot move :src to :dst -- denied access for writing.', array(':src' => $file->path( ), ':dst' => $target->path( )));
					}
					
					break;
					
				case File::COPY_SKIP:
					return $dest;
				
				
				case File::COPY_CANCEL:
					throw new File_Exception('Cannot move :src to :dst: destination already exists', array(':src' => $file->path( ), ':dst' => $dest->path( )));
			}
		}
		elseif ( ! $dest->dir( )->exists( ))
		{
			// create parents
			$dest->dir( )->driver('File_Directory')->create( );
		}

		// move file
		try
		{
			if (rename($file->path( ), $dest->path( )) === FALSE)
			{
				throw new File_Exception('Cannot move :src to :dst.', array(':src' => $file->path( ), ':dst' => $dest->path( )));
			}
		}
		catch (Exception $e)
		{
			throw new File_Exception('Cannot move file: '.$e->getMessage( ));
		}
		
		// change file path
		$file->overload($dest);

		// set default mode
// 		$file->chmod( );

		/* :TODO: WINDOWS exception for empty file copy; see http://php.net//manual/ru/function.copy.php */
		return $file;
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
		if (isset($text))
		{
			if ( ! acl('file_write', $file))
			{
				throw new Access_Exception;
			}
			
			file_put_contents($file->path( ), $text, $mode);
			
			return TRUE;
		}
		
		if ( ! acl('file_read', $file))
		{
			throw new Access_Exception;
		}
	
		
		return file_get_contents($file->path( ));
	}
	
	/** Remove from server
	 *
	 * @param	Kohana_File	source
	 * @return 	Kohana_File
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
		
		$orm = ORM::factory('file')
			->where('filename', '=', $file->path(FALSE));
			
		$result = $orm->find_all();
		
		// remove all database variables
		foreach ($result AS $db_record)
		{
			$db_record->delete();
		}
		
		// remove file
		if (unlink($file->path( )) === FALSE)
		{
			throw new File_Exception('Cannot remove file :file', array(':file' => $file->path( )));
		}
		
		// remove Kohana_File object from instances stack
		$file->overload( );
		
		return $file;
	}

	/** Create symlink
	 *
	 * @param	string
	 */
	public function symlink( )
	{

	}
	
	/** Write and/or get access variables for current file
	 *
	 * @param   Kohana_File parent Kohana_File instance
	 * @param 	array	access rules
	 * @return 	array
	 */
	public function access(Kohana_File $file, $rules = NULL)
	{
		// find privileges
		$access = $file->access_file( )->content( );
		
		/* write rules */
		/* :TODO: shall use access object methods for saving */
		if ($rules !== NULL)
		{
			if ($file instanceOf Archive_Item)
			{
				throw new File_Exception('Cannot save access rules for :file -- file is in archive.', array(':file' => $file->path(FALSE)));
			}
		
			$access[File::ACCESS_FILES_KEY][$this->_name( )] = $rules;
			
			$access_file->content($access);
		}
		
		return isset($access[File::ACCESS_FILES_KEY][$file->name( )])
				? $access[File::ACCESS_FILES_KEY][$file->name( )]
				: $access[File::ACCESS_DIR_KEY];
	}

	/** Print remove button
	 *
	 * @param   Kohana_File parent Kohana_File instance
	 * @return 	Html_Anchor
	 */
	public function btn_remove(Kohana_File $file)
	{
		if ( ! acl('file_write', $file))
		{
			throw new Access_Exception;
		}
		
		InclStream::instance( )->add('file.ctrl.js');
		
		return Html::factory('anchor')->href($file->action('remove'))->classes(array('f-remove', 'cms-delete'));
	}
	
	
	/** Record file
	 *
	 * @param   Kohana_File parent Kohana_File instance
	 * @return 	mixed
	 */
	public function action_remove(Kohana_File $file)
	{
		try {
			$this->remove($file);

			return Basic::json_safe_encode(array(
				'result' => TRUE,
				'message' => '',
			));
		} catch (Exception $e) {
		
			return Basic::json_safe_encode(array(
				'result' => FALSE,
				'message' => $e->getMessage(),
			));
		}
		
	}
	
}