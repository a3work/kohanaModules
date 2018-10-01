<?php
$disabled = $field->disabled( ) ? ' readonly' : '';
$placeholder = $field->placeholder != '' ? " placeholder='{$field->placeholder}'" : '';
$class = implode(' ', $field->classes( ));

?>
<input class='<?=$class?>' type='datetime' name='<?=$field->name( )?>' value='<?=$field->value_encoded( )?>'<?=$disabled?><?=$placeholder?>>