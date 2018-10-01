<?php defined('SYSPATH') or die('No direct script access.');

class Kohana_Model_Template extends ORM
{
	protected $_table_name = 'shop_templates';

	protected $_has_many = array(
		'suppliers'	=> array('model' => 'supplier', 'far_key' => 'supplier_id', 'foreign_key' => 'template_id', 'through' => 'shop_suppliers_templates'),
	);
}
