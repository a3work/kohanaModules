<p>Список зарегистрированных заказов:</p>
<?php
echo '<ul>';
foreach ($orders AS $order)
{
	$client_href = Html::factory('anchor')
					->href(Route::url('user_manage', array('id' => $order['user']->id)))
					->text($order['user']->username);
	
	$client_logo = $order['user']->attributes->logo ? " ({$order['user']->attributes->logo})" : '';
	
	echo "<li>№{$order['num']} для клиента {$client_href}{$client_logo}</li>";
}
echo '</ul>';