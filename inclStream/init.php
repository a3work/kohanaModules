<?php defined('SYSPATH') or die('No direct script access.');

// require Kohana::find_file('vendor', 'lessphp/lessc.inc');

// Static file serving (CSS, JS, images)
Route::set('static_files', '<filetype>(/<file>(/))', array(
        'filetype' => '(css|less|js|img|editor|fonts)',
        'file' => '(.|\/)+',
    ))
	->defaults(array(
		'controller' 	=> 'InclStream',
		'action'     	=> 'getfiles',
        'filetype'   	=> NULL,
		'file'     	 	=> NULL,
	));

Route::set('includes', '<type>/<id>(/)', array(
		'type'	=> 'js|css|less',
		'id'	=> '.{32}',
	))
	->defaults(array(
		'controller'=> 'InclStream',
		'action'	=> 'index',
	));


