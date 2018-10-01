jQuery.fn.extend({
	disable: function(duration) {
		return this.each(function( ) {
			if ($(this).prop('tagName').match(/^input|textarea|select$/i) && $(this).data('chosen')) {
				$(this).data('chosen').is_disabled = true;
			}
			$(this)./*find('input').eq(0).*/attr('disabled', 'disabled');
			$(this)./*find('input').eq(0).*/attr('readonly', 'true');
			$(this).addClass('no-valid');
		});
	},
	enable: function(duration) {
		return this.each(function( ) {
			if ($(this).prop('tagName').match(/^input|textarea|select$/i) && $(this).data('chosen')) {
				$(this).data('chosen').is_disabled = false;
			}

			$(this)./*find('input').eq(0).*/attr('disabled', false);
			$(this)./*find('input').eq(0).*/attr('readonly', false);
			$(this).removeClass('no-valid');
		});
	},
	make_readonly: function( ) {
		return this.each(function( ) {
			$(this).find('input').eq(0).attr('disabled', 'disabled');
			$(this).find('input').eq(0).attr('readonly', 'true');
			$(this).addClass('no-valid');
		});
	},
	make_editable: function( ) {
		return this.each(function( ) {
			$(this).find('input').eq(0).attr('disabled', false);
			$(this).find('input').eq(0).attr('readonly', false);
			$(this).removeClass('no-valid');
		});
	},
	check: function( ) {
		return this.each(function( ) {
			$(this).find('input').attr('checked', true);
		});
	},
	uncheck: function( ) {
		return this.each(function( ) {
			$(this).find('input').attr('checked', false);
		});
	},
	formHide: function(duration) {
// 					alert(duration);
		if (duration == -1) duration = 1;
				 
		return this.each(function( ) {
			if ( ! $(this).prop('tagName').match(/^input|textarea|select$/i) || ! $(this).parents('.form-element').size( )) {
				if (duration == 1) {
					$(this).stop( ).hide( );
				} else {
					$(this).stop( ).slideUp(duration);
				}
				$(this).addClass('no-valid');
			}
		});
	},
	formShow: function(duration) {
		// duration == -1 on init
		if (duration == -1) duration = 1;
				 
		return this.each(function( ) {
			if ( ! $(this).prop('tagName').match(/^input|textarea|select$/i) || ! $(this).parents('.form-element').size( )) {
				if (duration == 1) {
					$(this).stop( ).show( );
				} else {
					$(this).stop( ).slideDown(duration);
				}
				
				$(this).removeClass('no-valid');
			}
		});
	},
	load: function(duration, url) {
		
		thisObj = this;
		thisObj.html('<option>загрузка...</option>');
		
		$.getJSON(
			url,
			function(data) {
				thisObj.html('');
				for (i in data){
					thisObj
						.append($("<option></option>")
							.attr("value",i)
							.text(data[i])
						); 
					
				}
				
				console.log(data);
			}
		);
		
	}
});