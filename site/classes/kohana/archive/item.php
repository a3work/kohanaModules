<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Representation of archived file
 * @package 	Files
 * @author 		Stanislav U. Alkimovich <a3.work@gmail.com>
 * @date 		17.10.2014
 *
 **/

class Kohana_Archive_Item extends File
{
	/**
	 * @var array		Archive data values 
	 * 
	 * @example			
	 * 	Array
		(
			[name] => foobar/baz
			[index] => 3
			[crc] => 499465816
			[size] => 27
			[mtime] => 1123164748
			[comp_size] => 24
			[comp_method] => 8
		)
	 * 
	 */
	protected $_data;

    /** Archive item constructor
     * 	DON'T USE Archive_Item::factory instead
	 *
     * @param   string  absolute filepath (for ex.: /var/www/example.com/application/storage/my_file.tmp)
	 * @return  void
	 */
	public function __construct(Kohana_File $zip, $name)
	{
		// set up archive filename
		$this->_dir = $zip;
		$this->_dir_name = $zip->path(FALSE);

		// save filename
		$this->_name = $name;
		
        // initialize object
		$this->init( );
		
        /* load driver */
        $this->_init_driver( );
	}
	
	
	/** Initialize file parameters
	 *
	 * @param	array	ZipArchive::statIndex return value
	 * @return
	 */
	public function init( )
	{
        $this->_is_dir	= (substr($this->_name, -1) == DIRECTORY_SEPARATOR);
        
        $this->_path 	= $this->_dir->path( ).DIRECTORY_SEPARATOR.$this->_name;
        
        if ($this->_is_dir)
        {
			$this->_path .= DIRECTORY_SEPARATOR;
			$this->_path = str_replace('//', '/', $this->_path);
        }
        
		$this->_is_root	 = FALSE;
        $this->_dir_name = pathinfo($this->_path, PATHINFO_DIRNAME); // !
        $this->_exists   = TRUE;
        $this->_ext		 = pathinfo($this->_path, PATHINFO_EXTENSION); // !;
		
		$this->_size    = $this->dir( )->filesize($this->_name);
// 		$this->_mime    = File::mime($this->_path);
        
        // drop access file object
        $this->_access = NULL;
	}
	
	
	/** Write and/or get access variables for current file
	 *
	 * @param   Kohana_File parent Kohana_File instance
	 * @param 	array	access rules
	 * @return 	array
	 */
	public function access_file( )
	{
		return $this->dir( )->access_file( );
	}
	
	
	/** Unpack to temporary file :TODO:
	 *
	 * @return Kohana_File
	 */
	protected function _unpack_tmp( )
	{
		return $this->dir( )->unpack($this);
	}
	
	/** Extract file to specified folder
	 * 
	 * @param	mixed		destination
	 * @return	Kohana_File
	 */
	public function extract($dst = NULL)
	{
		return $this->dir( )->extract($dst, $this);
	}
}