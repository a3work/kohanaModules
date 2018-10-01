$(document).ready(function( ) {
	current = 0;
	
	if (chapter = location.href.split('#')[1]) {
		if (decodeURI) {
			chapter  = decodeURI(chapter);
		}
		current = $('a[href*="'+chapter+'"]').prevAll().size();
	}
	
	$("table.search:not(:eq("+current+"))").hide();
	$(".search-menu a:eq("+current+")").addClass("act");
	
	$(".search-menu a").bind("click", function () {
		$(".search-menu a").removeClass("act");
		$(this).addClass("act");
		var id = $(this).prevAll().size();
		$("table.search").hide( );
		$("table.search:eq("+id+")").fadeIn("medium");
	});
});
