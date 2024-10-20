<?php
namespace CreativeMail\Modules\WooCommerce\Emails;

use WC_Email;
use WC_Order;

class AbandonedCartEmail extends WC_Email {
	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->id             = 'cart_abandoned_ce4wp';
		$this->title          = __( 'Abandoned cart', 'creative-mail-by-constant-contact' );
		$this->description    = __( 'Send customers a reminder after they abandoned their shopping cart', 'creative-mail-by-constant-contact' );
		$this->customer_email = true;
		$this->enabled        = 'no';

		// We want all the parent's methods, with none of its properties, so call its parent's constructor, rather than my parent constructor.
		parent::__construct();
	}

	/**
	 * Triggers the email.
	 *
	 * @param WC_Order $order The order.
	 */
	public function trigger( $order ) {}
}
