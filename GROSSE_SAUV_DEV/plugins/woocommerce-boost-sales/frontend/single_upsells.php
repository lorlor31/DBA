<?php

/**
 * Class VI_WBOOSTSALES_Frontend_Single
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VI_WBOOSTSALES_Frontend_Single_Upsells {
	protected $settings;

	public function __construct() {
		$this->settings = VI_WBOOSTSALES_Data::get_instance();
		if ( $this->settings->enable() ) {
			if ( $this->settings->get_option( 'enable_upsell' ) ) {
				add_action( 'wp_footer', array( $this, 'init_upsells' ) );
				if ( $this->settings->get_option( 'ajax_add_to_cart_for_upsells' ) ) {
					add_filter( 'woocommerce_add_to_cart_fragments', array(
						$this,
						'woocommerce_add_to_cart_fragments'
					), 99 );
				}
				if ( $this->settings->get_option( 'show_recently_viewed_products' ) ) {
					add_action( 'template_redirect', array( $this, 'track_product_view' ), 21 );
				}
			}
		}
	}

	public function woocommerce_add_to_cart_fragments( $fragments ) {
		$cart       = WC()->cart;
		$total_cart = $cart->get_cart_subtotal();
//		if ( wc_tax_enabled() ) {
//			$tax        = $cart->get_cart_contents_tax();
//			$total_cart += $tax;
//		}
		$number_cart                          = $cart->get_cart_contents_count();
		$temporary_number                     = sprintf( _n( 'Your current cart(%s product): %s', 'Your current cart(%s products): %s', $number_cart, 'woocommerce-boost-sales' ), $number_cart, $total_cart );
		$fragments['.wbs-current_total_cart'] = '<p class="wbs-current_total_cart">' . $temporary_number . '</p>';

		return $fragments;
	}

	/**
	 * Track product views.
	 */
	public function track_product_view() {
		if ( ! is_singular( 'product' ) ) {
			return;
		}
		if ( is_active_widget( false, false, 'woocommerce_recently_viewed_products', true ) ) {
			return;
		}

		global $post;

		if ( empty( $_COOKIE['woocommerce_recently_viewed'] ) ) { // @codingStandardsIgnoreLine.
			$viewed_products = array();
		} else {
			$viewed_products = wp_parse_id_list( (array) explode( '|', wp_unslash( $_COOKIE['woocommerce_recently_viewed'] ) ) ); // @codingStandardsIgnoreLine.
		}

		// Unset if already in viewed products list.
		$keys = array_flip( $viewed_products );

		if ( isset( $keys[ $post->ID ] ) ) {
			unset( $viewed_products[ $keys[ $post->ID ] ] );
		}

		$viewed_products[] = $post->ID;

		if ( count( $viewed_products ) > 15 ) {
			array_shift( $viewed_products );
		}
		// Store for session only.
		wc_setcookie( 'woocommerce_recently_viewed', implode( '|', $viewed_products ) );
	}

	/**
	 * Show HTML code
	 */
	public function init_upsells() {
		$upsell_exclude_products = $this->settings->get_option( 'upsell_exclude_products' );
		if ( in_array( get_the_ID(), $upsell_exclude_products ) ) {
			return;
		}

		/*Get data form submition*/
		$product_id = filter_input( INPUT_POST, 'add-to-cart', FILTER_SANITIZE_NUMBER_INT );
		if ( ! $product_id ) {
			$product_id = filter_input( INPUT_GET, 'add-to-cart', FILTER_SANITIZE_NUMBER_INT );
		}
		if ( ! $product_id ) {
			if ( is_plugin_active( 'woo-sticky-add-to-cart/woo-sticky-add-to-cart.php' ) ) {
				$vi_satc_settings     = new VI_WOO_STICKY_ATC_DATA();
				$vi_wsatc_sb_ajax_atc = $vi_satc_settings->get_params( 'sb_ajax_atc' );
			}
			if ( ! $product_id && ! $this->settings->get_option( 'ajax_button' ) && empty( $vi_wsatc_sb_ajax_atc ) ) {
				return;
			}
		} else {
			if ( is_cart() && ! is_product() && $this->settings->get_option( 'hide_on_cart_page' ) ) {
				return;
			}
			if ( is_checkout() && ! is_product() && $this->settings->get_option( 'hide_on_checkout_page' ) ) {
				return;
			}
			if ( ! isset( VI_WBOOSTSALES_Frontend_Upsells::$added_to_cart[ $product_id ] ) ) {
				return;
			}
		}

		$variation_id = filter_input( INPUT_POST, 'variation_id', FILTER_SANITIZE_NUMBER_INT );

		if ( ! in_array( $product_id, $upsell_exclude_products ) ) {
			if ( $product_id || is_product() ) {
				if ( ! $product_id ) {
					$class      = 'wbs-ajax-loaded';
					$product_id = get_the_ID();
				} else {
					$class = 'wbs-form-submit';
				}
				$html = $this->show_product( $product_id, $variation_id );
				if ( $html ) {
					?>
                    <div id="wbs-content-upsells"
                         class="woocommerce-boost-sales wbs-content-up-sell wbs-single-page <?php echo esc_attr( $class ) ?>">
						<?php echo $html; ?>
                    </div>
					<?php
				} else {
					return;
				}
			}
		}
	}

	/**
	 * @param $product_id
	 * @param $variation_id
	 *
	 * @return false|string
	 */
	protected function show_product( $product_id, $variation_id ) {
		$upsells  = VI_WBOOSTSALES_Frontend_Upsells::get_upsells_ids( $product_id );
		$quantity = filter_input( INPUT_POST, 'quantity', FILTER_SANITIZE_NUMBER_INT );
		if ( ! $quantity ) {
			$quantity = 1;
		}
		$obj_upsell = new VI_WBOOSTSALES_Upsells( $product_id, $quantity, $upsells, $variation_id, VI_WBOOSTSALES_Frontend_Upsells::$cart_item_key );

		return $obj_upsell->show_html();
	}
}