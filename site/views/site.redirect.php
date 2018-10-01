<script type='text/javascript'>
	Delay = {
		time: null,
		iframe: false,
		elapse: function(x) {
			x = x + '';
<?
			if (Site::get_language( ) == 'ru_RU')
			{
				echo "var second_arr = new Array( 'секунду', 'секунды', 'секунд');";
			}
			else
			{
				echo "var second_arr = new Array( 'second', 'seconds', 'seconds');";
			}
?>
			if ( x >= 10 && x <= 20) {
				x = x + " " + second_arr[2];
			} else {
				if ( x.substr( x.length - 1) == 1) {
					x = x + " " + second_arr[0];
				} else {
					if (  x.substr( x.length - 1) == 0 || x.substr( x.length - 1) == 2 || x.substr( x.length - 1) == 3 || x.substr( x.length - 1) == 4) {
						x = x + " " + second_arr[1];
					} else {
						x = x + " " + second_arr[2];
					}
				}
			}
			return x;
		},
		init: function(time, iframe) {
			Delay.iframe = iframe || false;
			Delay.time = time;
			if (Delay.time > 0) {
				Delay.set( );
			} else {
				Delay.action( );
			}
		},
		set: function( ) {
			if (Delay.time > 0) {
				$('.cms-timer span').html(Delay.elapse(Delay.time));
				window.setTimeout(function() {
					Delay.time --;
					Delay.set( );
				}, 1000);
			} else {
 				Delay.action( );
			}
		},
		action: function( ) {
			<?=$command?>;
		}
	}

	$(document).ready(function( ) {
		Delay.init(<?=$time?>, true);
	});
</script>
<div class='cms-timer'><?=$message?><br><?=$elapse_message?> <span></span>.<?if ($url != '' && ! $is_close) {?><br><br><a href='<?=$url?>' target='<?=$target?>'><?=__u('go to next page')?></a><?}?></div>
