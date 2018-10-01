<?php

return array(
	// administration email for notifications (comma separated)
	'email_admin' => '',

	// default margin, %
	'margin_default' => 0,

	// probability for local pricelists
	'probability_local' => 100,
        
    // delay of local store
    'delivery_delay' => 1,
	
	// common viewing currency
	'default_currency' => 'RUR',
	
	// default accuracy
	'accuracy' => 2,

	// maximal delivery time of item to mark it as "quick", days
	'quick_order_max_time' => 5,
	
	// available currency
	'available_currency' => array(
		'USD',
		'EUR',
	),
	
	'catalog_files_dir' => 'catalog/files/',
	'catalog_item_files_dir_name' => 'files',
	'catalog_item_img_dir_name' => 'img',
	
	// send orders to this email addresses
	'email_admin' => '',

	// increase quantity of item (distinct_key = TRUE) or add item as new line
    'distinct_key' => FALSE, 

    // state of new order
    'begin_status' => 'initialize',
);

