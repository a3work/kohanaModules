<?php defined('SYSPATH') or die('No direct script access.');
Class Model_Site_Cookie_Store extends ORM
{
	protected $_table_name = 'site_cookie_store';
	
	protected $_table_columns = array(
		'id'			=> array('type' => 'int'),
		'cookie'		=> array('type' => 'string'),
		'user_agent'	=> array('type' => 'string'),
		'key'			=> array('type' => 'string'),
		'value'		=> array('type' => 'string'),
		'expire'		=> array('type' => 'int'),
	);
	 
	public function clear_auto_login( )
	{
		if ($this->_loaded)
		{
			$args = func_get_args( );
			
			foreach ($args AS $arg)
			{
				DB::query(Database::DELETE, "DELETE FROM ".$this->table_name( )." WHERE `value` LIKE '%$arg%' AND `key` = 'auto_login'")->execute( );
			}
		}
	}
}
