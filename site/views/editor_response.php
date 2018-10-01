<?php
$result = array("html" => $html);

if ($js_callback != '')
{
	$result['callback'] = $js_callback;
}

$text = Basic::json_safe_encode($result);


?>
<script type='text/javascript'>
$(document).ready(function( ) {
	window.parent.Editor.updateObj(<?=$text?>);
});
</script>
