<?php defined('SYSPATH') or die('No direct script access.');

/**
 *
 * @name		Interface of module with objected privileges
 * @package 	Access
 * @author 		Stanislav U. Alkimovich
 * @date 		2013-08-12
 *
 **/

interface Access_Objected_Module
{
	/** Remove privileges of daughter objects regarding specified
	 *  Must can to remove all privileges and privileges of specified user only
	 *
	 * @param 	integer		parent object id
	 * @param 	integer 	user id
	 * @return 	void
	 */
	public function clear_children($obj_id, $user_id = NULL);

	/** Add privileges of daughter objects regarding specified for selected user
	 *
	 * @param 	integer		parent object id
	 * @param 	integer 	user id
	 * @return 	void
	 */
	public function add_for_children($obj_id, $user_id);
}