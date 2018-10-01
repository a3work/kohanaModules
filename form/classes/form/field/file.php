<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		File field description
 * @package 	Form
 * @author 		Stanislav U. Alkimovich
 * @date 		2013-07-08
 *
 **/

class Form_Field_File extends Form_Field
{
	// input type
	public $view = 'file';

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
		return $this->rule('upload_not_empty', NULL, $message);
	}


	public $upload_dir;
	public $generate_name = FALSE;


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
		
		$item = $this->result();
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
		
		Upload::$remove_spaces = FALSE;
		Upload::save($item, $filename, ff($this->upload_dir( ))->path( ), Site::config('site')->new_file_mode);
		
		$file = ff($this->upload_dir( ))->child($filename);
		
		$file->init();
		
		$result = $file->path(FALSE);

		// reload value
		$this->result($result);
	}
}
