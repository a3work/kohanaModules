<?php defined('SYSPATH') or die('No direct script access.');
/**
 *
 * @name		File database-based collection engine
 * @package 	Files
 * @author 		Stanislav U. Alkimovich <a3.work@gmail.com>
 * @date 		2013-10-09
 *
 **/
class Kohana_File_Collection_Virtual extends File_Collection
{
	/** Add file to collection
	 *
	 * @param	Kohana_Collection		parent collection
	 * @param	mixed					file to add
	 * @return	Kohana_Collection		current collection
	 */
	public function append(Kohana_Collection $collection, $file)
	{
		if (is_string($file))
		{
			$file = File::factory($file);
		}
	
		if ( ! isset($collection->content[$file->path( )]))
		{
			$collection->content[$file->path( )] = $file;
		}
	
		return $collection;
	}
}