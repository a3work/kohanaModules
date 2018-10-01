<div class='main-widget cart'>
	<h2><span>Корзина</span></h2>
<?php if ($totals['quan'] != 0): ?>
	<table>
		<thead>
			<tr class='header'>
				<th>Артикул</th>
				<th>Кол&#8209;во</th>
				<th>Цена</th>
			</tr>
		</thead>
		<tfoot>
			<tr>
				<th>Итого:</th>
				<th><?=$totals['quan']?></th>
				<th class='price'><?=str_replace(' ', '&nbsp;', number_format($totals['amount'], 2, ',', $totals['amount'] > 10000 ? ' ' : ''))?><span class="rub">p</span></th>
			</tr>
		</tfoot>
		<tbody>
			<?=$body?>
		</tbody>
	</table>
	<a href='<?=Route::url('cart')?>' class='cart-submit'><span>Оформить</span></a>
	<a href='<?=Route::url('cart', array('action'=>'clear'))?>' class='cart-btn' onclick='return confirm("Очистить корзину?")'>Очистить</a>
<?php else: ?>
<p>Ваша корзина пуста.</p>
<?php endif; ?>
</div>