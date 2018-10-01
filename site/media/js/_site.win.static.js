w = 400;
h = 300;

m = 20;

x = $(this).offset( ).left;
y = $(this).offset( ).top - $(window).scrollTop( );

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

try {
	title
} catch (e) {
	title = this.title;
}
try {
	href
} catch (e) {
	href = this.href;
}


$.window({
   title: title,
   url:	href,
   draggable: false,
   resizable: false,
   maximizable: false,
   minimizable: false,
   showModal: true,
   bookmarkable: false,
   x: x,
   y: y,
   width: w,
   height: h
});