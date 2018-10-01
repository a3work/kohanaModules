<?php defined('SYSPATH') or die('No direct script access.');
Class Model_Form extends ORM
{
	protected $_table_name = 'form_forms';
		
	protected $_table_columns = array(
		'id'									=> array('type' => 'int'),
		'map_id'							=> array('type'=>'int'),
		'parent' 							=> array('type'=>'int'),
		'label' 								=> array('type'=>'string'),
		'method' 							=> array('type'=>'string'),
		'enctype' 							=> array('type'=>'string'),
		'action' 							=> array('type'=>'string'),
		'target' 							=> array('type'=>'string'),
		'template' 						=> array('type'=>'string'),
		'use_validation' 				=> array('type'=>'int'),
		'use_activator' 				=> array('type'=>'int'),
		'show_on_success' 			=> array('type'=>'int'),
		'clear_on_success' 			=> array('type'=>'int'),
		'modify_input_length' 		=> array('type'=>'int'),
		'enable_backup' 				=> array('type'=>'int'),
		'ru_RU' 							=> array('type'=>'string'),
		'comment' 						=> array('type'=>'string'),
	);		

	protected $_has_many = array(
		'form_field' => array ('model' => 'form_field','foreign_key' => 'form_id'),
	);
// 	protected $_belongs_to = array(
// 		'site_map' => array ('model' => 'site_map','foreign_key' => 'map_id'),
// 	);
}
?>