<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Access rules management
 * @package 	Access
 * @author 		Stanislav U. Alkimovich
 * @date 		2013-06-20
 *
 **/

class Kohana_Controller_Cms_Access extends Controller_Cms
{
	const PREV_USER = 'acc_prev_user';

	// object privileges form
	protected $form;

	// object access options
	protected $obj_id;
	protected $access_module;

	public function before( )
	{
		parent::before( );

		$class = $this->request->param('class');
		$this->obj_id = $this->request->param('obj_id');

		if (isset($class) && $class > '')
		{
			$class = "access_$class";
			$this->access_module = Access::module_get($class);
		}

	}

	/**  Edit user/group privileges
	 */
	public function action_user()
	{
		if ( ! acl('user_access'))
		{
			throw new Access_Exception('Permission denied');
		}

		$user_id = $this->request->param('user_id');

		// get current privileges of specified user / group
		$current = ORM::factory('access_rule')
						-> where('user_id', '=', $user_id)
						-> where('obj_id', 'IS', NULL)
						-> find_all( );

		$current_values = array( );

		foreach ($current AS $rule)
		{
			$current_values[] = $rule->privilege;
		}

		$out = array( );

		// get access module
		foreach (Access::modules( ) AS $module)
		{
			// add privileges of current module
			foreach ($module->privileges( ) AS $privilege)
			{
				// skip object privileges
				if ($privilege->objected( ))
				{
					continue;
				}

				if ( ! isset($out[$module->name( )]))
				{
					$out[$module->name( )] = array( );
				}

				// add privilege to list
				// - only root can add hidden privilege

				if ( ! $privilege->hidden( ))
				{
					$out[$module->name( )][$privilege->name( )] = $privilege->label( );
				}
				elseif (User::check(Site::config('user')->root_name))
				{
					$out[$module->name( )][$privilege->name( )] = View::factory('object.access.hidden.priv', array('text' => $privilege->label( )))->render( );
				}
			}
		}

		// get form
		$form = Form::factory( )
					-> class('data')
					-> clear_on_success(FALSE)
					-> message('Данные сохранены')
					-> field('checkbox_group', __u('privileges'), 'priv')->value($current_values)->options($out)
					-> field('submit', __u('save'))
					-> render( );

		$this->template->body = $form;

		if ($form->sent( ))
		{
			// clear personal privileges of user
			ORM::factory('Access_Rule')->clear_rules($user_id);

			// save new values
			ORM::factory('Access_Rule')->save_rules($user_id, $form->priv);
			
			// clear cache
			Access::instance()->clear_cache();
		}
	}

	/**  Edit permissions to manage specified object parameters
	 */
	public function action_obj( )
	{
		// Get privileges of current class
		$privileges = '"'.implode('","', array_keys($this->access_module->privileges_obj( ))).'"';

		if ($privileges == '""')
		{
			throw new Access_Process_Exception('Class ":class" hasn\'t object privileges.', array(':class' => $class));
		}

		$add_form = Form::factory('add')
					-> message(__u('permissions has been added'))
					-> handler(array($this, 'save_permissions_obj'));

		$users = ORM::factory('account')
					->select(array('id', Site::config('form')->db_opt_key))
					->select(array(
								DB::expr('CONCAT(IF(is_group = 1, "['.__('group').'] ", "['.__('account').'] "), username)'),
								Site::config('form')->db_opt_header,
							))
					->order_by('is_group')
					->order_by('username')
					->where('username', '!=', Site::config('user')->root_name)
					->find_all( );

		$form = View::factory('object.access.tab.line', array(
			'user' 		=> 	$add_form->field('select', NULL, 'user')->options($users),
			'actions' 	=> 	$add_form->field('checkbox_group', NULL, 'priv')->not_empty(__u('set privileges please'))->view_opt('object.access.checkbox')->options($this->access_module->privileges_obj( )),
			'extra'		=> 	$add_form->field('submit', 'add', 'add')->is_private(FALSE).
							($this->access_module instanceof Access_Objected_Module ? $add_form->field('submit', 'adda', 'adda')->is_private(FALSE) : ''),
		));

		if (Session::instance( )->get(Kohana_Controller_Cms_Access::PREV_USER) !== NULL)
		{
			$add_form->fields('user')->value(Session::instance( )->get(Kohana_Controller_Cms_Access::PREV_USER));
		}

		$add_form->render($form->render( ));

		// load data where:
		// - current privileges
		// - current obj id
		$current = ORM::factory('Access_Rule')->get_list($this->obj_id, $privileges);

		$current_user = NULL;
		$data = array( );
		$out = '';

		foreach ($current AS $line)
		{
			if ($line['user_id'] != $current_user)
			{
				if (isset($current_user))
				{
					$out .= $this->obj_line($current_user, $data[$current_user]['user'], $data[$current_user]['privileges'], $this->access_module);
				}

				$current_user = $line['user_id'];
			}

			if ( ! isset($data[$current_user]))
			{
				$data[$current_user] = array(
					'user' => $line['username'],
					'privileges' => array( ),
				);
			}

			$data[$current_user]['privileges'][$line['privilege']] = $line['privilege'];
		}

		if (isset($current_user))
		{
			$out .= $this->obj_line($current_user, $data[$current_user]['user'], $data[$current_user]['privileges'], $this->access_module);
		}

		if (isset($this->form))
		{
			$this->form->render($out);
			$out = $this->form;
		}

		$out = View::factory('object.access.tab', array(
			'body' => $out.$add_form
		));

		$this->template->body = $out->render( );
	}

	/** Save access rules of object
	 *
	 * @param Form_Result
	 * @return void
	 */
	public function save_permissions_obj($data)
	{
		if (ORM::factory('account', $data->user)->loaded( ))
		{
			// set current user as default for next addition
			Session::instance( )->set(Kohana_Controller_Cms_Access::PREV_USER, $data->user);

			// clear personal privileges of user
			ORM::factory('Access_Rule')->clear_rules($data->user, array_keys($this->access_module->privileges_obj( )), $this->obj_id);

			// save new values
			ORM::factory('Access_Rule')->save_rules($data->user, $data->priv, $this->obj_id);

			// write permissions of daughter objects
			if (isset($data->adda))
			{
				if ($this->access_module instanceof Access_Objected_Module)
				{
					$this->access_module->clear_children($this->obj_id, $data->user);
					$this->access_module->add_for_children($this->obj_id, $data->user);
				}
				else
				{
					throw new Access_Process_Exception('Cannot use group access rules for non-hierarhic structure of objects in class :class', array(':class' => get_class($this->access_module)));
				}
			}
		}
	}

	/** Save changed permissions
	 *
	 * @param Form_Result
	 * @return void
	 */
	public function save_changed($data)
	{
		$deleted = NULL;

		foreach ($data AS $key=>$item)
		{
			// save permissions of current object if don't delete
			if (preg_match('/priv_(\d+)/', $key, $matches) && $matches[1] != $deleted)
			{
				// clear personal privileges of user
				ORM::factory('Access_Rule')->clear_rules($matches[1], array_keys($this->access_module->privileges_obj( )), $this->obj_id);

				// save new values
				ORM::factory('Access_Rule')->save_rules($matches[1], $item, $this->obj_id);

				continue;
			}

			if (preg_match('/del_(\d+)/', $key, $matches))
			{
				$action = 0;
				$id = $matches[1];
			}
			// :TODO: delete permissions of daughter objects
			elseif (preg_match('/dela_(\d+)/', $key, $matches))
			{
				$action = 1;
				$id = $matches[1];
			}
			// :TODO: save permissions of daughter objects
			elseif (preg_match('/upda_(\d+)/', $key, $matches))
			{
				$action = 2;
				$id = $matches[1];
			}
		}


		if (isset($action))
		{
			// execute command according to submit button
			switch ($action)
			{
				case 0:
					// clear personal privileges of user
					ORM::factory('Access_Rule')->clear_rules($matches[1], array_keys($this->access_module->privileges_obj( )), $this->obj_id);

					break;
				case 1:
					// group access rules can use for hierarchic object structure only
					if ($this->access_module instanceof Access_Objected_Module)
					{
						$this->access_module->clear_children($this->obj_id, $id);
						ORM::factory('Access_Rule')->clear_rules($matches[1], array_keys($this->access_module->privileges_obj( )), $this->obj_id);
					}
					else
					{
						throw new Access_Process_Exception('Cannot use group access rules for non-hierarhic structure of objects in class :class', array(':class' => get_class($this->access_module)));
					}

					break;
				case 2:
					// group data can use for hierarchic object structure only
					if ($this->access_module instanceof Access_Objected_Module)
					{
						$this->access_module->clear_children($this->obj_id, $id);
						$this->access_module->add_for_children($this->obj_id, $id);
					}
					else
					{
						throw new Access_Process_Exception('Cannot use group access rules for non-hierarhic structure of objects in class :class', array(':class' => get_class($this->access_module)));
					}

					break;
			}
		}

	}

	/** Get object permissions change interface line
	 *
	 * @param string 	field name
	 * @param string 	user
	 * @param string	privileges
	 * @return string
	 */
	protected function obj_line($key, $user, $privileges)
	{
		$privileges_list = $this->access_module->privileges_obj( );
		$is_hierarchical = ($this->access_module instanceof Access_Objected_Module);

		if ( ! isset($this->form))
		{
			$this->form = Form::factory('edit')->message(__u('permissions has been saved'))->clear_on_success(FALSE)->return_changes(TRUE)->handler(array($this, 'save_changed'));
		}

		$out = View::factory('object.access.tab.line', array(
			'user' 		=> $user,
			'actions' 	=> $this->form->field('checkbox_group', NULL, 'priv_'.$key)->value($privileges)->options($privileges_list)->view_opt('object.access.checkbox'),
			'extra'		=> 	$this->form->field('submit', 'upd', 'upd_'.$key)->is_private(FALSE).
							($is_hierarchical ? $this->form->field('submit', 'upda', 'upda_'.$key)->is_private(FALSE) : '').
							$this->form->field('submit', 'del', 'del_'.$key)->is_private(FALSE).
							($is_hierarchical ? $this->form->field('submit', 'dela', 'dela_'.$key)->is_private(FALSE) : ''),
		));

		return $out->render( );
	}
}