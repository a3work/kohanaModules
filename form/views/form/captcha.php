<?php
$input_length = '';

if ($field->length( ) != 0)
{
	$input_length = " maxlength='".$field->length( )."'";
}

$disabled 			= $field->disabled( ) ? ' readonly' : '';
$placeholder 		= $field->placeholder != '' ? " placeholder='{$field->placeholder}'" : '';
$captcha_width 		= Site::config('captcha')->width;
$captcha_height 	= Site::config('captcha')->height;
$captcha_src 		= Route::url('captcha', array('id'=>mt_rand(100,999)));
$class 				= implode(' ', $field->classes( ));

?>
<div class='form-captcha <?=$class?>'><div class='captcha-img'><img src='<?=$captcha_src?>' width='<?=$captcha_width?>' height='<?=$captcha_height?>' border=0><a class='captcha-refresh'><?=__('refresh')?></a></div>&nbsp;=&nbsp;<input type='text' name='<?=$field->name( )?>' value='<?=$field->value_encoded( )?>' <?=$disabled?><?=$input_length?><?=$placeholder?>></div>
