<?php

class Access_Files extends Access_Module implements Access_Objected_Module
{
	public function __construct( )
	{
		// Module name
		$this->name('Управление файлами');

		// Module privileges
		$this->add('files_browse')->label(__u('file manager usage'));
		$this->add('files_upload')->label(__u('upload to server'));
		$this->add('files_read', TRUE)->defaults( )->label(__u('read'));
		$this->add('files_write', TRUE)->label(__u('write'));

		// Module access templates
		$this->template('Редактор')->attach('files_browse')->attach('files_upload');
	}

	/** Remove privileges of daughter objects regarding specified
	 *  Must can to remove all privileges and privileges of specified user only
	 *
	 * @param 	integer		parent object id
	 * @param 	integer 	user id
	 * @return 	void
	 */
	public function clear_children($obj_id, $user_id = NULL)
	{
		$privileges = array_keys($this->privileges_obj( ));

		ORM::factory('files_map', $obj_id)->delete_child_access($privileges, $user_id);
	}

	/** Add privileges of daughter objects regarding specified for selected user
	 *
	 * @param 	integer		parent object id
	 * @param 	integer 	user id
	 * @return 	void
	 */
	public function add_for_children($obj_id, $user_id)
	{
		$privileges = array_keys($this->privileges_obj( ));

		ORM::factory('files_map', $obj_id)->add_child_access($privileges, $user_id);
	}
}