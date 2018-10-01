<div class='proc pid-<?=$id?>'>
	<div class='proc-data'>
		<div class='proc-name'><span></span><small></small></div>
		<div class='proc-progress'><span></span><small></small></div>
	</div>
	<div class='proc-loadbar'>
		<div></div>
	</div>
	<a class='proc-button' href='<?=Route::url('cli', array('action' => 'cancel', 'task' => $id))?>'><?=__('cancel')?></a>
</div>