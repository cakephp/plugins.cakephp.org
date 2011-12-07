jQuery.noConflict();
jQuery(function() {
	jQuery('.clone-link').click(function(e) {
		jQuery('.clone-link').focus();
		jQuery('.clone-link').select();
	})
});
