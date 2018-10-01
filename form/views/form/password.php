<?php
$input_length = '';

if ($field->length( ) != 0)
{
	$input_length = " maxlength='".$field->length( )."'";
}

$disabled = $field->disabled( ) ? ' readonly' : '';
$placeholder = $field->placeholder != '' ? " placeholder='{$field->placeholder}'" : '';
$class = implode(' ', $field->classes( ));

?>
<input class='<?=$class?>' type='password' name='<?=$field->name( )?>' value='<?=$field->value_encoded( )?>'<?=$disabled?><?=$input_length?><?=$placeholder?>>