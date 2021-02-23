jQuery(document).ready(function() {
	
	jQuery('.nav-tab-wrapper-azrcrv-smtp .nav-tab').on('click',function(event) {
		var item_to_show = '.azrcrv_smtp_tabs' + jQuery(this).data('item');

		jQuery(this).siblings().removeClass('nav-tab-active');
		jQuery(this).addClass("nav-tab-active");
		
		jQuery(item_to_show).siblings().css('display','none');
		jQuery(item_to_show).css('display','block');
	});
	
});

jQuery(function($) {
	$(document).on('click', '.azrcrv-smtp-import-dismiss', function () {
		// Read the "data-notice" information to track which notice
		// is being dismissed and send it via AJAX
		var nonce = $(this).closest('.azrcrv-smtp-import-dismiss').data('nonce');
		console.log(nonce)
		$.ajax( ajaxurl,
			{
				type: 'POST',
				data: {
					action: 'azrcrv_smtp_import_dismiss',
					nonce: nonce,
			}
		  } );
	  } );
});