<?php if (isset($href['add']) && $href['add'] != ''):?>
<a href='<?=$href['add']?>' class='cms-add' title='<?=isset($title['add']) ? $title['add'] : __('add')?>'><?=isset($text['add']) ? $text['add'] : ''?></a>
<?php endif ?>
<?php if (isset($href['edit']) && $href['edit'] != ''):?>
<a href='<?=$href['edit']?>' class='cms-edit' title='<?=isset($title['edit']) ? $title['edit'] : __('edit')?>'><?=isset($text['edit']) ? $text['edit'] : ''?></a>
<?php endif ?>
<?php if (isset($href['delete']) && $href['delete'] != ''):?>
<a href='<?=$href['delete']?>' class='cms-del' title='<?=isset($title['delete']) ? $title['delete'] : __('delete')?>'><?=isset($text['delete']) ? $text['delete'] : ''?></a>
<?php endif ?>
<?php if (isset($href['show']) && $href['show'] != ''):?>
<a href='<?=$href['show']?>' class='cms-show' title='<?=isset($title['show']) ? $title['show'] : __('show')?>'><?=isset($text['show']) ? $text['show'] : ''?></a>
<?php endif ?>
<?php if (isset($href['hide']) && $href['hide'] != ''):?>
<a href='<?=$href['hide']?>' class='cms-hide' title='<?=isset($title['hide']) ? $title['hide'] : __('hide')?>'><?=isset($text['hide']) ? $text['hide'] : ''?></a>
<?php endif ?>