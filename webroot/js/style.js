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

})(jQuery);