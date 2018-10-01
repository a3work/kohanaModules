<?php

class Access_Site extends Access_Module implements Access_Objected_Module
{
	public function __construct( )
	{
		$this->name('Журнал событий');
		$this->add('log_browse')->label('Просмотр журнала событий');
		$this->add('log_browse_sys')->hidden(TRUE)->label('Просмотр системных событий');

		// Module name
		$this->name('Управление содержимым');

		// Module privileges
		$this->add('site_master_edit')->label('Неограниченное управление материалами.');
		$this->add('site_edit')->obj_parent( )->label('Управление материалами.');
		$this->add('site_config')->label('Изменение настроек сайта.');
// 		$this->add('page_edit', TRUE)->defaults( )->label('Редактирование материала.');
// 		$this->add('page_view', TRUE)->defaults( )->label('Просмотр материалов.');
// 		$this->add('page_add', TRUE)->label('Создание материалов.');
// 		$this->add('page_delete', TRUE)->label('Удаление материалов.');
// 		$this->add('page_access', TRUE)->label('Управление правами доступа.');
		$this->add('file_read', TRUE)->defaults( )->label('Чтение');
		$this->add('file_write', TRUE)->defaults( )->label('Запись');
		

		// Module access templates
		$this->template('Редактор')->attach('site_edit');
		$this->template('Администратор')->attach('site_master_edit')->attach('site_config');
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

		ORM::factory('site_map', $obj_id)->delete_child_access($privileges, $user_id);
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

		ORM::factory('site_map', $obj_id)->add_child_access($privileges, $user_id);
	}
}