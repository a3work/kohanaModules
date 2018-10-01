<?php defined('SYSPATH') or die('No direct access allowed.');

return array
(
	'width' => 120,
	'height' => 46, 

	'text_color' => array(152, 36, 38),
	'border_color' => array(152, 36, 38),
	'background_color' => array(226, 147, 148),
	
	'min_block' => 2,
	'max_block' => 14,
	
	'check_step' => 2,

	'session_variable' => 'captcha',

	'fonts' => (object) array(
		'text' => 'font5',
		'sign' => 'font7',
	),
);