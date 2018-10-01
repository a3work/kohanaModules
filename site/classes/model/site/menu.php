<?php defined('SYSPATH') or die('No direct script access.');

Class Model_Site_Menu extends ORM
{
	protected $_table_name = 'site_menu';

	protected $_has_many = array(
		'site_menu_item'		=> array ('model' => 'site_menu_item', 'foreign_key' => 'menu_id'),
	);

	public function data($id, $parent)
	{
		$lang = Site::get_language( );

		$query_text = <<<HERE
SELECT
	sm1 . * ,
	COUNT(sm2.id) AS children_count,
	IF (sm1.href != '', sm1.href, map.uri) AS href,
	c.`{$lang}` AS header,
	map.is_index

FROM
	`site_menu_items` AS sm1

	LEFT OUTER JOIN
	`site_menu_items` AS sm2
	ON ( sm2.parent = sm1.id )

	LEFT OUTER JOIN
	`site_map` AS map
	ON (sm1.map_id = map.id)

	JOIN
	`site_contents` AS c
	ON (c.id = sm1.contents_id)

WHERE
	sm1.menu_id = '$id'
	AND
	sm1.parent = '$parent'

GROUP BY
	sm1.id

ORDER BY
	sm1.position
HERE;

		return DB::query(Database::SELECT, $query_text)->as_object('Menu_Item')->execute( );
	}
}
?>