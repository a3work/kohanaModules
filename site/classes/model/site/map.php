<?php defined('SYSPATH') or die('No direct script access.');
Class Model_Site_Map extends ORM
{
	protected $insert_limit = 3000;
	protected $_table_name = 'site_map';

	protected $_has_many = array(
		'children' 			=> array('model' => 'site_map', 'foreign_key' => 'parent', 'far_key' => 'id'),
		'site_contents' 	=> array('model' => 'site_contents','foreign_key' => 'map_id'),
		'site_menu_items'	=> array('model' => 'site_menu_items', 'foreign_key' => 'map_id'),
		'personal_rights'	=> array('model' => 'personal_rights', 'foreign_key' => 'map_id', 'far_key' => 'rights_id', 'through' => 'personal_rights_map'),
	);

	protected $_belongs_to = array(
		'personal_user' 	=> array ('model' => 'personal_user','foreign_key' => 'owner_id'),
	);

	public function delete_rights( )
	{
		if ($this->_loaded)
		{
// 			// скидываем кэш правил
// 			$query_text = <<<HERE
// 				SELECT
// 					rights_id
//
// 				FROM
// 					`personal_rights_map`
//
// 				WHERE
// 					`map_id` = {$this->id}
// HERE;
//
// 			// удаляем все правила, затрагиваемые при изменении ролей
// 			foreach (DB::query(Database::SELECT, $query_text)->execute( )->as_array( ) AS $rights)
// 			{
// 				ORM::factory('personal_rights', $rights['id'])->delete( );
// 			}
		}
	}

	public function data($mode, $hash, $label, $type, $options = NULL)
	{
		$lang = Site::get_language( );

		$select_part = <<<HERE
c.`{$lang}` AS 'header',
c.`body_{$lang}` AS 'body',
c.`side_{$lang}` AS 'side',
c.`created`,
c.`title_{$lang}` AS 'title',
c.`descr_{$lang}` AS 'descr',
c.`kw_{$lang}` AS 'kw'
HERE;

		$where_part = $order_part = $offset_part = $limit_part = '';

		// LIMIT
		if (isset($label) && $label != '')
		{
			$where_part .= <<<HERE
 AND c.`label` = '{$label}'
HERE;
		}

		// TYPE
		if (isset($type))
		{
			switch ($type)
			{
				case 'header':
					$select_part = <<<HERE
c.`{$lang}`
HERE;
					break;
				case 'body':
					$select_part = <<<HERE
c.`body_{$lang}` AS 'body'
HERE;
					break;
				case 'side':
					$select_part = <<<HERE
c.`side_{$lang}` AS 'side'
HERE;
					break;
				case 'created':
					$select_part = <<<HERE
c.`created` AS 'created'
HERE;
					break;
				case 'descr':
					$select_part = <<<HERE
c.descr_{$lang}` AS 'descr'
HERE;
					break;
				case 'kwords':
					$select_part = <<<HERE
c.kw_{$lang}` AS 'kw'
HERE;
					break;
				case 'title':
					$select_part = <<<HERE
c.`title_{$lang}` AS 'title'
HERE;
					break;
				case 'meta':
					$select_part = <<<HERE
c.`title_{$lang}` AS 'title',
c.`descr_{$lang}` AS 'descr',
c.`kw_{$lang}` AS 'kw'
HERE;
					break;
			}
		}

		// обработка опций
		if ($mode == Content::$_MODE_CHILDREN && isset($options))
		{
// 			// WHERE
			if (isset($options['where']))
			{
				$and_where = array( );
				
				foreach ($options['where'] AS $condition)
				{
					$and_where[] = "$condition[0] $condition[1] \"$condition[2]\"";
				}
				
				$where_part .= ' AND ('.implode(' AND ', $and_where).')';
			}
			
			if (isset($options['or_where']))
			{
				$or_where = array( );
			
				foreach ($options['or_where'] AS $condition)
				{
					$or_where[] = "$condition[0] $condition[1] \"$condition[2]\"";
				}
				
				$where_part .= ' AND ('.implode(' OR ', $or_where).')';
			}
			
			// ORDER BY
			if (isset($options['order_by']) && is_array($options['order_by']))
			{
				if (isset($options['order_by'][1]))
				{
					$order_part = <<<HERE
ORDER BY {$options['order_by'][0]} {$options['order_by'][1]}
HERE;
				}
				else
				{
					$order_part = <<<HERE
ORDER BY {$options['order_by'][0]}
HERE;
				}
			}

			// LIMIT
			if (isset($options['limit']))
			{
				$options['limit'] = (integer) $options['limit'];
				$limit_part = <<<HERE
LIMIT {$options['limit']}
HERE;
			}

			// OFFSET
			if (isset($options['offset']))
			{
				$options['offset'] = (integer) $options['offset'];
				$offset_part = <<<HERE
OFFSET {$options['offset']}
HERE;
			}
		}

		// authorized users
		$auth = User::instance()->authorized( );

		$auth_where = $auth_join = '';
		if ( ! User::check(Site::config('user')->root_name))
		{
			$auth_join = <<<HERE
JOIN
access_rules AS a
ON (a.obj_id = sm0.id)
HERE;
			$auth_where = <<<HERE
AND a.privilege = 'page_view'
AND	a.user_id IN ($auth)
HERE;
		}

		if ($mode == Content::$_MODE_CHILDREN)
		{

			$query_text = <<<HERE
			SELECT
				c.id AS 'id',
				sm0.id AS 'map_id',
				sm0.id AS 'parent_uri',
				sm1.uri AS 'uri',
				sm1.template AS 'view',
				sm1.`address`,
				sm1.`is_index`,
				{$select_part}
			FROM
				site_map AS sm0
				JOIN
				site_map AS sm1
				ON (sm0.`id` = sm1.`parent`)
				JOIN
				site_contents AS c
				ON (c.map_id = sm1.id)
				$auth_join
			WHERE
				sm1.`is_deleted` = 0
				{$auth_where}
				AND
				sm0.`uri_hash` = '$hash'
				{$where_part}
				{$order_part}
				{$limit_part}
				{$offset_part}
HERE;

			$result = DB::query(Database::SELECT, $query_text)->as_object('Content_Item')->execute( );

			if ($result->count( ) == 0)
			{
				$result = NULL;
			}
		}
		else
		{
			$query_text = <<<HERE
			SELECT
				c.id AS 'id',
				sm0.id AS 'map_id',
				sm0.template AS 'view',
				sm0.`address`,
				sm0.`is_index`,
				{$select_part}
			FROM
				site_map AS sm0
				JOIN
				site_contents AS c
				ON (c.map_id = sm0.id)
				$auth_join
			WHERE
				sm0.`is_deleted` = 0
				{$auth_where}
				AND
				sm0.`uri_hash` = '$hash'
				{$where_part}
			LIMIT 1
HERE;

			$result = DB::query(Database::SELECT, $query_text)->as_object('Content_Item')->execute( )->as_array( );
			$result = count($result) > 0 ? $result[0] : NULL;
		}

		return $result;
	}

	/** Remove access permissions to daughter objects of specified user or all users
	 *
	 * @param integer 		user id
	 * @return Database_Result
	 */
	public function delete_child_access($privileges, $user_id = NULL)
	{
		// query will be executed for loaded site_map only -- need site_map id
		if ( ! $this->_loaded)
		{
			return;
		}

		// concat privileges for remove rules of specified privileges only
		$privileges = '"'.implode('","', $privileges).'"';

		// get access table name
		$access_table = ORM::factory('access_rule')->table_name( );

		$delete_text = <<<HERE
DELETE
	a.*
FROM
	{$this->_table_name} AS m
	JOIN
	$access_table AS a
	ON
	(a.obj_id = m.id)
WHERE
	m.address LIKE '{$this->address}%'
	AND
	m.id != {$this->id}
	AND
	a.privilege IN ($privileges)
HERE;

		// add specified user for exclude rules of other users
		if (isset($user_id))
		{
			$delete_text .= <<<HERE
 AND
a.user_id = {$user_id}
HERE;
		}

		return DB::query(Database::DELETE, $delete_text)->execute( );
	}

	/** Add access permissions to daughter objects
	 *
	 * @param array		permissions
	 * @param integer	user id
	 * @return Database_Result
	 */
	public function add_child_access($privileges, $user_id)
	{
		if ( ! $this->_loaded)
		{
			return;
		}

		// get access table name
		$access_table = ORM::factory('access_rule')->table_name( );

		// available privileges of current module
		$privileges = '"'.implode('","', $privileges).'"';

		// get privileges of current object for specified user
		$privileges_query = <<<HERE
SELECT
	privilege
FROM
	$access_table
WHERE
	user_id = $user_id
	AND
	privilege IN ($privileges)
	AND
	obj_id = {$this->id}
HERE;

		// get privileges rules
		$privileges = DB::query(Database::SELECT, $privileges_query)->execute( );
		$query_item = '';
		foreach ($privileges AS $rule)
		{
			$query_item .= <<<HERE
('$user_id', '{$rule['privilege']}', '***'),
HERE;
		}

		$query_item = trim($query_item, ',');

		$select_text = <<<HERE
SELECT
	id
FROM
	{$this->_table_name}
WHERE
	address LIKE '{$this->address}%'
	AND
	id != {$this->id}
ORDER BY
	id
HERE;

		$result = DB::query(Database::SELECT, $select_text)->execute( );

		$insert_body = '';
		foreach ($result AS $item)
		{
			$insert_body .= str_replace('***', $item['id'], $query_item).',';
		}

		if ($insert_body != '')
		{
			$insert_text = <<<HERE
INSERT
INTO
	{$access_table} (`user_id`, `privilege`, `obj_id`)
VALUES
HERE;

			$insert_text .= trim($insert_body, ',');


			return DB::query(Database::INSERT, $insert_text)->execute( );
		}

		return;
	}
}
