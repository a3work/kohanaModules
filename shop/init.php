<?php defined('SYSPATH') or die('No direct script access.');
	
Route::ext('cms.base', 'cms_cart', 'cart(/<action>(/<id>(/)))'/*, array(
		'id' => '\d+',
	)*/)
	->defaults(array(
		'controller' 	=> 'cms_cart',
	));
	
Route::set('cart', 'cart(/<action>(/<id>(/)))', array(
		'id' => '[0-9'.Kohana_Controller_Cart::IDS_SEPARATOR.']+',
	))
	->defaults(array(
		'controller' 	=> 'cart',
	));

// Route::set('orders', 'orders(/<id>(/<action>/))', array(
// 		'id' => '[0-9'.Kohana_Controller_Cart::IDS_SEPARATOR.']+',
// 	))
// 	->defaults(array(
// 		'controller' 	=> 'orders',
// 	));
	
	
Route::set('goods', 'goods(/<category>(/<id>.html))(/)', array(
		'category' => '((?!\.html$).|\/)+',
		'id'	  => '[^\/]+',
	))
	->defaults(array(
		'controller' 	=> 'shop',
		'action'	 	=> 'translate',
	));
	
Route::set('shop', 'shop(/<action>(/<id>))(/)')
	->defaults(array(
		'controller' 	=> 'shop',
		'action'	 	=> 'index',
	));

Access::module('Access_Shop');
CMS::module('Cms_Shop');
