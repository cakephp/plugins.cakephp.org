(function($) {
	if (!String.prototype.camelize) {
		String.prototype.camelize = function(lowFirstLetter) {
			var str = this.toLowerCase();
			var str_path = str.split('/');
			for (var i = 0; i < str_path.length; i++) {
				var str_arr = str_path[i].split('_');
				var initX = ((lowFirstLetter && i + 1 === str_path.length) ? (1) : (0));
				for (var x = initX; x < str_arr.length; x++) {
					str_arr[x] = str_arr[x].charAt(0).toUpperCase() + str_arr[x].substring(1);
				}
				str_path[i] = str_arr.join('');
			}
			str = str_path.join('::');
			return str;
		};
	}

	$.fn.flash = function (options) {
		var template = function (flash) {
					return [
						'<div class="alert-message fade in ' + flash.status + '" style="display:none">',
							'<a class="close-alert" href="#">×</a>',
							'<p>' + flash.message + '</p>',
						'</div>'
					].join("\n");
				},
				settings = {
					'status'  : 'success',
					'message' : null
				};

		return this.each(function () {
			if (options) $.extend(settings, options);

			var $this = $(this),
					el = $this.hasClass('is-modal') ? $this.find('.modal-body') : $this,
					alert = $(template(settings));

			el.parent().find('.alert-message').remove();
			el.prepend(alert);
			alert.alert('.close-alert').fadeIn();
		});
	};

	$.fn.validationErrors = function (errors) {
		return this.each(function () {
			var $this = $(this);
			$.each(errors, function (modelName, err) {
				$.each(err, function (fieldName, message) {
					var $el = $this.find('#' + modelName + fieldName.camelize()).parent();
					$el.find('.error-message').remove();
					$el.addClass('error').append('<div class="error-message">' + message + '</div>');
				});
			});
		});
	};

	$.fn.cakephpAjax = function (e, options) {
		var settings = {
			selector : '.content',
			isModal  : false,
			success  : null,
			error    : null,
			url      : null,
			data     : null
		},
		finish = function () {
			$('input[type=submit]', e.target).removeAttr('disabled');
		};

		return this.each(function () {
			if (options) $.extend(settings, options);
			var $this = $(this),
					url   = settings.url  || $(e.target).attr('action'),
					data  = settings.data || $(e.target).serialize();

			$('input[type=submit]', this).attr('disabled', 'disabled');

			$.post(url, data, function (response) {
				finish();

				if (response.status === undefined) {
					$this.flash({status: 'error', message: response.message});
					return false;
				}
			
				if (response.status !== 'success') {
					$this.flash({status: response.status, message: response.message});
					$this.validationErrors(response.validationErrors);
					return false;
				}
			
				if ($this.hasClass('is-modal') || settings.isModal)
					$this.closest('.modal').modal('hide');

				$(settings.selector).flash({status: 'success', message: response.message});
			
				if (typeof settings.success === 'function')
					settings.success($(e.target).serializeArray(), response);
			
				return true;
			}, 'json').error( function (jqXHR, textStatus, err) {
				finish();

				if (typeof settings.error === 'function')
					settings.error($this, settings);
			
				return $this.flash({status: 'error', message: err});
			});
		});
	};
})( window.jQuery || window.ender );