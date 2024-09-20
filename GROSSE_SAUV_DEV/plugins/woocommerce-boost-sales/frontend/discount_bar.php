<?php

/**
 * Class VI_WBOOSTSALES_Frontend_Single
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VI_WBOOSTSALES_Frontend_Discount_Bar {
	protected $settings;
	protected static $coupon;

	public function __construct() {
		$this->settings = VI_WBOOSTSALES_Data::get_instance();
		if ( $this->settings->enable() ) {
			self::$coupon = $this->settings->get_option( 'coupon' );
			if ( $this->settings->get_option( 'enable_discount' ) && self::$coupon ) {
				add_action( 'wp_ajax_wbs_show_bar', array( $this, 'show_bar' ) );
				add_action( 'wp_ajax_nopriv_wbs_show_bar', array( $this, 'show_bar' ) );
				add_action( 'wp_footer', array( $this, 'init_bar' ), 20 );
//				if ( $this->settings->get_option( 'discount_always_show' ) ) {
				add_filter( 'woocommerce_add_to_cart_fragments', array( $this, 'get_refresh_fragment' ) );
//				}
			}
			if ( self::$coupon && $this->settings->get_option( 'enable_discount' ) ) {
				add_action( 'wp_footer', array( $this, 'wp_footer' ) );
			}
		}
	}

	public function get_refresh_fragment( $fragments ) {
		$product_id = filter_input( INPUT_POST, 'add-to-cart', FILTER_SANITIZE_NUMBER_INT );
		$quantity   = filter_input( INPUT_POST, 'quantity', FILTER_SANITIZE_NUMBER_INT );
		if ( ! $product_id || ! $quantity ) {
			$class     = 'woocommerce-boost-sales';
			$style     = '';
			$html      = '';
			$bar       = new VI_WBOOSTSALES_Discount_Bar();
			$show_html = $bar->show_html();
			switch ( $show_html['code'] ) {
				case 200:
					$style .= 'display:none;position:fixed;';
					$html  = $show_html['html'];
					break;
				case 201:
					$html = $show_html['html'];
					if ( ! $this->settings->get_option( 'discount_always_show' ) ) {
						$style .= 'display:none;';
					} else {
						$class .= ' woocommerce-boost-sales-active-discount';
					}
					$style .= 'position:relative;';
					break;
				default:
					$style = 'display:none;';
			}
			$fragments['wbs_discount_bar']          = array(
				'code' => $show_html['code'],
			);
			$fragments['#wbs-content-discount-bar'] = '<div id="wbs-content-discount-bar" class="' . esc_attr( $class ) . '" style="' . esc_attr( $style ) . '">' . $html . '</div>';
		}

		return $fragments;
	}

	public function wp_footer() {
		if ( is_checkout() || is_cart() ) {
			self::apply_coupon( self::$coupon );
		}
	}

	public static function apply_coupon( $coupon_code ) {
		$coupon = new WC_Coupon( $coupon_code );

		if ( ! count( WC()->cart->applied_coupons ) && $coupon->is_valid() ) {
			WC()->cart->apply_coupon( $coupon->get_code() );
		}
	}

	/**
	 * Show bar with ajaxt
	 */
	public function show_bar() {
		global $wbs_language;
		$wbs_language = isset( $_POST['language'] ) ? sanitize_text_field( $_POST['language'] ) : '';
		$bar          = new VI_WBOOSTSALES_Discount_Bar();

		wp_send_json( $bar->show_html() );
	}

	/**
	 * Show HTML Discount bar
	 */
	public function init_bar() {
		$html       = '';
		$class      = 'woocommerce-boost-sales';
		$product_id = filter_input( INPUT_POST, 'add-to-cart', FILTER_SANITIZE_NUMBER_INT );
		$quantity   = filter_input( INPUT_POST, 'quantity', FILTER_SANITIZE_NUMBER_INT );
		if ( $product_id && $quantity ) {
			$style     = '';
			$bar       = new VI_WBOOSTSALES_Discount_Bar();
			$show_html = $bar->show_html();
			switch ( $show_html['code'] ) {
				case 200:
					$html  = $show_html['html'];
					$style = 'position:fixed;';
					break;
				case 201:
					$html = $show_html['html'];
					break;
				default:
			}
			if ( $this->settings->get_option( 'discount_always_show' ) ) {
				$class .= ' woocommerce-boost-sales-active-discount';
			}
		} else {
			if ( $this->settings->get_option( 'discount_always_show' ) ) {
				$style     = '';
				$bar       = new VI_WBOOSTSALES_Discount_Bar();
				$show_html = $bar->show_html();
				switch ( $show_html['code'] ) {
					case 200:
						$style = 'display:none;';
						$html  = $show_html['html'];
						break;
					case 201:
						$html  = $show_html['html'];
						$class .= ' woocommerce-boost-sales-active-discount';
						break;
					default:
						$style = 'display:none;';
				}
			} else {
				$style = 'display:none;';
			}
		}
		?>
        <div id="wbs-content-discount-bar" class="<?php echo esc_attr( $class ) ?>"
             style="<?php echo esc_attr( $style ) ?>">
			<?php echo $html; ?>
        </div>
		<?php
	}
}