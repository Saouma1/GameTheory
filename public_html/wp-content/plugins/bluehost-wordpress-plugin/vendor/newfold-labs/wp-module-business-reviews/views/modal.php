<?php
/**
 * Markup for Business Reviews modal window
 */
?>
<div class="nfd_modal nfd_modal_bg" onclick="nfdbr.hideModal();"></div>
<div class="nfd_modal nfd_modal_content" role="dialog">
	<div class="nfd_br_close" onclick="nfdbr.hideModal();"><?php echo file_get_contents( plugin_dir_path( dirname( __FILE__ ) ) . 'assets/images/times.svg' ); ?></div>
	<div class="nfd_br_list" style="display:none;"></div>
	<div class="nfd_br_contact" style="display:none;">
		<h2>Thank you for helping us improve</h2>
		<p>Please tell us more so we can address your concerns.</p>
		<form action="/" method="POST" id="nfd_br_contact_form">
			<label for="nfd_br_contact_name">Your name<sup>*</sup></label><br />
			<input id="nfd_br_contact_name" type="text" name="nfd_br_name" value="" placeholder="Joe Smith" /><br />
			<label for="nfd_br_contact_email">Email<sup>*</sup></label><br />
			<input id="nfd_br_contact_email" type="email" name="nfd_br_email" value="" placeholder="joesmith@example.com" /><br />
			<label for="nfd_br_contact_message">Message</label><br />
			<textarea id="nfd_br_contact_message" id="nfd_br_message" name="nfd_br_message" value="" placeholder="Please write your feedback here. We look forward to addressing your concerns."></textarea><br />
			<p class="nfd_br_toggle_sentence">If you do not want us to address your concerns, <a href="#" onclick="nfdbr.doReview()">please continue to reviews</a>.</p>
			<input type="submit" value="Submit" />
		</form>
	</div>
</div>