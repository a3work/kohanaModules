<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		File field description
 * @package 	Form
 * @author 		Stanislav U. Alkimovich
 * @date 		2013-07-08
 *
 **/

class Form_Field_File_Multiple extends Form_Field
{
		// multiple flag
	public $multiple = TRUE;
	
	// input type
	public $view = 'file_multiple';

	// is file
	public $file = TRUE;

	public function on_attach( )
	{
		$this->form( )->enctype('multipart/form-data');
	}

	/** not_empty shortcut
	 *
	 * @param string message
	 * @return this
	 */
	public function not_empty($message = NULL)
	{
		return $this->rule('upload_not_empty_multiple', NULL, $message);
	}


	public $upload_dir;


	/** Fetch data handler
	 *
	 * @return this
	 */
	public function on_submit( )
	{
		if ($this->upload_dir( ) === NULL)
		{
			$this->upload_dir(Site::config('site')->upload_dir);
		}
		
		if (ff($this->upload_dir( ))->exists( ) === FALSE)
		{
			// create directory without checking of permissions
			ff($this->upload_dir( ))->driver('File_Directory')->create(FALSE);
		}
		
		$result = array();

		if (count($this->result()) > 0)
		{
			foreach ($this->result() AS $item)
			{
				$filename = $item['name'];
				
				if ($filename == '')
				{
					continue;
				}
	
				if ($this->generate_name( ) === TRUE)
				{
					$filename = Basic::get_hash(mt_rand( ), 'md5', 8).'.'.ff($item['name'])->ext( );
				}
				else
				{
					$filename = $item['name'];
				}
				
				$i = 0;
				while (ff($this->upload_dir( ))->child($filename)->exists( ) === TRUE)
				{
					$filename = ff($this->upload_dir( ))->child($item['name'])->name(TRUE).'.'.$i++.'.'.ff($this->upload_dir( ))->child($item['name'])->ext( );
				}

				Upload::save($item, $filename, ff($this->upload_dir( ))->path( ), Site::config('site')->new_file_mode);

				$result[] = ff($this->upload_dir( ))->child($filename)->path(FALSE);
				ff($this->upload_dir( ))->child($filename)->init();
			}
		}
		
		// reload value
		$this->result($result);
	}
}
