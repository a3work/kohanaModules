$(document).ready(function( ){
	$('.form-chosen').each(function( ) {
// 		alert($(this).hasClass('chosen-no-search'));
		$(this).chosen({
			disable_search_threshold:	<?=Form_Field_Chosen::DISABLE_SEARCH_THRESHOLD?>,
			no_results_text:			"<?=__('found nothing')?>",
		});
// 		alert($(this).data('chosen').disable_search_threshold)
	});
})
