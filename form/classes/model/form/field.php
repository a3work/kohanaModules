<?php defined('SYSPATH') or die('No direct script access.');
Class Model_Form_Field extends ORM
{
	protected $_table_name = 'form_fields';

	protected $_table_columns = array(
		'id'			=> array('type' => 'int'),
		'form_id'		=> array('type'=>'int'),
		'relation_id' 	=> array('type'=>'int'),
		'parent' 		=> array('type'=>'int'),
		'can_extends' 	=> array('type'=>'int'),
		'type' 			=> array('type'=>'string'),
		'position' 		=> array('type'=>'float'),
		'disabled' 		=> array('type'=>'int'),
		'name' 			=> array('type'=>'string'),
		'value' 		=> array('type'=>'string'),
		'default' 		=> array('type'=>'string'),
		'selected' 		=> array('type'=>'int'),
		'template' 		=> array('type'=>'string'),
		'placeholder' 	=> array('type'=>'string'),
		'ru_RU' 		=> array('type'=>'string'),
	);

	protected $_has_many = array(
		'form_rule' => array ('model' => 'form_rule','foreign_key' => 'field_id'),
		'form_relation' => array('model' => 'form_relation', 'foreign_key' => 'field_id'),
	);
	protected $_belongs_to = array(
		'form' => array ('model' => 'form','foreign_key' => 'form_id'),
		'form_relation' => array ('model' => 'form_relation','foreign_key' => 'relation_id'),
	);
}
?>