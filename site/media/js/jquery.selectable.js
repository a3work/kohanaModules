Selectable = {
	/** Bind selectable plugin to specified elements
	 * 
	 * @param 	string	jquery selector
	 * @return 	object
	 **/
	init: function(selector) {
		return View.parent(selector).selectable({
			filter:selector,
			delay:150,
			stop:function( ) {
				obj = this;
				$($(obj).selectable('option', 'filter')+'.ui-selected').on('clickoutside', function( ) {
					if ($($(obj).selectable('option', 'filter')+'.ui-selected').size( )) {
						$($(obj).selectable('option', 'filter')+'.ui-selected').removeClass('ui-selected');
					}
				});
			},
			create:function(e) {
				obj = this;
				$(document).on('keydown', function(ev) {
					$(obj).selectable("option", "delay", ev.ctrlKey ? null : 150);
				});
				$(document).on('keyup', function(ev) {
					if (!ev.ctrlKey) {
						$(obj).selectable("option", "delay", 150);
					}
				});
			}
		}).selectable('widget');
	},
	
	/** Get selected elements collection
	 * 
	 * @param 	string	jquery selector
	 * @return 	jquery object
	 **/
	get: function(selector) {
		alert(selector+'.ui-selected');
		return View.parent(selector).find(selector+'.ui-selected');
	}
}