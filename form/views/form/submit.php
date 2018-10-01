<?php

$class = implode(' ', $field->classes( ));

?>
<input class='<?=$class?>' type='submit' name='<?=$field->name( )?>' value='<?=$field->header?>'<?=$field->disabled( ) ? ' readonly' : ''?>>