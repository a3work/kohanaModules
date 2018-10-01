<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Common interface of archive
 * @package 	Files
 * @author 		Stanislav U. Alkimovich <a3.work@gmail.com>
 * @date 		17.10.2014
 *
 **/

interface Kohana_File_Archive {
	
	/** Unpack archive or specified file
	 *
	 * @param 	Kohana_Archive_Item		source Archive
	 * @param 	mixed 					destination
	 * @param 	mixed 					file
	 * @return 	void
	 */
	public function extract(Kohana_File $src, $dst = NULL, $file = NULL);
	
	/** Pack specified file to archive
	 *
	 * @param 	Kohana_File		destination archive
	 * @param 	mixed			file
	 * @param 	string 			filename
	 * @return 	Kohana_Archive_Item
	 */
	public function add(Kohana_File $archive, $file, $filename = NULL);
	
	/** Get size of specified file
	 *
	 * @param 	Kohana_File		archive
	 * @param 	string			child filename
	 * @return 	Kohana_File
	 */
	public function filesize(Kohana_File $file, $filename);
}