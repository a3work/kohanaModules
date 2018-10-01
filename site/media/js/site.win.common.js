function site_win_common(obj, title, href, options) {
	w = 400;
	h = 400;

	m = 20;

	x = $(obj).offset( ).left-w;
	y = $(obj).offset( ).top - $(window).scrollTop( )-h;

	if (x < 0) {
		x = m;
	} else if (x + w > $(window).width( )) {
		x = $(window).width( ) - m - w;
	}

	if (y < 0) {
		y = m;
	} else if (y + h > window.screen.availHeight) {
		y = window.screen.availHeight - 5*m - h;
	}

	win = $.window($.extend(
						{
							title: title,
							url:	href,
							draggable: true,
							resizable: true,
							maximizable: true,
							minimizable: false,
							showModal: false,
							bookmarkable: false,
							x: x,
							y: y,
							width: w,
							height: h,
							maxWidth: $(window).width( ),
							maxHeight: $(window).height( )
						},
						options
	));

	id = 'win'+Math.random( )*10000;
	$(obj).attr('id', id);
	win.getFrame( ).attr("obj", id);
	
	if (!document.body.win) {
		document.body.win = {};
	}
}
