<?php

$out = '';

foreach($items AS $item)
{
	$out .= ($out != '' ? '&nbsp;&rang;&nbsp;' : '').$item;
}

echo $out;