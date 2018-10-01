<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Superclass of filetypes
 * @package 	Files
 * @author 		Stanislav U. Alkimovich
 * @date 		2013-10-09
 *
 **/

abstract class Kohana_File_Driver
{
	/**
	 * @var string		default file extension
	 */
	protected $_default_ext = '';

	/**
	 * @var array		list of drivers
	 */
	protected static $_drivers = array( );

	
	/** This method calls when driver is initializing for concrete File object
	 *
	 * @param 	Kohana_File
	 * @return 	Kohana_File
	 */
	public function before(Kohana_File $file)
	{
		return $file;
	}
	
	/** algorythm of file creation
	 *
	 * @param   Kohana_File parent Kohana_File instance
	 * @return 	void
	 */
	abstract protected function _create(Kohana_File $file);
	
	/** Get file view
	 *
	 * @param	string		route name
	 * @param	array		array of parameters
	 * @param	string		query string
	 * @param	string		presents name
	 * @return 	string
	 */
	abstract public function html($route, $params = array( ), $query = '', $name = NULL);

	/** Download file
	 *
	 * @param	Kohana_File
	 * @return void
	 */
	abstract public function download(Kohana_File $file);

	/** Copy
	 *
	 * @param	Kohana_File	source
	 * @param	mixed		destination
	 * @return
	 */
	abstract public function copy(Kohana_File $file, $dest);

	/** Rename / move
	 *
	 * @param	mixed	destination
	 * @return
	 */
	abstract public function move(Kohana_File $file, $dest);

	/** Remove from server
	 *
	 * @param   Kohana_File parent Kohana_File instance
	 * @return 	void
	 */
	abstract public function remove(Kohana_File $file);

	/** Set or get access rules
	 *
     * @param   Kohana_File parent Kohana_File instance
	 * @param 	array	access rules
	 * @return 	array	rules array
	 */
	abstract public function access(Kohana_File $file, $rules = NULL);

	/** Create symlink
	 *
	 * @param	string
	 */
	abstract public function symlink( );
	
	/** Return default extension for current driver
	 *
	 * @return 	string
	 */
	public function default_ext( )
	{
		return $this->_default_ext;
	}
	
	/** Get child object of collection using specified pattern
	 *  
	 *  This method describes the default behavior for regular file and shall be overloaded in subclasses.
	 *
	 * @param 	string	glob pattern
	 * @return 	Kohana_File
	 */
	public function child(Kohana_File $file, $pattern)
	{
		throw new File_Exception('Cannot load child for regular file.');
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
			$mode = Site::config('site')->new_file_mode;
		}
	
		/* :TODO: write execute of OS command chmod if need */
		chmod($file->path( ), $mode);
		
		return $file;
	}
	
	/** Object constructor
	 *
	 * @return void
	 */
	public function __construct( )
	{
	}

	/** Standart setter/getter
	 *
	 * @param string 	variable name
	 * @param array		parameters
	 *
	 * @return mixed
	 */
	public function __call($var, $args = NULL)
	{
		if (is_array($args) && count($args) > 0)
		{
			$this->$var = $args[0];

			return $this;
		}

		return $this->$var;
	}

	/** File driver factory
	 *
	 * @param 	string	class name
	 * @return 	string
	 */
	public static function factory($classname)
	{
		if ( ! isset(self::$_drivers[$classname]))
		{
			$class = new ReflectionClass($classname);
			self::$_drivers[$classname] = $class->newInstanceArgs(array( ));
		}
		
		return self::$_drivers[$classname];
	}
	
	/** Common destructor
	 *
	 * @return void
	 */
	public function __destruct( )
	{
// 		$properties = get_class_vars(get_class($this));
// 
// 		foreach ($properties AS $property)
// 		{
// 			if (isset($this->$property))
// 			{
// 				unset($this->$property);
// 			}
// 		}
	}

	/** Get parent file browser view
	 * 
	 * :TODO: rework
	 *
	 * @param	string		route name
	 * @param	array		array of parameters
	 * @param	string		query string
	 * @return 	string
	 */
	public function html_parent($route, $params = array( ), $query = '')
	{
		$out = '';

		if ($this->_orm( )->id != Model_Files_Map::ROOT_ID && acl('files_read', $this->_orm( )->parent))
		{
			$parent = File::load_by_id((int) $this->_orm( )->parent);

			if ($parent->loaded( ))
			{
				$out = $parent->html($route, $params, $query, '..');
			}
		}

		return $out;
	}
	
	/** Create file and copy access rules
	 *
     * @param   Kohana_File 	parent Kohana_File instance
     * @param	boolean			use permissions
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
	
	
	/** Test path for match to glob-like pattern
	 *
	 * @param 	string	path
	 * @param 	string	pattern
	 * @param 	boolean	ignore case
	 * @author	DaveRandom  < http://stackoverflow.com/users/889949/daverandom >
	 * @source	http://stackoverflow.com/questions/13913796/php-glob-style-matching
	 *
	 * @return 	boolean
	 */
	function _path_matches($path, $pattern, $ignoreCase = FALSE)
	{
		$expr = preg_replace_callback('/[\\\\^$.[\\]|()?*+{}\\-\\/]/',
					function($matches)
					{
						switch ($matches[0]) {
							case '*':
								return '.*';
							case '?':
								return '.';
							default:
								return '\\'.$matches[0];
						}
					},
					$pattern
				);

		$expr = '/'.$expr.'/';
		if ($ignoreCase) {
			$expr .= 'i';
		}

		return (bool) preg_match($expr, $path);
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
			$dst = File::factory($src->path( ).'.'.File_Driver::factory($driver)->default_ext( ))
					->driver($driver)
					->create( ); 
		}
		
		if (is_string($dst))
		{
			$driver_ext = File_Driver::factory($driver)->default_ext( );
		
			// add extension if not specified
			if (
				($ext = pathinfo($dst, PATHINFO_EXTENSION)) == ''
				||
				$ext != $driver_ext
			)
			{
				$dst .= '.'.$driver_ext;
			}
			
		
			// fetch Kohana_File for destination
			$dst = File::factory($dst);
		}
		
		// create destination archive if not exists
		if ( ! $dst->exists( ))
		{
			// set up specified type for new archive and create
			$dst->driver($driver)->create( );
		}
		else
		{
			if ( ! $dst->driver_obj( ) instanceOf File_Archive)
			{
				throw new File_Exception('Destination :dst must be an instance of archive.', array(':dst' => $dst->path( )));
			}
		}

		// add source file to archive
		$dst->add($src, $filename);
		
		return $dst;
	}
	
	/** Register parent collection: add object of collection to the list of parents
	 *
	 * @param 	Kohana_File		current file
	 * @param 	Kohana_File		parent collection
	 * @return 	void
	 * /
	public function reg_parent(Kohana_File $file, Kohana_File $parent)
	{
		var_dump($file->path( ));
		var_dump(get_class($file));
		var_dump($parent->path( ));
		var_dump(get_class($parent));
		$file->parents[] = $parent;
	}

	/** Remove parent collection from list of parents
	 *
	 * @param 	Kohana_File		current file
	 * @param 	Kohana_File		parent collection
	 * @return 	void
	 
	public function forget_parent(Kohana_File $file, Kohana_File $parent)
	{
		$key = array_search($parent, $file->parents);
		
		if ($key !== FALSE)
		{
			unset($file->parents[$key]);
		}
	}*/
}