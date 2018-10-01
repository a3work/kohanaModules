<?php
$count = 0;
$total = count($contents) - 1;

$out = '';
foreach ($contents AS $item)
{
	if ($count < $total)
	{
		$out .= "<a href='{$item['href']}'>{$item['name']}</a> &rsaquo; ";
	}
	else
	{
		$out .= "{$item['name']}";
	}
	
	$count ++;
}

echo $out;