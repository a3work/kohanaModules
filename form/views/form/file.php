<?php

$class = implode(' ', $field->classes( ));

?>
<input class='<?=$class?>' type='file' name='<?=$field->name( )?>'<?=$field->disabled( ) ? ' readonly' : ''?>>