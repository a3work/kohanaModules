<?php defined('SYSPATH') or die('No direct script access.');
Class Model_Access_Rule extends ORM
{
	/** Get access list for current obj_id with specified privileges only
	 *
	 * @param integer 	object id
	 * @param string	comma separated labels of privileges
	 * @return Database_Result
	 */
	public function get_list($obj_id, $privileges)
	{
		$users_tab = ORM::factory('account')->table_name( );

		$query = <<<HERE
SELECT
	r.*,
	a.username,
	(a.password = '') AS is_group
FROM
	{$this->_table_name} AS r
	JOIN
	$users_tab AS a
	ON (r.user_id = a.id)
WHERE
	r.obj_id = $obj_id
	AND
	privilege IN ($privileges)
ORDER BY
	is_group,
	a.username
HERE;

		return DB::query(Database::SELECT, $query)->execute( );
	}

	/** Delete all rule of specified user / group
	 *
	 * @param integer	user id
	 * @param mixed		privilege or array of privileges
	 * @param integer	object id
	 * @return Database_Result
	 */
	public function clear_rules($user_id = NULL, $rules = NULL, $obj_id = NULL)
	{
		$where = '';

		if (isset($user_id))
		{
			$where .= "user_id = '$user_id'";
		}

		$where_obj = (isset($obj_id)) ? "obj_id = '$obj_id'" : 'obj_id IS NULL';
		$where .= ($where != '' ? ' AND ' : '').$where_obj;

		$where_rules = '';
		if (isset($rules))
		{
			if (is_array($rules) AND count($rules) > 0)
			{
				$where_rules = 'privilege IN ("'.implode('","', $rules).'")';
			}
			elseif ( ! is_array($rules))
			{
				$where_rules = "privilege = \"$rules\"";
			}


			$where .= ($where != '' ? ' AND ' : '').$where_rules;
		}

		$query = <<<HERE
DELETE
FROM
	{$this->_table_name}
WHERE
	$where
HERE;

		return DB::query(Database::DELETE, $query)->execute( );
	}


	/** save privileges of user / group
	 *
	 * @param integer	user id
	 * @param array		array of privileges
	 * @param integer	object id
	 * @return mixed
	 */
	public function save_rules($user_id, $rules, $obj_id = NULL)
	{
		if (count($rules) > 0)
		{
			if (isset($obj_id))
			{
				$query = <<<HERE
INSERT
INTO
	{$this->_table_name} (`user_id`, `privilege`, `obj_id`)
VALUES
HERE;
			}
			else
			{
				$query = <<<HERE
INSERT
INTO
	{$this->_table_name} (`user_id`, `privilege`)
VALUES
HERE;
			}

			$query_body = '';
			foreach ($rules AS $rule)
			{
				if (isset($obj_id))
				{
					$query_body .= <<<HERE
('$user_id', '$rule', '$obj_id'),
HERE;
				}
				else
				{
					$query_body .= <<<HERE
('$user_id', '$rule'),
HERE;
				}
			}

			$query_body = trim($query_body, ',');

			return DB::query(Database::INSERT, $query.$query_body)->execute( );
		}

		return FALSE;
	}

	/** Copy rules of @source object to @destination
	 *
	 * @param 	integer		source object id
	 * @param	integer		destination object
	 * @param	array		privileges
	 * @return 	Database_Result
	 */
	public function copy($src, $dst, $privileges)
	{
		$privileges = '"'.implode('","', $privileges).'"';

		$query = <<<HERE
INSERT
INTO
	{$this->_table_name} (`user_id`, `privilege`, `obj_id`)
SELECT
	`user_id`,
	`privilege`,
	'$dst' AS 'obj_id'
FROM
	{$this->_table_name}
WHERE
	`obj_id` = '$src'
	AND
	`privilege` IN ($privileges)
HERE;

		return DB::query(Database::INSERT, $query)->execute( );
	}
}
