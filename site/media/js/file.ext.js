$(document).ready(function( ) {
	files = $('.file-ext').damnUploader({
		url: '/files/upload'
	}).on('du.add', function(e) {
		alert('added '+ e.uploadItem.file.name);
		e.uploadItem.fieldName = 'f[files][]';
		e.uploadItem.upload( );
		e.uploadItem.completeCallback = function(succ, data, status) {
			alert(this.file.name + " was uploaded.\nRecieved data: "+data+"\ncode: "+ status)
		};
	});

	$('input[type="button"]').bind('click', function( ) {
		files.duStart();
	});
});