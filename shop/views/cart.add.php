<td class='ctrl'>
<span class="popup-wrapper">
	<a class="cart" href=""></a>
	<form class="popup" action="<?=Route::url('cart', array('action' => 'add', 'id' => $id))?>" method="post">
		Сколько штук?
		<input type="text" name='<?=Kohana_Cart::PROP_QUANTITY?>' value="1">
		<input type="submit" value="">
	</form>
</span>
</td>