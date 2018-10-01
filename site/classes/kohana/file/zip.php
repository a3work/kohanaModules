<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
This driver has provided extracting and writing of zip archives.
For every extraction needs access privilege "file_write" for destination directory and all files, who will be re-writen.

### Extracting archive completely
~~~
// create zip file object and extract it to specified folder
File::factory('tmp.zip')->extract('data');

// extract archive to auto-created folder "tmp"
File::factory('tmp.zip')->extract( );

~~~

### Extracting single file
~~~
// create zip file object and extract it to data folder
File::factory('tmp.zip')->extract('data', 'myfile.zip');

~~~

### Iterator of archive content

~~~
// for each archive item will be created instance of Archive_Item 
foreach (File::factory('tmp.zip') AS $name => $file)
{
	// you can extract it or do something other
	$file->extract( ); 
}

~~~

### Add file or directory to archive

~~~
// Add file to archive, create archive if not found
File::factory('sample.txt')->compress('/path/to/archive');

// Compress directory to specified archive
File::factory('testing_dir')->compress('/path/to/archive');
File::factory('testing_dir')->compress('/path/to/archive.zip');
File::factory('testing_dir')->compress(File::factory('/path/to/archive.zip'));

// Generate filename of archive and write it to the same directory
File::factory('testing_dir')->compress( )

~~~

 
 * @name		Zip archive representation
 * @package 	Files
 * @author 		Stanislav U. Alkimovich
 * @date 		2014-07-07
 * @use			PHP ZipArchive
 * 
 * 
 *
 **/

class Kohana_File_Zip extends File_Regular implements File_Archive
{
	/**
	 * @var string		default file extension
	 */
	protected $_default_ext = 'zip';

	/** Redirect queries to phpExcel_worksheet object
	 *
	 * @param 	Kohana_File
	 * @param 	mixed 		variable
	 * @return 	mixed		phpExcel functions output
	 */
	public function __call($var, $args = array( ))
	{
		$file = array_shift($args);

		return @call_user_func_array(array($this->zip_obj($file), $var), $args);
	}
	
	/** algorythm of zip archive creation
	 *
	 * @param   Kohana_File parent Kohana_File instance
	 * @return 	void
	 */
	protected function _create(Kohana_File $file)
	{
		// create file and set up access mode
		$file->zip_obj( );
// 		$this->chmod($file, Site::config('site')->new_file_mode);
	}
	
	/** Create and return zip object for specified file
	 *
	 * @param 	Kohana_File
	 * @return 	ZipArchive
	 */
	public function zip_obj(Kohana_File $file)
	{
		if ( ! isset($file->zip_obj))
		{
			$file->zip_obj = new ZipArchive( );
			$res = $file->zip_obj->open($file->path( ), ZIPARCHIVE::CREATE);
			
			if ($res !== TRUE)
			{
				switch ($res)
				{
					case ZipArchive::ER_EXISTS:
						$error = "File already exists.";
						break;

					case ZipArchive::ER_INCONS:
						$error = "Zip archive inconsistent.";
						break;
						
					case ZipArchive::ER_MEMORY:
						$error = "Malloc failure.";
						break;
						
					case ZipArchive::ER_NOENT:
						$error = "No such file.";
						break;
						
					case ZipArchive::ER_NOZIP:
						$error = "Not a zip archive.";
						break;
						
					case ZipArchive::ER_OPEN:
						$error = "Can't open file.";
						break;
						
					case ZipArchive::ER_READ:
						$error = "Read error.";
						break;
						
					case ZipArchive::ER_SEEK:
						$error = "Seek error.";
						break;
					
					default:
						$error = "Unknow (Code $rOpen)";
						break;
						
				}
				
				throw new File_Exception('ZipArchive error (":error") for file ":file"', array(':error' => $error, ':file' => $file->path(FALSE)));
			}
			
			// get length of archive file list
			$file->zip_num_files = $file->zip_obj->numFiles;
			
			// get archive comment
			$file->zip_comment = $file->zip_obj->comment;
			
			// set flag of pattern usage
			$file->zip_use_pattern = FALSE;
		}
		
		return $file->zip_obj;
	}
	
 	/** rewind Kohana_File content
	 *
	 * @param 	Kohana_File	file
	 * @return 	void
	 */
	public function rewind(Kohana_File $file)
    {
		// initialize ZipArchive
		$this->zip_obj($file);
		
		// reset count of files
		$file->zip_count = 0;
    }


	/** Return current worksheet
	 *
	 * @param 	Kohana_File	file
	 * @return 	Kohana_File
	 */
    public function current(Kohana_File $file)
    {
		$data = $file->zip_obj( )->statIndex($file->zip_count);
		
		$obj = new Archive_Item($file, $data['name']);
		
		// save file information
		$obj->zip_data = $data;
		
		return $obj;
    }

	/** Get current key of Kohana_File content
	 *
	 * @param 	Kohana_File	file
	 * @return 	scalar
	 */
    public function key(Kohana_File $file)
    {
		// return current key
		return $file->zip_obj( )->getNameIndex($file->zip_count);
    }

	/** Switch Kohana_File content to the next element
	 *
	 * @param 	Kohana_File	file
 	* @return 	void
	 */
    public function next(Kohana_File $file)
    {
		// increase key
		$file->zip_count ++;
    }

	/** Validate current content item
	 *
	 * @param 	Kohana_File	file
	 * @return 	boolean
	 */
    public function valid(Kohana_File $file)
    {
		if ($file->zip_use_pattern === FALSE)
		{
			if ($file->zip_count == $file->zip_num_files)
			{
				return FALSE;
			}
			
			return TRUE;
		}
		else
		{
			// get first of matched files
			while ($file->zip_count < $file->zip_num_files)
			{
				$info = $file->zip_obj( )->statIndex($file->zip_count);
			
				if ( ! $this->_path_matches($info['name'], $file->pattern( )))
				{
					$file->zip_count ++;
					
					continue;
				}
				
				return TRUE;
			}
			
			return FALSE;
		}
    }
    
    /** Set pattern and search mode
	 *
	 * @param 	Kohana_File
	 * @param 	string	pattern
	 * @return 	Kohana_File
	 */
    public function find(Kohana_File $file, $pattern = '*')
    {
		// save pattern
		$file->zip_pattern = $pattern;
		
		// set usage mode
		$file->zip_use_pattern = (isset($file->zip_pattern) && $file->zip_pattern != '*');
		
		return $file;
    }
    
	/** Unpack archive or specified file
	 *
	 * @param 	Kohana_File		source Archive
	 * @param 	mixed 					destination
	 * @param 	mixed 					file
	 * @return 	Kohana_File
	 */
	public function extract(Kohana_File $src, $dst = NULL, $file = NULL)
	{
		if ($src->exists( ) === FALSE)
		{
			return;
		}
	
		// generate subdirectory name if not specified
		if ( ! isset($dst))
		{
			$dst = preg_replace('/\.'.$src->ext( ).'$/', '', $src->name( ));
		}
	
		// get instance of Kohana_File
		if ( ! $dst instanceOf Kohana_File)
		{
			$dst = File::factory($dst);
		}
		
		// check existence of directory, define driver manualy if not
		if ( ! $dst->exists( ))
		{
			$dst->driver('File_Directory');
			$dst->create( );
		}
		
		// check permissions
		if ( ! acl('file_write', $dst))
		{
			throw new Access_Exception( );
		}
		
		// extract single file
		if (isset($file))
		{
			if ($file instanceOf Kohana_Archive_Item)
			{
				$file = $file->name( );
			}
			
			// check destination file existence
			$dst_file = $dst->child($file);
			
			// check write permissions if file exists
			if ($dst_file->exists( ) && ! acl('file_write', $dst_file))
			{
				throw new Access_Exception( );
			}
			
			// create subdirectory instead of extract it from archive
			if (substr($file, -1) == DIRECTORY_SEPARATOR)
			{
				if ( ! $dst_file->exists( ))
				{
					$dst_file->driver('File_Directory')->create( );
					
				}
			}
			else
			{
				// extract regular file and return it
				$src->zip_obj( )->extractTo($dst->path( ), array($file));
				
				$dst_file = $dst->child($file)->chmod(Site::config('site')->new_file_mode);
			}
			
			// return directory
			return $dst_file;
		}
		else
		{
			$result = Collection::factory( );
			
			foreach ($src AS $file)
			{
				$result->append($file->extract($dst));
			}
			
			// return collection
			return $result;
		}
	}
	
	/** Save all changes and close ZipArchive instance
	 *
	 * @param 	Kohana_File
	 * @return 	Kohana_File
	 */
	public function save(Kohana_File $file)
	{
		if (isset($file->zip_obj))
		{
			$file->zip_obj->close( );
			
			unset($file->zip_obj);
		}
		
		return $file;
	}
	
	/** Pack specified file to archive
	 *
	 * @param 	Kohana_File		destination archive
	 * @param 	mixed			file
	 * @param	string			filename
	 * @return 	Kohana_Archive_Item
	 */
	public function add(Kohana_File $archive, $file, $filename = NULL)
	{
		// fetch path
		if ( ! $file instanceOf Kohana_File)
		{
			$file = File::factory($file);
		}
		
		
		// process directory
		if ($file->is_dir( ))
		{
			// add empty directory
			$archive->zip_obj( )->addEmptyDir(str_replace($archive->dir( )->path( ), '', $file->path( )));
		
			// initialize collection for instances of Archive_Item
			$collection = Collection::factory( );
		
			// append to archive children
			foreach ($file AS $item)
			{
				$collection->append($item->compress($archive, str_replace($archive->dir( )->path( ), '', $item->path( ))));
			}
			
			return $collection;
		}
		else
		{
			if ( ! acl('file_read', $file) ||  ! acl('file_write', $file) ||  ! acl('file_write', $archive))
			{
				throw new Access_Exception( );
			}
		
			// define filename
			$filename = isset($filename) ? $filename : $file->name( );
		
			
		
			// add file to archive
			if (FALSE === $archive
						->zip_obj( )
						->addFile(
							$file->path( ),
							$filename
						)
			)
			{
				throw new File_Exception('Cannot add file :file (new name ":filename") to archive :archive', array(
					':file' 	=> $file->path( ),
					':archive'	=> $archive->path( ),
					':filename'	=> $filename,
				));
			}
	
			// fetch and return Kohana_Archive_Item object
			$obj = new Archive_Item($archive, $filename);
			
			// increase file count
			$archive->zip_num_files ++;
			
			// save file information
			$obj->zip_data = $archive->zip_obj( )->statName($filename);
		}
		
		return $obj;
	}
	
	/** Get size of specified file
	 *
	 * @param 	Kohana_File		archive
	 * @param 	mixed			child filename
	 * @return 	Kohana_File
	 */
	public function filesize(Kohana_File $file, $filename)
	{
		if ( ! acl('file_read', $file))
		{
			throw new Access_Exception( );
		}
	
		if ($filename instanceOf Kohana_Archive_Item)
		{
			return $filename->zip_data['size'];
		}
		else
		{
			$result = $file->zip_obj( )->statName($filename);
			
			if ($result === FALSE)
			{
				throw new File_Exception('Cannot load file ":filename"', array(':filename' => $filename));
			}
			
			return $result;
		}
	}
}
