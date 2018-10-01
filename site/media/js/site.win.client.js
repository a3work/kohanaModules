/** 
 * popup window supports auto-refresh of main page
 */
function site_win_client(obj, title, href, options) {

	w = 900;
	h = 600;
	
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
							onShow:function(wnd) {
								wnd.getContainer( ).find('.frame_loading').hide( );
							},
							onIframeStart: function(wnd, url) {
								wnd.getContainer( ).find('frame_loading').hide( );
							},
							onIframeEnd: function(wnd, url) {
// 								alert('ends');
							},
							onClose: function(wnd) {
								$('body').scrollTop($(wnd).data('bodyScrollTop'));
								$('body').css({overflow:$(wnd).data('bodyOverflow')});
								$('body').unbind('scroll');
// 								location.reload( );
							}
						},
						options
	));

	id = 'win'+Math.random( )*10000;
	$(obj).attr('id', id);
	win.getFrame( ).attr("obj", id);
	win.getFrame( ).attr("name", id);
	win.getFrame( ).attr("id", id);
	
	if (!document.body.win) {
		document.body.win = {};
	}
}
