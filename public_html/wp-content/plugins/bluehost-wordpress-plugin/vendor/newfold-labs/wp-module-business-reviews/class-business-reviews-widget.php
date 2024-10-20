<?php

/**
 * Class NFD_Business_Reviews_Widget
 */
class NFD_Business_Reviews_Widget extends WP_Widget {

	/**
	 * NFD_Business_Reviews_Widget constructor.
	 */
	public function __construct() {
		$widget_options = array(
			'classname'   => 'nfd_business_reviews',
			'description' => __( 'Add buttons to your website to allow visitors to share feedback or reviews on Facebook, Yelp, Google or anywhere your visitors can post reviews.', 'newfold-business-reviews' ),
		);

		parent::__construct(
			'nfd_business_reviews',
			'Write a Review',
			$widget_options
		);

	}

	/**
	 * Output the widget content
	 * @param array $args
	 * @param array $instance
	 */
	public function widget( $args, $instance ) {
		add_action( 'wp_footer', array( $this, 'modal_html' ) );
		wp_enqueue_style( 'nfd-business-reviews' );
		echo $args['before_widget'];
		echo $args['before_title'] . 'Write a Review' . $args['after_title'];
		include 'views/widget.php';
		echo $args['after_widget'];
	}

	/**
	 * Handle the output of the initial modal window content
	 *
	 * This gets hooked into wp_footer and output at the bottom of the page to avoid any CSS issues that might be
	 * caused by sidebar placement (floats, etc).
	 */
	public function modal_html() {
		include 'views/modal.php';
	}

}