/**
 * Footer rating javascript.
 *
 * @package CreativeMail
 */
jQuery( 'a.ce4wp-rating-link' ).click( function() {
	jQuery.post( 'admin-ajax.php', { action: 'woocommerce_ce4wp_rated' } );
	jQuery( this ).parent().text( jQuery( this ).data( 'rated' ) );
});
