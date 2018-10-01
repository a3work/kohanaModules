<?php defined('SYSPATH') or die('No direct script access.');
Class Model_Menu_Item extends ORM
{
	protected $_table_name = 'site_menu_items';
		
	protected $_table_columns = array(
		'id'						=> array('type' => 'int'),
		'map_id'				=> array('type'=> 'int'),
		'menu_id'				=> array('type'=> 'int'),
		'contents_id'		=> array('type'=> 'int'),
		'position'				=> array('type'=> 'float'),
	);
		
	protected $_belongs_to = array(
		'map' 			=> array ('model' => 'map','foreign_key' => 'map_id'),
		'menu' 			=> array ('model' => 'menu','foreign_key' => 'menu_id'),
		'contents' 	=> array ('model' => 'contents','foreign_key' => 'contents_id'),
	);
}
?>