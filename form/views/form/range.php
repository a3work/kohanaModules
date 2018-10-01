<?php

$disabled = $field->disabled( ) ? ' readonly' : '';
$class = implode(' ', $field->classes( ));
$field_name = $field->name( );
$settings = Basic::json_safe_encode($field->_settings( ));

$code = <<<HERE
var settings_$field_name = $settings;
jQuery.extend(settings_$field_name, {onstatechange:function(){\$('[name="$field_name"]').trigger('jrange.change');},ondragend:function(){console.log('sadf');\$('[name="$field_name"]').trigger('jrange.dragend');},onbarclicked:function(){\$('[name="$field_name"]').trigger('jrange.onbarclicked');}});
$('[name="$field_name"]').jRange(settings_$field_name);
$('[name="$field_name"]').on('reset', function(){\$('[name="$field_name"]').jRange('setValue', settings_$field_name.from+','+settings_$field_name.to);});
HERE;

InclStream::instance( )->write($code);

?>
<input class='<?=$class?>' type='hidden' name='<?=$field_name?>' value='<?=$field->value_encoded( )?>'<?=$disabled?>>