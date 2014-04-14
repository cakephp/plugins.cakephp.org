(function ($) {
	$.fn.bar = function (options) {
		var opts = $.extend({}, $.fn.bar.defaults, options);
		return this.each(function () {
			$this = $(this);
			var o = $.meta ? $.extend({}, opts, $this.data()) : opts;

			$this.click(function (e) {
				$.bar_notify(o);
			});
		});
	};

	var timeout;
	$.fn.bar.removebar = function (txt) {
		if($('.notify').length){
			clearTimeout(timeout);
			$('.notify').fadeOut('slow', function () {
				$(this).remove();
			});
		}
	};

	$.fn.bar.notify = function(o) {
		if (typeof o === 'string') {
			o = {message: o};
		}

		o = $.extend($.fn.bar.defaults, o);
		if ($('.notify').length && !o.force) {
			return;
		}

		timeout = setTimeout(function () {
			$.fn.bar.removebar();
		}, o.time);

		var _wrap_bar,
			_remove_cross = $(document.createElement('span')).css({display: 'none'}),
			_message_span = $(document.createElement('span'))
				.addClass('notify-content')
				.css({"color" : o.color})
				.html(o.message);

		_wrap_bar = (o.position == 'bottom') ?
			$(document.createElement('div')).addClass('notify notify-bottom'):
			$(document.createElement('div')).addClass('notify notify-top');

		_wrap_bar.css({backgroundColor : o.background, cursor: "pointer"});
		if (o.removebutton) {
			_remove_cross = $(document.createElement('a')).addClass('notify-cross');
			_remove_cross.click(function (e) {
				$.fn.bar.removebar();
			});
		}
		_wrap_bar.click(function (e) {
			$.fn.bar.removebar();
		});

		if (o.insert === 'after') {
			_wrap_bar.append(_message_span)
					.append(_remove_cross)
					.hide()
					.insertAfter($(o.el))
					.fadeIn('slow');
		} else {
			_wrap_bar.append(_message_span)
					.append(_remove_cross)
					.hide()
					.insertBefore($(o.el))
					.fadeIn('slow');
		}
	};

	$.fn.bar.defaults = {
		background   : '#fff',
		color        : '#000',
		position     : 'top',
		removebutton : true,
		time         : 2000,
		force        : false,
		message      : 'Notification',
		el           : 'body',
		insert       : 'after'
	};
})(jQuery);

(function ($) {
	$.fn.error_notification = function (message) {
		return $.fn.bar.notify({message: message, background: '#C73E14', color: '#fff'});
	};
	$.fn.success_notification = function (message) {
		return $.fn.bar.notify({message: message, background: '#69A95F', color: '#fff'});
	};
})(jQuery);

(function () {
	// Show all the errors
	if (typeof window.error_notifications !== "undefined") {
		jQuery.each(window.error_notifications, function(i, notification) {
			$.fn.error_notification(notification);
		});
	}
	if (typeof window.success_notifications !== "undefined") {
		jQuery.each(window.success_notifications, function(i, notification) {
			$.fn.success_notification(notification);
		});
	}
})();