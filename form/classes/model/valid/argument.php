<?php defined('SYSPATH') or die('No direct script access.');
Class Model_Valid_Argument extends ORM
{
	protected $_table_name = 'valid_arguments';
	
	protected $_belongs_to = array(
		'rule' => array('model' => 'valid_rule', 'foreign_key' => 'rule_id'),
	);
}
?>