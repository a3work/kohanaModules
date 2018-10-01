<?php

$disabled = $field->disabled( ) ? ' readonly' : '';
$placeholder = $field->placeholder != '' ? " placeholder='{$field->placeholder}'" : '';
$class = implode(' ', $field->classes( ));

?>
<textarea class='<?=$class?>' name='<?=$field->name( )?>'<?=$disabled?><?=$placeholder?>><?=$field->value( )?></textarea>