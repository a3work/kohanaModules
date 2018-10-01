<?php

$class = implode(' ', $field->classes( ));

?>
<input class='<?=$class?>' type='file' multiple="multiple" name='<?=$field->name( )?>'<?=$field->disabled( ) ? ' readonly' : ''?>>