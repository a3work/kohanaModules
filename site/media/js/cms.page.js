$(document).ready(function( ){
	obj = window.frameElement.getAttribute("obj");
	$(window.parent.document.getElementById(obj)).html($('form textarea').eq(0).val( ));
});