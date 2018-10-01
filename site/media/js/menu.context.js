MenuContext = {
	_replacements: function(link, context) {
		var link = link;
		
		regex = /((\:)([a-z_0-9]+))/g;
		while ((match = regex.exec(link) ) != null) {
			link.replace(match[1], '\;');
		}
		
		
		regex = /(:([a-z_0-9]+))/g;
		
		var_sep = '<?=Kohana_Menu_Context::SEPARATOR_VAR?>';

		repl = {};
		while ((match = regex.exec(link) ) != null) {
			rg = new RegExp(var_sep+match[2]+var_sep+'([^ ]+)');

			if ((replacements = context.attr('class').match(rg)) != null) {
				repl[match[1]] = replacements[1];
			} else {
				repl[match[1]] = '';
			}
		}
		
		return repl;
	},
	
	link: function(link, context) {
		link = decodeURIComponent(decodeURIComponent(link));
		r = [];
		r[0] = MenuContext._replacements(link, context);
		
		siblings = $(context).siblings('.ui-selected');
		
		i = 0;
		
		if (siblings.size( )) {
			siblings.each(function( ) {
				i ++;
				r[i] = MenuContext._replacements(link, $(this));
			});
		}

		out = {};
		
		// combine many id
		for (var i in r) {
			for (var j in r[i]) {
// 				alert(i);
				if (!out[j]) {
					out[j] = '';
				} else {
					out[j] += '<?=Site::ID_SEPARATOR?>';
				}

				out[j] += r[i][j];
			}
		}

		link = link.replace('\\:', ';;;;;');

		// replace var stubs
		for (var i in out) {
			link = link.replace(i, out[i]);
		}

		link = link.replace(';;;;;', ':');
		console.log(link);
		return link;
	}
}