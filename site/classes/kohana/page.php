<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Page management handlers
 * @package 	Site
 * @author 		Stanislav U. Alkimovich
 * @date 		2013-06-21
 *
 **/

class Kohana_Page extends File
{
    /**
     * @const   string   main part of page
     */
    const MAIN_PART = 'body';

    /**
     * @const   string  data file extension
     */
    const DATA_EXTENSION = '.php';
    
	/**
	 * @var Page		parent of current page
	 */
	protected $_parent_page;
	
	/** Object constructor
	 *
     * @param   string  filepath
	 * @return void
	 */
	public function __construct($path)
	{
		$this->driver('File_Page');
		
        parent::__construct($path);
        
		$this->_pattern = Site::get_language( ).File::SEPARATOR.'*'.Kohana_File_Content::EXTENSION;
	}

	/** Initialize file parameters
	 *
	 * @return
	 */
	public function init( )
	{
		parent::init( );
		
        if ($this->_exists)
        {
			// check existence of main part 
			$this->page_main_part = $this->child(Site::get_language( ).File::SEPARATOR.Kohana_Page::MAIN_PART.Kohana_File_Content::EXTENSION);
			$this->_exists = $this->page_main_part->exists( );
		}
	}
	
	/** Returns filename for main part of page according to current language
	 *
	 * @param	string	language	
	 * @return 	string
	 */
	public static function main_part($language = NULL)
	{
		if ($language === NULL)
		{
			$language = Site::get_language( );
		}
		
		return $language
				.File::SEPARATOR
				.Kohana_Page::MAIN_PART
				.Kohana_File_Content::EXTENSION;
	}
	
	/** Return page texts
	 *
	 * @return 	array
	 */
	public function text( )
	{
		$out = array( );
	
		foreach ($this AS $key => $file)
		{
			// fetch content key
			$key = str_replace(array($file->dir( )->path( ).Site::get_language( ).File::SEPARATOR, Kohana_File_Content::EXTENSION), '', $key);

			$out[$key] = $file->content( );
		}
		
		return $out;
	}
	
	/** Getter of parent page
	 *
	 * @return 	Page
	 */
	public function parent_page( )
	{
		if ($this->is_root( ))
		{
			throw new File_Exception('Cannot load parent for index page.');
		}
	
		if ($this->_parent_page === NULL)
		{
			$this->_parent_page = Page::factory($this->_dir_name);
		}
	
		return $this->_parent_page;
	}
	
}