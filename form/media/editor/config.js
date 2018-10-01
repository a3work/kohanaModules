/**
 * @license Copyright (c) 2003-2013, CKSource - Frederico Knabben. All rights reserved.
 * For licensing, see LICENSE.html or http://ckeditor.com/license
 */

CKEDITOR.editorConfig = function( config ) {
	// Define changes to default configuration here.
	// For complete reference see:
	// http://docs.ckeditor.com/#!/api/CKEDITOR.config

	// The toolbar groups arrangement, optimized for two toolbar rows.
	config.toolbarGroups = [
		{ name: 'clipboard',   groups: [ 'clipboard', 'undo' ] },
		{ name: 'editing',     groups: [ 'find', 'selection', 'spellchecker' ] },
		{ name: 'links' },
		{ name: 'insert' },
		{ name: 'forms' },
		{ name: 'tools' },
		{ name: 'document',	   groups: [ 'mode', 'document', 'doctools' ] },
		{ name: 'others' },
		'/',
		{ name: 'basicstyles', groups: [ 'basicstyles', 'cleanup' ] },
		{ name: 'paragraph',   groups: [ 'list', 'indent', 'blocks', 'align', 'bidi' ] },
		{ name: 'styles' },
		{ name: 'colors' },
		{ name: 'about' },
		{ name: 'alignment', items : [ 'JustifyLeft', 'JustifyCenter', 'JustifyRight', 'JustifyBlock' ] },
	];

	// Remove some buttons provided by the standard plugins, which are
	// not needed in the Standard(s) toolbar.
	config.removeButtons = 'Underline,Subscript,Superscript';

	// Set the most common block elements.
	config.format_tags = 'p;h1;h2;h3;pre';

	// Simplify the dialog windows.
	config.removeDialogTabs = 'image:advanced;link:advanced';
	
	//savebtn is the plugin's name
	config.extraPlugins = 'savebtn,floatpanel,panelbutton,colorbutton,codemirror,sourcedialog,fakeobjects,dialog,iframe,justify,div';
// 	config.extraPlugins = 'savebtn,floatpanel,panelbutton,colorbutton,clipboard,codemirror,sourcedialog,fakeobjects,dialog,iframe,justify,widget,widgetselection,lineutils,filetools,notificationaggregator,notification,toolbar,button,uploadwidget,uploadimage';
	
	config.removePlugins = 'sourcearea';
	
	//link to serverside script to handle the post
	config.saveSubmitURL = 'http://server/link/to/post/';
	
	config.uploadUrl = '/cms/Main/upload';
	
	
	config.extraAllowedContent = 'div(*)';
	config.allowedContent = true;
};
