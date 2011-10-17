jQuery.noConflict();
jQuery(function() {
	jQuery("select, input:checkbox, input:radio, input:file, .contents input").uniform();
	jQuery('.clone-link').click(function(e) {
		jQuery('.clone-link').focus();
		jQuery('.clone-link').select();
	})
});
