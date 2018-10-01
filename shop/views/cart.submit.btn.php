<?php
if (empty($show_reset))
{
	$show_reset = TRUE;
}
?>
<p class='cart-message'><?=$message?>
<?php if (isset($show_button) && $show_button): ?>
<a href='<?=Route::url('cms_cart', array('action' => 'submit'))?>' class='cms-btn cms-btn-submit'>Оформить заказ</a>
<?php endif; ?>
<?php if ($show_reset): ?>
<a href='<?=Route::url('cms_cart', array('action' => 'clear'))?>' class='cms-btn cms-btn-clear' onclick='return confirm("<?=__u('are you sure')?>?");'>Очистить корзину</a>
<?php endif; ?>
</p>
