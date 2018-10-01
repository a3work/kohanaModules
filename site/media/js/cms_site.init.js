is_load = false;

current_id = null;
current_type = null;
current_obj = null;

Editor = {
	idFull: "editor",
	idSimple: "editor_simple",
	areaFull: "textarea_editor",
	areaSimple: "textarea_editor_simple",
	areaCurrent: null,
	instanceFull: null,
	instanceSimple: null,
	is_show: false,

	minimal_size: {
		full: {
			width: 560,
			height: 50
		},
		simple: {
			width: 320,
			height: 50
		}
	},

	toolbars: {
		full: [
			{ name: 'document', items : [/* 'Source','-'*/,'Save'/*,'NewPage','DocProps','Preview'*/,'Print'/*,'-','Templates' */] },
			{ name: 'clipboard', items : [ 'Cut','Copy','Paste','PasteText','PasteFromWord','-','Undo','Redo' ] },
			{ name: 'editing', items : [ 'Find','Replace','-','SelectAll', 'ShowBlocks'/*,'-','SpellChecker', 'Scayt' */] },
// 			{ name: 'forms', items : [ 'Form', 'Checkbox', 'Radio', 'TextField', 'Textarea', 'Select', 'Button', 'ImageButton',
// 				'HiddenField' ] },
			{ name: 'insert', items : [ 'Image'/*,'Flash'*/,'Table','HorizontalRule'/*,'Smiley'*/,'SpecialChar','PageBreak'/*,'Iframe'*/ ] },
			'/',
			{ name: 'basicstyles', items : [ 'Bold','Italic','Underline','Strike','Subscript','Superscript','-','RemoveFormat' ] },
			{ name: 'paragraph', items : [ 'NumberedList','BulletedList','-','Outdent','Indent','-','Blockquote','CreateDiv',
			'-','JustifyLeft','JustifyCenter','JustifyRight','JustifyBlock','-','BidiLtr','BidiRtl' ] },
			'/',
			{ name: 'styles', items : [/* 'Styles'*/,'Format','Font','FontSize' ] },
			{ name: 'colors', items : [ 'TextColor','BGColor' ] },
			{ name: 'links', items : [ 'Link','Unlink','Anchor' ] }
		],
		simple:	[
			['Save','-', 'Cut','Copy','Paste','-','Undo','Redo' ]
		],
	},


	init: function( ) {
		if ($('#' + Editor.idFull).length == 0) {
			$('body').prepend(
				"<form id='"+ Editor.idFull +"' class='cms-editor-wrapper'><textarea id='"+ Editor.areaFull +"' class='"+ Editor.areaFull +"'></textarea></form>"
			);
		}
	},
	show: function(obj, type) {
		Editor.areaFull = Editor.areaFull;
		Editor.idFull =Editor.idFull;
		Editor.areaCurrent = Editor.areaFull;
		obj_offset = $(obj).offset( );
		width = $(obj).width( );
		height = $(obj).height( )+50;

		text = $(obj).html( );
		Editor.process(type, text, width, height);

		y = obj_offset.top;
		x = obj_offset.left;
		if ((Editor.width + x) > $(window).width( ))
		{
			x -= Editor.width + x - $(window).width( );
		}

		Editor.resetDirty( );
		$('#' + Editor.idFull)
			.css({
				display:"block",
				visibility:'visible',
				top: y + 'px',
				left: x + 'px',
	// 			width: width + 'px',
	// 			height: height + 'px',
// 				opacity:0
			}).show( );
		$('#' + Editor.idFull).stop( ).animate({opacity:1},'medium', function ( ) {
			Shadow.show( );
		});
	},
	confirmCancel: function( ) {
		if (Editor && Editor.is_show && Editor.instanceFull && Editor.instanceFull.checkDirty()) {
			return window.confirm("Все несохранённые данные будут потеряны.\nЗакрыть редактор без сохранения изменений?");
		} else {
			return true;
		}
	},
	hide: function( ) {
		if (Editor.confirmCancel( )) {
			Shadow.hide( );
			Editor.remove('fast');
			Editor.resetDirty( );
		}
	},
	resetDirty: function( ) {
		if (Editor.instanceFull) {
			Editor.instanceFull.resetDirty( );
		}
	},
	remove: function(speed) {
		Editor.is_show = false;
		$('#' + Editor.idFull).hide(speed);
		$('#' + Editor.idSimple).hide(speed);
		Editor.resetDirty( );
	},
	get: function(obj) {
		if ( ! is_load) {
			$(window).load(function( ) {
				window.setTimeout(function( ) {
					Editor.get(obj);
				}, 500);
			});
		} else {
			if (Editor.is_show)
				return;

			Editor.is_show = true;
			current_obj = obj;
			matches = obj.className.match(/cms-(\d+)-([^\s]+)/);
			current_id = matches[1];
			current_type = matches[2];

			switch (current_type) {
				case 'header':
					Editor.show(obj, 'simple');
					break;
				default:
					Editor.show(obj, 'full');
			}
		}
	},

	unload: function( ) {
		if (Editor.confirmCancel( )) {
			$('#'+Editor.idFull + " span").remove( );
			CKEDITOR.remove(Editor.instanceFull);
		}
	},
	process: function(type, text, width, height) {


		type = type || 'full';
		if (Editor.instanceFull)
			Editor.unload( );


		Editor.width = width < Editor.minimal_size[type].width ? Editor.minimal_size[type].width : width;
		Editor.height = height < Editor.minimal_size[type].height
						? Editor.minimal_size[type].height
						: (
							height > $(window).height( )
							? $(window).height( )
							: height
						  );

		Editor.instanceFull = CKEDITOR.replace(Editor.areaFull, {
			toolbar: Editor.toolbars[type],
			contentsCss: [
				"<?=Route::url('static_files', array('filetype'=>'css', 'file'=>'template.css'));?>",
				"<?=Route::url('static_files', array('filetype'=>'css', 'file'=>'cms.css'));?>"
			],
			filebrowserBrowseUrl : "<?=Route::url('file_browse', array());?>",
			filebrowserUploadUrl : "<?=Route::url('file_upload', array('action'=>'upload'));?>",
			filebrowserImageBrowseUrl : "<?=Route::url('file_browse', array());?>?filter=images",
			filebrowserImageUploadUrl : "<?=Route::url('file_upload', array('action'=>'upload'));?>?filter=images",
			filebrowserWindowWidth  : 900,
			filebrowserWindowHeight : 700,
			width: Editor.width,
			height: Editor.height,
			resize_minWidth: Editor.minimal_size[type].width,
			resize_minHeight: Editor.minimal_size[type].height,
			skin: 'v2'
		});
		CKEDITOR.plugins.registered['save']=
		{
			init : function( editor ) {
				var command = editor.addCommand( 'save',
					{
						modes : { wysiwyg:1, source:1 },
						exec : function( editor ) {
							save_data( );
						}
					}
				);
				editor.ui.addButton( 'Save',{label : 'сохранить',command : 'save'});
			}
		}
		Editor.instanceFull.setData(text);
		Editor.resetDirty( );
	}
}

function save_data( ) {
	$.post(
		"<?=Route::url('cms_content', array('view'=>'ajax', 'page'=>'contents', 'param'=>'save'));?>",
		{
			id: 	current_id,
			type: 	current_type,
			data: 	Editor.instanceFull.getData( )
		},
		function (data) {
			$('.'+current_obj.className.replace(' ', '.')).html(data);
			Editor.resetDirty( );
			Editor.hide( );
		}
	  );

}

$(document).ready(function() {


	Editor.init( );
});

$(window).load(function( ) {
	is_load = true;
});