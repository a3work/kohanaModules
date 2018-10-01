<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		File browser visualization
 * @package 	Files
 * @author 		Stanislav U. Alkimovich
 * @date 		2013-10-07
 *
 **/

class Kohana_Controller_Browser extends Controller_Cms {

	/* :TODO: table and tree views */
// 	public $template = 'files.folder';

// 	public function before( )
// 	{
// 		parent::before( );
// 
// 		$this->template->content = '';
// 	}
// 
	/**
	 * Display current directory
	 *
	 * @param string 	variable name
	 * @param array		parameters
	 *
	 * @return mixed
	 */
	public function action_index( )
	{
// 		InclStream::instance( )->add('browser.js');
		InclStream::instance( )->add('browser.css');
// 		var_dump(md5_file('/var/www/lesgaft.works.spb.ru/application/media/_files/front.jpg'));
// 		var_dump(md5_file('/var/www/lesgaft.works.spb.ru/application/media/_files/_front.jpg'));
//
// 		var_dump(rename('/var/www/lesgaft.works.spb.ru/application/media/_files/front.jpg', '/var/www/lesgaft.works.spb.ru/application/media/_files/_front.jpg'));

	// 		File::load_by_id(0)->refresh( );
		$out = '';
		$file = File::load_by_uri($this->request->param('file'));

		if ($file instanceof File_Collection)
		{
// 			$this->template->content = $form;
			// add link to parent
			$out .= $file->html_parent('files_browse');

			// load content and traverse it
			foreach ($file->content( ) AS $item)
			{
				// check removed files
				if ( ! $item->file_exists( ))
				{
					continue;
				}

				$out .= $item->html('files_browse');
			}
		}
		else
		{
			$this->auto_render = FALSE;

			// download file
			$file->download( );
		}
		
		$this->template->body = View::factory('files.folder', array('content' => $out))->render( );

	}
}
