<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Json file representation
 * @package 	Files
 * @author 		Stanislav U. Alkimovich
 * @date 		2014-07-07
 *
 **/

abstract class Kohana_File_Json extends File_Regular
{
	/** Content setter / getter
	 *
	 * @param 	Kohana_File	
	 * @param 	string		text for saving
	 * @param	string		insert mode
	 * @return 	string
	 */	
	public function content(Kohana_File $file, $text = NULL, $mode = NULL)
	{
		if ($text !== NULL)
		{
			if ( ! is_writable($file->dir( )->path( )))
			{
				throw new File_Exception('Directory :file must be a writable.', array(':file' => $file->dir( )->path( )));
			}
			
			if (file_put_contents($file->path( ), Basic::json_safe_encode($text, JSON_FORCE_OBJECT), $mode) === FALSE)
			{
				throw new File_Exception('Cannot write file :file', array(':file' => $file->path( )));
			}
			
			return;
		}
		
		return Basic::json_safe_decode(file_get_contents($file->path( )));
	}
}