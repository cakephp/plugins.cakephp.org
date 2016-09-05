(function () {
	$('.support-form').on('submit', function (e) {
		e.preventDefault();
		var phoneInput = $('#intl-phone-number'),
			countryInfo = phoneInput.intlTelInput("getSelectedCountryData"),
			__this = this;

		phoneInput.val(countryInfo.dialCode + phoneInput.val());

		$.post('/contacts/rapid', $(this).serialize(), function (response) {
			$('.rapid-contact-feedback').css('background', 'green').html('Your contact was sent.');
		}).fail(function () {
			$('.rapid-contact-feedback').css('background', 'red').html('Please try again.');
		}).always(function () {
			$(__this).parents('.modal').modal('toggle');
		});
	});

	$('.type-select').on('change', function (e) {
		$('.hidden-fields').hide();
		$('.hidden-fields input').prop('required', false);

		if ($(this).val() == 'call') {
			$('.phone-field').show();
			$('.phone-field input').prop('required', true);
		} else if ($(this).val() == 'skype') {
			$('.skype-field').show();
			$('.skype-field input').prop('required', true);
		}
	});

	$('#intl-phone-number').intlTelInput();
})();
