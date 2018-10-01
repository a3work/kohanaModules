<div class='form-line <?=$class?>'>
	<div class='form-label'><?=$label?></div>
	<div class='form-element'><?=$element != '' ? $element : '&mdash;'?>
<?php if ($message != ''): ?>
		<div class='form-message'><?=$message > ' ' ? "<span>{$message}</span>" : ''?></div>
<?php endif; ?>
	</div>
</div>
