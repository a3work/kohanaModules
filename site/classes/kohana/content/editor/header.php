<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Site content header editor
 * @package 	Site
 * @author 		Stanislav U. Alkimovich
 * @date 		2013-06-03
 *
 **/

class Kohana_Content_Editor_Header extends Editor_Element_CKE_Simple {

	public function __construct( )
	{
		parent::__construct('site_contents', Site::get_language( ));
	}

	protected function check_permissions( )
	{
		return (acl('site_master_edit') || acl('page_edit', $this->orm_instance( )->map_id));
	}

	/**
	 * Prepare data, clear cache
	 *
	 */
	protected function prepare( )
	{
		// get item
		$item = $this->orm_instance->site_map;

		// get account id
		$act_user_id = Access::instance( )->get('page_edit', $item->id)->id;

		// if account is group get group member id
		if (($user = User::get($act_user_id)) === FALSE)
		{
			$user = User::instance( )->id_by_group($act_user_id);
		}

		// write to access log
		Access_Log::instance( )->write("Пользователь <b>{$user->login}</b> редактировал заголовок страницы <a href='{$item->uri}'>{$item->uri}</a>");

		// clear html cache
		while ($item->parent > -1)
		{
			HtmlDump::instance( )->clean($item->uri_hash);

			$item = ORM::factory('site_map', $item->parent);
		}

		// trim all tags
		$this->processed_data(strip_tags($this->processed_data( )));
	}
}