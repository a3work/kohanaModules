<?php

$format = $field->format( );
$disabled = $field->disabled( ) ? ' readonly' : '';
$placeholder = $field->placeholder != '' ? " placeholder='{$field->placeholder}'" : '';
$class = implode(' ', $field->classes( ));

?>
<input class='form-date <?=$class?>' type='text' title='<?=$format?>' name='<?=$field->name( )?>' value='<?=$field->value_encoded( )?>'<?=$disabled?><?=$placeholder?>>