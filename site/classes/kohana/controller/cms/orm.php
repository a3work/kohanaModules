<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Common CMS ORM controller
 * @package 	Site
 * @author 		A. St.
 * @date 		12.01.2015
 *
 * :TODO: ?
 *
 **/

class Kohana_Controller_Cms_Orm extends Controller_Cms
{

	/** Action: index
	 *  list of CONTROLLER
	 *
	 * @return void
	 */
	public function action_index( )
	{

		
		$this->template->header = __u('files CONTROLLER');
		$this->template->body = 'index';
	}	

	/** Action: edit
	 *  Edit template
	 *
	 * @return void
	 */
	public function action_edit( )
	{
		$this->template->parent = __u('files CONTROLLER');
		$this->template->parent_href = Route::url('cms_CONTROLLER');
		$this->template->header = __u('edit file template :name', array('name' => ''));
		$this->template->body = 'edit';
	}	

	/** Action: delete
	 *  Delete template
	 *
	 * @return void
	 */
	public function action_delete( )
	{
		$this->template->parent = __u('files CONTROLLER');
		$this->template->parent_href = Route::url('cms_CONTROLLER');
		$this->template->header = __u('delete file template :name', array('name' => ''));
		$this->template->body = 'delete';
	}	
	
	
	/** Action: add
	 *  add new template
	 *
	 * @return void
	 */
	public function action_add( )
	{
		
		$this->template->parent = __u('files CONTROLLER');
		$this->template->parent_href = Route::url('cms_CONTROLLER');
		$this->template->header = __u('new file template');
		$this->template->body = 'add';
	}	

}