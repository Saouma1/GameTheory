// phpcs:disable
/**
 * ConsentCheckoutCapture.
 *
 * @package CreativeMail
 */
export default class ConsentCheckoutCapture {

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
        this.els.ce4wpConsent = document.getElementById('ce4wp_no_consent');
        this.els.wcCheckoutNonce = document.getElementById('woocommerce-process-checkout-nonce');
    }

    /**
     * Bind callbacks to events.
     *
     * @since 1.3.0
     */
    bindEvents() {
        var self = this;
        if (this.els.ce4wpConsent && typeof this.els.ce4wpConsent.addEventListener === "function")
        {
            this.els.ce4wpConsent.addEventListener('click', e => {
                e.preventDefault();
                window.setTimeout(function () {
                    self.noConsentCaptureGuestCheckout();
                }, 1500);
            });
        }
    }

    /**
     * Changes the consent of the checkout.
     *
     * @since 1.3.0
     *
     */
    noConsentCaptureGuestCheckout() {
        var self = this;
        wp.ajax.send('ce4wp_abandoned_checkouts_no_consent_checkout', {
            data: {
                nonce: this.els.wcCheckoutNonce.value
            },
            success: function() {
                self.els.ce4wpConsent.parentElement.style.display = 'none';
            }
        });
    }
}