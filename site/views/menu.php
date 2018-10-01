<?
$out = '';
foreach ($menu AS $menu_item)
{
	$children = '';
	if (isset($menu_item['children']))
	{
		foreach ($menu_item['children'] AS $child)
		{
			$children .= <<<HERE
<li><a href="{$child['href']}" class="dashed">{$child['name']}</a></li>
HERE;
		}
		$children = '<ul>'. $children .'</ul>';
	}
	$class = $menu_item['class'] != '' ? ' '.$menu_item['class'] : '';
	$out .= <<<HERE
<a class='menu{$class}' href="{$menu_item['href']}">{$menu_item['name']}</a>{$children}
HERE;
}
?>
<?=$out?>