$(document).ready(function( ) {
	$('.to-cart').bind('click', function( ) {
		this.href = this.href+'?return='+$(location).attr('href');
	});
	
});