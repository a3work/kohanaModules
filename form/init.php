<?php defined('SYSPATH') or die('No direct script access.');

$config = Kohana::$config->load('form');

Route::set('captcha', 'captcha/<id>')
	->defaults(array(
		'controller' => 'captcha',
	));

Route::set('form_tree_level', $config->route_header.'/tree/<element>/<value>(/)')
	->defaults(array(
		'controller'	=> 'form',
		'action'		=> 'tree_level',
	));

Route::set('form_by_id', $config->route_header.'/<id_type>/<id>(/)',
	array(
		'id_type' 		=> 'map|id|label',
		'id' 				=> '.+',
	))
	->defaults(array(
		'controller'	=> 'form',
		'action'			=> 'init',
		'id_type'		=> 'label',
	));

Route::set('form', $config->route_header.'(/<page>(/))',
	array(
		'page' 			=> '(.|\/)+',
	))
	->defaults(array(
		'controller' 	=> 'form',
		'action'     	=> 'index',
		'page'		 	=> '',
	));
?>