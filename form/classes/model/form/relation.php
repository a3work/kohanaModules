<?php defined('SYSPATH') or die('No direct script access.');
Class Model_Form_Relation extends ORM
{
	protected $_table_name = 'form_relations';

	protected $_table_columns = array(
		'id'					=> array('type' => 'int'),
		'field_id'			=> array('type'=>'int'),
		'parent' 			=> array('type'=>'int'),
		'condition' 		=> array('type'=>'string'),
		'rule_id' 			=> array('type'=>'int'),
		'args' 				=> array('type'=>'string'),
	);		
	
	protected $_belongs_to = array(
		'form_field' => array ('model' => 'form_field','foreign_key' => 'field_id'),
		'valid_rule' => array ('model' => 'valid_rule','foreign_key' => 'rule_id'),
	);
	
	protected $_has_many = array(
		'form_field' => array ('model' => 'form_field','foreign_key' => 'relation_id'),
		'form_action' => array(
			'model' 			=> 'form_action',
			'through' 			=> 'form_actions_relations',
			'foreign_key' 	=> 'relation_id',
			'far_key' 			=> 'action_id',
		),
	);
}
?>