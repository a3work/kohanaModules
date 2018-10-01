<?php defined('SYSPATH') or die('No direct script access.');
Class Model_Form_Backup extends ORM
{
	protected $_table_name = 'form_backup';

	protected $_table_columns = array(
		'id'		=> array('type' => 'int'),
		'data'	=> array('type'=> 'string'),
		'time' 	=> array('type'=> 'string'),
		'ip' 		=> array('type'=> 'string'),
		'agent' => array('type'=> 'string'),
	);		
	
}
?>