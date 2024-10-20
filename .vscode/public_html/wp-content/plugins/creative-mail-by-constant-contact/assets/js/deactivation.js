/**
 * Deactivation survey javascript.
 *
 * @package CreativeMail
 */
jQuery(function($){
	var deactivateLink = $('#the-list').find('[data-slug="creative-mail-by-constant-contact"] span.deactivate a');
	var form           = $('#ce4wp-deactivate-survey-form');
	var thankyou       = $('#ce4wp-deactivate-survey-form-success');
	var overlay        = $('#ce4wp-deactivate-survey');
	var closeButton    = $('#ce4wp-deactivate-survey-close');
	var formOpen       = false;

	deactivateLink.on('click', function(event) {
		event.preventDefault();
		overlay.css('display', 'table');
		formOpen = true;
	});

	form.on('submit', function (event) {
		event.preventDefault();

		var formData = jQuery(this).serialize();
		jQuery.ajax({
			type   : "POST",
			url    : ce4wp_data.url,
			data   : {
				nonce: ce4wp_data.nonce,
				data: formData,
				action: 'ce4wp_deactivate_survey'
			},
			success: function(data){
				form.hide();
				thankyou.show();
			}
		});
	});

	closeButton.on('click', function(event) {
		event.preventDefault();
		overlay.css('display', 'none');
		formOpen      = false;
		location.href = deactivateLink.attr('href');
	});

	$(document).keyup(function(event) {
		if ((event.keyCode === 27) && formOpen) {
			location.href = deactivateLink.attr('href');
		}
	});
});
