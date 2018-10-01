<?php

$class = implode(' ', $field->classes( ));

?>
<label><input type='radio' name='<?=$field->name( )?>' class='<?=Site::config('form')->input_class_name.$field->id( ).' '.$class?>' value='<?=$value?>'<?=$field->disabled( ) ? ' readonly' : ''?><?=$selected ? 'checked' : ''?>><?=$header?></label>