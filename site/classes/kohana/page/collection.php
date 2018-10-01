<?php

/**
 *
 * @name		File collection engine
 * @package 	Files
 *
 **/
class Kohana_Page_Collection extends Kohana_Collection {

    /** Search object constructor
     * 	use File::search or Search::factory instead
	 *
     * @param   string  absolute filepath (for ex.: /var/www/example.com/application/storage/my_file.tmp)
	 * @return  void
	 */
	public function __construct($path)
	{
		parent::__construct($path);
		
		// define driver class
		$this->driver('File_Page_Collection');
	}	
}
