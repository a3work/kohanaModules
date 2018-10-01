<?php defined('SYSPATH') or die('No direct script access.');

return array(
	'hello' => strftime('%H') >= 18 ? 'good evening' : (strftime('%H') >= 15 ? 'good day' : (strftime('%H') >= 12 ? 'good afternoon' : (strftime('%H') >= 6 ? 'good morning' : 'good night'))),
);


