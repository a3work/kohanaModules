<?php defined('SYSPATH') or die('No direct script access.');

class Upload extends Kohana_Upload {

	/**
	 * Override Kohana_Upload::save( ): add directory creation
	 *
	 * Save an uploaded file to a new location. If no filename is provided,
	 * the original filename will be used, with a unique prefix added.
	 *
	 * This method should be used after validating the $_FILES array:
	 *
	 *     if ($array->check())
	 *     {
	 *         // Upload is valid, save it
	 *         Upload::save($array['file']);
	 *     }
	 *
	 * @param   array    uploaded file data
	 * @param   string   new filename
	 * @param   string   new directory
	 * @param   integer  chmod mask
	 * @return  string   on success, full path to new file
	 * @return  FALSE    on failure
	 */
	public static function save(array $file, $filename = NULL, $directory = NULL, $chmod = 0644)
	{
		if (ff($directory)->exists( ) === FALSE)
		{
			ff($directory)->driver('File_Directory')->create(FALSE);
		}
	
		return Kohana_Upload::save($file, $filename, ff($directory)->path( ), $chmod);
	}
	
	/**
	 * Validation rule.
	 * Check existence of uploaded files
	 *
	 * @param	array	array of files
	 * @return	boolean
	 */
	public static function not_empty_multiple(array $files)
	{
		if (count($files) == 0)
		{
			return FALSE;
		}
		
		$result = TRUE;
		foreach ($files AS $file)
		{
			$file_res = (isset($file['error'])
				AND isset($file['tmp_name'])
				AND $file['error'] === UPLOAD_ERR_OK
				AND is_uploaded_file($file['tmp_name']));
				
			if ($file_res === FALSE)
			{
				$result = FALSE;
				break;
			}
		}
		
		return $result;
	}

}
