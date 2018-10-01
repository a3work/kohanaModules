View = {
	/** process variable (parameter of response begins from "_")
	 *
	 * @param 	string	parameter name
	 * @param	string	value
	 * @return 	void
	 **/
	process: function(key, val) {
		switch (key) {
			case '_message':
				alert(val);
				break;
			case '_close':
				for (var i in wins = parent.$.window.getAll()) {
					console.log();
					if (wins[i].getFrame().attr('src') == window.location.pathname+window.location.search) {
						wins[i].close();
					}
				}
		}
	},
	/** fetch data as parameter or load from server and replace target
	 *
	 * @param 	string	target name (view output place)
	 * @param	string	HTML for replacing
	 * @return 	void
	 **/
	update: function(target, data) {
		if (!data) {
			Cms.action(null, null, null, Cfg.refreshUri.replace(':id', target));
			return;
		}
		
		node = $('.<?=Kohana_Controller_Cms::VIEW_MARKER?>.'+target);
		<?php if (IN_PRODUCTION): ?>
		if (!node.length) {
			alert('node '+target+' does not exist');
		}
		<?php endif ?>
		node.html(data);
	},
	/** get parent view node of specified element
	 *
	 * @param 	string	target name (view output place)
	 * @param	string	HTML for replacing
	 * @return 	void
	 **/
	parent: function(selector) {
		return $(selector).parents('.<?=Kohana_Controller_Cms::VIEW_MARKER?>');
	},
	/** get parent view node of specified element
	 *
	 * @param 	string	target name (view output place)
	 * @param	string	HTML for replacing
	 * @return 	void
	 **/
	reload: function(marker) {
		url = decodeURIComponent($('.'+marker).data('<?=View::AJAX_MARKER_URL?>'));

		$.ajax({
			url: url,
			context: $('.'+marker)
		}).done(function(data) {
			$(this).replaceWith(data);
		});
	},
}