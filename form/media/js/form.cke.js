CKE = {
// 	autoSaveTimeout: 60000,
	autoSaveTimeout: 5000,
	settings: {
		'simple': [
			{ name: 'basic', items : ['Save', 'Cut','Copy','Paste','-','Undo','Redo']}
		],
		'basic': [
			[ 'savebtn', '-', 'Undo', 'Redo', '-', 'Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Source', '-', 'Scayt'],
			'/',
			[ 'Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'Outdent', 'Indent', '-', 'RemoveFormat', '-', 'NumberedList', 'BulletedList', '-', 'Blockquote', 'Link', 'Unlink', 'Anchor', 'Image', 'Flash', 'Table', 'HorizontalRule', 'SpecialChar','-','TextColor', 'BGColor'],
			'/',
			[ 'Styles', 'Format' ],
			[ 'Maximize' ],
			['About']
					//     { name: 'about' }		
// 			{ name: 'document', items : [/*(settings.show_save_btn ? */'Save'/* : '')*//* 'Source','-','NewPage','DocProps','Preview','Print'/*,'-','Templates' */] },
// 			{ name: 'clipboard', items : [ /*'Cut','Copy','Paste','PasteText','PasteFromWord','-',*/'Undo','Redo' ] },
// 			{ name: 'editing', items : [ 'Find','Replace','-','SelectAll', 'ShowBlocks'/*,'-','SpellChecker', 'Scayt' */] },
// // 			{ name: 'forms', items : [ 'Form', 'Checkbox', 'Radio', 'TextField', 'Textarea', 'Select', 'Button', 'ImageButton',
// // 				'HiddenField' ] },
// 			{ name: 'insert', items : [ 'Image'/*,'Flash'*/,'Table','-', 'Link','Unlink','Anchor','-' ,'Blockquote','HorizontalRule'/*,'Smiley'*/,'SpecialChar','PageBreak'/*,'Iframe'*/ ] },
// 			'/',
// 			{ name: 'basicstyles', items : [ 'Bold','Italic','Underline','Strike','Subscript','Superscript','-','RemoveFormat' ] },
// 			{ name: 'paragraph', items : [ 'NumberedList','BulletedList','-','Outdent','Indent',/*,'CreateDiv'*/,
// 			'-','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock','-','BidiLtr','BidiRtl' ] },
// 			'/',
// 			{ name: 'styles', items : [/* 'Styles'*/,'Format'/*,'Font','FontSize' */] },
// 			{ name: 'colors', items : [ /*'TextColor',*//*'BGColor' */] }
		],
		'inline': [
			[ 'savebtn', '-', 'Undo', 'Redo', '-', 'Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Sourcedialog', 'Iframe', '-'],
			'/',
			[ 'Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat', '-', 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock' ],
			[ 'NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote' ],
			[ 'Link', 'Unlink', 'Anchor' ],
			[ 'Image', 'Table', 'HorizontalRule', 'SpecialChar','-','TextColor', 'BGColor'],
			'/',
			[ 'Styles', 'Format' ],
			[ 'About' ]
		],
		'extended': [
			[ 'savebtn', '-', 'Undo', 'Redo', '-', 'Cut', 'Copy', 'Paste', 'PasteText', 'PasteFromWord', '-', 'Source', '-'],
			'/',
			[ 'Bold', 'Italic', 'Underline', 'Strike', 'Subscript', 'Superscript', '-', 'RemoveFormat' ],
			[ 'NumberedList', 'BulletedList', '-', 'Outdent', 'Indent', '-', 'Blockquote' ],
			[ 'Link', 'Unlink', 'Anchor' ],
			[ 'Image', 'Table', 'HorizontalRule', 'SpecialChar','-','TextColor', 'BGColor'],
			'/',
			[ 'Styles', 'Format' ],
			[ 'About' ]
		],
		'source': [
			{ name: 'source', items : ['Save']}
		],
	},
	
	init: function(id, toolbar, width, height, settings) {

		if ($('#'+id).size( ) == 0 && $('textarea[name='+id+']').size( ) == 0) {
			return;
		}

		options = {
				contentsCss: [
					"<?=Route::url('static_files', array('filetype'=>'css', 'file'=>'reset.css'));?>",
					"<?=Route::url('static_files', array('filetype'=>'css', 'file'=>'grid.css'));?>",
					"<?=Route::url('static_files', array('filetype'=>'css', 'file'=>'main.css'));?>",
				],
// 				filebrowserBrowseUrl : "<?=Route::url('files_browse', array());?>",
// 				filebrowserUploadUrl : "<?=Route::url('files', array('action'=>'upload'));?>",
// 				filebrowserImageBrowseUrl : "<?=Route::url('files_browse', array());?>?filter=images",
// 				filebrowserImageUploadUrl : "<?=Route::url('files', array('action'=>'upload'));?>?filter=images",
				filebrowserWindowWidth  : 900,
				filebrowserWindowHeight : 700,
				filebrowserUploadUrl: '/cms/Main/upload',
// 				width: width,
// 				height: height,
// 				resize_minWidth: (width-200),
// 				resize_minHeight: (height-50),
				skin: 'moono',
				toolbar: toolbar,
				extraAllowedContent: 'div(*){*}[*]',
				allowedContent:true,
				toolbarGroups: [
					{ name: 'others' },
					{ name: 'clipboard',   groups: [ 'clipboard', 'undo' ] },
					{ name: 'document',    groups: [ 'mode', 'document', 'doctools' ] },
					{ name: 'editing',     groups: [ 'find', 'selection', 'spellchecker' ] },
					{ name: 'forms' },
					'/',
					{ name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
					{ name: 'paragraph',   groups: [ 'list', 'indent', 'blocks', 'align', 'bidi' ] },
					{ name: 'links' },
					{ name: 'insert' },
					'/',
					{ name: 'styles' },
					{ name: 'colors' },
					{ name: 'tools' },				
				]
			};

		if (settings.saveSubmitURL) {
			options['saveSubmitURL'] = settings.saveSubmitURL;
		} else {
			
		}
			
		if (settings.sourceOnly) {
			options['startupMode'] = 'source';
		}
		
		if (settings.maximize) {
// 			window.parent.Editor.iframe.resize(width, height, true);
			options['on'] = {
					'instanceReady': function (evt) {
						evt.editor.execCommand('maximize');
					}
				};
		}
		
		if (settings.inline) {
			$('#'+id).parents('a').removeAttr('href');
			editor = CKEDITOR.inline(
				id,
				options
			);
		} else {
			editor = CKEDITOR.replace(
				id,
				options
			);
		}
		
		if (settings.useAutoSave) {
			CKE.autoSave(editor, settings);
		}
	},
	simple: function(id, settings) {
			CKE.init(id, CKE.settings.simple, 250, 180, settings);
	},
	basic: function(id, settings) {
			CKE.init(id, CKE.settings.basic, 400, 250, settings);
	},
	extended: function(id, settings) {
			CKE.init(id, CKE.settings.extended, 750, 550, settings);
	},
	source: function(id, settings) {
			CKE.init(id, CKE.settings.source, 750, 550, settings);
	},
	inline: function(obj, key, opt, href, attr)
	{
		obj.attr('contenteditable', true);
		CKEDITOR.disableAutoInline = true;
		CKE.init(obj.attr('id'), CKE.settings.inline, 400, 250, $.extend({
				inline: true,
				saveSubmitURL: href
			}, attr));
	},
	autoSave: function(editor, settings) {
		
		
		$.ajax({
			dataType: "json",
			type: 'POST',
			url: settings.autoSaveHandler,
			data: {
				'filename': editor.autoSaveFile,
				'text':		editor.getData( )
			},
			success: function(data) {
// 				console.log('success')
// 				console.log(data);
				editor.autoSaveFile = data['filename'];
				
				window.setTimeout(
					function( ) {
						CKE.autoSave(editor, settings);
					},
					CKE.autoSaveTimeout
				);
			},
			error: function(data) {
// 				console.log('success')
// 				console.log(data);
			}
		});
// 		$.getJSON(settings.autoSaveHandler, );
	}
}

$(document).ready(function( ) {
	CKEDITOR.disableAutoInline = true;
});