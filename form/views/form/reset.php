<?php

$class = implode(' ', $field->classes( ));

?>
<input class='<?=$class?>' type='reset' name='<?=$field->name( )?>' value='<?=$field->header?>'<?=$field->disabled( ) ? ' readonly' : ''?>>