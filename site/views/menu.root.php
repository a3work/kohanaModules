<?php
if ((boolean) $_is_selected)
{
	$_classes[] = 'menu-act';
}

$class = count($_classes) > 0 ? ' class="'.implode(' ', $_classes).'"' : '';
?>
<div style='border:1px dashed red'>
<a href='<?=$_href?>'<?=$class?>><?=$_text?></a>
<?php
if (count($_list) > 0)
{
	$list_out = '';
	
	foreach ($_list AS $item)
	{
		$list_out .= $item->render(FALSE);
	}
	
	echo '<ul>'.$list_out.'</ul>';
}
?>
</div>