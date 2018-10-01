<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Files catalogue
 * @package 	Files
 * @author 		Stanislav U. Alkimovich
 * @date 		2013-10-09
 *
 **/

class Kohana_File_Directory extends File_Collection
{
	// view name
	protected $_item_template = 'files.item.directory';

	/** Load content
	 *
	 * @param 	Kohana_File
	 * @return 	void
	 */
	protected function _load_content(Kohana_File $file)
	{
		if ( ! acl('file_read', $file))
		{
			throw new Access_Exception( );
		}
		
		parent::_load_content($file);
	}

	/** Add file to collection
	 *
	 * @param	Kohana_File				parent collection
	 * @param	mixed					file to add
	 * @return	Kohana_Collection		current collection
	 */
	public function append(Kohana_File $dir, $file)
	{
		if ($dir->loaded( ))
		{
		
			if (is_string($file))
			{
				$file = File::factory($file);
			}
			
			// remove slashes from end of path
			$path = $file->is_dir( ) ? rtrim($file->path( ), DIRECTORY_SEPARATOR) : $file->path( );

			$dir->content[$path] = $file;
		}
	
		return $dir;
	}

	/** Replace file in collection
	 *
	 * @param	Kohana_File				collection
	 * @param	mixed					path
	 * @param	Kohana_File				new file object
	 * @return	Kohana_Collection		current collection
	
	public function replace(Kohana_File $dir, $path, Kohana_File $file)
	{
		if ($dir->loaded( ))
		{
			if (isset($dir->content[$path]))
			{
				$dir->content[$path] = $file;
			}
		}
	
		return $dir;
	}
 */
	/** Load to collection
	 *
	 * @param 	Kohana_File		Collection
	 * @param 	mixed			File
	 * @return 	Kohana_File
	 */
	public function cut(Kohana_File $dir, $file)
	{
		if ($dir->loaded( ))
		{
			if (is_string($file))
			{
				$file = File::factory($file);
			}
		
			/* roll cursor back */
			// array of content keys
			$keys = array_keys($dir->content);
			
			// :TODO: standard of path-keys
			$path = $file->is_dir( ) ? rtrim($file->path( ), DIRECTORY_SEPARATOR) : $file->path( );
			
			// current key
			$key = array_search($path, $keys);
			
			if ($key !== FALSE)
			{
				unset($dir->content[$path]);
				
// 				// if $file has been processed move back pointer of list
				if (array_search(key($dir->content), $keys) >= $key)
				{
					$dir->content_removed = TRUE;
				}
			}
		}
		
		return $file;
	}

	/** chmod realization
	 *
	 * @param	integer		octal mode
     * @param   Kohana_File parent Kohana_File instance
	 * @return 	Kohana_File
	 */
	public function chmod(Kohana_File $file, $mode = NULL)
	{
		if (empty($mode))
		{
			$mode = Site::config('site')->new_dir_mode;
		}
	
		/* :TODO: write execute of OS command chmod if need */
		chmod($file->path( ), $mode);
		
		return $file;
	}
	
	
	/** algorythm of file creation
	 *
	 * @param   Kohana_File 	parent Kohana_File instance
	 * @param   boolean 		create without access file
	 * @return 	Kohana_File
	 */
	protected function _create(Kohana_File $file, $without_access_file = FALSE)
	{
		try
		{
			mkdir($file->path( ));
			
			$file->chmod( );
		}
		catch (Exception $e)
		{
			throw new File_Exception($e->getMessage( ));
		}

		// re-initialize object
		$file->init( );
		
		if ( ! $without_access_file)
		{
			// create access file
			$file->access_file( )->create( );
		}
		
		return $file;
	}
	
	/** Create file and copy access rules
	 *
     * @param   Kohana_File 	parent Kohana_File instance
     * @param   boolean 		check permissions flag
	 * @return 	Kohana_File
	 */
	public function create(Kohana_File $file, $use_acl = TRUE)
	{
		// check file existence
		if ($file->exists( ))
		{
			throw new File_Exception('File :file is already exists.', array(':file' => $file->path( )));
		}
		
		// recoursive creation of parents
		if ( ! $file->dir( )->exists( ))
		{
			$file->dir( )->driver('File_Directory')->create( );
		}
		
		if ($file->is_root( ))
		{
			throw new File_Exception('Cannot create root directory.');
		}
		
		if ($use_acl && ! acl('file_write', $file->dir( )))
		{
			throw new Access_Exception;
		}
		
		// create file if not exists
		call_user_func_array(array($this, '_create'), func_get_args( ));
		
		// re-init file
		$file->init( );
		
		return $file;
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
		
		if ($file->is_root( ))
		{
			throw new File_Exception('Cannot remove root directory.');
		}
	
		parent::remove($file);

		// remove access file
		$file->access_file( )->remove( );
		
		// remove directory
		if (rmdir($file->path( )) === FALSE)
		{
			throw new File_Exception('Cannot remove directory :dir', array(':dir' => $file->path( )));
		}
		
		// remove Kohana_File object from instances stack
		$file->overload( );
		
		// drop file parameters
		$file->init( );
		
		return $file;
	}
	
	/** Copy directory
	 *
	 * @param	Kohana_File		source
	 * @param	mixed			destination
	 * @param	mixed			new name
	 * @param	integer			mode of collision resolution 
	 * @param	boolean			rewrite access file for destination
	 * @return	Kohana_File		file object of copied directory
	 */
	public function copy(Kohana_File $file, $dest = NULL, $target = NULL, $collision_mode = File::COPY_CANCEL, $rewrite_access = FALSE)
	{
		if ( ! acl('file_read', $file))
		{
			throw new Access_Exception('Cannot read :src -- permission denied.', array(':src' => $file->path( )));
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
	
		if ( ! $target instanceOf Kohana_File)
		{
			if ( ! $dest->exists( ))
			{
				$dest->driver('File_Directory')->create( );
			}
			elseif ( ! $dest->is_dir( ))
			{
				throw new File_Exception('Cannot copy :src to :dst: destination must be a directory', array(':src' => $file->path( ), ':dst' => $dest->path( )));
			}
			
			if (empty($target) || ! is_object($target))
			{
				$target = $dest->child(empty($target) ? $file->name( ) : $target);
			}
			elseif ( ! $target instanceOf Kohana_File)
			{
				throw new File_Exception(
					'Destination must be a Kohana_File instance, :type given',
					array(':type' => gettype($target) == 'object' ? get_class($target) : gettype($target))
				);
			}
		}

		if ( ! acl('file_write', $target->dir( )))
		{
			throw new Access_Exception('Cannot copy :src to :dst -- permission denied.', array(':src' => $file->path( ), ':dst' => $target->dir( )->path( )));
		}
		
		if ($target->exists( ))
		{
			if ($src == $target)
			{
				throw new File_Exception('Cannot copy directory :src into itself', array(':src' => $file->path( )));
			}
		
			switch ($collision_mode)
			{
				case File::COPY_REWRITE:
					if ( ! acl('file_write', $target))
					{
						throw new Access_Exception('Cannot move :src to :dst -- denied access for writing.', array(':src' => $file->path( ), ':dst' => $target->path( )));
					}
					
					// write into existing target directory or remove destination file
					if ( ! $target->is_dir( ))
					{
						$target->remove( );
					}
					
					break;
					
				case File::COPY_SKIP:
					return $target;
				
				case File::COPY_CANCEL:
					throw new File_Exception('Cannot copy :src to :dst: target already exists', array(':src' => $file->path( ), ':dst' => $target->path( )));
			}
		}
		else
		{
			// create directory without child access file
			$target->driver('File_Directory')->create( );
		}
		
		/* copy each child file excluding files of access rules to destination */
		foreach ($file AS $child)
		{
			if ( ! $rewrite_access && $child->driver_obj( ) instanceOf File_Access)
			{
				continue;
			}
			
			$child->copy($target, NULL, $collision_mode, $rewrite_access);
		}
		
		/* :TODO: WINDOWS exception for empty file copy; see http://php.net//manual/ru/function.copy.php */
		
		return $target;		
	}
	
	/** Move file
	 *
	 * @param	Kohana_File		source
	 * @param	mixed			destination
	 * @param	string			new name
	 * @param	integer			mode of collision resolution 
	 * @param	boolean			rewrite access file for destination
	 * @return 	Kohana_File
	 */
	public function move(Kohana_File $file, $dest = NULL, $target = NULL, $collision_mode = File::COPY_CANCEL, $rewrite_access = FALSE)
	{
		if ($file->is_root( ))
		{
			throw new File_Exception('Cannot move parent directory.');
		}
	
		if ( ! acl('file_read', $file))
		{
			throw new Access_Exception('Cannot read :src -- permission denied.', array(':src' => $file->path( )));
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
	
		if ( ! $target instanceOf Kohana_File)
		{
			if ( ! $dest->exists( ))
			{
				$dest->driver('File_Directory')->create( );
			}
			elseif ( ! $dest->is_dir( ))
			{
				throw new File_Exception('Cannot move :src to :dst: destination must be a directory', array(':src' => $file->path( ), ':dst' => $dest->path( )));
			}
			
			if (empty($target) || ! is_object($target))
			{
				$target = $dest->child(empty($target) ? $file->name( ) : $target);
			}
			elseif ( ! $target instanceOf Kohana_File)
			{
				throw new File_Exception(
					'Destination must be a Kohana_File instance, :type given',
					array(':type' => gettype($target) == 'object' ? get_class($target) : gettype($target))
				);
			}
		}

		if ( ! acl('file_write', $target->dir( )))
		{
			throw new Access_Exception('Cannot move :src to :dst -- permission denied.', array(':src' => $file->path( ), ':dst' => $target->dir( )->path( )));
		}
		
		if ($target->exists( ))
		{
			switch ($collision_mode)
			{
				case File::COPY_REWRITE:
					if ( ! acl('file_write', $target))
					{
						throw new Access_Exception('Cannot move :src to :dst -- denied access for writing.', array(':src' => $file->path( ), ':dst' => $target->path( )));
					}
					
					// write into existing target directory or remove destination file
					if ( ! $target->is_dir( ))
					{
						$target->remove( );
					}
					
					break;
					
				case File::COPY_SKIP:
					return $target;
				
				case File::COPY_CANCEL:
					throw new File_Exception('Cannot move :src to :dst: target already exists', array(':src' => $file->path( ), ':dst' => $target->path( )));
			}
		}
		else
		{
			// create directory without child access file
			$target->driver('File_Directory')->create(FALSE);
		}
		
		/* move all children to destination */
		foreach ($file AS $child)
		{
			if ($child->driver_obj( ) instanceOf File_Access)
			{
				continue;
			}
			
			$child->move($target, NULL, $collision_mode, $rewrite_access);
		}
		
		// move access file
		if ($rewrite_access)
		{
			$file->access_file( )->move($target, NULL, File::COPY_REWRITE);
		}
		else
		{
			$file->access_file( )->remove( );
		}

		
		// delete current directory
		if (rmdir($file->path( )) === FALSE)
		{
			throw new File_Exception('Cannot remove old directory :dir after moving', array(':dir' => $file->path( )));
		}
		

		// change file path
		$file->overload($target);
		
		/* :TODO: WINDOWS exception for empty file copy; see http://php.net//manual/ru/function.copy.php */
		return $file;
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
}