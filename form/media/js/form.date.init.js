$(document).ready(function( ) {
// 	$('input[type="date"]').each(function( ) {
	
	var i = 0;
	date_obj = {};
	
	$('.form-date').each(function( ) {
		date_obj[i] = this;
		currFormat = $(date_obj[i]).attr('title');
		currValue = $(date_obj[i]).val();
		if (currValue == '')
		{
			today = new Date();
			currValue = currFormat.replace('d',((day=today.getDate()) < 10 ? '0'+day : day)).replace('m', ((month=today.getMonth( )+1)<10 ? '0'+month : month)).replace('Y',today.getFullYear());
		}
		
		$(date_obj[i]).DatePicker({
// 			format:'d.m.Y',
			format:currFormat,
			date: $(date_obj[i]).val(),
			current: currValue,
			starts: 1,
			position: 'r',
			onBeforeShow: function(){
				$(date_obj[i]).DatePickerSetDate(currValue, true);
			},
			onChange: function(formated, dates) {
				$($(this).data('datepicker').el).val(formated);
					$($(this).data('datepicker').el).DatePickerHide();
// 				}
			}
		});
		
	});
	
});
