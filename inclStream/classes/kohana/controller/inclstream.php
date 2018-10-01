<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Publish js and css, concatenate it and minify (:TODO:)
 * @package 	inclStream
 * @author 		A. St.
 * @date 		16.01.14
 *
 **/

class Kohana_Controller_InclStream extends Controller
{
	/** Action: index
	 *  publish js and css
	 *
	 * @return void
	 */
	public function action_index( )
	{
		$id = $this->request->param('id');

		$data = Cache::instance( )->get($id);
		
		$this->response->headers('content-type',  File::mime_by_ext($this->request->param('type')));
		$this->response->headers('last-modified', date('r', $data->modify_time));
		$this->response->body($data->body);
	}	
	
	/** Return static files
	 *
	 * @return 	void
	 */
    public function action_getfiles( )
    {
        $file = Security::xss_clean($this->request->param('file'));
        $filetype = Security::xss_clean($this->request->param('filetype'));
		
		if (in_array($filetype, array('js', 'css')) && Kohana::$caching === TRUE && Site::config('inclStream')->catenation_mode)
		{
			$filebody = Cache::instance( )->get($file);
		
			if ($filebody !== NULL && isset($filebody->body[$filetype]))
			{
				// Send the file content as the response
				$this->response->body($filebody->body[$filetype]);

				// Set the proper headers to allow caching
				$this->response->headers('content-type',  File::mime_by_ext($filetype));
				// $this->response->headers('content-type',  'text/html');
				$this->response->headers('last-modified', date('r', $filebody->modify_time));
			}
			else
			{
				// Return a 404 status
				throw new HTTP_Exception_404;
			}
		}
		else
		{
	// 			// Find the file extension
			$ext = pathinfo($file, PATHINFO_EXTENSION);

			// Remove the extension from the filename
			if ($ext != '')
			{
				$file = substr($file, 0, -(strlen($ext) + 1));
			}

			$file = Kohana::find_file('media/'.$filetype, $file, $ext);

			if ($file)
			{
				// Check if the browser sent an "if-none-match: <etag>" header, and tell if the file hasn't changed
				$this->response->check_cache(sha1($this->request->uri()).filemtime($file), $this->request);

				// execute php in js and css
				if (in_array($filetype, array('js', 'css', 'less')))
				{
					ob_start( );
					
					include $file;
					
					$body = ob_get_contents( );
					ob_end_clean( );
				}
	// 			// convert less
	// 			elseif ($filetype == 'less')
	// 			{
	// 				$less = new lessc;
	// 				$body = $less->compileFile($file);
	// 			}
				else
				{
					$body = file_get_contents($file);
				}
				
				
				// Send the file content as the response
				$this->response->body($body);

				// Set the proper headers to allow caching
				$this->response->headers('content-type',  File::mime_by_ext($ext));
	// 				$this->response->headers('content-type',  'text/html');
				$this->response->headers('last-modified', date('r', filemtime($file)));
			}
			else
			{
				// Return a 404 status
				throw new HTTP_Exception_404;
			}
		}
	}
}