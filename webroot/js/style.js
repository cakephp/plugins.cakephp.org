(function($) {

	$('#clone').click(function (e) {
		$(e.delegateTarget).focus();
		$(e.delegateTarget).select();
	});

	if ($('.packages-index').length) {
		$('.next-page a').live('click', function (e) {
			e.preventDefault();
			var el = $(e.target);

			$('.next-page').replaceWith(window.templates.loader());
			$.ajax({
				url: el.attr('href'),
				dataType: 'json',
				success: function (data, textStatus, jqXHR) {
					var packages = '';
					jQuery.each(data.content.packages, function(i, v) {
						packages += window.templates.package_listing(v);
					});

					if (data.content.packages.length === 0) {
						$('.packages').append(window.templates.noMoreResults());
					} else {
						$('.packages').append(packages + window.templates.nextPage(data.content.next));
					}
				},
				complete: function() {
					$('.loading').remove();
				}
			});
		});
	}

	if (!window.matchMedia) {
		var detect_width = function () {
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
		$(this).cakephpAjax(e, {
			selector: '.' + $(e.target).attr('class'),
			success: function(data, response) {
				$(e.target).find('.github').val('');
			}
		});
	});

	$('.ajax-toggle').click(function (e) {
		e.preventDefault();
		var el = $(e.target);

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

$.fn.serializeParams = function (obj) {
	var str = [];
	for (var p in obj) {
		if (jQuery.isArray(obj[p])) {
			for (var i = 0; i < obj[p].length; i++) {
				if (obj[p] && typeof(obj[p][i]) != "function") {
					str.push(encodeURIComponent(p + "[]=" + obj[p][i]));
				}
			}
		} else if (typeof obj[p] === "object") {
			for (var k in obj[p]) {
				if (obj[p]) {
					str.push(encodeURIComponent(p + "[" + k + "]=" + obj[p][k]));
				}
			}
		} else {
			if (obj[p]) {
				str.push(p + "=" + encodeURIComponent(obj[p]));
			}
		}
	}
	return str.join("&");
};