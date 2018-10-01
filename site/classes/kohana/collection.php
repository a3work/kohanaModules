<?php defined('SYSPATH') or die('No direct script access.');
/**
 *
 * @name		File collection engine
 * @package 	Files
 * @author 		Stanislav U. Alkimovich <a3.work@gmail.com>
 * @date 		24.07.14
 *
 **/
class Kohana_Collection extends File
{
	/**
	 * directory flag (TRUE if current object is directory)
	 * 
	 * @var string
	 */
	protected $_is_dir = TRUE;

	/**
	 * @var boolean	root directory flag (TRUE if path == STORAGE)
	 */
	protected $_is_root = FALSE;

	/**
	 * count of results
	 * @var integer
	 */
	protected $_count;

	/**
	 * @var array
	 */
	public $content = array( );

	/**
	 * @var string		classname of children 
	 */
	public $item_class = 'File';
	
	
    /** Search object constructor
     * 	use File::search or Search::factory instead
	 *
     * @param   string  absolute filepath (for ex.: /var/www/example.com/application/storage/my_file.tmp)
	 * @return  void
	 */
	public function __construct($path)
	{
		if (is_dir($path))
		{
			// save root path of collection
			$this->_path = $path.DIRECTORY_SEPARATOR;
			
			// save pattern
			$this->_pattern = '*';
		}
		else
		{
			// save root path of collection
			$this->_path = pathinfo($path, PATHINFO_DIRNAME).DIRECTORY_SEPARATOR;
			
			// save pattern
			$this->_pattern = basename($path);
		}
		
		$this->_path 	= str_replace('//', '/', $this->_path);
		
		// define driver class
		$this->driver('File_Collection_Virtual');
	}

	/**
	 * item class setter
	 * 
	 * @return Kohana_Collection OR string
	 */
	public function item_class($class = NULL)
	{
		$this->item_class = $class;
		
		return $this;
	}

	
	/** switch to DB search mode -- set File_Collection_Database driver
	 *
	 * @return object 	this collection
	 */
	public function set_db_driver( )
	{
		$this->driver('File_Collection_Database');
		
		return $this;
	}
	
	/** Load file
	 *  
	 *  overload File::factory, remove saving to global static array
	 *
	 * @param   string  path
	 * @return  File
	 */
	public static function factory($path = STORAGE)
	{
		$path = STORAGE.self::clear_path($path);
		$classname = get_called_class( );
		
		$class = new ReflectionClass($classname);
		return $class->newInstanceArgs(array($path));
	}
}