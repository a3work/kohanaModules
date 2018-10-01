<?php defined('SYSPATH') or die('No direct access allowed.');

return array
(
	'obfuscation' => FALSE,
	'upload_dir' => APPPATH. 'media/upload',
	'protected_files' => array(
		'msk.xls',
		'uae.txt',
	),
	'encoding' => 'utf-8',

	// длина элемента строки родителей
	'parents_item_length' => 6,

	// available filetypes
	'available_types' => '(css|less|js|img|editor|fonts|ico|gallery)',

	// types with default behavior of basic controller
	'types_def' => array('js', 'css', 'less', 'img'/*, 'files'*/, 'editor', 'fonts'),

	// default directory contains images
	'images_work_dir' => APPPATH.'media/img/',

	// directory for preview
	'images_preview_dir' => APPPATH.'media/preview/',

	// images cache switch
	'images_cache_enabled' => TRUE,

	// resize settings (see controller_basic:117)
	'images_resize' => array(
		'ico' => array(
			'work_dir' 	=> APPPATH.'media/img/gallery/',
			'width' 	=> 176,
			'height' 	=> 176,
			'align'		=> 'center',
			'valign'	=> 'middle',
			'normal'	=> 1,
		),
		'gallery' => array(
			'work_dir' 	=> APPPATH.'media/img/gallery/',
			'width'		=> 800,
			'height'	=> 600,
			'align'		=> 'center',
			'valign'	=> 'middle',
		)
	),
);