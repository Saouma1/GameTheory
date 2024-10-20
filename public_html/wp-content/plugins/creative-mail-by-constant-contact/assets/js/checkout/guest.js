/**
 * Javascript.
 *
 * @package CreativeMail
 */

import GuestCheckoutCapture from './GuestCheckoutCapture';

// Capture abandoned carts from non-logged-in users.
const MyGuestCheckoutCapture = new GuestCheckoutCapture();
MyGuestCheckoutCapture.init();
