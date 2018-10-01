<?php

$disabled = $field->disabled( ) ? ' readonly' : '';
$class = implode(' ', $field->classes( ));

?>
<input class='<?=$class?>' type='hidden' name='<?=$field->name( )?>' value='<?=$field->value_encoded( )?>'<?=$disabled?>>