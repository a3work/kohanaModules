$(document).ready(function( ){
	if (window.parent.Editor) {
		window.parent.Editor.iframe.resize($('.cms-editor-wrapper form').width( ), $('.cms-editor').height( ));
	}
});