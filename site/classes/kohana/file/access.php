<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Access file manipulation
 * @package 	Files
 * @author 		Stanislav U. Alkimovich
 * @date 		2014-07-07
 *
 **/

class Kohana_File_Access extends File_Json
{
	/** get default access rules
	 *
	 * @return 	array
	 */
	public static function defaults( )
	{
		$out = array(
					File::ACCESS_DIR_KEY => array(
						User::instance( )->guest( )->id => array('file_read'),
					)
				);

		return $out;
	}

	/** algorythm of file creation
	 *
	 * @param   Kohana_File parent Kohana_File instance
	 * @return 	void
	 */
	protected function _create(Kohana_File $file)
	{
		$directory = $file->dir( );
		
		if ( ! $directory->is_root( ))
		{
			// copy parent access file to current directory
			$access_file = $directory->dir( )->access_file( );
			
			// recursive creation of access files
			if ( ! $access_file->exists( ))
			{
				$access_file->create( );
			}
			
			// fetch content of parent file
			$content = $access_file->content( );
			
			if (/*isset($content[File::ACCESS_FILES_KEY]) && */isset($content[File::ACCESS_FILES_KEY][$file->name( )]))
			{
				// remove all privileges for files exclude privileges for access file
				$content[File::ACCESS_FILES_KEY] = array($file->name( ) => $content[File::ACCESS_FILES_KEY][$file->name( )]);
			}
			else
			{
				// remove all privileges for files
				unset($content[File::ACCESS_FILES_KEY]);
			}
			
			// save new content
			parent::content($file, $content);
			
			// set default mode
			$this->chmod($file, Site::config('site')->new_dir_mode);

			// re-initialize file
			$file->init( );
		}
		else
		{
			// create new file
			parent::_create($file);
			
			// write default access rules
			parent::content($file, File_Access::defaults( ));
		}
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
		if ( ! $file->exists( ))
		{
			$file->create( );
		}
		
		if (isset($text))
		{
			if ( ! acl('file_write', $file))
			{
				new Access_Exception( );
			}
		}
		
		return parent::content($file, $text);
	}
	
	/** Create file and copy access rules
	 *
	 *  This method overloads the method Kohana_File_Regular::create -- check of access rules has been excluded.
	 *  Flag of ACL usage has been disabled!
	 *
     * @param   Kohana_File 	parent Kohana_File instance
     * @param	boolean 		use permissions
	 * @return 	void
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
		
		// create file if not exists
		call_user_func_array(array($this, '_create'), func_get_args( ));
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
		
		// for _access files will be used file privileges only -- privileges of current directory don't work
		return isset($access[File::ACCESS_FILES_KEY][$file->name( )])
				? $access[File::ACCESS_FILES_KEY][$file->name( )]
				: array( );
	}
	/** Copy
	 *
	 * :TODO: rework
	 *
	 * @param	File	destination
	 * @return
	 
	public function copy(Kohana_File $file, $dest)
	{
		$dest_path = $dest->is_dir( ) ? $dest->path( ) : $dest->dir_name( );
	
		if ($dest->is_dir( ))
		{
			$dest_path .= DIRECTORY_SEPARATOR.$file->name( );
		}
	
		$result = copy($file->path( ), $dest_path);

		/* :TODO: WINDOWS exception for empty file copy; see http://php.net//manual/ru/function.copy.php * /
		return $result;
	}*/
}