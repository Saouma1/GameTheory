<?php
/**
 * MemberPress Product Education class.
 *
 * @since 2.13.5
 *
 * @package OMAPI
 * @author  Matt Sparks
 */

// Exit if accessed directly.
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * MemberPress Product Education class.
 *
 * @since 2.13.5
 */
class OMAPI_MemberPress_ProductEducation {
	/**
	 * The post types that we want to add the meta box to.
	 *
	 * @since 2.13.5
	 *
	 * @var array
	 */
	public $post_types = array(
		'memberpressproduct',
		'memberpressgroup',
		'memberpressrule',
		'memberpresscoupon',
		'mp-reminder',
	);

	/**
	 * The path to the SVGs.
	 *
	 * @since 2.13.5
	 *
	 * @var string
	 */
	public $svg_path;

	/**
	 * Primary class constructor.
	 *
	 * @since 2.13.5
	 */
	public function __construct() {
		$this->svg_path = plugin_dir_path( OMAPI_FILE ) . '/assets/images/memberpress/';
	}

	/**
	 * Registers the add_meta_box hook.
	 *
	 * @since 2.13.5
	 *
	 * @return void
	 */
	public function add_meta_box() {
		add_action( 'add_meta_boxes', array( $this, 'meta_box' ) );
	}

	/**
	 * Adds the meta box to the post types.
	 *
	 * @since 2.13.5
	 *
	 * @return void
	 */
	public function meta_box() {
		$output = OMAPI_ApiKey::has_credentials() ? 'meta_box_output_connected' : 'meta_box_output_not_connected';

		foreach ( $this->post_types as $type ) {
			add_meta_box(
				'om-mp-education',
				esc_html__( 'Create a Popup', 'optin-monster-api' ),
				array( $this, $output ),
				$type,
				'side',
				'default'
			);
		}
	}

	/**
	 * Outputs the meta box content when connected.
	 *
	 * @since 2.13.5
	 *
	 * @return void
	 */
	public function meta_box_output_connected() {
			$explore_text = __( 'Explore Templates', 'optin-monster-api' );

			$type_buttons = array(
				'popup'    => __( 'Create a Popup', 'optin-monster-api' ),
				'floating' => __( 'Create a Floating Bar', 'optin-monster-api' ),
				'slide'    => __( 'Create a Slide-in', 'optin-monster-api' ),
				'full'     => __( 'Create a Fullscreen', 'optin-monster-api' ),
				'inline'   => __( 'Create a Inline', 'optin-monster-api' ),
			);
			?>
		<div class="om-mp-education">
			<div class="om-mp-education-love">
				<?php include $this->svg_path . 'love.svg'; ?>
			</div>
			<p class="om-mp-education-description">
				<?php esc_html_e( 'Create a Targeted Offer', 'optin-monster-api' ); ?>
			</p>
			<div class="om-mp-education-body">
				<nav>
					<ul>
						<?php foreach ( $type_buttons as $type => $text ) : ?>
							<li>
								<a href="<?php echo esc_url( OMAPI_Urls::templates( array( 'type' => $type ) ) ); ?>" title="<?php echo esc_attr( $text ); ?>" class="om-mp-cta">
									<?php
										include $this->svg_path . $type . '.svg';
										echo esc_html( $text );
									?>
								</a>
							</li>
						<?php endforeach; ?>
					</ul>
				</nav>
			</div>
			<a href="<?php echo esc_url( OMAPI_Urls::templates() ); ?>" title="<?php echo esc_attr( $explore_text ); ?>" class="om-mp-button">
				<?php echo esc_html( $explore_text ); ?>
			</a>
		</div>
		<?php
	}

	/**
	 * Outputs the meta box content when not connected.
	 *
	 * @since 2.13.5
	 *
	 * @return void
	 */
	public function meta_box_output_not_connected() {
			$get_started_text = __( 'Get Started For Free', 'optin-monster-api' );
		?>
		<div class="om-mp-education">
			<div class="om-mp-education-love">
				<?php include $this->svg_path . 'love.svg'; ?>
			</div>
			<p class="om-mp-education-description not-connected">
				<?php esc_html_e( 'Show Popups, Floating Bars, and More to Members and Visitors with OptinMonster', 'optin-monster-api' ); ?>
			</p>
			<div class="om-mp-education-body">
				<ul class="om-mp-education-benefits">
					<li><?php echo esc_html_x( '...for active members of specific memberships or groups.', 'benefits of using OptinMonster with MemberPress', 'optin-monster-api' ); ?></li>
					<li><?php echo esc_html_x( '...on MemberPress pages such as Register, Checkout, and Thank You.', 'benefits of using OptinMonster with MemberPress', 'optin-monster-api' ); ?></li>
					<li><?php echo esc_html_x( '...on Group pages, Membership pages, Courses, Lessons, and Quizzes', 'benefits of using OptinMonster with MemberPress', 'optin-monster-api' ); ?></li>
					<li><?php echo esc_html_x( 'And tons more!', 'benefits of using OptinMonster with MemberPress', 'optin-monster-api' ); ?></li>
				</ul>
			</div>
			<a href="<?php echo esc_url( OMAPI_Urls::onboarding() ); ?>" title="<?php echo esc_attr( $get_started_text ); ?>" class="om-mp-button">
				<?php echo esc_html( $get_started_text ); ?>
			</a>
		</div>
		<?php
	}
}
