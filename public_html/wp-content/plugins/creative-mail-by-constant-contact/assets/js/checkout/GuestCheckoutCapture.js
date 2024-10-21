// phpcs:disable
/**
 * GuestCheckoutCapture.
 *
 * @package CreativeMail
 */

import validateEmail from 'filter-validate-email';

export default class GuestCheckoutCapture {

    /**
     * @constructor
     *
     * @since 1.3.0
     */
    constructor() {
        this.els = {};
    }

    /**
     * Init public JS.
     *
     * @since 1.3.0
     */
    init() {
        this.cacheEls();
        this.bindEvents();
    }

    /**
     * Cache some DOM elements.
     *
     * @since 1.3.0
     */
    cacheEls() {
        this.els.billingEmail = document.getElementById( 'billing_email' );
        this.els.wcCheckoutNonce = document.getElementById( 'woocommerce-process-checkout-nonce' );
    }

    /**
     * Bind callbacks to events.
     *
     * @since 1.3.0
     */
    bindEvents() {
        this.els.billingEmail.addEventListener( 'focusout', e => {
            if ( validateEmail( e.target.value ) ) {
                this.maybeCaptureGuestCheckout(e.target.value);
            }
        } );
    }

    /**
     * Captures guest checkout if billing email is valid.
     *
     * @since 1.3.0
     *
     * @param {string} emailAddr Billing email address entered by user.
     */
    maybeCaptureGuestCheckout( emailAddr ) {
        wp.ajax.send( 'ce4wp_abandoned_checkouts_capture_guest_checkout', {
            data: {
                nonce: this.els.wcCheckoutNonce.value,
                email: emailAddr
            }
        } );
    }
}