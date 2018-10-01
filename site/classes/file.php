<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		File common functions
 * @package 	Files
 * @author 		Stanislav U. Alkimovich
 * @date 		2013-10-07
 *
 **/

class File extends Kohana_File implements Iterator
{
    /**
     * @const   string  options separator
     */
    const SEPARATOR = '_';
    
    /**
     * @const   string  object access filename
     */
    const ACCESS_FILENAME = 'access';
    
    /**
     * @const   string  object access file extension
     */
    const ACCESS_EXTENSION = '.json';
    
    /**
     * @const   string  directory section key of access file
     */
	const ACCESS_DIR_KEY = 'dir';

    /**
     * @const   string  files section key of access file
     */
	const ACCESS_FILES_KEY = 'files';

    /**
     * @const   string  browser route path variable
     */
	const ROUTE_PATH_VAR = 'file';

	/**
	 * @const integer	database parameters saving mode: not save into database
	 */
	const DBSYNC_NEVER = 0;
	
	/**
	 * @const integer	database parameters saving mode: save when __destruct will be executed
	 */
	const DBSYNC_LATER = 1;
	
	/**
	 * @const integer	database parameters saving mode: save immediatly
	 */
	const DBSYNC_NOW = 2;
	
	/**
	 * @const integer	copy mode: rewrite if destination exists
	 */
	const COPY_REWRITE = 1;
	
	/**
	 * @const integer	copy mode: skip file if destination exists
	 */
	const COPY_SKIP = 2;
	
	/**
	 * @const integer	copy mode: cancel coping if destination exists
	 */
	const COPY_CANCEL = 3;
	
	/**
	 * @var string		classname of children (this class name used for creation of file instances in cycles of the file collections)
	 */
	public $item_class = 'File';
	
	/**
     * @var string  array of file instances
     */
    public static $_instances = array( );

	/**
     * @var string  File ORM instance
     */
    protected static $_orm;

    /**
	 * @var string  filepath
	 */
	protected $_path;

	/**
	 * @var string  file URL
	 */
	protected $_url;

	/**
	 * @var string  filename
	 */
	protected $_name;

	/**
	 * @var string  file extension
	 */
	protected $_ext;

	/**
	 * @var string  content pattern
	 */
	protected $_pattern;

	/**
	 * @var string  directory flag (TRUE if current object is directory)
	 */
	protected $_is_dir;

	/**
	 * @var boolean	root directory flag (TRUE if path == STORAGE)
	 */
	protected $_is_root = FALSE;

	/**
	 * @var string  directory name
	 */
	protected $_dir_name;

	/**
	 * @var File	parent directory object
	 */
	protected $_dir;

	/**
	 * @var string  access filename object
	 */
	protected $_access;

	/**
	 * @var boolean  file content loading state
	 */
	protected $_loaded = FALSE;

	/**
	 * @var boolean  file existence flag
	 */
	protected $_exists = FALSE;

	/**
	 * @var File_Driver  instance of driver
	 */
	public $_driver;

	/**
	 * @var string	 driver class name
	 */
	public $_driver_name;

	/**
	 * @var integer  size
	 */
	protected $_size;

	/**
	 * @var string  MIME type
	 */
	protected $_mime;

	/**
	 * @var string  flag of removed file object
	 */
	protected $_removed = FALSE;

	/**
	 * @var mixed	list of children instances or file content
	 */
	public $content;

	/**
	 * @var boolean		TRUE if all parameters already loaded from database
	 */
	protected $_params_loaded = FALSE;
	
	/**
	 * @var array		various parameters of file, will be loaded from database
	 */
	protected $_params = array( );

	/**
	 * @var array		parameters of file for saving into database
	 */
	protected $_saving_params = array( );

	/**
	 * @var array		parameters of file for removing from database
	 */
	protected $_removing_params = array( );

	
	/**
	 * @var Request		Object of Request for usage in actions -- according to Controller_Filesystem
	 */
	public $request;
	
	/**
	 * @var Response	Object of Response for usage in actions -- according to Controller_Filesystem
	 */
	public $response;
	
	/**
	 * @var View		Object of View for usage in actions -- according to Controller_Filesystem
	 */
	public $template;
	
	
	
	/**
	 * begin:Iterator implementation
	 */
	public function rewind( )
    {
		$this->_driver( )->rewind($this);
    }

    public function current( )
    {
		return $this->_driver( )->current($this);
    }

    public function key( )
    {
		return $this->_driver( )->key($this);
    }

    public function next( )
    {
		$this->_driver( )->next($this);
    }

    public function valid( )
    {
		return $this->_driver( )->valid($this);
    }
	/**
	 * end:Iterator implementation
	 */
	 
	 
	 
	/** Getter of current directory File object
	 *
	 * @return 	File
	 */
	public function dir( )
	{
		if ($this->is_root( ))
		{
			throw new File_Exception('Cannot load parent for root directory.');
		}
	
		if ($this->_dir === NULL)
		{
			$this->_dir = File::factory($this->_dir_name)->driver('File_Directory');
		}
	
		return $this->_dir;
	}
	
	/** Getter of current directory name
	 *
	 * @return 	string
	 */
	public function dir_name( )
	{
		return $this->_dir_name;
	}
	
	
	/** Initialize file parameters
	 *
	 * @return
	 */
	public function init( )
	{
        $this->_is_dir	= is_dir($this->_path);
        
        if ($this->_is_dir)
        {
			$this->_path   .= DIRECTORY_SEPARATOR;
			$this->_path 	= str_replace('//', '/', $this->_path);
			$this->_pattern	= '*';
        }
        
		// drop access file object
		$this->_access	= NULL;
		
		// drop object of parent directory
        $this->_dir		= NULL;
        
        // drop driver
        $this->_driver 	= NULL;
        
        /* set basic parameters */
        $this->_name	 = basename($this->_path);
		$this->_is_root	 = $this->_path == STORAGE;
		
        $this->_dir_name = $this->_is_root ? NULL : pathinfo($this->_path, PATHINFO_DIRNAME).DIRECTORY_SEPARATOR;
        
        // fetch extenstion
		$this->_ext		= pathinfo($this->_path, PATHINFO_EXTENSION);
		$this->_exists	= file_exists($this->_path);
		
		if ($this->removed( ) === FALSE)
		{
			
			if ($this->_exists === TRUE)
			{
				/* set attributes */
				$this->_size    = filesize($this->_path);
				$this->_mime    = @File::mime($this->_path);
			}
			
			// add this object to list of parent dir
			if ( ! $this->_is_root && $this->_exists)
			{
				$this->dir( )->append($this);
			}
		}
	}
	
	
	/** ORM getter
	 *
	 * @return 	Model_File
	 */
	public static function orm( )
	{
		if ( ! isset(self::$_orm))
		{
			self::$_orm = ORM::factory('file');
		}
		
		return self::$_orm;
	}
	
    /** Object constructor
     * 	use File::factory instead
	 *
	 * :TODO: call specific method to determine driver
	 *
     * @param   string  absolute filepath (for ex.: /var/www/example.com/application/storage/my_file.tmp)
	 * @return  void
	 */
	public function __construct($path)
	{
		$this->_path = $path;
	
        // initialize object
		$this->init( );
        
        /* load driver */
        $this->_init_driver( );
        
// 		if ( ! acl('file_read', $this))
// 		{
// 			throw new Access_Exception( );
// 		}
        
        // :TODO: load other file attributes
	}
	
	/** Define class of driver for current file object
	 *
	 * @return 	boolean		TRUE if loaded successfuly, FALSE if already loaded
	 */
	protected function _init_driver( )
	{
        if ( ! isset($this->_driver_name))
        {
			if ($this->_is_dir)
			{
				$this->_driver_name = 'File_Directory';
			}
			elseif ($this->_name == File::SEPARATOR.File::ACCESS_FILENAME.File::ACCESS_EXTENSION)
			{
				$this->_driver_name = 'File_Access';
			}
// 			elseif ($this->_ext == 'json'/*strpos($this->_name, 'json') !== FALSE*/)
// 			{
// 				$this->_driver_name = 'File_Json';
// 			}
			elseif ($this->_ext == 'sav')
			{
				$this->_driver_name = 'File_Content_Autosave';
			}
			elseif ($this->_ext == 'zip')
			{
				$this->_driver_name = 'File_Zip';
			}
			elseif ($this->_ext == 'xls')
			{
				$this->_driver_name = 'File_Xls';
			}
			elseif (in_array($this->_ext, array('mp3', 'webm', 'wav', 'ogg')))
			{
				$this->_driver_name = 'File_Sound';
			}
			elseif ($this->_ext == 'xlsx')
			{
				$this->_driver_name = 'File_Xls';
			}
			elseif (strpos($this->_name, '.menu') === 0)
			{
				$this->_driver_name = 'File_Menu';
			}
			elseif ($this->_ext == str_replace('.', '', Kohana_File_Content::EXTENSION))
			{
				$this->_driver_name = 'File_Content';
			}
			else
			{
				$this->_driver_name = 'File_Regular';
			}
			
			return TRUE;
        }
        
        return FALSE;
	}
	
	/** Destruct class: save attributes into database
	 *
	 * @return 	void
	 */
	public function __destruct( )
	{
	}
	
	/** Fetch access file
	 *
	 * @return 	File
	 */
	public function access_file( )
	{
		if ($this->_access === NULL)
		{
			$this->_access = File::factory(
								($this->_is_dir ? $this->_path : $this->_dir_name)
								.File::SEPARATOR
								.File::ACCESS_FILENAME
								.File::ACCESS_EXTENSION
							);
		}
		
		return $this->_access;
	}

	/**
	 * Redirect query to driver
	 *
	 * @param string 	method name
	 * @param array		parameters
	 *
	 * @return mixed
	 */
	public function __call($var, $args = array( ))
	{
		// add link to current file object
		array_unshift($args, $this);
		
		return call_user_func_array(array($this->_driver( ), $var), $args);
// 		$result = call_user_func_array(array($this->_driver( ), $var), $args);
		
// 		return $result === NULL ? $this : $result;
	}
	
	/** load and return driver object
	 *
	 * @return 	File_Driver
	 */
	protected function _driver( )
	{
        if (empty($this->_driver_name))
        {
            throw new File_Exception('Cannot load file driver.');
        }
        
        if (empty($this->_driver))
        {
			$this->_driver = File_Driver::factory($this->_driver_name);
			
			// call before method
			$this->_driver->before($this);
        }
	
		return $this->_driver;
	}
	
	
	/** Initialize and return object of driver
	 *
	 * @return 	Kohana_File_Driver
	 */
	public function driver_obj( )
	{
		return $this->_driver( );
	}
	
	/** Set up driver or return driver classname
	 *
	 * @param 	string					driver classname
	 * @return 	Kohana_File / string	this file / driver classname
	 */
	public function driver($driver = NULL)
	{
		if (isset($driver))
		{
			$this->_driver_name = $driver;
			
			// drop driver
			$this->_driver = NULL;
			
			return $this;
		}
		else
		{
			return $this->_driver_name;
		}
	}
	
	/** Is this file directory?
	 *
	 * @return @boolean
     */
    public function is_dir( )
    {
        return $this->_is_dir;
    }

	/** Load file
	 *
	 * @param   string  path
	 * @return  File
	 */
	public static function factory($path = STORAGE)
	{
		$path = STORAGE.File::clear_path($path);
		
		$classname = get_called_class( );
		
		if ( ! isset(File::$_instances[$classname][$path]))
		{
			$class = new ReflectionClass($classname);
			File::$_instances[$classname][$path] = $class->newInstanceArgs(array($path));
		}
			
		return File::$_instances[$classname][$path];
	}
	
	/** Find file
	 *
	 * @param 	string			glob pattern, see http://php.net//manual/ru/function.glob.php
	 * @param	Kohana_File		find here
	 * @return 	Kohana_File		collection or array with the results of search (:TODO:)
	 */
// 	public static function find($pattern, Kohana_File $context)
// 	{
// 		if ($context->is_dir( ))
// 		{
// 			return new Search()
// 		}
// 	}
	
	/** Get link to file action
	 *
	 * @param 	Kohana_File	file object
	 * @param 	string		action name
	 * @param 	array		parameters
	 * @param 	boolean		use full cms view 
	 * @param	mixed		Protocol string or boolean, adds protocol and domain
	 * @return 	string
	 */
	public function action($action, $params = array( ), $is_full = FALSE, $protocol = NULL)
	{
		return	Route::url(	'filesystem',
							array(
								'mode'	=> ($is_full ? CMS::VIEW_MODE_FULL : CMS::VIEW_MODE_SIMPLE),
								'action'=> $action,
								'type'	=> get_class($this),
								'path'	=> $this->path(FALSE),
							),
							$protocol)
				.http_build_query($params);
	}
	
	/** Run action and return result
	 *
	 * @param 	Kohana_File	file object
	 * @param 	string		action name
	 * @param 	array		parameters
	 * @param 	boolean		use full cms view 
	 * @return 	string
	 */
	public function run($action, $params = array( ), $is_full = FALSE)
	{
		return Request::factory($this->action($action, $params, $is_full))->execute( )->body( );
	}
	
	/** Remove Kohana_File instance from stack
	 *
	 *	don't remove file from filesystem
	 *	use public Kohana_File_Driver::remove instead
	 *
	 * @param 	Kohana_File	removing file
	 * @return 	void
	 */	
// 	public function clear( )
// 	{
// 		unset(self::$_instances[[$this->_path]);
// 	}
	
	/** Check file
	 *
	 * @param 	string		path
	 * @return	boolean
	 */
// 	public static function check($path)
// 	{
// 		$file = File::clear_path($path);
// 
// // 		echo "\nCheck: \"".$file."\"\n";
// 		return $path != '' && file_exists($file);
// 	}

    /** Return file existence
     *
     * @return  boolean
     */
    public function exists( )
    {
        return $this->_exists;
    }

    /** Write and/or return file content loading state
     *
     * @param	boolean	value
     * @return	boolean
     */
    public function loaded($value = NULL)
    {
		if (isset($value))
		{
			$this->_loaded = $value;
		}
		
        return $this->_loaded;
    }
    
    /** Mark file as removed (zombie-object =)
     *
     * @param	boolean	value
     * @return	boolean
     */
    public function removed($value = NULL)
    {
		if (isset($value))
		{
			$this->_removed = $value;
		}
		
        return $this->_removed;
    }
    
    /** Return file existence
     *
     * @return  boolean
     */
    public function is_root( )
    {
        return $this->_is_root;
    }
    
    /** Return file path
     *
     * @param	boolean	return complete path (include STORAGE)
     * @return  string
     */
	public function path($complete = TRUE)
	{
        return 	$complete
				? $this->_path
				: str_replace(STORAGE, '', $this->_path);
	}
	
    /** Return pattern
     *
     * @return  string
     */
    public function pattern($pattern = NULL)
    {
		if (isset($pattern))
		{
			$this->_pattern = $pattern;
		}
		
        return $this->_pattern;
    }
	
    /** Return file URL
     * 
     * @param	string	protocol
     * @return  string
     */
    public function url($protocol = NULL)
    {
		if (empty($this->_url))
		{
			$this->_url = Route::url('default', array('page' => str_replace(STORAGE, '', $this->_path)), $protocol);
		}
    
        return $this->_url;
    }
	
	/** Return file name
	 *
	 * @param	boolean		return name without extension
	 * @return  string
	 */
	public function name($without_ext = FALSE)
	{
		return $without_ext ? str_replace('.'.$this->_ext, '', $this->_name) : $this->_name;
	}
    
	
    /** Return file extension
     *
     * @return  string
     */
    public function ext( )
    {
        return $this->_ext;
    }
    
    /** Return file size
     *
     * @return  string
     */
    public function size( )
    {
        return $this->_size;
    }
    
    /** Return file size
     *
     * @return  string
     */
    public function mtime( )
    {
        return filemtime($this->path());
    }
    
    /** Return file MIME type
     *
     * @return  string
     */
    public function mime_type( )
    {
        return $this->_mime;
    }
    
	/** Normalize file path
	 *
	 * @param 	string		path
	 * @return	string
	 */
    public static function clear_path($path)
    {
		if (strpos($path, 'http') !== FALSE)
		{
			$path = str_replace(array(STORAGE, 'https', 'http', ':', '//'), '', $path);
		}

		$parsed = parse_url(urldecode(trim(/*trim(*/str_replace(trim(STORAGE, '/'), '', $path)/*)*/, '/')));
		$result = $parsed['path'];
/*		
		if (strpos($path, '#') !== FALSE)
		{
			$result .= '#';
		
			if (isset($parsed['fragment']))
			{
				$result .= $parsed['fragment'];
			}
		}
 */
		return trim($result, '/');
    }

	/** Filter string for usage as alias
	 *
	 * @param 	string		alias
	 * @return 	string
	 */
	public static function filter_url($string)
	{
	
		// Тестовая страница !@#$%^&*()[];:'"`,.\//\/ страница
//         return preg_replace("/'\"[,;:!@#$%^&*~><|?\{\}\[\]+=]+/", '', str_replace(' ', '_', preg_replace("/\s{2,}/", ' ', $string)));
        return str_replace(' ', '_', trim(preg_replace("/\s{2,}/", ' ', preg_replace('/[\/#\\\%&\[\]]+/', '', $string))));
	}

    /** Filter string for usage as alias
     *
     * @param   string      alias
     * @return  string
     */
    public static function decode_uri($string)
    {
        return urldecode($string);
    }

	/** Move source file to destination
	 *
	 * @param	string	source
	 * @param	string	destination
	 * @return	boolean
	 
	public static function move($src, $dst)
	{
		if ($src == $dst)
		{
			return FALSE;
		}

		// rewrite destination
		if ( ! is_dir($dst) && file_exists($dst))
		{
			unlink($dst);
		}

		return rename($src, $dst);
	}*/

	/** Change path, modify instances list
	 *
	 * @param	string			new path
	 * @return 	Kohana_File		this file
	 */
	public function overload(Kohana_File $file = NULL)
	{
		$classname = get_class($this);
		
		/* write new path and re-init object */
		if (isset($file))
		{
			// clear and define new filepath
			$this->_path = $file->path( );
			
			// add new link to current file in __instances array
			self::$_instances[$classname][$this->_path] = $this;
			
			// mark old file as removed
			$file->removed(TRUE);
		}
		else
		{
			
			// remove links to this file from parent objects
			$this->dir( )->cut($this);
			
			if (isset(self::$_instances[$classname][$this->_path]) && self::$_instances[$classname][$this->_path] == $this)
			{
				// remove old link to current file in __instances array
				unset(self::$_instances[$classname][$this->_path]);
			}

			// mark as removed
			$this->removed(TRUE);
		}
		
		// initialize file vars
		$this->init( );
		
		return $this;
	}

	/** Load file parameters from database
	 *
	 * @return 	Kohana_File		this file
	 */
	protected function _load_params( )
	{
		foreach (File::orm( )->where('filename', '=', $this->path(FALSE))->find_all( ) AS $param)
		{
			if ( ! isset($this->_params[$param->key]))
			{
				$this->_params[$param->key] = $param->value;
			}
		}
		
		// change loading state
		$this->_params_loaded = TRUE;
		
		return $this;
	}
	
	
	/** Set file parameters and store into database later
	 *
	 * @param 	string		key
	 * @param 	mixed		value
	 * @param 	integer		store mode
	 * @return 	Kohana_File		this file
	 */
	public function db_set($key, $value = NULL, $mode = File::DBSYNC_LATER)
	{
		$this->_params[$key] = $value;
	
		// store all parameters using single query in __destruct
		if ($mode == File::DBSYNC_LATER)
		{
			File::orm( )->param($this->path(FALSE), $key, $value);
		}
		// store parameter value immediatly
		elseif ($mode == File::DBSYNC_NOW)
		{
			File::orm( )->save_param($this->path(FALSE), array($key => $value));
		}
		
		return $this;
	}
	
	/** Delete file parameters
	 *
	 * @param 	string			key
	 * @param 	integer			store mode
	 * @return 	Kohana_File		this file
	 */
	public function db_del($key, $mode = File::DBSYNC_LATER)
	{
		// load parameters 
		if ( ! isset($this->_params[$key]) && $this->_params_loaded === FALSE)
		{
			$this->_load_params( );
		}

		// delete parameter if exists
		if (isset($this->_params[$key]))
		{
			$this->_params[$key] = NULL;

			if ($mode == File::DBSYNC_LATER)
			{
				File::orm( )->param($this->path(FALSE), $key, NULL);
			}
			elseif ($mode == File::DBSYNC_NOW)
			{
				File::orm( )->delete_params($this->path(FALSE), array($key));
			}
		}
		
		return $this;
	}
	
	/** Get file parameter value
	 *
	 * @param 	string	key
	 * @return 	mixed
	 */
	public function db_get($key)
	{
		if (isset($this->_params[$key]))
		{
			return $this->_params[$key];
		}
		
		// load parameters 
		if ( ! isset($this->_params[$key]) && $this->_params_loaded === FALSE)
		{
			$this->_load_params( );
		}
		
		if (isset($this->_params[$key]))
		{
			return $this->_params[$key];
		}
		else
		{
			return NULL;
		}
	}
	
	/**
	 * generate printable representation of file
	 * 
	 * @return string
	 */
	public function __toString()
	{
		if (method_exists($this->_driver( ), 'to_string'))
		{
			try
			{
				return (string) call_user_func_array(array($this->_driver( ), 'to_string'), array($this));
			}
			catch (Exception $ex)
			{
				return $ex->getMessage();
			}
		}
		else
		{
			return __('cannot convert file :file to string', array(':file' => $this->path(FALSE)));
		}
		
	}
}

if ( ! function_exists('ff'))
{
	/** Shortcut for File::factory( )
	 *
	 * @param   string  path
	 * @return  File
	**/
	function ff($path = NULL)
	{
		return File::factory($path);
	}
}

if ( ! function_exists('note')) {

	/** get note by name
	 * 
	 *	examples:
	 *		note('phone') -- get content of STORAGE.'/ru_phone.php'
	 *		note('address/phone') -- get content of STORAGE.'/address/ru_phone.php'
	 *
	 * @param 	string	path
	 * @return 	Note
	 */
	function note($path)
	{
		$path = explode(DIRECTORY_SEPARATOR, $path);
		$file = array_pop($path);
		
		$file = File::factory(implode(DIRECTORY_SEPARATOR, $path).DIRECTORY_SEPARATOR.Site::get_language( ).File::SEPARATOR.$file.Kohana_File_Content::EXTENSION)
				->driver('File_Content');
		
		return $file->content( );
	}
}