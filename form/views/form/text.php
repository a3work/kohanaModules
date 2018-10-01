<?php
$input_length = '';

if ($field->length( ) != 0)
{
	$input_length = " maxlength='".$field->length( )."'";
}

$disabled = $field->disabled( ) ? ' readonly' : '';
$placeholder = $field->placeholder != '' ? " placeholder='{$field->placeholder}'" : '';
$class = implode(' ', $field->classes( ));

/* set up unit */
$unit = $field->unit( );
$unit = ($unit !== NULL)
		? (
			strip_tags($unit) != $unit
			? $unit
			: "<span class='form-unit'>$unit</span>"
		  )
		: '';

?>
<input type='text' name='<?=$field->name( )?>' class='<?=$class?>' value='<?=$field->value_encoded( )?>'<?=$disabled?><?=$input_length?><?=$placeholder?>><?=$unit?>