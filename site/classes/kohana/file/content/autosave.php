<?php defined('SYSPATH') or die('No direct script access.');
/**
 *
 * @name		Html autosave file
 * @package 	Files
 * @author 		Stanislav U. Alkimovich <a3.work@gmail.com>
 * @date 		22.07.14
 *
 **/
class Kohana_File_Content_Autosave extends File_Regular
{
	/**
	 * @const string	autosave extension
	 */
	const EXTENSION = ".sav";
	
	/** Check autosave file source
	 *
	 * @param   Kohana_File autosave Kohana_File instance
	 * @param   Kohana_File parent Kohana_File instance
	 * @return 	boolean
	 */
	public function check(Kohana_File $autosave, Kohana_File $file)
	{
	
		return preg_match(
		
			'/'
			.str_replace('.', File::SEPARATOR, $file->name( ))
			.File::SEPARATOR
			.'\d{8}'
			.File::SEPARATOR
			.'\d{6}'
			.Kohana_File_Content_Autosave::EXTENSION
			.'/',
			
			$autosave->name( )
		);
	}
	
}