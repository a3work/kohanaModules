<?php

$class = implode(' ', $field->classes( ));

?>
<label><input class='<?=$class?>' type='checkbox' name='<?=$name?>' value='<?=$value?>'<?=$field->disabled( ) ? ' readonly' : ''?><?=$selected ? 'checked' : ''?>><?=$header?></label>