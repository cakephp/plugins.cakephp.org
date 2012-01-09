(function($){
	$('#clone').click(function(e) {
		$('#clone').focus();
		$('#clone').select();
	});
})(jQuery);

(function($){

	if (!window.matchMedia) {
		var detect_width = function() {
			var current_width = $(window).width();
			if (current_width > 980) {
				jQuery('body').removeClass("minimal");
				jQuery('body').removeClass("mobile");
			} else if (current_width > 610) {
				jQuery('body').addClass("minimal");
				jQuery('body').removeClass("mobile");
			} else {
				jQuery('body').removeClass("minimal");
				jQuery('body').addClass("mobile");
			}
		};

		//detect the width on page load
		$(document).ready(function() {
			detect_width();
		});

		$(window).resize(function() {
			detect_width();
		});
	}

	// Form processing
	$('.PackageSuggestForm').live('submit', function (e) {
		e.preventDefault();
		$(this).cakephpAjax(e, {selector: '.' + $(e.delegateTarget).attr('class')});
	});

	$('.ajax-toggle').click(function (e) {
		e.preventDefault();
		var el = $(e.delegateTarget);

		$.ajax({
			dataType: 'json',
			url: el.attr('href'),
			success: function (data, textStatus, jqXHR) {
				$('.content').flash({ message: data.message, status: data.status });

				if (data.status != 'success') {
					return;
				}

				if (el.hasClass('is_activated')) {
					el.removeClass('is_activated');
				} else {
					el.addClass('is_activated');
				}
			},
			error: function (jqXHR, textStatus, errorThrown) {
				var data = {};
				try {
					data = $.parseJSON(jqXHR.responseText);
				} catch (e) {
					data = { message: "Server error, please try again in a bit", status: "error" };
				}
				$('.content').flash({ message: data.message, status: data.status });
			}
		});
	});

})(jQuery);