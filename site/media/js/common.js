bccl_show = '<?=__("show")?>';
bccl_hide = '<?=__("hide")?>';

ready = function( ) {
	$('.bccl-sp').each(function( ) {
		txt = ($(this).hasClass('show') ? bccl_hide : bccl_show)+' '+$(this).attr('title');
		$("<a href='' class='bccl-toggle'>"+txt+"</a>").insertBefore(this);
	});

	$('.bccl-toggle').bind('click', function( ) {
		div = $(this).next('.bccl-sp');
		div.toggle( );

		if ($(this).next('.bccl-sp').css('display') == 'block')
		{
			$(this).html(bccl_hide+' '+div.attr('title'));
		}
		else
		{
			$(this).html(bccl_show+' '+div.attr('title'));
		}

		return false;
	});
};

$(document).ready(ready);
$(document).ajaxComplete(ready);

tabs = function(classname) {
	this.classname 	= '.'+(classname || "<?=Site::DEFAULT_TABS_CSS?>");
	
	this.init( );
}
tabs.prototype = {
	init: function( ) {
		if (chapter = location.href.split('#')[1]) {
			if (decodeURI) {
				chapter  = decodeURI(chapter);
			}
			current = $(this.classname+' a[href*="'+chapter+'"]').prevAll().size();
		} else {
			current = 0;
		}
		
		this.mark($(this.classname+".tbs-menu a:eq("+current+")"));
		
// 		$(this.classname+".tbs "+this.classname+".tbs-item:not(:eq("+current+"))").hide();
// 		$(this.classname+".tbs-menu a:eq("+current+")").addClass("act");
		
		obj = this;
		
		$(this.classname+".tbs-menu a").bind("click", function () {
			obj.mark(this);
		});
	},
	
	mark: function(obj) {
		if ($(obj).hasClass('act'))
			return;
		
		$(this.classname+".tbs-menu a").removeClass("act");
		$(obj).addClass("act");
		var id = $(obj).prevAll().size();
		
		$(this.classname+".tbs "+this.classname+".tbs-item").hide( );
		$(this.classname+".tbs "+this.classname+".tbs-item:eq("+id+")").fadeIn("medium");
	}
}
