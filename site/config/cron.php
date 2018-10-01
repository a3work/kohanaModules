<?php defined('SYSPATH') or die('No direct script access.');

return array(
	'route_header' => 'cron',
	
	'rules' => array(
		'* * * * *' 	=> __('every minute'),
		'*/15 * * * *' 	=> __('four times in hour'),
		'*/30 * * * *' 	=> __('twice in hour'),
		'0 * * * *' 	=> __('every hour'),
		'0 */2 * * *' 	=> __('every second hour'),
		'0 */6 * * *' 	=> __('four times in day'),
		'0 */12 * * *' 	=> __('twice in day'),
	),
);