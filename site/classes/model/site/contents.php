<?php defined('SYSPATH') or die('No direct script access.');
Class Model_Site_Contents extends ORM
{
	protected $_table_name = 'site_contents';

	protected $_belongs_to = array(
		'site_map' => array ('model' => 'site_map','foreign_key' => 'map_id'),
		'site_menu_item' => array ('model' => 'site_menu_item','foreign_key' => 'contents_id'),
	);
}
?>