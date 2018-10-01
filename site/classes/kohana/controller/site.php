<?php defined('SYSPATH') or die('No direct script access.');

class Kohana_Controller_Site extends Controller_Cms
{
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