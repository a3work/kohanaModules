<?php

class Access_CMS extends Access_Module
{
	public function __construct( )
	{
		$this->name('Управление содержимым');

		$this->add('cms_menu')->label('Доступ к меню администратора');		// view popup cms menu

		// Module access templates
		$this->template('Редактор')->attach('cms_menu');
		$this->template('Администратор')->attach('cms_menu');
	}

	/** :TODO: **/

	/** Remove privileges of daughter objects regarding specified
	 *  Must can to remove all privileges and privileges of specified user only
	 *
	 * @param 	integer		parent object id
	 * @param 	integer 	user id
	 */
	public function clear_child($obj_id, $user_id = NULL) {}

	/** Add privileges of daughter objects regarding specified
	 *  Must can to add all privileges and privileges of specified user only
	 *
	 * @param 	integer		parent object id
	 * @param 	integer 	user id
	 */
	public function add_child($obj_id, $user_id = NULL) {}
}