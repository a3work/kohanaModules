<?php if (IN_PRODUCTION): ?>
function dump(obj) {
    var out = "";
    if(obj && typeof(obj) == "object"){
        for (var i in obj) {
            out += i + ": " + obj[i] + "\n";
        }
    } else {
        out = obj;
    }
    alert(out);
}
<?php endif ?>

Cms = {
	ajax: function(href, func) {
		$.getJSON(href, func)
		.fail(function(data){
			<?php if (IN_PRODUCTION): ?>
			alert('error: ajax request to '+href+' failed');
// 			dump(data.responseText);
			View.update('body', data.responseText);
			<?php else: ?>
			alert('cannot load data');
			<?php endif ?>
		});
	},
	action: function(obj, key, opt, href) {
		$('body').addClass('waiting');
		
		Cms.ajax(href, function(data) {
			try {
				for (var i in data) {
					if (i.match(/^_/)) {
						View.process(i, data[i]);
					} else {
						View.update(i, data[i]);
					}
				}
				
				$('body').removeClass('waiting');
			}
			catch(e) {
				
			}
		});
	}
}

$(document).ready(function( ) {
	// confirmation handler has been registered first
	$(document).on('click', '.confirm', function(e) {
		
		msg = $(this).data('confirmMsg') ? $(this).data('confirmMsg') : '<?=__u("are you sure")."?"?>';
		
		if ( ! window.confirm(msg)) {
			// drop execution of other handlers
			e.stopImmediatePropagation();
			return false;
		}
	});
		
	$(document).on('click', '.cms-del', function( ) {
		if ( ! window.confirm('<?=__u("are you sure")."?"?>')) {
			return false;
		}
	});
	
	$(document).ready(function() {
		$(document).on('click', '.ajax', function(e) {
			Cms.action(null, null, null, $(this).attr('href'));
			return false;
		});
	});
	
});

function formatStringSecNum(num_, words_) {
	if (num_) {
		num_ = num_+'';
		
		var lastOne = parseInt(num_.substring(num_.length-1, num_.length_));
		var lastTwo = parseInt(num_.substring(num_.length-2, num_.length_));
		
		if ( lastTwo < 10 || lastTwo > 19)
		{
			if ( lastOne == 1)
			{
				return words_[0];
			}
			else if (lastOne == 2 || lastOne == 3 || lastOne == 4)
			{
				return words_[1];
			}
			else
			{
				return words_[2];
			}
		}
		else
		{
			return words_[2];
		}
	}
	else
	{
		return words_[2];
	}
}

/**
 * Decline and return string according to number
 *
 * 		Найден:>(а|о|о) :quan детал:(ь|и|ей).
 *
 * @param 	string		text to decline
 * @return 	string
 */
function decline(value_)
{
	var findMore = false;
	
	var result = value_.match(/(\d+)[^0-9]*(:\(([^|\)]*)\|([^|\)]*)\|([^|\)]*)\))/);
	if (result)
	{
		value_ = value_.replace(result[2], formatStringSecNum(result[1], result.slice(3)));
		findMore = true;
	}

	var result = value_.match(/(:>\(([^|\)]*)\|([^|\)]*)\|([^|\)]*)\))[^0-9]*(\d+)/);
	if (result)
	{
		value_ = value_.replace(result[1], formatStringSecNum(result[5], result.slice(2, 5)));
		findMore = true;
	}

	return findMore ? decline(value_) : value_;
}