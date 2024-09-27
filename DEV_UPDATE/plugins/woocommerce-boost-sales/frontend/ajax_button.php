<?php

/**
 * Class VI_WBOOSTSALES_Frontend_Ajax_Button
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VI_WBOOSTSALES_Frontend_Ajax_Button {
	protected $settings;

	public function __construct() {
		$this->settings = VI_WBOOSTSALES_Data::get_instance();
		/*Check global enable*/
		if ( $this->settings->enable() ) {
			/*Check upsell enable*/
			if ( $this->settings->get_option( 'enable_upsell' ) ) {
				if ( $this->settings->get_option( 'ajax_button' ) ) {
					add_action( 'woocommerce_before_add_to_cart_form', function () {
						global $product;
						if ( ! $product->is_type( 'grouped' ) ) {
							add_action( 'woocommerce_after_add_to_cart_quantity', array( $this, 'ajax_button_add_to_cart' ), 30 );
						} else {
							add_action( 'woocommerce_before_add_to_cart_button', array( $this, 'ajax_button_add_to_cart' ), 1 );
						}
					} );

					add_action( 'wp_ajax_nopriv_wbs_ajax_add_to_cart', array( $this, 'ajax_add_to_cart' ) );
					add_action( 'wp_ajax_wbs_ajax_add_to_cart', array( $this, 'ajax_add_to_cart' ) );
					add_filter( 'woocommerce_add_to_cart_redirect', [ $this, 'prevent_add_to_cart_redirect' ], 1 );
				}
			}
		}
	}

	/**
	 * Ajax add to cart
	 */
	public function ajax_add_to_cart() {
//		$quantity     = isset( $_POST['quantity'] ) ? sanitize_text_field( $_POST['quantity'] ) : '';
		$variation_id = isset( $_POST['variation_id'] ) ? sanitize_text_field( $_POST['variation_id'] ) : '';
//		$product_id      = isset( $_POST['product_id'] ) ? sanitize_text_field( $_POST['product_id'] ) : '';
		$variation_image = '';
		if ( $variation_id ) {
			$variation = wc_get_product( $variation_id );
			if ( $variation ) {
				$variation_image_id = $variation->get_image_id();
				if ( $variation_image_id ) {
					$variation_image = wp_get_attachment_image_url( $variation_image_id, 'woocommerce_thumbnail' );
				}
			}
		}
		$discount_bar = new VI_WBOOSTSALES_Discount_Bar();
		$cart         = WC()->cart;
		$total_cart   = $cart->get_cart_subtotal();
//		if ( wc_tax_enabled() ) {
//			$tax        = $cart->get_cart_contents_tax();
//			$total_cart += $tax;
//		}
		$number_cart      = $cart->get_cart_contents_count();
		$temporary_number = sprintf( _n( 'Your current cart(%s product): %s', 'Your current cart(%s products): %s', $number_cart, 'woocommerce-boost-sales' ), $number_cart, $total_cart );
		wp_send_json( array(
				'html'                => wc_print_notices( true ),
				'variation_image_url' => $variation_image,
				'discount_bar_html'   => $discount_bar->show_html(),
				'added_to_cart'       => VI_WBOOSTSALES_Frontend_Upsells::$added_to_cart,
				'total'               => $temporary_number,
				'grouped_quantity'    => VI_WBOOSTSALES_Frontend_Upsells::$grouped_quantity,
				'grouped_total'       => wc_price( VI_WBOOSTSALES_Frontend_Upsells::$grouped_total ),
			)
		);
	}

	/**
	 * Show Ajax add to cart button on single page
	 *
	 * @param array $args
	 */
	public function ajax_button_add_to_cart( $args = array() ) {
		if ( ! is_product() ) {
			return;
		}
		global $product;

		if ( $product ) {
			if ( apply_filters( 'wbs_custom_ajax_add_to_cart_button_for_product', ! in_array( $product->get_type(), [ 'assorted_product', 'bundle', 'grouped', 'booking' ] ), $product ) ) {
				$defaults = array(
					'quantity'   => 1,
					'class'      => implode(
						' ', array_filter(
							array(
								'button alt single_add_to_cart_button',
								'product_type_' . $product->get_type(),
								$product->is_purchasable() && $product->is_in_stock() ? 'add_to_cart_button' : '',
								'wbs-ajax-add-to-cart',
							)
						)
					),
					'attributes' => array(
						'data-product_id'  => $product->get_id(),
						'data-product_sku' => $product->get_sku(),
						'aria-label'       => $product->add_to_cart_description(),
						'rel'              => 'nofollow',
					),
//				'name'=>'add-to-cart'
				);

				$args = apply_filters( 'wbs_woocommerce_loop_add_to_cart_args', wp_parse_args( $args, $defaults ), $product );

				wbs_get_template( 'single-product/add-to-cart/add-to-cart.php', $args, '', VI_WBOOSTSALES_TEMPLATES );
			} else {
				?>
                <button type="submit" class="single_add_to_cart_button wbs-ajax-add-to-cart wbs-is-grouped button alt<?php echo esc_attr( wc_wp_theme_get_element_class_name( 'button' ) ? ' ' . wc_wp_theme_get_element_class_name( 'button' )
					: '' );
				?>">
					<?php echo esc_html( $product->single_add_to_cart_text() ); ?>
                </button>

				<?php
			}
		}
	}

	public function prevent_add_to_cart_redirect( $args ) {
		if ( ! empty( $_REQUEST['action'] ) && $_REQUEST['action'] == 'wbs_ajax_add_to_cart' ) {
			remove_all_filters( 'woocommerce_add_to_cart_redirect' );
		}

		return $args;
	}
}