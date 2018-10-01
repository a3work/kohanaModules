<?php
if ((boolean) $_is_selected)
{
	$_classes[] = 'menu-act';
}

$class = count($_classes) > 0 ? ' class="'.implode(' ', $_classes).'"' : '';
?>
<li>
<a href='<?=$_href?>'<?=$class?><?=isset($_confirm) ? " onclick='return confirm(\"".str_replace('"', '\"', $_confirm)."\")'" : ''?>><?=$_text?></a>
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
</li>