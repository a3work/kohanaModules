checkBoxHandler = function( ) {
	if (this.checked) {
		prev_span = $(this).parent().prev('span');
		if (prev_span.eq(0).html( ) == '+')
			prev_span.each(tree.toggleSubLevel);

		$(this).parent().next('div').eq(0).find('input:not(:checked)').attr('checked', 'checked');

		result = {};
		length_arr = [];

		$(this).parents('.form-tree div').prev('label').each(function( ) {
			next_div = $(this).next('div');
			result[next_div.html( ).length] = next_div;
			length_arr.push(next_div.html( ).length);
		});

		for (var i in length_arr) {
			if ($(result[length_arr[i]]).find('input:not(:checked)').size( ) == 0) {

				$(result[length_arr[i]]).prev('label').find('input').attr('checked', 'checked');
			} else {
				break;
			}
		}
	} else {
		$(this).parent().next('div').eq(0).find('input:checked').attr('checked', false);
		$(this).parents('.form-tree div').prev('label').find('input:checked').attr('checked', false);
	}
};


Form = {
	_settings: {},
	_recent: {},
	_callback: {},
	init:function(name, immediatly, errorCallback, successCallback, settings) {
		$(document).on('submit', 'form[name="'+name+'"]', function(e) {
			if (!Form.onSubmit(this)) {
				e.preventDefault();
				return false;
			}
		});
		
		Form._settings[name] = settings || {};
		
		if (immediatly && Form._settings[name]['allocate_errors']) {
			$('form[name="'+name+'"]').find('input, textarea, select').each(function() {
				Form.bind($(this));
				
			});
		}
		
		if (errorCallback) {
			if (!Form._callback[name]) {
				Form._callback[name] = {};
			}
			Form._callback[name]['error'] = errorCallback;
		}

		if (successCallback) {
			if (!Form._callback[name]) {
				Form._callback[name] = {};
			}
			Form._callback[name]['success'] = successCallback;
		}
		
	},
	defineVld:function( ) {
		try {
			Vld;
		} catch (e) {
			Vld = {};
		} finally {
			return Vld;
		}
	},
	showError: function(obj, txt) {
		obj.parents('.form-line').eq(0).addClass(Cfg.form_missing_field).removeClass(Cfg.form_ready_field);
		if (!obj.parents('.form-line').eq(0).find('.form-element .form-message').size( )) {
			obj.parents('.form-line').eq(0).find('.form-element').eq(0).append('<div class="form-message"></div>');
		}
		if (!obj.parents('.form-line').eq(0).find('.form-element .form-message span').size( )) {
			obj.parents('.form-line').eq(0).find('.form-element .form-message').append('<span></span>');
		}
		
		obj.parents('.form-line').eq(0).find('.form-element .form-message span').html(txt);
		
		Form.bind(obj);
	},
	addRecent: function(formName, fieldName) {
		if (!Form._recent[formName]) {
			Form._recent[formName] = {};
		}
		if (!Form._recent[formName][fieldName]) {
			Form._recent[formName][fieldName] = 1;
		}
	},
	validateRecent: function(useAjax) {
		/* :TODO: ajax validation */
		for (var formName in Form._recent) {
			for (var fieldName in Form._recent[formName]) {
				if (Vld[formName][fieldName]) {
					Form.checkForField(Vld[formName][fieldName], true);
				}
			}
		}
	},
	bind: function(obj) {
		// bind handler of focus for missing field
		obj.on('focus', function(e) {
// 			$(this).off(e).parents('.form-line').eq(0).removeClass(Cfg.form_missing_field).removeClass(Cfg.form_ready_field);
		});
		
		// bind handler of blur for missing field
		obj.off('blur').on('blur', function(e) {
			$(this).off(e);
			var formName = $(this).parents('form').attr('name');
			var fieldName = $(this).attr('name');
			
			Form.addRecent(formName, fieldName);
			Form.validateRecent();
		});
		
		obj.off('keyup');
		if (obj.parents('.form-line').eq(0).hasClass(Cfg.form_missing_field) || obj.parents('.form-line').eq(0).hasClass(Cfg.form_ready_field)) {
			obj.on('keyup', function(e) {
				$(this).off(e);
				var formName = $(this).parents('form').attr('name');
				var fieldName = $(this).attr('name');
				
				Form.addRecent(formName, fieldName);
				Form.validateRecent(false);
			});
		}
	
	},
	hideError: function(obj, txt) {
		obj.parents('.form-line').eq(0).addClass(Cfg.form_ready_field).removeClass(Cfg.form_missing_field);
		if (!obj.parents('.form-line').eq(0).find('.form-element .form-message').size( )) {
			obj.parents('.form-line').eq(0).find('.form-element').eq(0).append('<div class="form-message"></div>');
		}
		obj.parents('.form-line').eq(0).find('.form-element .form-message span').remove('');
		Form.bind(obj);
	},
	eval: function(callback, callback_type, obj, txt) {
		try {
			var tmpFunc = new Function('obj, txt', 'return '+callback+'(obj, txt);');
			tmpFunc(obj, txt);
		} catch (e) {
			console.log('cannot find function '+callback+': '+e.message);
			if (callback_type == 'success') {
				Form.hideError(obj, txt);
			} else {
				Form.showError(obj, txt);
			}
		}
	},
	check: function(rule, obj, txt, res) {
		if (obj.parents('.no-valid').size( )) {
			return {
				'res': null,
			};
		}
		
		if (obj.attr('name').indexOf('[]') != -1 && obj.attr('type') == 'checkbox') {
			current_value = [];
			$('[name="' + obj.attr('name')+'"]:checked').each(function( ) {
				current_value.push($(this).attr('value'));
			});
		} else if (obj.attr('type') == 'checkbox') {
			current_value = obj.attr('checked') ? obj.attr('value') : '';
		} else if (obj.attr('type') == 'radio') {
			current_value = $('[name="' + obj.attr('name')+'"]:checked').val();
		} else {
			current_value = obj.val( );
		}
		
		var res = res || false;
		var form_name = obj.parents('form').attr('name');
		
		if (rule != 'not_empty' && !current_value) {
			return {
				'res': null,
			};
		}
		
		return {
			'res': res,
			'form_name': form_name,
			'obj': obj,
			'txt': txt
		};
	},
	onSubmit: function(form) {
		var res;
		
		try {
			if (Vld[form.name]) {
				var result = {
					'res': null,
				};
				
				if (Form._settings[form.name]['allocate_errors']) {
					for (var i in Vld[form.name]) {
						var resultTmp = Form.checkForField(Vld[form.name][i]);
						if (resultTmp.res === false) {
							result = resultTmp;
						}
					}
				} else {
					for (var i in Vld[form.name]) {
						var resultTmp = Form.checkForField(Vld[form.name][i]);
						if (resultTmp.res === false) {
							result = resultTmp;
							break;
						}
					}
				}
				
				if (!Form._settings[form.name]['allocate_errors'] && result.res !== null) {
					Form.runHandler(result, false);
				}
				
				return result.res !== null ? result.res : true;
				
			} else {
				return true;
			}
		} catch (e) {
			console.log(e);
		}
	},
	runHandler: function(result, forceRunHandler) {
		if (result.res) {
			if (Form._callback[result.form_name] && Form._callback[result.form_name]['success']) {
				Form.eval(Form._callback[result.form_name]['success'], 'success', result.obj, result.txt);
			} else {
				Form.hideError(result.obj, result.txt);
			}
		} else {
			if (Form._callback[result.form_name] && Form._callback[result.form_name]['error']) {
				Form.eval(Form._callback[result.form_name]['error'], 'error', result.obj, result.txt);
			} else {
				Form.showError(result.obj, result.txt);
			}
		}
	},
	checkForField: function(field, forceRunHandler) {
		for (var j in field) {
			var tmpFunc = new Function('return '+field[j]+'();');
			var result = tmpFunc();
			
			if (!result.res) {
				break;
			}
		}
		
		if (result.res !== null && (Form._settings[result.form_name]['allocate_errors'] || forceRunHandler)) {
			Form.runHandler(result, forceRunHandler);
		}
	
		return result;
	}
}

tree = {
	handlers: {},

	registerHandler: function(name, handler) {
		tree.handlers[name] = handler;
	},

	toggleSubLevel: function(e){
		if (!$(this).next().next().is('div') && $(this).parents('.form-ajax-tree').size( ) == 1)
		{
			inp = $(this).next().find('input');
			$(this).next().after('<div class="form-tree-loading"></div>');
			span = $(this);
			$.ajax({
				url:'<?=Route::url("form_tree_level",array("element"=>"elem","value"=>"value"))?>'.replace('elem', inp.attr('name')).replace('value', inp.attr('value')),
				success:function(data) {
					span.next( ).next( ).remove( );
					span.next( ).after(data);
					tree.bindTreeHandlers(span.nextAll('div:eq(0)'));
				},
			});
		}
		if ($(this).html( ) == '+') {
			$(this).nextAll('div').eq(0).slideDown('fast', function( ) {
				$(this).show( );
			});
			$(this).html('&minus;');
		} else {
			$(this).nextAll('div').eq(0).slideUp('fast', function( ){
				$(this).hide( );
			});
			$(this).html('+');
		}
	},

	bindTreeHandlers: function(obj) {
		obj = obj || $('.form-tree');

		name = obj.find('input:eq(0)').attr('name');
		if (name && tree.handlers[name]) {
			tree.handlers[name](obj);
		};

		obj.find('span').bind('click', tree.toggleSubLevel);
		obj.find('input[type=checkbox]').bind('change', checkBoxHandler);

		obj.find('input[type=checkbox]:checked').each(checkBoxHandler);
		obj.find('div:not(.form-tree-root)').each(function( ) {
			if ($(this).find('input:checked').size( ) == 0) {
				$(this).hide( );
				$(this).prev( ).prev( ).html('+');
			}
		});

		obj.find('span').each(function( ) {
			if (!$(this).next().next().is('div'))
			{
				$(this).html('+');
			}
		});
	},

	init: function() {
		tree.bindTreeHandlers( );
	}
}

$(document).ready(function( ) {
	$('form[target=iframe]').each(function( ){
		iframeName = $(this).attr('name') + '_iframe';
// 		$('body').append('<iframe name="'+iframeName+'" width=300 height=300 frameborder=2 style="position:relative; z-index:67; margin-left:500px; border:1px dashed red"></iframe>');
		$(document).append('<iframe name="'+iframeName+'" width=0 height=0 frameborder=0></iframe>');
		$(this).attr('target', iframeName);
	});
	$('form[target=iframe]').bind('submit', function(){
		returnFunc = function(){
			alert('alfdskj');
// 			obj.html(currentIframe.contents( ));
// 			alert('adfjladskf');
// 			currentIframe.unbind('load', returnFunc);
		};
		$(this).wrap('<span></span>');
		obj = $(this).parent( );
		currentIframe = $('iframe[name='+$(this).attr('target')+']');
		currentIframe.bind('load', returnFunc);
	});

	$('.select-all').bind('click', function( ) {
		$(this.parentNode.parentNode).find('input').attr('checked', true);
	});

	$('.deselect-all').bind('click', function( ) {
		$(this.parentNode.parentNode).find('input').attr('checked', false);
	});

	$('input[maxlength]').bind('keypress', function(e) {
		if (e.keyCode == 8 && this.value.length == 0) {
			$(this).prev('input').focus( );
		} else if ((e.keyCode >= 48 && e.keyCode <= 57 || e.keyCode >= 96 && e.keyCode <= 105 ) && this.value.length == parseInt($(this).attr('maxlength'))) {
			$(this).next('input').focus( );
		}
	});

	$('textarea[wrap=off]').bind('keypress', function(e) {

	});
	
	$('.captcha-refresh').on('click', function() {
		img = $(this).parent().find('img');
		src = img.attr('src');
		d = new Date();
		img.attr('src', '/captcha/'+d.getTime());
		
	});
	
	tree.init( );
});