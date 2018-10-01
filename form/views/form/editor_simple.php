<?php
$textarea_id = 'ta'.mt_rand(0,99);

$options = Basic::json_safe_encode($field->settings( ));
$js = <<<HERE
CKE.simple("{$textarea_id}", $options);
HERE;

InclStream::instance( )->add('editor/ckeditor.js');
InclStream::instance( )->add('form.cke.js');
InclStream::instance( )->write($js);

$disabled = $field->disabled( ) ? ' readonly' : '';
$placeholder = $field->placeholder != '' ? " placeholder='{$field->placeholder}'" : '';
$class = implode(' ', $field->classes( ));

?>
<textarea class='<?=$class?>' id='<?=$textarea_id?>' name='<?=$field->name( )?>' <?=$disabled?><?=$placeholder?>><?=$field->value( )?></textarea>