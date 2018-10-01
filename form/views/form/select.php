<?php

$class = implode(' ', $field->classes( ));

?>
<select class='<?=$class?>' name='<?=$field->name( )?>' <?=$field->disabled( ) ? ' disabled' : ''?>><?=$options?></select>