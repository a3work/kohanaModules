<style>
.message-error, .message-success {
	display:block;
	margin:20px 0 10px;
}
.message-error {
	color:red;
}
.message-success {
	color:#000;
}

</style>
<?/*
<div class='message-<?=$type?>'><?=$message?></div>
*/?>
<div class='form-line message-<?=$type?>'>
	<div class='form-label'></div>
	<div class='form-element'><?=$message?></div>
</div>
