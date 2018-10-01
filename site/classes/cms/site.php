<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Site cms module
 * @package 	Site
 * @author 		Stanislav U. Alkimovich
 * @date 		2013-08-16
 *
 **/

class CMS_Site extends CMS_Module
{
	/** Object constructor
	 *
	 * @return void
	 */
	public function __construct( )
	{
		$page = Page::factory(Request::detect_uri( ));
		
		if ($page->exists( ))
		{
			$menu = $this->menu(__('page'));

			
			if (acl('file_write', $page))
			{
				$menu->item(__('add page'), $page->action('add'))->window(TRUE);
			}
			if (acl('file_write', $page->access_file( )))
			{
				$menu->item(__('access control list'), $page->action('access'))->window(TRUE);
			}
			if (acl('file_write', $page))
			{
				$menu->item(__('edit metadata'), $page->action('attributes'))->window(TRUE);
	// 			$menu->item(__('edit properties'), Route::url('site_manage', array('action' => 'prop')))->add_opener(TRUE);
			}
			if (acl('file_write', $page->menu('main')))
			{
				$menu->item(__('edit menu'), $page->menu('main')->action('manage'))->window('popup_client');
			}			
			if ( ! $page->is_root( ) && acl('file_write', $page->dir( )))
			{
				$menu->item(__('delete page'), $page->action('remove'));
			}
		}

		$menu = $this->menu(__('site'));

		if (acl('site_config'))
		{
// 			$menu->item(__('edit configuration'), Route::url('site_manage', array('action' => 'config')));
// 			$menu->item(__('edit properties'), Route::url('site_manage', array('action' => 'prop')))->add_opener(TRUE);
		}
		if (acl('page_access', File::factory( )))
		{
// 			$menu->item(__('access to site root'), Route::url('site_manage', array('action' => 'access')))->add_opener(FALSE);
		}
		if (acl('log_browse'))
		{
			$menu->item(__('event log'), Route::url('log'));
		}
		
		$menu->item(__('go to main page'), '/');
	}
}