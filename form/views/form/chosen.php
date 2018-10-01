<?php
$placeholder = $field->placeholder != '' ? $field->placeholder : __('select options');
$class = implode(' ', $field->classes( ));
$multiple = $field->multiple( ) ? ' multiple' : '';
?>
<select class='form-chosen <?=$class?>' name='<?=$field->name( )?>' <?=$field->disabled( ) ? ' disabled' : ''?> data-placeholder="<?=$placeholder?>"<?=$multiple?>><?=$options?></select>