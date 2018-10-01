<?php defined('SYSPATH') or die('No direct script access.');

class Kohana_Controller_Page extends Controller_Cms
{
	/** Add new page
	 *
	 * @return 	void
	 */
	public function action_add( )
	{
		if ( ! acl('file_write', Page::factory($this->opener)))
		{
			throw new Access_Exception( );
		}
		
		$form = Form::factory( )
				->field('text', __u('header'), 'header')->not_empty( )->rule('page_exists')
				->field('text', __u('alias'), 'alias')
				->field('checkbox', __u('extra options'), 'extra')->hidden(TRUE)->rel('extra_on')->checked( )
				/* :TODO: make pages shortcuts */
// 				->field('text', __u('href'), 'href')->beh('extra_on')->action('show')
				->field('textarea', __u('title'), 'title')->beh('extra_on')->action('show')
				->field('textarea', __u('description'), 'descr')->beh('extra_on')->action('show')
				->field('textarea', __u('keywords'), 'kw')->beh('extra_on')->action('show')
				->field('submit', __u('add page'))
				->field('hidden', NULL, 'parent')->value($this->opener)
				->render( );

		if ($form->sent( ))
		{
			$result = $form->result( )->as_array( );
		
// 			if ($result['href'] != '')
// 			{
// 				$href = File::encode_uri($result['href']);
// 			}
// 			else
// 			{
// 			$href = URL::base('http');
// 			$parent = ORM::factory('site_map', $result['parent']);
// 
// 			if ($parent->loaded( ))
// 			{
// 				$href .= $parent->uri;
// 			}

			$href = $result['parent'].DIRECTORY_SEPARATOR.File::encode_uri($result['alias'] != '' ? $result['alias'] : $result['header']);

            Page::factory($href)->create( );
		}
				
		$this->template->header	= __u('new page');
		$this->template->body = $form;
	}

	/** Action: access to object **/
	public function action_access( )
	{
		if ( ! isset($this->opener))
		{
			$page = ORM::factory('site_map', 0);
		}
		else
		{
			$page = Page::get($this->opener);
		}

		if ( ! $page->loaded( ))
		{
			return Site::redirect(NULL, __u('page not found').'.');
		}

		if ( ! acl('page_access', $page->id))
		{
			throw new Access_Exception(__u('permission denied'));
		}
		$this->template->header = __u('access control list');
		$this->template->body = Request::factory(Route::get('access_obj')->uri(array('class'=>'site', 'obj_id'=>$page->id)))->execute( )->body( );
	}

	/** Action: delete current page **/
	public function action_delete( )
	{
		$page = Page::factory($this->opener);

		if ( ! $page->exists( ))
		{
			return Site::redirect(NULL, __u('page not found').'.');
		}

		if ($page->is_root( ))
		{
			throw new File_Exception('Cannot remove index page.');
		}

		if ( ! acl('file_write', $page->dir( )))
		{
			throw new Access_Exception;
		}

		$redirect_uri = URL::site($page->dir( )->path(FALSE));
		
		$page->remove( );
		
		Site::redirect($redirect_uri, __u('page has been removed successfuly').'.');
	}

	/** Delete current page and clear permissions
	 *
	 * @param Form_Result
	 * @param Site_Map		page for deleting
	 * @return View
	 */
	protected function delete($data, $page)
	{
		$data = $data->as_array( );

		if (isset($data['yes']))
		{
			$result = Page::delete($page);

			if ((boolean) $result)
			{
				return Site::redirect($result, __u('page has been deleted').'.');
			}
			else
			{
				return Site::redirect(NULL, '', array('time' => 0));
			}
		}
		else
		{
			return Site::redirect(NULL, '', array('time' => 0));
		}
	}

	/** Action: edit metadata **/
	public function action_meta( )
	{
		$page = Page::get($this->opener);

		if ( ! $page->loaded( ))
		{
			return Site::redirect(NULL, __u('page not found').'.');
		}

		if ( ! acl('page_edit', $page->id))
		{
			throw new Access_Exception(__u('permission denied'));
		}

		$content = ORM::factory('site_contents')
			->where('map_id', '=', $page->id)
			->where('label', '=', '')
			->order_by('id', 'desc')
			->find( );

		if ( ! $content->loaded( ))
		{
			throw new HTTP_Exception_404;
		}

		$form = Form::factory( )
					->handler(array($this, 'save_meta'), $content)
					->field('textarea', __u('title'), 'title_'.Site::get_language( ))->value($content->{'title_'.Site::get_language( )})
					->field('textarea', __u('description'), 'descr_'.Site::get_language( ))->value($content->{'descr_'.Site::get_language( )})
					->field('textarea', __u('keywords'), 'kw_'.Site::get_language( ))->value($content->{'kw_'.Site::get_language( )})
					->field('submit', __u('save'))
					->render( );

		$this->template->header = __u('metadata');
		$this->template->body = $form;
	}

	/** save metadata to database
	 *
	 * @param Form_Result
	 * @param Site_Content
	 * @return void
	 */
	public function save_meta($data, $content)
	{
		$content->values($data->as_array( ))->save( );

		return Site::redirect($content->site_map->uri, __u('metadata has been saved').'.');
	}

	/** Action: sql based configuration management **/
	public function action_config( )
	{
		// separator of group and key
		$separator = '__';

		$form = Form::factory( )
				->return_changes(TRUE)
				->handler(array($this, 'save_config'), array($separator));

		if ( ! acl('site_config'))
		{
			throw new Access_Exception(__u('permission denied'));
		}

		$out = '';

		$config = ORM::factory('site_config') -> order_by('position') -> find_all( );

		foreach ($config AS $config_item)
		{
			$form->field('text', $config_item->label, $config_item->group_name.$separator.$config_item->config_key)->value(Site::config($config_item->group_name)->{$config_item->config_key});
		}

		$this->template->header = __u('configuration');
		$this->template->body = $form->field('submit', __u('save'))->render( );
	}

	/** save metadata to database
	 *
	 * @param Form_Result
	 * @param string			separator of group and key
	 * @return void
	 */
	public function save_config($data, $separator)
	{
		foreach ($data AS $key=>$item)
		{
			list($group, $key) = explode($separator, $key);

			Site::config($group)->set($key, $item);
		}

		return __u('data saved').'. '.'<a href="'.Route::url('site_manage', array('action' => 'config')).'">'.__('back').' '.__('to configuration').'</a>';
	}

}