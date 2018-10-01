<?php
if ((boolean) $_is_selected)
{
	$_classes[] = 'menu-act';
}

$class = count($_classes) > 0 ? ' class="'.implode(' ', $_classes).'"' : '';
?>
<div class='cms-submenu'>
<h3 <?=$class?>><?=$_text?></h3>
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