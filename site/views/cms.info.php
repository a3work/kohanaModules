<?php
$out = '';

foreach ($data AS $key => $value)
{
	$out .= "<div class='cms-info'><h3>$key</h3>$value</div>";
}

echo $out;
?>