<?php defined('SYSPATH') or die('No direct script access.');
/**
 *
 * @name		Common engine of files collection
 * @package 	Files
 * @author 		Stanislav U. Alkimovich <a3.work@gmail.com>
 * @date 		2013-10-09
 *
 **/
class Kohana_File_Page_Collection extends File_Collection_Virtual
{
	// view name
	protected $_item_template = 'files.item.directory';

	/** Load content
	 *
	 * @param 	Kohana_File
	 * @return 	void
	 */
	protected function _load_content(Kohana_File $file)
	{
		$file->content = $this->_glob($file, GLOB_ONLYDIR);
		
		$trim_keys = array();
		
		foreach ($file->content AS $key => $page)
		{
			$file->content[$key] = Page::factory($page);
			
			if ($file->content[$key]->exists() === FALSE) {
				$trim_keys[] = $key;
			}
		}
		
		foreach ($trim_keys AS $key)
		{
			unset($file->content[$key]);
		}

		// mark collection as loaded
		$file->loaded(TRUE);
	}

	/** Create and return current item of Kohana_File content
	 *
	 * @param 	Kohana_File	file
	 * @return 	Kohana_File
	 */
    public function current(Kohana_File $file)
    {
		// get current key
		$key = key($file->content);

		if (is_string($file->content[$key]))
		{
			// get child file object
			$file->content[$key] = Page::factory(current($file->content));
		}
    
        return $file->content[$key];
    }
}