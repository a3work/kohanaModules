<?php

foreach ($_list AS $item)
{
	echo Html::factory('anchor')->href($item->_href( ))->text($item->_text( )).'<br>';
}