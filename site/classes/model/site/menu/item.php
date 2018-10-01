<?php defined('SYSPATH') or die('No direct script access.');
Class Model_Site_Menu_Item extends ORM
{
	protected $_table_name = 'site_menu_items';

	protected $_belongs_to = array(
		'site_map' 			=> array ('model' => 'site_map','foreign_key' => 'map_id'),
		'site_menu' 			=> array ('model' => 'site_menu','foreign_key' => 'menu_id'),
		'site_contents' 	=> array ('model' => 'site_contents','foreign_key' => 'contents_id'),
	);
}
?>