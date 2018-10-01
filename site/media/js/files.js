Files = {
	restore: function(obj, key, opt, href, attr) {
		
		$.getJSON(href, function(data) {
			obj.html(data.text);
			console.log(data);
		});
		
/*
		$.ajax({
			dataType: "json",
			type: 'POST',
			url: href,
			success: function(data) {
// 				editor.autoSaveFile = data['filename'];
// 				
// 				window.setTimeout(
// 					function( ) {
// 						CKE.autoSave(editor, settings);
// 					},
// 					CKE.autoSaveTimeout
// 				);
				console.log(data);
			},
			error: function(data) {
				alert('error');
				console.log(data);
			}
		});
		*/
	}
}