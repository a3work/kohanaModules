function site_win_editor(obj, title, href, options) {

	w = $(obj).width( )+20;
	if (w < 250) {
		w = 250;
	} else if (w > $(window).width( )) {
		w = $(window.width( ))
	}
	
	h = 0.6*$(obj).height( )+100;
	if (h < 250) {
		h = 400;
	} else if (h > $(window).height( )) {
		h = $(window).height( );
	}

// 	x = $(obj).offset( ).left;
// 	if (x < 0) {
// 		x = 0;
// 	} else if (x + w > $(window).width( )) {
// 		x = $(window).width( ) - w;
// 	}
// 
// 	y = $(obj).offset( ).top - $(window).scrollTop( );
// 	if (y < 0) {
// 		y = 0;
// 	} else if (y + h > window.screen.availHeight) {
// 		y = window.screen.availHeight - h;
// 	}
	

	win = $.window($.extend(
						{
							title: title,
							url:	href,
							draggable: true,
							resizable: true,
							maximizable: true,
							minimizable: false,
							showModal: true,
							bookmarkable: false,
							checkBoundary: true,
							withinBrowserWindow: true,
// 							x: x,
// 							y: y,
							width: w,
							height: h,
							maxWidth: $(window).width( ),
							maxHeight: $(window).height( ),
							onOpen: function(wnd) {
								$(wnd).data('bodyScrollTop', $('body').scrollTop( ));
								$(wnd).data('bodyOverflow', $('body').css('overflow'));
								$('body').scrollTop(0);
								$('body').css({overflow:'hidden'});
								$('body').bind('scroll', function(e) {
									e.preventDefault( );
									return false;
								});
							},
							onClose: function(wnd) {
								$('body').scrollTop($(wnd).data('bodyScrollTop'));
								$('body').css({overflow:$(wnd).data('bodyOverflow')});
								$('body').unbind('scroll');
							}
						},
						options
	));

	id = 'win'+Math.random( )*10000;
	obj.attr('id', id);
	win.getFrame( ).attr("obj", id);
	
	if (!document.body.win) {
		document.body.win = {};
	}
}
