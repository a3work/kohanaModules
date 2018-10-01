

LoadSign = {
	init: function( ) {
		$('body').prepend(
			"<div class='cms-load-sign'></div>"
		);
		LoadSign.hide( );
	},
	show: function( ) {
		$('.cms-load-sign').show( );
	},
	hide: function( ) {
		$('.cms-load-sign').hide( );
	}
}

Popup = {
	init: function( ) {
		$('body').prepend("<div class='cms-popup'></div>");
		Popup.hide( );
	},

	show: function (text, x, y) {
		$('.cms-popup').html(text).show( );
		Popup.move(x, y);
	},

	move: function (x, y) {
	// 	alert(parseInt($('.cms-popup').width( )) + x);
		margin = 15;
		width = $('.cms-popup').width( ) + x;
		body_width = $('body').width( ) - margin * 2;
		if (width >= body_width) {
			x -= width - body_width;
		}
		$('.cms-popup').css({top:(y + margin)+'px',left:(x + margin)+'px'});
	},

	hide: function ( ) {
		$('.cms-popup').hide( );
	}
}

Shadow = {
	isInitialized: false,
	opacityLevel: 0.7,
	init: function( ) {
		if (Shadow.isInitialized) return;

		$('body').append("<div class='cms-shadow'></div>");
// 		$('.cms-shadow').bind('click', function( ) {
// 			Iframe.hideAll( );
// 			Shadow.hide( );
// 			CMS.clean( );
// 		})
		
		Shadow.hide( );
		Shadow.isInitialized = true;
	},
	show: function( ) {
		if ($('.cms-shadow').css('opacity') == Shadow.opacityLevel)
			return;

		$('.cms-shadow')
			.css({
				width:  $(document).width( ),
				height: $(document).height( ),
				display: 'block',
				opacity: 0
			})
			.stop( );
		if (CMS.useAnimation)
			$('.cms-shadow')
				.animate({
					opacity: Shadow.opacityLevel
				},'medium');
		else
			$('.cms-shadow')
				.css({
					opacity: Shadow.opacityLevel
				});
			
	},
	resize: function( ) {
		$('.cms-shadow')
			.css({
				width:  $(document).width( ),
				height: $(document).height( ),
			});
	},
	hide: function( ) {
		if (CMS.useAnimation)
			$('.cms-shadow').stop( ).animate({opacity:0},'medium').hide( );
		else
			$('.cms-shadow').stop( ).css({opacity:0}).hide( );
		
		window.setTimeout(function(){LoadSign.hide( )},100);
	}
}

get_window_height = function( )
{
	return /*$.browser.opera? */window.innerHeight /*: $(window).height()*/;
}

Iframe = function(src, x, y, autoResize) {
	this.id 						= null;
	this.src 						= null;
	this.isShow 					= false;
	this.width 						= 0;
	this.height 					= 0;
	this.x							= x;
	this.y							= y;
	this.autoResize					= autoResize == undefined ? true : autoResize;
	this.margin 					= [0, 0];
	this.can_reload 				= true;
	this.refresh_size_timeout = null;
// 	alert(this.y);
	this.setId( );
	this.init( );
	if (src) {
		this.load(src);
	}
}

Iframe.current = null;
Iframe.idPrefix = 'cmsiframe';
Iframe.minWidth = 100;
Iframe.maxWidth = 1850;
Iframe.instances = {};
Iframe.instances.length = 0;
Iframe.hideAll = function( ) {
	for (var i in Iframe.instances) {
		if (i != 'length') {
			Iframe.instances[i].remove( );
		}
	}
}

Iframe.prototype = {
	setId: function( ) {
		this.id = Iframe.idPrefix + Iframe.instances.length;
		Iframe.instances[this.id] = this;
		Iframe.instances.length ++;
	},
	getId: function( ) {
		return this.id;
	},
	// возвращаем dom-элемент iframe
	instance: function( ) {
		return $('#'+this.getId( )).get(0);
	},
	// инициализация
	init: function( ) {
		scrolling = ($.browser && $.browser.mozilla) ? 'yes' : 'auto';
// 		scrolling = 'no';
		$('body').prepend(
			"<span class='cms-iframe-close'></span><iframe id='"+ this.getId( ) +"' class='cms-iframe' border=no vspace=0 hspace=0 scrolling="+scrolling+" src=''></iframe>"
		);
		obj = this;

		$('.cms-iframe-close').bind('click', function( ) {
			Iframe.hideAll( );
			Shadow.hide( );
			CMS.clean( );
		});

		onLoadHandler = function( ) {
			src = $('#'+obj.getId( )).attr('src');
			contents = $('#'+obj.getId( )).contents( );
	// 			alert(src +"\n" + obj.src + "\n" + contents.get(0).location.href);
	// 			if (src == obj.src && contents.get(0).location.href == obj.src || src == '')
	// 				return;
			contents.bind('click', function( ) {
				window.clearTimeout(obj.refresh_size_timeout);
				obj.refresh_size_timeout = window.setTimeout(function( ) {
					obj.refresh_size( );
				}, 700);

			});
			obj.src = contents.get(0).location.href;
			obj.refresh_binding(contents);
			obj.refresh_size( );
		}

		$('#'+this.getId( )).css({opacity:0}).bind('load', onLoadHandler);
	},
	// загрузить iframe
	load: function(src) {
		src = this.modifySrc(src) + "?"+ (rnd = Math.random( )) +"=" + rnd;
		if (src == this.src && contents.get(0).location.href == obj.src  || src == '')
			return;

		this.remove( );
		Shadow.show( );
		obj = this;
			$('#'+obj.getId( )).attr('src', src);
	},
	// показать iframe
	show: function( ) {
		LoadSign.hide( );
		
		if (CMS.useAnimation)
			$('#' + this.getId( )).css({opacity:0, display:'block'}).stop( ).animate({opacity:1}, 'medium');
		else
			$('#' + this.getId( )).css({opacity:0, display:'block'}).stop( ).css({opacity:1});
	},
	// повесить обработчики событий на активные элементы содержимого
	refresh_binding: function(contents) {
		if ( ! contents) {
			contents = $('#'+obj.getId( )).contents( );
		}
		contents.find('a').bind('click', function( ) {
			if (this.href && this.href != '' && this.href.indexOf('iframe') == -1) {
				this.href = obj.modifySrc(this.href);
				if (this.target != '_blank') {
					this.href += "?"+ (rnd = Math.random( )) +"=" + rnd;
				}
			}
		});
		contents.find('form').bind('submit', function( ) {
			if (obj.can_reload) {
				if ( ! this.target || this.target == '') {
					if (this.action > '' && this.action.indexOf('iframe') == -1) {
						this.action = obj.modifySrc(this.action) + "?"+ (rnd = Math.random( )) +"=" + rnd;
					}
					obj.hide( );
				}
			} else {
				obj.can_reload = true;
			}
		});
	},

	// определить габариты содержимого
	refresh_size: function(contents) {
		if (!this.autoResize) {
			return;
		}

		if (location.href.indexOf('admin') != -1) {
			return;
		}
		if ( ! contents) {
			contents = $('#'+obj.getId( )).contents( );
		}
		resize_width = tmp_width = tmp_height = resize_height = 0;
// 		alert(contents.find('.data').children(/*':visible'*/).html( ));
		children =  contents.find('.data').children(':visible').each(function(key, value) {
			current_width = $(value).width( );
			current_offset = $(value).offset( );
			current_height = $(value).height( );
			tmp_width = current_width + current_offset.left;
			tmp_height = current_height + current_offset.top;
// 				alert(value.tagName+"\n\nwidth: "+current_width+"\nheight: "+current_height+"\n\nframe_width: "+resize_width+"\nframe_height: "+ resize_height);

			if (value.tagName != 'SCRIPT') {
				if (tmp_width > resize_width)
					resize_width = tmp_width;

				if (tmp_height > resize_height)
					resize_height = tmp_height;
			}
		});
		if (src)
			obj.resize(resize_width, resize_height);
	},
	coord: function(x, y) {
		this.x = x;
		this.y = y;
	},
	// переделать размеры iframe
	resize: function(width, height, force) {
// 		alert(width+"\n"+height);
// 		alert(this.width + "\n\n" + width);
// 		if (this.width == 0) {
// 			this.width =width + this.margin[0];
// 		} else {
// 			this.width =width;
// 		}
		if (!force) {
			width += this.margin[0];
			height += this.margin[1];
	// 		alert($(window).width( ) + "\n" + height + ' :: ' + $(window).height( ));
			this.width = width < get_window_height( ) && width != this.margin[0] ? width  : $(window).width( ) - this.margin[0];
			this.height = height < get_window_height( ) && height != this.margin[1] ? height : get_window_height( ) - this.margin[1];
			this.width = (this.width < Iframe.minWidth ? Iframe.minWidth : (this.width > Iframe.maxWidth ? Iframe.maxWidth : this.width));
// 			position = 'fixed';
			position = 'absolute';
		} else {
			this.width = width;
			this.height = height;
		}

		if (this.height > $(window).height) {
			$('#'+obj.getId( )).css({'overflow':'scroll'}).attr('scrolling', 'yes');
		} else {
			$('#'+obj.getId( )).css({'overflow':'hidden'}).attr('scrolling', 'no');
		}

		if (!this.y) {
			if (parseInt(this.height) > parseInt(get_window_height( ))) {
				top_position = 0;
				position = 'absolute';
			} else {
				top_position = (((get_window_height( ) - this.height) / 2) /*+ $(window).scrollTop( )*/);
	// 			alert(top_position);
			}
		} else {
			top_position = this.y;
		}

		if (!this.x) {
			left_position = ($(window).width( ) - this.width) / 2;
		} else {
			left_position = this.x;
		}

		if (left_position + this.width > $(window).width( )) {
			left_position = $(window).width( ) - this.width - 20;
		}

// 		if ((top_position + this.height - $(document).scrollTop( )) > $(window).height( )) {
// 			top_position = $(window).height( ) - this.height - 20 /*+ $(document).scrollTop( )*/;
// 		}

		top_position -= 5;
		left_position -= 5;

		options = {
			position: 	position,
			width: 		this.width+'px',
			height: 	this.height+'px',
			top: 		top_position+'px',
			left: 		left_position+'px',
			opacity: 	1
		};

		$('.cms-iframe-close').css({
			top: 		(top_position-30)+'px',
			left: 		(left_position)+'px',
		});
		$('.cms-iframe-close').show();
		
		if ($('#' + this.getId( )).is(':visible'))
		{
			
			if (CMS.useAnimation)
				$('#' + this.getId( )).stop( ).animate(options, 'medium');
			else
				$('#' + this.getId( )).stop( ).css(options);
				
		}
		else
		{
			$('#' + this.getId( )).css(options);
			this.show( );
		}
	},
	hide: function( ) {
		LoadSign.show( );
		$('.cms-iframe-close').hide();
		if (CMS.useAnimation)
			$('#' + this.getId( )).css({opacity:1}).stop( ).animate({opacity:0}, 'medium', function ( ) {$(this).css({display:'none'})});
		else
			$('#' + this.getId( )).css({opacity:1}).stop( ).css({opacity:0,display:'none'});
		
		this.instance.src = '';
	},
	modifySrc: function(src) {
		if (src.indexOf('/iframe') > -1) {
			return src;
		} else {
			return src.replace('admin', 'admin/iframe');
		}
	},
	remove: function( ) {
		this.width = 0;
		this.height = 0;
		this.hide( );
		this.src = '';
		if (obj && obj.getId) {
			$('#'+obj.getId( )).attr('src', '');
			$('#'+obj.getId( )).attr('width', 0);
			$('#'+obj.getId( )).attr('height', 0);
		}
	},
	clear: function( ) {
		$('#'+this.getId( )).remove( );
	}
}

Editor = {
	obj: null,
	iframe: null,

	init: function(obj, x, y) {
		Editor.obj = obj;

		id_arr = obj.className.match(/ (\d+)-(\d+)/);
		href = '<?=Route::url("editor_by_id", array("settings_id" => "~~~", "item_id" => "###"))?>';
		position = $(obj).offset( );
// 		alert($(obj).width( ));
// 		alert(href.replace('~~~', id_arr[1]).replace('###', id_arr[2]));
// 		alert("position.top < $(document).scrollTop() || position.top > ($(document).scrollTop() + $(window).height( ))\n"+position.top+"<"+$(document).scrollTop()+"||"+position.top+">"+($(document).scrollTop() + $(window).height( )));
// 		y = (position.top < $(document).scrollTop() || position.top > ($(document).scrollTop() + $(window).height( )))
// 			? 100
// 			: position.top - $(document).scrollTop();
		y = position.top;
// 		alert(y);
// 		alert(href.replace('~~~', id_arr[1]).replace('###', id_arr[2]));
		Editor.iframe = new Iframe(href.replace('~~~', id_arr[1]).replace('###', id_arr[2]), position.left,  y/*y+ $(document).scrollTop()*/, false);
	},
	updateObj: function(response)	{
		$(Editor.obj).html(response.html);
		if (response.callback) {
			eval(response.callback+"(Editor.obj)");
		}
		Editor.close( );
	},
	close: function( ) {
		if (Editor.iframe) {
			Editor.iframe.remove( );
			Editor.iframe.clear( );
			Shadow.hide( );
			Editor.obj = null;
		}
	}
}

CMS = {
	useAnimation: false,
	iframe: null,
	load: function(href) {
		if (!CMS.iframe) {
			CMS.iframe = new Iframe(href);
		} else {
			CMS.iframe.load(href);
		}
	},
	clean: function() {
		CMS.iframe = null;
	},
	resize: function() {
		if (CMS.iframe) {
			CMS.iframe.refresh_size( );
		}
	},
	menu: {
		winName: 'popupCMS',
		win:null,
		init: function( ) {
			$('.cms-menu-item a').bind('click', function( ) {
				<?/* :TODO: jquery.window usage */?>
				<?/* :FIXME: temporary open all in cms/full */?>
				if (this.href.indexOf('cms/full') == -1 && this.href.indexOf('cms/simple') == -1) {
					this.href = this.href.replace('cms', 'cms/full');
				}
				return true;
// 					if (!CMS.menu.win || !CMS.menu.win.location) {
// 						CMS.menu.win = window.open(this.href, CMS.menu.winName, "width=600,height=600,menubar=no,location=no,resizable=no,scrollbars=yes,status=yes");
// 					} else {
// 						CMS.menu.win.location.href = this.href;
// 						CMS.menu.win.focus( );
// 					}
// 				}
				
				return false;
			});
			return;

			if (location.href.indexOf('/admin/') < 0) {
				$('.cms-menu-item a:not(.cms-ext)').bind('click', function(event) {
					if (this.href && this.href != '') {
						Editor.close( );
						if ($(this).hasClass('cms-confirm') && ! window.confirm('Вы уверены?'))
						{
							return false;
						}
						CMS.load(this.href);
						return false;
					}


				});
			}

			$(window).resize(function() {
				CMS.resize( );
				Shadow.resize( );
			});
		}
	},
	init: function(target) {
		if (target) {
			target = target + ' ';
		} else {
			target = '';
		}

		$(target+'.e').bind('mouseenter', function(event) {
			text = '<b>клик</b>: редактирование';
			if ($(this).parent( ).is('a')) {
				text += '<br><b>двойной&nbsp;клик</b>: переход по ссылке';
			}
			Popup.show(text, event.pageX, event.pageY);
			return false;
		});
		$(target+'.e').bind('mousemove', function(event) {
			Popup.move(event.pageX, event.pageY);
			return false;
		});
		$(target+'.e').bind('mouseleave', function(event) {
			Popup.hide( );
		});

		$(target+'.e').bind('click', function(event) {
			obj = this;

			if ($(obj).parent( ).is('a')) {
				obj._clicks = obj._clicks ? obj._clicks + 1 : 1;
				if (obj._clicks == 2) {
					obj.clickTimer = setTimeout(function() {
						Editor.init(obj/*, event.pageX, event.pageY*/);
						obj._clicks = 0;
					}, 200);
					return false;
				}
				return true;
			} else {
				Editor.init(obj/*, event.pageX, event.pageY*/);
			}

			Popup.hide( );
			return false;
		});
	}
}

$(document).ready(function( ) {
	Popup.init( );
// 	Iframe.current = new Iframe( );
	LoadSign.init( );
	Shadow.init( );
	CMS.menu.init( );
	CMS.init( );
});