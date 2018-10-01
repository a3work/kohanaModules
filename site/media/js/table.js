$(document).ready(function( ) {
	$('tr[title^="<?=__('go to')?>"]')
		.bind('dblclick', function( ) {
			location.href = this.title.replace("<?=__('go to')?>", '');
			return false;
		})
		.bind('mouseenter', function( ) {
			window.status = this.title.replace("<?=__('go to')?>", '');
		})
		.bind('mouseleave', function( ) {
			window.status = '';
		});
});