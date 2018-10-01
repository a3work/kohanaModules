<?php defined('SYSPATH') or die('No direct script access.');
Class Model_Form_Rule extends ORM
{
	protected $_table_name = 'form_rules';
		
	protected $_table_columns = array(
		'id'					=> array('type' => 'int'),
		'field_id'			=> array('type'=>'int'),
		'position' 			=> array('type'=>'float'),
		'rule_id' 			=> array('type'=>'int'),
		'args' 				=> array('type'=>'string'),
	);		

	protected $_belongs_to = array(
		'form_field' => array ('model' => 'form_field','foreign_key' => 'field_id'),
		'valid_rule' => array ('model' => 'valid_rule','foreign_key' => 'rule_id'),
	);
}
?>