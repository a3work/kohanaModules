<?php

$class = isset($class) ? ' class="'.$class.'"' : '';

echo '<style>
.tmp-tab-act {
	background:#fcf !important;
}
</style><table'.$class.'><tr><th>'.implode('</th><th>', $header).'</th></tr>'.$body.'</table>';