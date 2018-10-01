<?php

$class = implode(' ', $field->classes( ));

?>
<select class='<?=$class?>' name='<?=$field->name( )?>' multiple<?=$field->disabled( ) ? ' disabled' : ''?>><?=$options?></select>