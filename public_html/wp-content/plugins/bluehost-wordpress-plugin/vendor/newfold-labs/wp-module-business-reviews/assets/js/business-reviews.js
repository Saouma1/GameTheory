ndfbr.links = [];

ndfbr.doFeedback = function() {
	jQuery( '.ndf_modal' ).show();
	jQuery( '.ndf_br_contact' ).show();
	jQuery( '.ndf_br_list' ).hide();
}

ndfbr.doReview = function() {
	if ( ! Array.isArray( this.links ) || ! this.links.length ) {
		var ajaxRequest = jQuery.post( {
			url: ndfbr.ajaxUrl,
			data: {
				action: ndfbr.actionSlug + '_get_links',
				_ajax_nonce: ndfbr._ajax_nonce
			}
		} ).then( function( data ) {
			ndfbr.links = data;
			if ( ndfbr.links.length === 1 ) {
				window.location.href = this.links[0];
			} else {
				ndfbr.buildReviewList( ndfbr.links );
			}
		}, function( jqXHR, status, error ) {
			console.log( error );
		} );
	} else {
		ndfbr.buildReviewList( ndfbr.links );
	}
}

ndfbr.buildReviewList = function( links ) {

	var reviewContent =
		'<h2>We need your reviews!</h2>\n' +
		'<p>Please select where you would like to review our business.</p>\n';

    if ( links && links.length !== 0 ) {
        if ( links.length % 2 ) {
            reviewContent += '<ul class="odd">';
        } else {
            reviewContent += '<ul>';
        }

        links.forEach( function( site ) {
            if ( site.logo !== '' ) {
                linkContent = '<img src="' + site.logo + '" alt="' + site.name + '" />';
            } else {
                linkContent = site.name;
            }
            reviewContent += '<li><a href="' + site.url + '">' + linkContent + '</a></li>\n';
        } );

        reviewContent += '</ul>\n';

    } else {
        reviewContent += '<p>No review sites available.</p>';
    }

	reviewContent += '<p class="ndf_br_toggle_sentence">To leave feedback for the website owner directly, <a href="#" onclick="ndfbr.doFeedback()">please click here</a>.</p>';

	jQuery( '.ndf_br_list' ).html( reviewContent ).show();
	jQuery( '.ndf_modal' ).show();
	jQuery( '.ndf_br_contact' ).hide();
}

ndfbr.hideModal = function() {
	jQuery( '.ndf_modal' ).hide();
}

ndfbr.contactForm = jQuery( '#ndf_br_contact_form' );
ndfbr.contactForm.submit( function( event ) {
	event.preventDefault();
	jQuery.post({
		url: ndfbr.ajaxUrl,
		data: {
			url: ndfbr.ajaxUrl,
			action: ndfbr.actionSlug + '_feedback',
			_ajax_nonce: ndfbr._ajax_nonce,
			name: jQuery( 'input[name=ndf_br_name]' ).val(),
			email: jQuery( 'input[name=ndf_br_email]' ).val(),
			message: jQuery( 'textarea[name=ndf_br_message]' ).val(),
		}
	} ).then( function( data ) {
		jQuery( '.ndf_br_contact' ).html(
			'<h2>Your feedback was submitted.</h2>\n' +
			'<p>We look forward to reading your message.</p>\n' +
			'<p><a class="ndf_return_button" href="#" onclick="ndfbr.hideModal();">Return to website</a></p>'
		);
	}, function( error ) {
		console.log( error );
	} );
} );