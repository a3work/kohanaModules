Process = function( ) {
	// interval holder
	this.interval = null;
	// found id
	this.ids = [];
	// separator of id array
	this.separator = '<?=Cli::IDS_SEPARATOR?>';
	// interval period
	this.refreshTime = 1000;
	// server script
	this.handler = '<?=Route::url("cli",array("action"=>"state"))?>';
	// current data
	this.data = null;
	//
}

Process.prototype = {

	points: '',

	/** find and load id
	 *
	 * @return void
	 */
	load: function( ) {
		obj = this;

		// search cli processes
		this._list( ).each(function( ) {
			id = this.className.match(/pid-(\d+)/);
			if (id && id.length > 0) {
				obj.ids.push(id[1]);
			}
		});
	},

	/** send request
	 *
	 * @return void
	 */
	send: function( ) {
		obj = this;

		$.get(this.handler, {"ran": Math.random(), "ids": this.ids.join(this.separator)}, function(data) {
			obj.data = $.parseJSON(data);

			if (obj.data) {

				Process.points = Process.points == '' ? '.' : (Process.points == '.' ? '..' : (Process.points == '..' ? '...' : ''));

				isAlive = false;

				for (var i in obj.data) {
					Process._process(obj.data[i], i);
					isAlive = isAlive || obj.data[i].alive;
				}

				if (isAlive) {
					obj.timeout = window.setTimeout(function( ) {
						obj.send( );
					}, obj.refreshTime);
				}
			}
		});
	},

	/** get process list
	 *
	 * @return	jquery
	 */
	_list: function() {
		return $('.proc');
	},

	/** initialize
	 *
	 * @param	object	data
	 * @return	void
	 */
	init: function( ) {
		this.load( );

		if (this.ids.length > 0) {
			obj = this;
			obj.send( );
		}
	}
}

Process._process = function(data, num) {
	proc = $('.pid-'+num);

	points = data.alive && data.pid > 0 && ! data.is_final ? Process.points : '.';

	proc.find('.proc-name span').html(data.name);
	proc.find('.proc-name small').html(data.status + points);
	
	if (data.is_final) {
		proc.find('.proc-button').fadeOut( );
	}

	if (data.progress) {
		proc.find('.proc-progress span').show('slow').html(data.progress+'%');
		proc.find('.proc-loadbar').show( );
		proc.find('.proc-loadbar div').stop().animate({width:data.progress+'%'}, 'slow');
	}
	else
	{
		proc.find('.proc-loadbar').hide( );
		proc.find('.proc-progress span').hide( );
	}

	if (data.processed) {
		proc.find('.proc-progress small').html(data.processed);
	}
}


$(document).ready(function( ) {
// 	$('.pg-wrapper').fadeIn( );
	proc = new Process;
	proc.init( );
});