<?php defined('SYSPATH') or die('No direct script access.');

class Kohana_Model_Movement extends ORM
{
	protected $_table_name = 'shop_movement';

	protected $_belongs_to = array(
		'order' => array('model' => 'order', 'foreign_key' => 'item_id'), /* :DEPRECATED */
		'order_item' => array('model' => 'order_item', 'foreign_key' => 'item_id'),
		'state' => array('model' => 'state', 'foreign_key' => 'state_id'),
	);
}