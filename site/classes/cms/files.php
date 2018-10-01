<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Site cms module
 * @package 	Site
 * @author 		Stanislav U. Alkimovich
 * @date 		2013-08-16
 *
 **/

class CMS_Files extends CMS_Module
{
	/** Object constructor
	 *
	 * @return void
	 */
	public function __construct( )
	{
		$menu = $this->menu(__('files'));

		if (acl('files_browse'))
		{
			$menu->item(__('file manager'), Route::url('cms.common', array('controller' => 'Filebrowser')));
// 			$menu->item(__('file manager'), Route::url('files_browse', array(File::ROUTE_PATH_VAR => '/')));
			
			if (acl('files_upload'))
			{
				$menu->item(__('upload to server'), Route::url('files', array('action' => 'upload')));
			}
		}


		// 		if (Route::name(Request::current()->route( )) == 'default')
// 		{
//
// 			if (acl('page_access', Page::current( )->id))
// 			{
// 				$menu->item(__('access control list'), Route::url('site_manage', array('action' => 'access')))->add_opener(TRUE);
// 			}
// 			if (acl('page_edit', Page::current( )->id))
// 			{
// 				$menu->item(__('edit metadata'), Route::url('site_manage', array('action' => 'meta')))->add_opener(TRUE);
// 	// 			$menu->item(__('edit properties'), Route::url('site_manage', array('action' => 'prop')))->add_opener(TRUE);
// 			}
// 			if (acl('page_delete', Page::current( )->parent))
// 			{
// 				$menu->item(__('delete page'), Route::url('site_manage', array('action' => 'delete')))->add_opener(TRUE);
// 			}
// 		}
//
// 		$menu = $this->menu(__('site'));
//
// 		if (acl('site_config'))
// 		{
// 			$menu->item(__('edit configuration'), Route::url('site_manage', array('action' => 'config')));
// // 			$menu->item(__('edit properties'), Route::url('site_manage', array('action' => 'prop')))->add_opener(TRUE);
// 		}
// 		if (acl('page_access', 0))
// 		{
// 			$menu->item(__('access to site root'), Route::url('site_manage', array('action' => 'access')))->add_opener(FALSE);
// 		}
	}
}