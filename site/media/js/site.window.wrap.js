$('<?=$id?>').bind('click', function(e) {
	<?=$code?>(this, this.title, this.href);

	return false;
});