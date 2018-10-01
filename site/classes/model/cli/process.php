<?php defined('SYSPATH') or die('No direct script access.');

class Model_Cli_Process extends ORM
{
	protected $_table_name = 'cli_processes';
/*
	public function get_list( )
	{
		$package_tab = $this->_table_name;
		$goods_tab = ORM::factory('goods')->table_name( );
		$query = <<<HERE
SELECT
	g.package_id,
	SUM(g.weight) AS weight_total,
	p.*
FROM
	$package_tab AS p
	JOIN
	$goods_tab AS g
	ON (g.package_id = p.id)
GROUP BY
	(g.package_id)
HERE;
		return DB::query(Database::SELECT, $query)->execute( )->as_array('package_id');
	}*/
}
