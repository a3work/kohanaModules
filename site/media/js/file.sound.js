var isFirefox = true;

function Sound(source, volume, loop)
{
    this.source	= source;
    this.volume	= volume || 100;
    this.loop	= loop;
	this.state  = 0;		// 0: stop; 1: play; 2: record;
	
	// callback for onended event
    this.onended= null;
	
    var son;
    this.son=son;
    this.finish=false;
	
    this.stop = function() {
		if (this.son)
			this.son.pause();
		
		this.state = 0;
		
		
    }
    
    this.start = function() {
		if (this.state >= 1) return;

		this.state = 1;
		this.son = new Audio(this.source);
		this.son.play();
		
		this.son.onended = this.onended;
    }
    
    this.init = function(volume,loop) {
        this.finish=false;
		
        this.volume=volume;
        this.loop=loop;
		this.state = 0;
    }
    
    this.record = function( ) {
		if (this.state != 0) return;
		
		this.state = 2;

		navigator.getUserMedia({
			audio: true,
			video: false
		}, function(stream) {
			recordAudio = RecordRTC(stream, {
				//bufferSize: 16384,
				//sampleRate: 45000,
				onAudioProcessStarted: function() {
					if(!isFirefox) {
						recordVideo.startRecording();
					}
				}
			});
			
			if(isFirefox) {
				recordAudio.startRecording();
			}
			
			if(!isFirefox) {
				recordVideo = RecordRTC(stream, {
					type: 'video'
				});
				recordAudio.startRecording();
			}

		}, function(error) {
			alert( JSON.stringify (error, null, '\t') );
		});
	},
	this.save = function(blob, fileType, fileName) {
		if (this.state == 0) return;
		
		this.state = 0;
		obj = this;
		fileName = Math.round(Math.random() * 99999999) + 99999999;
		recordAudio.stopRecording(function() {
			
			var fileType = 'audio'; // or "audio"

			var formData = new FormData();
			formData.append(fileType + '-blob', recordAudio.getBlob( ));


			$.ajax({
				url: obj.source,
				data: formData,
				processData: false,
				contentType: false,
				type: 'POST',
				success: function( ){
				}
			});				
		});

			

		// 		var request = new XMLHttpRequest();
		// 		request.onreadystatechange = function () {
		// 			if (request.readyState == 4 && request.status == 200) {
		// 				callback(location.href + request.responseText);
		// 			}
		// 		};
		// 		request.open('POST', this.source);
		// 		request.send(formData);
	}
}

Sound.instances = {};
Sound.factory = function(source, volume, loop) {
	if ( ! Sound.instances[source]) {
		Sound.instances[source] = new Sound(source, volume, loop);
	}
	
	return Sound.instances[source];
}

$(document).ready(function( ) {
	$('.f-sound-play').on('click', function() {
		s = Sound.factory($(this).attr('href')+'?r='+Math.random());
		obj = $(this);
		
		s.onended = function() {
			obj.addClass('cms-play').removeClass('cms-pause');
		}
		

		if (s.state) {
			$(this).addClass('cms-play').removeClass('cms-pause');
			s.stop( );
		} else {
			$(this).addClass('cms-pause').removeClass('cms-play');
			s.start( );
		}
		
		return false;
	});
	
	$('.f-sound-rec').on('click', function() {
		s = Sound.factory($(this).attr('href'));
		
		if (s.state) {
			$(this).removeClass('cms-pause').addClass('cms-rec');
			s.save( );
		} else {
			$(this).removeClass('cms-rec').addClass('cms-pause');
			s.record( );
		}

		return false;
/*		

		
		return false;*/
	});
});
