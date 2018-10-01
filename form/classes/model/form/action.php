<?php defined('SYSPATH') or die('No direct script access.');
Class Model_Form_Action extends ORM
{
	protected $_table_name = 'form_actions';
		
	protected $_table_columns = array(
		'id'					=> array('type' => 'int'),
		'action_id'		=> array('type' => 'int'),
		'relation_id'		=> array('type' => 'int'),
		'args' 				=> array('type' => 'string'),
		'position' 			=> array('type '=> 'float'),
	);		

	protected $_has_many = array(
		'form_relation' => array(
			'model' 			=> 'form_relation',
			'through' 			=> 'form_actions_relations',
			'foreign_key' 	=> 'action_id',
			'far_key' 			=> 'relation_id',
		),
	);
}
?>