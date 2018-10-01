<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Personal- and object- privileges check, common functions
 * @package 	Access
 * @author 		Stanislav U. Alkimovich
 * @date 		2013-06-20
 *
 **/

class Kohana_Access
{
	// modules list object
	protected static $modules;

	// templates list
	public static $templates = array( );
	
	// initialization flag
	public static $is_init = FALSE;

	// instance of class
	protected static $instance;

	// access cache
	protected $_cache;
	
	protected $_cache_ids = array( );
	
	/**
	 * @const string	access cache variable name
	 */
	const CACHE_VAR = 'access_cache_';
	
	/**
	 * @const string	access cache tag
	 */
	const CACHE_TAG = 'access_cache';
	


	/**
	 * Singleton factory
	 *
	 * @return object
	 */
	public static function instance( )
	{
		if ( ! isset(self::$instance))
		{
			self::$instance = new Access;
		}

		return self::$instance;
	}

	public function __destruct( )
	{
		if (Kohana::$caching)
		{
			// write changes for users 
			foreach ($this->_cache_ids AS $uid => $count)
			{
				Cache::instance( )->set_with_tags(Kohana_Access::CACHE_VAR.$uid, $this->_cache[$uid], NULL, array(Kohana_Access::CACHE_TAG));
			}
		}
	}
	
	/** Modules setter
	 *
	 * @param string		Access_Module subclass name
	 * @return void
	 */
	public static function module($param0)
	{
		if ( ! isset(self::$modules))
		{
			self::$modules = new Access_Modules_List( );
		}

		self::$modules->add($param0);
	}

	/** Module getter
	 *
	 * @param string		Access_Module subclass name
	 * @return array
	 */
	public static function module_get($param0 = NULL)
	{
		return self::$modules->get($param0);
	}

	/** Modules list getter
	 *
	 * @return Access_Modules_List
	 */
	public static function modules()
	{
		return self::$modules;
	}

	/** Access object initialization:
	 * 	- create Access_Modules instances
	 * 	- add templates to list
	 *
	 * 	@return void
	 */
	public static function init( )
	{
		if (self::$is_init)
		{
			return;
		}
	
		foreach (self::modules( ) AS $module)
		{
		}
		
		self::$is_init = TRUE;
	}

	/**
	 * Standart setter/getter
	 *
	 * @param string 	variable name
	 * @param array		parameters
	 *
	 * @return mixed
	 */
	public function __call($var, $args = NULL)
	{
		if (is_array($args) && count($args) > 0)
		{
			$this->$var = $args[0];

			return $this;
		}

		return $this->$var;
	}

	/** Check privileges of user/group or guest
	 *
	 * @param string 	privilege
	 * @param int		object id
	**/
	public function check($privilege, $obj = NULL, $user_id = NULL)
	{
		$result = (boolean) $this->get($privilege, $obj, $user_id)->result;

		return $result;
	}

	/** Search privileges, cache and return
	 *
	 * @param string 	finder
	 * @param mixed 	id
	 * @return object
	 */
	public function get($privilege, $obj = NULL, $user_id = NULL)
	{
		// get authorized users and groups
		$users = isset($user_id)
				 ? array($user_id)
				 : User::instance( )->authorized(TRUE);

        if ($obj !== NULL && $obj instanceOf File)
        {
			// get saved values from cache
			$cached = $this->cache($privilege, $obj->path( ), NULL, $users);

			if ($cached['result'] !== NULL)
			{
				return (object) $cached;
			}
			
			/* refresh cache for users, who were not found */
			$users = $cached['ids'];
			
			// get rules
			$rules = $obj->access( );
			
			// allow from root after access file creation
			if (User::is_root( ))
			{
				return 	(object) array(
							'result' => TRUE,
							'id' => Site::config('user')->root_id,
						);
			}

			// find matches rule
			$result = FALSE;
			$result_uid = NULL;
			foreach ($users AS &$uid)
			{
				if (isset($rules[$uid]) && array_search($privilege, $rules[$uid]) !== FALSE)
				{
					$result = TRUE;
					$result_uid = $uid;
					break;
				}
			} unset($uid);
            
            return (object) $this->cache(
                $privilege,
                $obj->path( ),
                (object) array(
                    'result'  => $result,
                    'user_id' => $result_uid,
                ),
                ($result_uid === NULL ? $users : $result_uid)
            );
        }
        else
        {
			// allow from root
			if (User::is_root( ))
			{
				return 	(object) array(
							'result' => TRUE,
							'id' => Site::config('user')->root_id,
						);
			}

			// check cache
			$cached = $this->cache($privilege, $obj, NULL, $users);

			if ($cached['result'] !== NULL)
			{
				return (object) $cached;
			}
			
			/* refresh cache for users, who were not found */
			$users = $cached['ids'];

			$access = ORM::factory('access_rule')
						-> where('user_id', 'IN', DB::expr('('.implode(Kohana_User::SEPARATOR, $users).')'))
						-> where('privilege', '=', $privilege);
						
			if (isset($obj))
			{
				$access->where('obj_id', '=', intval($obj));
			}
			else
			{
				$access->where('obj_id', 'IS', NULL);
			}

			if ( ! isset($user_id))
			{
				$access->order_by(DB::expr('FIELD(user_id,'.implode(Kohana_User::SEPARATOR, $users).')'));
			}
			
			$access = $access->find( );
			
			return (object) $this->cache($privilege, $obj, $access, $users);
        }
	}

	/** Load cache, get or save check result
	 *
	 *		returns hash-table {'result': <boolean result>, 'id': <integer user_id>}
	 *
	 * @param 	string				privilege
	 * @param	int					object id		:TODO: may be string?!
	 * @param 	object				
	 * @param	int					user id
	 * @return 	mixed				array on success or NULL
	 */
	protected function cache($privilege, $obj = NULL, $access = NULL, $user_id = NULL)
	{
		if ( ! isset($user_id))
		{
			$user_id = 0;
		}

		// setter mode
		if (is_object($access))
		{
			// allow if rule exists
			if ($access instanceOf Model_Access_Rule)
			{
                if ($access->loaded( ) && isset($access->id))
                {
                    $data = array(
                                'result' => TRUE,
                                'id' => $access->user_id,
                            );
                            
					$user_id = array($access->user_id);
                }
                else
                {
                    // default behavior
                    $data = array(
                                'result' => FALSE,
                                'id' => NULL
                            );
				}
			}
			else
			{
				if ($access->result === TRUE)
				{
					$user_id = array($access->user_id);
				}
				
				$data = array(
                            'result' => $access->result,
                            'id' => $access->user_id,
                        );
				
			}
			
			foreach ($user_id AS &$uid)
			{
				$uid = (int) $uid;
			
				// check cache
				if ( ! isset($this->_cache[$uid]))
				{
					$cached = Cache::instance( )->get(Kohana_Access::CACHE_VAR.$uid);
					
					if ($cached !== NULL)
					{
						$this->_cache[$uid] = $cached;
					}
					else
					{
						$this->_cache[$uid] = array( );
					}
				}
				

				if (isset($obj))
				{
					if ( ! isset($this->_cache[$uid][$privilege]))
					{
						$this->_cache[$uid][$privilege] = array( );
					}
					
					$this->_cache[$uid][$privilege][$obj] = $data;

					$result = $this->_cache[$uid][$privilege][$obj];
				}
				else
				{
					if ( ! isset($this->_cache[$uid][$privilege]))
					{
						$this->_cache[$uid][$privilege] = NULL;
					}
					
					$this->_cache[$uid][$privilege] = $data;

					$result = $this->_cache[$uid][$privilege];
				}
				
				if ( ! isset($this->_cache_ids[$uid]))
				{
					$this->_cache_ids[$uid] = 1;
				}
				else
				{
					$this->_cache_ids[$uid] ++;
				}
				
			} unset($uid);
			
			return $result;
		}
		else
		// getter mode
		{
			$uid_for_check = array( );
		
			foreach ($user_id AS &$uid)
			{
				$uid = (int) $uid;
				
				// check cache
				if ( ! isset($this->_cache[$uid]))
				{
					$cached = Cache::instance( )->get(Kohana_Access::CACHE_VAR.$uid);
					
					if ($cached !== NULL)
					{
						$this->_cache[$uid] = $cached;
					}
					else
					{
						$this->_cache[$uid] = array( );
					}
				}
			
				if (isset($obj))
				{
					if (isset($this->_cache[$uid][$privilege]) && isset($this->_cache[$uid][$privilege][$obj]))
					{
						$cached = $this->_cache[$uid][$privilege][$obj];
						
						if ($cached['result'] === TRUE)
						{
							return $cached;
						}
					}
					else
					{
						$uid_for_check[] = $uid;
					}
				}
				else
				{
					if (isset($this->_cache[$uid][$privilege]))
					{
						$cached = $this->_cache[$uid][$privilege];
						
						if ($cached['result'] === TRUE)
						{
							return $cached;
						}
					}
					else
					{
						$uid_for_check[] = $uid;
					}
				}
			} unset($uid);

			if (count($uid_for_check) > 0)
			{
				return array(
					'result'=> NULL,
					'ids'	=> $uid_for_check,
				);
			}
			else
			{
				return array(
					'result' => FALSE,
					'id'	 => NULL,
				);
			}
		}
	}

	/** Add defaults access rules for user according to specified access templates (only for non-object privileges)
	 * 	Merge privileges of many access templates
	 *
	 * @param integer	user id
	 * @param integer	object id
	 * @param mixed		non-object privileges: template name or list of names / object privileges: module name
	 */
	public function defaults($user_id, $obj = NULL, $templates = NULL)
	{
		// Access modules initialization
		Access::init( );
	
		// non-object privileges
		if ( ! isset($obj))
		{
			$out = array_keys(Access_Module::privileges_def( ));

			if (isset($templates))
			{
				if ( ! is_array($templates))
				{
					$templates = array($templates);
				}

				foreach ($templates AS $template)
				{
					$out = array_merge($out, array_keys(Access::$templates[$template]->privileges( )));
				}
			}
			
			// clear personal privileges of user
			ORM::factory('Access_Rule')->clear_rules($user_id);

			// save new values
			ORM::factory('Access_Rule')->save_rules($user_id, $out);
		}
		else
		{
			if (isset($templates))
			{
				// get default privileges of specified module
				$out = array_keys(Access::instance( )->module_get($templates)->privileges_obj_def( ));

				// set up default privileges if exists
				if (count($out) > 0)
				{
					// clear object privileges of user:
					// remove object privileges of current access module only
					ORM::factory('Access_Rule')->clear_rules($user_id, $out, $obj);

					// save new object privileges of user
					// create defaults of current access module
					ORM::factory('Access_Rule')->save_rules($user_id, $out, $obj);
				}
			}
			else
			{
				throw new Access_Process_Exception("Don't set module name.");
			}
		}
	}

	/** :TODO: **/
	/** Copy access rules of parent object for specified
	 *
	 * @param 	int		parent object id
	 * @param 	int		object id
	 * @param 	mixed	module name
	 * @return 	void
	 */
	public function copy($src, $dst, $module)
	{
		// get privileges list
		$out = array_keys(Access::instance( )->module_get($module)->privileges_obj( ));

		// copy privileges
		ORM::factory('Access_Rule')->copy($src, $dst, $out);
	}

	/** 
	 * Clear access cache
	 *
	 * @return 	void
	 */
	public function clear_cache()
	{
		Cache::instance()->delete_tag(Kohana_Access::CACHE_TAG);
	}
	
	/** Merge and return template privileges
	 *
	 * @return array
	 */
}

if ( ! function_exists('acl'))
{
	/** Check global or object privileges of user
	 *	Shortcut for Access::instance( )->check( )
	 *
	 * :TODO: expressions processing
	 *
	 * @param 	string 		privilege
	 * @param 	int			object id
	 * @param 	int			user id
	 * @return 	boolean
	**/
	function acl($privilege, $obj = NULL, $user_id = NULL)
	{
		return Access::instance( )->check($privilege, $obj, $user_id);
	}
}
