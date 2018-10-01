<?php
$out = '';
foreach ($path AS &$item)
{
	$out .= ($out != '' ? ' // ' : ''). $item['name'];
} unset($item);
echo $out;
?>