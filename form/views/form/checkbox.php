<?php

$class = implode(' ', $field->classes( ));

?>
<div class='form-labels'>
<label><input type='checkbox' name='<?=$field->name( )?>' class='<?=Site::config('form')->input_class_name.$field->id( )?> <?=$class?>' value='<?=$field->value( )?>'<?=$field->disabled( ) ? ' disabled' : ''?><?=$field->selected( ) ? ' checked' : ''?>><?=$field->header( )?></label>
</div>