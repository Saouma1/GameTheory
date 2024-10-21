/**
 * Feedback notice javascript.
 *
 * @package CreativeMail
 */
jQuery(function($){

	window.addEventListener('load', () => {
		const parent          = document.getElementById('wpbody-content')
		const screenMetaLinks = document.getElementById('screen-meta-links')

		const notice = document.getElementById('ce4wp-admin-feedback-notice')

		if ([parent, screenMetaLinks, notice].some(element => element == null)) {
			return
		}

		parent.insertBefore(notice, screenMetaLinks.nextSibling)
		notice.hidden = false
	});

});

function hideAdminFeedbackNotice (banner) {
	document.querySelector('#ce4wp-admin-feedback-notice').hidden = true

	const { hide_banner_url } = ce4wp_data
	fetch(`${hide_banner_url}${banner}`, { method: 'POST' })
}
