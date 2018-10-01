<?php defined('SYSPATH') or die('No direct script access.');
Class Model_Valid_Rule extends ORM
{
	protected $_table_name = 'valid_rules';
	
	protected $_has_many = array(
		'rule' => array('model' => 'rule', 'foreign_key' => 'rule_id'),
		'relation' => array('model' => 'relation', 'foreign_key' => 'rule_id'),
	);
}
?>