$(document).ready(function() {
	$('.f-remove').on('click', function() {
		$.getJSON($(this).attr('href'), function(data) {
			if (!data.result) {
				alert('Cannot remove file: ' + data.message);
			}
		});
		
		
		return false;
	});
});