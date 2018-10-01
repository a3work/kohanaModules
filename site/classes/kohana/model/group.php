<?php defined('SYSPATH') or die('No direct access allowed.');
/**
 * @package    Kohana/Personal
 * @author     A. St.
 */
class Kohana_Model_Group extends ORM {

	protected $_table_name = 'user_accounts';

	/** A user has many tokens and roles
	 *
	 * @var array Relationhips
	 */
	protected $_has_many = array(
		'users'			=> array('model' => 'user', 'far_key' => 'user_id', 'foreign_key' => 'group_id', 'through' => 'user_groups'),
	);

	public function get_list( )
	{
		$group_href = Route::url('user_manage', array('list' => 'groups', 'id' => 0));

		return DB::query(Database::SELECT, "SELECT id AS 'key', CONCAT('<a href=\"', REPLACE('$group_href', '0', id), '\" target=\"_blank\">', username, '</a>') AS 'header' FROM `{$this->_table_name}` WHERE is_group = 1 AND is_system = 0")->execute( );
	}
	
	/** Get group by name
	 *
	 * @param 	string	name
	 * @return 	Model_Group
	 */
	public function get_by_name($name)
	{
		return $this
				->where('is_group', '=', 1)
				->where('username', '=', $name)
				->find( );
	}
}