<?php

namespace Vibe\Clone_Orders;

use WC_Data_Exception;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly

/**
 * AJAX request handlers
 *
 * @since 1.0.0
 */
class AJAX {

	/**
	 * Creates an instance and sets up the AJAX actions
	 */
	public function __construct() {
		$action = Clone_Orders::hook_prefix( 'clone_order' );
		add_action( "wp_ajax_{$action}", array( __CLASS__, 'clone_order' ) );
	}

	/**
	 * Handles an AJAX request to clone an order
	 *
	 * If a request argument with name 'response' and value 'json' is provided a JSON response will be returned
	 * containing a success flag and any notices. Otherwise a redirect is performed to the orders listing.
	 */
	public static function clone_order() {
		$success       = false;
		$nonce         = isset( $_REQUEST['_wpnonce'] ) ? wc_clean( $_REQUEST['_wpnonce'] ) : false;
		$response_type = isset( $_REQUEST['response'] ) ? wc_clean( $_REQUEST['response'] ) : false;

		if ( ! empty( $nonce ) && wp_verify_nonce( $nonce, Clone_Orders::hook_prefix( 'cloning-nonce' ) ) ) {
			$order_id = isset( $_REQUEST['order_id'] ) ? absint( $_REQUEST['order_id'] ) : false;

			if ( Orders::can_clone( $order_id ) ) {
				try {
					$items       = isset( $_REQUEST['items'] ) ? boolval( $_REQUEST['items'] ) : false;
					$meta_fields = Settings::meta_fields();

					$clone = Orders::clone_order( $order_id, $items, $meta_fields );
					/* translators: 1: Link to the new order 2: The order number of the new order */
					$message = sprintf( __( 'Successfully cloned to order <a href="%1$s">#%2$s</a>', 'clone-orders' ), get_edit_post_link( $clone->get_id() ), $clone->get_order_number() );

					Admin::add_notice( $message, 'success' );
					$success = ! empty( $clone );
				} catch ( WC_Data_Exception $e ) {
					$success = false;
					Admin::add_notice( __( 'Error occurred creating order', 'clone-orders' ), 'error' );
				}
			}
		}

		if ( 'json' === $response_type ) {
			$response = array(
				'success' => $success,
				'notices' => Admin::get_notices_html()
			);

			wp_send_json( $response );
		} else {
			wp_safe_redirect( wp_get_referer() ? wp_get_referer() : admin_url( 'edit.php?post_type=shop_order' ) );
			exit;
		}
	}
}
