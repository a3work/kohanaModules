<?php defined('SYSPATH') or die('No direct script access.');
/**
 *
 * @name		Common engine of files collection
 * @package 	Files
 * @author 		Stanislav U. Alkimovich <a3.work@gmail.com>
 * @date 		2013-10-09
 *
 **/
abstract class Kohana_File_Collection extends File_Driver
{
	/*
	 * @var string		view name
	 */
	protected $_item_template = 'files.item.directory';
	

	/** Return count if child files
	 *
	 * @param 	Kohana_File	file
	 * @return 	integer
	 */
	public function count(Kohana_File $file)
	{
		if ($file->loaded( ) === FALSE)
		{
			$this->_load_content($file);
		}
		
		return count($file->content);
	}
	
	/** Load content
	 *
	 * @param 	Kohana_File
	 * @return 	void
	 */
	protected function _load_content(Kohana_File $file)
	{
		$file->content = $this->_glob($file);

		// mark collection as loaded
		$file->loaded(TRUE);
	}
	
	/** rewind Kohana_File content
	 *
	 * @param 	Kohana_File	file
	 * @return 	void
	 */
	public function rewind(Kohana_File $file)
    {
		if ($file->loaded( ) === FALSE)
		{
			$this->_load_content($file);
		}

		reset($file->content);
    }

	/** Create and return current item of Kohana_File content
	 *
	 * @param 	Kohana_File	file
	 * @return 	Kohana_File
	 */
    public function current(Kohana_File $file)
    {
		// get current key
		$key = key($file->content);

		if (is_string($file->content[$key]))
		{
			// get child file object
			$file->content[$key] = call_user_func_array(array($file->item_class, 'factory'), array(current($file->content)));
		}
    
        return $file->content[$key];
    }

	/** Get current key of Kohana_File content
	 *
	 * @param 	Kohana_File	file
	 * @return 	scalar
	 */
    public function key(Kohana_File $file)
    {
        return key($file->content);
    }

	/** Switch Kohana_File content to the next element
	 *
	 * @param 	Kohana_File	file
 	* @return 	void
	 */
    public function next(Kohana_File $file)
    {
		if ( ! isset($file->content_removed))
		{
			next($file->content);
		}
		else
		{
			unset($file->content_removed);
		}
    }

	/** Roll cursor back
	 *
	 * @param 	Kohana_File	file
 	* @return 	void
	 */
    public function prev(Kohana_File $file)
    {
		prev($file->content);
    }

	/** Validate current content item
	 *
	 * @param 	Kohana_File	file
	 * @return 	boolean
	 */
    public function valid(Kohana_File $file)
    {
        if (key($file->content) !== NULL)
        {
			return TRUE;
        }
        else
        {
			return FALSE;
        }
    }
 	
	/** algorythm of file creation
	 *
	 * @param   Kohana_File parent Kohana_File instance
	 * @return 	void
	 */
	protected function _create(Kohana_File $file)
	{
		throw new File_Exception('Cannot create search instance.');
	}
	
	/** Find file by pattern
	 *
	 * @param   Kohana_File parent Kohana_File instance
	 * @param   integer		PHP glob flags :TODO: make possible usage of flags
	 * @return array
	 */
	protected function _glob(Kohana_File $file, $flags = 0)
	{
		$pattern = $file->path( ).$file->pattern( );
	
		// find file using pattern
        $result = glob($pattern, $flags);
		
        // set file names as keys
        if (count($result) > 0)
        {
			if ($flags != 0)
			{
				$result = array_merge(array_combine($result, $result));
			}
			else
			{
				// add directories to begin of result array
				$dirs = glob($pattern, GLOB_ONLYDIR);
				$result = array_merge(
							array_combine($dirs, $dirs),
							array_combine($result, $result)
						  );
			}
        }

        return $result;
	}
	
	/** Write and/or get access variables for current directory
	 *
	 *	 example record for access file: {"dir":{"0":["page_view"]}}
	 *	
	 * @param   Kohana_File parent Kohana_File instance
	 * @param 	array	access rules
	 * @return 	array
	 */
	public function access(Kohana_File $file, $rules = NULL)
	{
		if ( ! $file->access_file( )->loaded( ))
		{
			throw new Access_Exception('Cannot load access file for :path', array(':path' => $file->path( )));
		}

		// find privileges
		$access = $file->access_file( )->content( );
		
		/* write rules */
		/* :TODO: shall use access object methods for saving */
		if ($rules !== NULL)
		{
			$access[File::ACCESS_DIR_KEY] = $rules;
			
			$access_file->content($access);
		}
		
		return $access[File::ACCESS_DIR_KEY];
	}
	
	/** Load content
	 *
	 * @param   Kohana_File parent Kohana_File instance
	 * @return 	array
	 */	
	public function content(Kohana_File $file)
	{
		if ( ! $file->loaded( ))
		{
			// make file objects
			foreach ($file AS $item) {}
		}
		
		return $file->content;
	}
	
	/** Find files in current directory
	 *
     * @param   Kohana_File		parent Kohana_File instance
	 * @param 	string			pattern
	 * @return 	Collection
	 */	
	public function find(Kohana_File $file, $pattern)
	{
		$collection = Collection::factory($file->path( ));
		$collection->pattern($pattern);
		
		return $collection;
	}
	
	/** Find files by pattern and get first result
	 *
	 * shortcut for find( )
	 *
	 * @param 	Kohana_File		parent Kohana_File instance
	 * @param 	string			pattern
	 * @return 	Kohana_File
	 */
	public function child(Kohana_File $file, $pattern)
	{
		$collection = $this->find($file, $pattern);
		
		foreach ($collection AS $item)
		{
			return $item;
		}
		
		$location = $file->path( ).ltrim($pattern, DIRECTORY_SEPARATOR);
		return File::factory($location);
	}
	
	/** Get file collection filtered by db-stored attribute and filepath
	 *  optional filter collection by key and/or value
	 *
     * @param   Kohana_File		parent Kohana_File instance
	 * @param 	string			key
	 * @param 	string			value
	 * @return 	Collection
	 */	
	public function db_find(Kohana_File $file, $key = NULL, $value = NULL)
	{
		$collection = Collection::factory($file->path( ))->set_db_driver( );
		
		// filter by key
		if (isset($key))
		{
			$collection->where('key', '=', $key);
		}

		// filter by value
		if (isset($value))
		{
			$collection->where('value', '=', $value);
		}
		
		return $collection;
	}
	
	/** Add to archive
	 *
	 * @param 	Kohana_File		source
	 * @param 	Kohana_File		destination
	 * @param 	string			new filename
	 * @param 	string			class of archive driver
	 * @return 	Kohana_File 	destination
	 */
	public function compress(Kohana_File $src, $dst = NULL, $filename = NULL, $driver = 'File_Zip')
	{
		// auto create archive for specified file
		if ( ! isset($dst))
		{
			if ($src->name( ) !== NULL)
			{
				$dst = File::factory(
							rtrim($src->path( ), DIRECTORY_SEPARATOR).
							'.'.
							File_Driver::factory($driver)->default_ext( )
						)
						->driver($driver)
						->create( );
			}
			else
			{
				$dst = File::factory(
							rtrim($src->path( ), DIRECTORY_SEPARATOR).
							Basic::get_hash(mt_rand( ), 'md5', 8).
							'.'.
							File_Driver::factory($driver)->default_ext( )
						)
						->driver($driver)
						->create( ); 
			}
		}
		
		return parent::compress($src, $dst, $filename, $driver);
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
	 * @return 	void
	 */
	public function download(Kohana_File $file)
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
		Request::current( )->response( )->headers('Content-Disposition',  'attachment; "'.$file->name( ).'"');

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
	
	/** Copy collection
	 *
	 *  Files will be copied into specified target directory with saving relative path if target is defined.
	 *  If target not defined, then all files will be copied into specified directory.
	 *  All collisions will be resolved using collision_mode.
	 *
	 * @param	Kohana_File		source
	 * @param	mixed			destination
	 * @param	mixed			new name
	 * @param	integer			mode of collision resolution 
	 * @param	boolean			rewrite access file for destination
	 * @return	Kohana_File		collection of copied files OR target directory (if target specified)
	 */
	public function copy(Kohana_File $file, $dest = NULL, $target = NULL, $collision_mode = File::COPY_CANCEL, $rewrite_access = FALSE)
	{
		/* Getting of destination object */
		if (empty($dest) || is_string($dest))
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
		/* Getting of destination object ends */
		
		if ( ! $dest->exists( ))
		{
			$dest->driver('File_Directory')->create( );
		}
		
		if ( ! $dest->is_dir( )) 
		{
			throw new File_Exception('Cannot copy collection to :dst: destination must be a directory.', array(':dst' => $dest->path( )));
		}
	
		/* Processing of target */
		if (isset($target))
		{
			if (is_string($target))
			{
				$dest = $dest->child($target);
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
			
			if ( ! $dest->is_dir( )) 
			{
				throw new File_Exception('Cannot copy collection to :dst: destination must be a directory.', array(':dst' => $dest->path( )));
			}
		
			foreach ($file AS $child)
			{
				if ( ! $rewrite_access && $child->driver_obj( ) instanceOf File_Access)
				{
					continue;
				}
				
				$child->copy($dest, $child->path(FALSE), File::COPY_REWRITE, $rewrite_access);
			}
			
			// return new directory
			return $dest;
		}
		else
		{
			/* Write all files to target directory and return collection */
			$result = Collection::factory( );
			
			/* copy each child to specified destination directory */
			foreach ($file AS $child)
			{
				if ( ! $rewrite_access && $child->driver_obj( ) instanceOf File_Access)
				{
					continue;
				}
				
				$result->append($child->copy($dest, NULL, $collision_mode, $rewrite_access));
			}
			
			// return new collection
			return $result;
		}
		/* Processing of target ends */
	}

	/** Move collection
	 *
	 *  Files will be moved into specified target directory with saving relative path if target is defined.
	 *  If target not defined, then all files will be moved into specified directory. All collisions will be resolved using collision_mode.
	 *
	 * @param	Kohana_File		source
	 * @param	mixed			destination
	 * @param	mixed			new name
	 * @param	integer			mode of collision resolution OR target directory (if target specified)
	 * @param	boolean			rewrite access file for destination
	 * @return 	Kohana_File		target directory (if target specified) or this collection
	 */
	public function move(Kohana_File $file, $dest = NULL, $target = NULL, $collision_mode = File::COPY_CANCEL, $rewrite_access = FALSE)
	{
		/* Getting of destination object */
		if (empty($dest) || is_string($dest))
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
		/* Getting of destination object ends */
	
		/* Processing of target */
		if (isset($target))
		{
			if (is_string($target))
			{
				$dest = $dest->child($target);
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
			
			if ( ! $dest->is_dir( )) 
			{
				throw new File_Exception('Cannot copy collection to :dst: destination must be a directory.', array(':dst' => $dest));
			}
			
			$access_objects = array( );
			
			// move files
			foreach ($file AS $child)
			{
				if ($child->driver_obj( ) instanceOf File_Access)
				{
					$access_objects[] = $child;
					continue;
				}
				
				$child->move($dest, $child->path(FALSE), $collision_mode, $rewrite_access);
			}
			
			// move access files
			if ($rewrite_access)
			{
				foreach ($access_objects AS $child)
				{
					$child->move($dest, $child->path(FALSE), $collision_mode, $rewrite_access);
				}
			}
		}
		else
		{
			/* Write all files to target directory and return collection */
			$access_objects = array( );
			
			/* Move all files to target directory */
			foreach ($file AS $child)
			{
				if ($child->driver_obj( ) instanceOf File_Access)
				{
					$access_objects[] = $child;
					continue;
				}
				
				$child->move($dest, NULL, $collision_mode, $rewrite_access);
			}
			
			// move access files
			if ($rewrite_access)
			{
				foreach ($access_objects AS $child)
				{
					$child->move($dest, NULL, $collision_mode, $rewrite_access);
				}
			}
			
		}
		/* Processing of target ends */
		
		// return new collection
		return $file;
	}

	/** Remove from server
	 *
	 * @param	Kohana_File	source
	 * @return 	Kohana_File
	 */
	public function remove(Kohana_File $file)
	{
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
		
		return $file;
	}


	/** Set or get attributes
	 *
     * @param   Kohana_File parent Kohana_File instance
	 * @param 	array	attrubutes
	 * @return 	array	rules array
	 */
	public function attr(Kohana_File $file, $rules = NULL)
	{

	}

	/** Create symlink
	 *
	 * @param	string
	 */
	public function symlink( )
	{

	}
}