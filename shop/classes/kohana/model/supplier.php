<?php defined('SYSPATH') or die('No direct script access.');

class Kohana_Model_Supplier extends ORM {
	protected $_table_name = 'shop_suppliers';

	protected $_has_many = array(
		'templates'	=> array('model' => 'template', 'far_key' => 'template_id', 'foreign_key' => 'supplier_id', 'through' => 'shop_suppliers_templates'),
	);
	
	protected $_has_one = array(
		'markup'	=> array('model' => 'markup', 'foreign_key' => 'supplier_id'),
	);

	protected $_belongs_to = array(
		'user'  => array(
			'model'       => 'users',
			'foreign_key' => 'user_id',
		)
	);
}
