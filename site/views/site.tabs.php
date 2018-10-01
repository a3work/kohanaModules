<?php
$menu = $out = '';

foreach ($data AS $tname => $tbody)
{
	if ($tbody != '')
	{
		$menu .= <<<HERE
<a href='#{$tname}' class='dashed'>{$tname}</a>
HERE;
	}
	
	$out .= <<<HERE
<div class='tbs-item {$class}'>{$tbody}</div>
HERE;

}

?>
<div class='tbs-menu <?=$class?>'><?=$menu?></div>
<div class='tbs <?=$class?>'><?=$out?></div>