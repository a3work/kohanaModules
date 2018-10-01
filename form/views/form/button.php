<?php

$class = implode(' ', $field->classes( ));

?>
<input class='<?=$class?>' type='button' name='<?=$field->name( )?>' value='<?=$field->header?>'<?=$field->disabled( ) ? ' readonly' : ''?>>