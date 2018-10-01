Cron = {
	delete: function(obj, key, opt, href) {
		$('body').addClass('waiting');
		Cms.ajax(href, function(data) {
			View.update('body', data.body);
			$('body').removeClass('waiting');
		});
	}
}