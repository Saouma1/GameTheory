<?php
/**
 * This file adds support for the business reviews module
 */

class NFD_Business_Reviews {

	public $slug = 'nfdbr';

	public $name = 'nfd-business-reviews';

	public $sites_endpoint = 'https://my.bluehost.com/siteapi/sites';

	public $domain;

	/**
	 * Singleton
	 *
	 * @return NFD_Business_Reviews
	 */
	static function init() {
		static $instance = false;
		if ( ! $instance ) {
			$instance = new NFD_Business_Reviews();
		}
		return $instance;
	}

	/**
	 * NFD_Business_Reviews constructor.
	 */
	public function __construct() {

		include 'class-business-reviews-widget.php';

		$this->domain = parse_url( get_option( 'siteurl' ), PHP_URL_HOST );

		// Set up the widget
		add_action( 'widgets_init', array( $this, 'widget_init' ) );

		// Register and load our JS
		add_action( 'wp_loaded', array( $this, 'register_assets' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );
		add_action( 'wp_enqueue_scripts', array( $this, 'localize_scripts' ) );

		// Register our ajax actions
		add_action( "wp_ajax_{$this->slug}_feedback", array( $this, 'ajax_send_email' ) );
		add_action( "wp_ajax_nopriv_{$this->slug}_feedback", array( $this, 'ajax_send_email' ) );
		add_action( "wp_ajax_{$this->slug}_get_links", array( $this, 'ajax_get_links' ) );
		add_action( "wp_ajax_nopriv_{$this->slug}_get_links", array( $this, 'ajax_get_links' ) );

	}

	/**
	 * Register required javascript and CSS files
	 */
	public function register_assets() {

		wp_register_script( $this->name, plugin_dir_url( __FILE__ ) . '/assets/js/business-reviews.js', array( 'jquery' ), filemtime( plugin_dir_path( __FILE__ ) . 'assets/js/business-reviews.js' ), true );
		wp_register_style( $this->name, plugin_dir_url( __FILE__ ) . '/assets/css/business-reviews.css', array(), filemtime( plugin_dir_path( __FILE__ ) . 'assets/css/business-reviews.css' ) );

	}

	/**
	 * Enqueue required javascript files
	 */
	public function enqueue_scripts() {

		wp_enqueue_script( $this->name );

	}

	/**
	 * Add localized script to handle ajax nonce
	 */
	public function localize_scripts() {

		$nonce    = wp_create_nonce( "{$this->slug}_action" );
		$localize = array(
			'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
			'actionSlug'  => $this->slug,
			'_ajax_nonce' => $nonce,
		);

		wp_localize_script( $this->name, $this->slug, $localize );

	}

	/**
	 * Initialize and register the Business Reviews widget
	 */
	public function widget_init() {

		register_widget( 'NFD_Business_Reviews_Widget' );

	}

	/**
	 * Handle our AJAX response from the front-end
	 */
	public function ajax_get_links() {

		if ( ! check_ajax_referer( "{$this->slug}_action", '_ajax_nonce', false ) ) {
			wp_send_json_error( check_ajax_referer( "{$this->slug}_action", '_ajax_nonce', false ) );
		};

		wp_send_json( $this->get_links() );

	}

	/**
	 * Handle AJAX POST on the contact form
	 */
	public function ajax_send_email() {

		if ( ! check_ajax_referer( "{$this->slug}_action", '_ajax_nonce', false ) ) {
			wp_send_json_error( check_ajax_referer( "{$this->slug}_action", '_ajax_nonce', false ) );
		};


		// This is where we process the data and actually send the email
		if ( is_email( $_POST['email'] ) ) {
			$email = sanitize_email( $_POST['email'] );
		} else {
			wp_send_json_error( 'Invalid Email' );
		}

		$name      = sanitize_text_field( $_POST['name'] );
		$message   = sanitize_textarea_field( $_POST['message'] );
		$headers[] = 'From: ' . $name . '<' . $email . '>';

		wp_mail( $this->get_email(), 'Website Feedback', $message, $headers );

		wp_send_json( 'Thanks for your feedback!' );

	}

	/**
	 * Hits the Business Reviews API to get list of sites user has configured
	 *
	 * @return mixed Array of URLs configured for Business Reviews
	 */
	public function get_links() {

		$links = array();

		// If the current user is an editor or greater, don't use any cached value
		if ( ! current_user_can( 'edit_posts' ) ) {
			$review_sites = get_transient( 'nfd_business_reviews' );
		} else {
			$review_sites = false;
		}

		if ( false === $review_sites ) {
			$request  = wp_remote_get( $this->sites_endpoint . '/' . $this->domain . '/' . mojo_site_bin2hex() . '/business-reviews' );
			$response = json_decode( wp_remote_retrieve_body( $request ) );
			if ( $response->status && 'disabled' !== $response->status ) {
				$review_sites = $response->review_sites;
				// Cache results for 1 hour
				set_transient( 'nfd_business_reviews', $review_sites, 3600 );
			}
		}

		if ( $review_sites ) {
			foreach ( $review_sites as $site ) {
				$links[] = $this->get_site_data( $site->url );
			}
		}

		return $links;

	}

	/**
	 * Returns email address to be used for receiving feedback from Business Reviews widget
	 *
	 * @return string Email address the user has specified or the admin email if not set
	 */
	public function get_email() {

		return get_option( 'business_reviews_email', get_option( 'admin_email' ) );

	}

	/**
	 * Parse supplied URL to determine additional data about the URL for use in review buttons
	 * @param string $url URL of the review site
	 *
	 * @return array Name, logo and URL of the review site for use in review button
	 */
	public function get_site_data( $url ) {

		$supported_sites = array(
			'#https?://(www)?\.facebook\.com.*#i' => array(
				'name' => 'Facebook',
				'logo' => plugin_dir_url( __FILE__ ) . 'assets/images/facebook.svg',
			),
			'#https?://(www)?\.google\.com.*#i'   => array(
				'name' => 'Google',
				'logo' => plugin_dir_url( __FILE__ ) . 'assets/images/google.svg',
			),
			'#https?://(www)?\.yelp\.com.*#i'     => array(
				'name' => 'Yelp',
				'logo' => plugin_dir_url( __FILE__ ) . 'assets/images/yelp.svg',
			),
		);

		foreach ( $supported_sites as $regex => $data ) {
			if ( preg_match( $regex, $url ) ) {
				$data['url'] = $url;
				return $data;
			}
		}

		// We didn't match a supported site, so try to parse the domain
		$data = array(
			'name' => str_replace( 'www.', '', parse_url( $url, PHP_URL_HOST ) ),
			'logo' => '',
			'url'  => $url,
		);

		return $data;

	}

}

/**
 * Initialize Business Reviews
 */
NFD_Business_Reviews::init();
