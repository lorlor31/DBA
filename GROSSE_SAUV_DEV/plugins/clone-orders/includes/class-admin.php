<?php

namespace Vibe\Clone_Orders;

use WC_Order;
use Automattic\WooCommerce\Utilities\OrderUtil;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly

/**
 * Sets up Admin modifications
 *
 * @since 1.0.0
 */
class Admin {

	/**
	 * Creates an instance and sets up the hooks to integrate with the admin
	 */
	public function __construct() {
		add_action( 'woocommerce_order_item_add_action_buttons', array( __CLASS__, 'output_clone_button' ) );
		add_filter( 'woocommerce_admin_order_actions', array( __CLASS__, 'clone_action' ), 10, 2 );

		add_action( 'in_admin_footer', array( __CLASS__, 'output_modal' ) );

		add_action( 'admin_enqueue_scripts', array( __CLASS__, 'enqueue_scripts' ) );
		add_action( 'admin_notices', array( __CLASS__, 'admin_notices' ) );
	}

	/**
	 * Outputs an action button to clone an order if the given order can be cloned
	 *
	 * @param WC_Order $order The order that would be cloned
	 */
	public static function output_clone_button( WC_Order $order ) {
		if ( static::is_cloneable_screen() && Orders::can_clone( $order->get_id() ) ) {
			printf( '<button type="button" class="button clone-order" data-id="%d">%s</button>', esc_attr__( $order->get_id(), 'clone-orders' ), esc_html__( 'Clone order', 'clone-orders' ) );
		}
	}

	/**
	 * Checks whether the current screen is one to display clone functionality on
	 *
	 * By default cloneable screens would be any for the shop_order post type, except adding a new post
	 *
	 * @return bool True if the current screen is one that should include a clone button, false otherwise
	 */
	public static function is_cloneable_screen() {
		$screen        = get_current_screen();
		$screen_base   = isset( $screen->base ) ? $screen->base : '';
		$screen_id     = isset( $screen->id ) ? $screen->id : '';
		$screen_action = isset( $screen->action ) ? $screen->action : '';

		// Check we're on the order screen, which could be the old order post type
		$order_screen      = function_exists( 'wc_get_page_screen_id' ) && wc_get_page_screen_id( 'shop-order' ) == $screen_base;
		$post_order_screen = ( 'post' === $screen_base && 'shop_order' === $screen_id && 'add' !== $screen_action ) || 'edit-shop_order' === $screen_id;

		$is_cloneable = $order_screen || $post_order_screen;

		return apply_filters( Clone_Orders::hook_prefix( 'is_cloneable_screen' ), $is_cloneable, $screen );
	}

	/**
	 * Adds an action for cloning an order if the given order can be cloned
	 *
	 * @param array     $actions Existing order actions
	 * @param WC_Order $order   The order to be cloned
	 *
	 * @return array Updated actions
	 */
	public static function clone_action( array $actions, WC_Order $order ) {
		if ( apply_filters( Clone_Orders::hook_prefix( 'display_action_buttons' ), true, $order ) && Orders::can_clone( $order->get_id() ) ) {
			$action = Clone_Orders::hook_prefix( 'clone_order' );
			$nonce_action = Clone_Orders::hook_prefix( 'cloning-nonce' );

			$actions['clone'] = array(
				'url'    => wp_nonce_url( admin_url( 'admin-ajax.php?action=' . $action . '&items=1&order_id=' . $order->get_id() ), $nonce_action ),
				'name'   => __( 'Clone', 'clone-orders' ),
				'action' => 'clone-order',
			);

			$actions['clone_empty'] = array(
				'url'    => wp_nonce_url( admin_url( 'admin-ajax.php?action=' . $action . '&items=0&order_id=' . $order->get_id() ), $nonce_action ),
				'name'   => __( 'Clone without Items', 'clone-orders' ),
				'action' => 'clone-order-empty',
			);
		}

		return $actions;
	}

	/**
	 * Outputs the HTML for a modal to be used for cloning an order
	 */
	public static function output_modal() {
		if ( static::is_cloneable_screen() ) {
			// Whether to tick clone line items checkbox by default
			$clone_items_checked = apply_filters( Clone_Orders::hook_prefix( 'clone_items_checked' ), false );
			?>
			<script type="text/template" id="tmpl-wc-modal-clone-order">
				<div class="wc-backbone-modal">
					<div class="wc-backbone-modal-content">
						<section class="wc-backbone-modal-main" role="main">
							<header class="wc-backbone-modal-header">
								<h1><?php esc_html_e( 'Clone order', 'clone-orders' ); ?></h1>
								<button class="modal-close modal-close-link dashicons dashicons-no-alt">
									<span class="screen-reader-text">Close modal panel</span>
								</button>
							</header>
							<article id="vibe-clone-orders-modal-options">

								<label for="vibe-clone-orders-clone-items">
									<input
										name="vibe-clone-orders-clone-items"
										id="vibe-clone-orders-clone-items"
										type="checkbox"
										value="1"
										<?php checked( $clone_items_checked ); ?>
									/> <?php esc_html_e( 'Copy products, shipping and other line items to the new order?', 'clone-orders' ); ?>
								</label>

								<input name="vibe-clone-orders-source-id" id="vibe-clone-orders-source-id" type="hidden" value="" />

							</article>
							<footer>
								<div class="inner">
									<span id="vibe-clone-orders-modal-footer-notes"><em><?php esc_html_e( 'This will create a new order. Continue?', 'clone-orders' ); ?></em></span>
									<button id="btn-ok" class="button button-primary button-large"><?php esc_html_e( 'Complete clone', 'clone-orders' ); ?></button>
								</div>
							</footer>
						</section>
					</div>
				</div>
				<div class="wc-backbone-modal-backdrop modal-close"></div>
			</script>
			<?php
		}
	}

	/**
	 * Enqueues scripts and styles on the order admin pages
	 */
	public static function enqueue_scripts() {
		if ( ! static::is_cloneable_screen() ) {
			return;
		}

		$handle = Clone_Orders::hook_prefix( 'js' );

		wp_register_script(
			$handle,
			vibe_clone_orders()->uri( 'assets/js/vibe-clone-orders.min.js' ),
			array( 'jquery' ),
			vibe_clone_orders()->get_version(),
			true
		);
		wp_localize_script( $handle, 'vibe_clone_orders_data', static::script_data() );

		wp_enqueue_script( $handle );

		$handle = Clone_Orders::hook_prefix( 'css' );
		wp_enqueue_style(
			$handle,
			vibe_clone_orders()->uri( 'assets/css/vibe-clone-orders.min.css' ),
			array(),
			vibe_clone_orders()->get_version(),
			'all'
		);
	}

	/**
	 * Sets up data to be passed to front end via script localisation
	 *
	 * @return array An array of data items
	 */
	public static function script_data() {
		$script_data['ajaxurl']       = admin_url( 'admin-ajax.php' );
		$script_data['cloning_nonce'] = wp_create_nonce( Clone_Orders::hook_prefix( 'cloning-nonce' ) );

		return apply_filters( Clone_Orders::hook_prefix( 'script_data' ), $script_data );
	}

	/**
	 * Display any admin notices we have on order pages
	 */
	public static function admin_notices() {
		$screen = get_current_screen();
		if ( ( 'post' == $screen->base && 'shop_order' == $screen->id ) || ( 'edit-shop_order' == $screen->id ) ) {
			static::display_notices();
		}
	}

	/**
	 * Add a notice to be displayed
	 *
	 * @param string $message Message to display on the notice
	 * @param string $type The type of notice, one of error, warning, success or info
	 */
	public static function add_notice( $message, $type = 'info' ) {
		$type    = in_array( $type, array( 'error', 'warning', 'success', 'info' ) ) ? $type : 'info';
		$notices = get_transient( 'vibe_clone_orders_notices' );
		$notices = $notices ? $notices : array();

		array_push( $notices, array(
				'type' => $type,
				'message' => $message
		) );

		set_transient( 'vibe_clone_orders_notices', $notices, MINUTE_IN_SECONDS * 5 );
	}

	/**
	 * Returns the HTML for any notices that are awaiting display and clears them
	 *
	 * @return string HTML string for the notices if there are any
	 */
	public static function get_notices_html() {
		ob_start();

		static::display_notices();

		return ob_get_clean();
	}

	/**
	 * Outputs the HTML for any notices and clears them
	 */
	public static function display_notices() {
		$notices = get_transient( 'vibe_clone_orders_notices' );

		if ( $notices ) {

			foreach ( $notices as $notice ) {
				static::render_notice( $notice['message'], $notice['type'] );
			}

			delete_transient( 'vibe_clone_orders_notices' );
		}
	}

	/**
	 * Output a notice with the given message
	 *
	 * @param string $message Message to display on the notice
	 * @param string $type The type of notice, one of error, warning, success or info
	 */
	public static function render_notice( $message, $type = 'info' ) {
		$type = in_array( $type, array( 'error', 'warning', 'success', 'info' ) ) ? $type : 'info';
		?>

		<div id="message" class="updated notice notice-<?php echo esc_attr( $type ); ?> is-dismissible">
			<p><?php echo wp_kses_post( $message ); ?></p>
			<button type="button" class="notice-dismiss"><span class="screen-reader-text"><?php esc_html_e( 'Dismiss this notice.', 'clone-orders' ); ?></span></button>
		</div>

		<?php
	}

	public static function is_using_hpos() {
		return class_exists('OrderUtil') && OrderUtil::custom_orders_table_usage_is_enabled();
	}
}
