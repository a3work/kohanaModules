<?php defined('SYSPATH') or die('No direct script access.');

return array(
	'providers' => array(
		'vk' => array(
			'client_id'     => '7777877',
			'client_secret' => 'u1Em3C6Of3Pe9TPjou7V',
			'redirect_uri'  => 'http://example.com/login/vk/',
		),
// 		'odnoklassniki' => array(
// 			'client_id'     => '777777777',
// 			'client_secret' => 'C3504630A7778066F25C4C7',
// 			'redirect_uri'  => 'http://example.com/auth?provider=odnoklassniki',
// 			'public_key'    => 'BBAKCBABADEDCBBAB'
// 		),
		'mailru' => array(
			'client_id'     => '777777',
			'client_secret' => 'aee26929c6fc1ccdd50e2f4ae0958c14',
			'redirect_uri'  => 'http://example.com/login?provider=mailru'
		),
		'yandex' => array(
			'client_id'     => '38ef777777b44614b302f6a439a7defc',
			'client_secret' => 'b2ade2ee4d9342358c95e84aee42d48b',
			'redirect_uri'  => 'http://example.com/login/yandex/'
		),
		'google' => array(
			'client_id'     => '318951577232-cu7989aui4aq3qa3kaqo0cndlk0ec6ou.apps.googleusercontent.com',
			'client_secret' => 'm_TU9Ema8K96GkIyCW538qJI',
			'redirect_uri'  => 'http://example.com/login/google/'
		),
		'facebook' => array(
			'client_id'     => '777777417687777',
			'client_secret' => '1b60e68c967ddcq269438f79874018f0',
			'redirect_uri'  => 'http://example.com/login/?provider=facebook',
		)
		
	),
	
	'common' => array(
		'redirect_url' => '/lib/',
		'error_url' => '/login/',
	),
);
