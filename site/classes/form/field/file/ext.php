<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Extended file field description
 * @package 	Form
 * @author 		Stanislav U. Alkimovich
 * @date 		2013-07-08
 *
 **/

class Form_Field_File_Ext extends Form_Field_File
{
	public $upload_dir;

	// input type
	public $view = 'file.extended';
	public $multiple = TRUE;


	/** Fetch data handler
	 *
	 * @return this
	 */
	public function on_submit( )
	{
		if ($this->upload_dir( ) !== NULL)
		{
			foreach ($this->result( ) AS $item)
			{
				Upload::save($item, $item['name'], $this->upload_dir( ), File::NEW_FILE_MODE);
			}
		}
	}

}