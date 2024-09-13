<?php

/**
 * Class VI_WBOOSTSALES_Frontend_Notify
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

use Automattic\Jetpack\Constants;

class VI_WBOOSTSALES_Frontend_Scripts {
	protected $settings;
	protected $message;
	protected $auto_open_cart;

	public function __construct() {
		$this->settings       = VI_WBOOSTSALES_Data::get_instance();
		$this->auto_open_cart = false;
		if ( $this->settings->enable() ) {
			add_action( 'wp_enqueue_scripts', array( $this, 'init_scripts' ), 99999 );
			add_action( 'wp_enqueue_scripts', array( $this, 'init_scripts_side_cart' ), 999999 );
//			add_action( 'wp_ajax_nopriv_wbs_set_notice', array( $this, 'wbs_set_notice' ) );
//			add_action( 'wp_ajax_wbs_set_notice', array( $this, 'wbs_set_notice' ) );
		}
		/*WordPress lower 4.5*/
//		add_action( 'wp_print_scripts', array( $this, 'custom_script' ) );
		add_action( 'woocommerce_before_main_content', array( $this, 'ajax_add_to_cart_notices' ) );
	}

	public function ajax_add_to_cart_notices() {
		?>
        <div class="wbs-add-to-cart-notices-ajax"></div>
		<?php
	}

	public function wbs_set_notice() {
		$product_id = isset( $_GET['product_id'] ) ? wc_clean( $_GET['product_id'] ) : '';
		$notices    = '';
		if ( $product_id && function_exists( 'wc_add_to_cart_message' ) ) {
			$notices .= '<div class="woocommerce-message">' . wc_add_to_cart_message( $product_id, false, true ) . '</div>';
		}
		wp_send_json( array( 'html' => $notices ) );
		die;
	}

	/**
	 * Script in Wp 4.2
	 */

	public function custom_script() {
		$script = 'var wboostsales_ajax_url = "' . admin_url( 'admin-ajax.php' ) . '"'; ?>
        <script type="text/javascript" data-cfasync="false">
			<?php echo $script; ?>
        </script>
		<?php
	}

	/**
	 * Auto open cart feature of WooCommerce Side cart for non-ajax crosssell
	 */
	public function init_scripts_side_cart() {
		if ( $this->auto_open_cart !== false && isset( $_POST['add-to-cart'], $_POST['quantity'] ) && $_POST['add-to-cart'] && $_POST['quantity'] ) {
			if ( WP_DEBUG || Constants::is_true( 'SCRIPT_DEBUG' ) ) {
				$js_ext = '.js';
			} else {
				$js_ext = '.min.js';
			}
			wp_enqueue_script( 'woocommerce-boost-sales-woo-side-cart-script', VI_WBOOSTSALES_JS . 'woocommerce-boost-sales-side-cart' . $js_ext, array(
				'jquery',
				'xoo-wsc'
			) );
		}
	}

	/**
	 * Add Script and Style
	 */

	public function init_scripts() {
		global $wbs_language;
		$wbs_language = '';
		if ( is_plugin_active( 'sitepress-multilingual-cms/sitepress.php' ) ) {
			$default_lang     = apply_filters( 'wpml_default_language', null );
			$current_language = apply_filters( 'wpml_current_language', null );

			if ( $current_language && $current_language !== $default_lang ) {
				$wbs_language = $current_language;
			}
		} else if ( class_exists( 'Polylang' ) ) {
			$default_lang     = pll_default_language( 'slug' );
			$current_language = pll_current_language( 'slug' );
			if ( $current_language && $current_language !== $default_lang ) {
				$wbs_language = $current_language;
			}
		}
		/*Check mobile*/
		$select_template                  = $this->settings->get_option( 'select_template' );
		$button_color                     = $this->settings->get_option( 'button_color' );
		$button_bg_color                  = $this->settings->get_option( 'button_bg_color' );
		$process_color                    = $this->settings->get_option( 'process_color' );
		$custom_gift_image                = $this->settings->get_option( 'custom_gift_image' );
		$process_background_color         = $this->settings->get_option( 'process_background_color' );
		$text_color_discount              = $this->settings->get_option( 'text_color_discount' );
		$bg_color_cross_sell              = $this->settings->get_option( 'bg_color_cross_sell' );
		$bg_image_cross_sell              = $this->settings->get_option( 'bg_image_cross_sell' );
		$text_color_cross_sell            = $this->settings->get_option( 'text_color_cross_sell' );
		$price_text_color_cross_sell      = $this->settings->get_option( 'price_text_color_cross_sell' );
		$save_price_text_color_cross_sell = $this->settings->get_option( 'save_price_text_color_cross_sell' );
		$custom_css                       = $this->settings->get_option( 'custom_css' );
		$icon_color                       = $this->settings->get_option( 'icon_color' );
		$icon_bg_color                    = $this->settings->get_option( 'icon_bg_color' );
		if ( WP_DEBUG || Constants::is_true( 'SCRIPT_DEBUG' ) ) {
			$css_ext = '.css';
			$js_ext  = '.js';
		} else {
			$css_ext = '.min.css';
			$js_ext  = '.min.js';
		}
		/*Flexslider*/

		wp_enqueue_style( 'jquery-vi_flexslider', VI_WBOOSTSALES_CSS . 'vi_flexslider' . $css_ext, array(), '2.7.0' );
		wp_enqueue_script( 'jquery-vi_flexslider', VI_WBOOSTSALES_JS . 'jquery.vi_flexslider' . $js_ext, array( 'jquery' ), '2.7.0', true );
		wp_enqueue_style( 'woocommerce-boost-sales', VI_WBOOSTSALES_CSS . 'woocommerce-boost-sales' . $css_ext, array(), VI_WBOOSTSALES_VERSION );
		if ( $select_template == '2' ) {
			wp_enqueue_style( 'woocommerce-boost-sales-template2', VI_WBOOSTSALES_CSS . 'styles/style-2' . $css_ext, array(), VI_WBOOSTSALES_VERSION );
		}
		if ( is_rtl() ) {
			wp_enqueue_style( 'woocommerce-boost-sales-rtl', VI_WBOOSTSALES_CSS . 'woocommerce-boost-sales-rtl' . $css_ext, array(), VI_WBOOSTSALES_VERSION );
		}

		wp_enqueue_script( 'woocommerce-boost-sales', VI_WBOOSTSALES_JS . 'woocommerce-boost-sales' . $js_ext, array(
			'jquery',
			'jquery-vi_flexslider'
		), VI_WBOOSTSALES_VERSION, true );

		$gl_options         = get_option( 'xoo-wsc-gl-options', array() );
		$auto_open_cart     = isset( $gl_options['sc-auto-open'] ) ? $gl_options['sc-auto-open'] : 1;
		$auto_redirect_time = $this->settings->get_option( 'redirect_after_second' );
		wp_localize_script( 'woocommerce-boost-sales', 'woocommerce_boost_sales_params', array(
				'ajax_add_to_cart_for_upsells'    => ( $this->settings->get_option( 'ajax_add_to_cart_for_upsells' ) ) ? 'yes' : 'no',
				'ajax_add_to_cart_for_crosssells' => ( $this->settings->get_option( 'ajax_add_to_cart_for_crosssells' ) && is_product() ) ? 'yes' : 'no',
				'i18n_added_to_cart'              => esc_attr__( 'Added to cart', 'woocommerce-boost-sales' ),
				'added_to_cart'                   => count( VI_WBOOSTSALES_Frontend_Upsells::$added_to_cart ) ? VI_WBOOSTSALES_Frontend_Upsells::$added_to_cart : '',
				'url'                             => admin_url( 'admin-ajax.php' ),
				'side_cart_auto_open'             => $auto_open_cart,
				'product_option_warning'          => esc_attr__( 'Please choose product option you want to add to cart', 'woocommerce-boost-sales' ),
				'hide_out_of_stock'               => $this->settings->get_option( 'hide_out_of_stock' ),
				'show_if_empty'                   => $this->settings->get_option( 'show_if_empty' ),
				'wc_hide_out_of_stock'            => get_option( 'woocommerce_hide_out_of_stock_items' ),
				'language'                        => $wbs_language,
				'crosssells_max_item_desktop'     => apply_filters( 'woocommerce_boost_sales_crosssells_max_item', 3, 'desktop' ),
				'crosssells_max_item_tablet'      => apply_filters( 'woocommerce_boost_sales_crosssells_max_item', 2, 'tablet' ),
				'crosssells_max_item_mobile'      => apply_filters( 'woocommerce_boost_sales_crosssells_max_item', 2, 'mobile' ),
				'show_thank_you'                  => false,
				'auto_redirect'                   => $this->settings->get_option( 'enable_checkout' ),
				'crosssell_enable'                => $this->settings->get_option( 'crosssell_enable' ),
				'auto_redirect_time'              => $auto_redirect_time,
				'auto_redirect_message'           => sprintf( __( 'Redirect to checkout after <span>%s</span>s', 'woocommerce-boost-sales' ), $auto_redirect_time ),
				'modal_price'                     => wc_price( 1 ),
				'decimal_separator'               => wc_get_price_decimal_separator(),
				'thousand_separator'              => wc_get_price_thousand_separator(),
				'decimals'                        => wc_get_price_decimals(),
				'price_format'                    => get_woocommerce_price_format(),
			)
		);
		$css_inline = '';
		if ( $button_bg_color ) {
			$css_inline .= "
			.woocommerce-boost-sales .wbs-upsells .product-controls button.wbs-single_add_to_cart_button,
			.woocommerce-boost-sales .wbs-upsells-items .product-controls button.wbs-single_add_to_cart_button,
			.wbs-content-inner-crs .wbs-crosssells-button-atc button.wbs-single_add_to_cart_button,
			.woocommerce-boost-sales .wbs-upsells .product-controls .wbs-cart .wbs-product-link,
			.wbs-content-inner-crs .wbs-crosssells-button-atc button.wbs-single_add_to_cart_button,
			.woocommerce-boost-sales .wbs-breadcrum .wbs-header-right a,
			.vi-wbs-btn-redeem{
				background-color: {$button_bg_color};
			}";
		}
		if ( $button_color ) {
			$css_inline .= ".wbs-content-inner-crs .wbs-crosssells-button-atc .wbs-single_add_to_cart_button,
			.vi-wbs-btn-redeem:hover,.woocommerce-boost-sales .wbs-breadcrum .wbs-header-right a::before,
			.woocommerce-boost-sales .wbs-upsells .product-controls button.wbs-single_add_to_cart_button:hover,
			.woocommerce-boost-sales .wbs-upsells-items .product-controls button.wbs-single_add_to_cart_button:hover,
			.wbs-content-inner-crs .wbs-crosssells-button-atc button.wbs-single_add_to_cart_button:hover,
			.woocommerce-boost-sales .wbs-upsells .product-controls .wbs-cart .wbs-product-link:hover{
			background-color: {$button_color};
			}	";
		}
		if ( $bg_color_cross_sell || $text_color_cross_sell ) {
			$css_inline .= "
				.woocommerce-boost-sales .wbs-content-crossell{
				background-color: {$bg_color_cross_sell}; 
				color:{$text_color_cross_sell}
				}";

		}
		if ( $bg_image_cross_sell ) {
			$bg_image_cross_sell = wp_get_attachment_image_url( $bg_image_cross_sell );
			$css_inline          .= "
				.woocommerce-boost-sales .wbs-content-crossell{
				background-image: url('{$bg_image_cross_sell}'); 
				back
				}";
		}
		if ( $price_text_color_cross_sell ) {
			$css_inline .= "
				.wbs-crs-regular-price{
				color: {$price_text_color_cross_sell}; 
				}";
		}
		if ( $save_price_text_color_cross_sell ) {
			$css_inline .= "
				.wbs-crosssells-price > div.wbs-crs-save-price > div.wbs-save-price{
				color: {$save_price_text_color_cross_sell}; 
				}";
		}
		if ( $custom_gift_image ) {
			$custom_gift_image = wp_get_attachment_image_url( $custom_gift_image );
			$css_inline        .= ".gift-button.wbs-icon-custom{
				background-image: url('{$custom_gift_image}');
			}";
		}
		if ( $text_color_discount ) {
			$css_inline .= "
				.woocommerce-boost-sales .vi-wbs-topbar,.woocommerce-boost-sales .vi-wbs-topbar > div{
				color: {$text_color_discount}; 
				}";
		}
		if ( $process_color ) {
			$css_inline .= "
				.vi-wbs-topbar .vi-wbs-progress-container .vi-wbs-progress{
				background-color: {$process_color}; 
				}";
		}
		if ( $process_color ) {
			$css_inline .= "
				.vi-wbs-progress .vi-wbs-progress-bar.vi-wbs-progress-bar-success{
				background-color: {$process_background_color}; 
				}";
		}
		if ( $icon_color && $icon_bg_color ) {
			$css_inline .= "
				.gift-button.gift_right.wbs-icon-font:before{
				background-color: {$icon_bg_color}; 
				color: {$icon_color}; 
				}";
		}
		if ( $this->settings->get_option( 'crosssell_template' ) !== 'slider' ) {
			$css_inline .= '#wbs-content-cross-sells.woocommerce-boost-sales .wbs-content-crossell .wbs-bottom {padding-bottom:5px;}
			#wbs-content-cross-sells.woocommerce-boost-sales .wbs-content-inner.wbs-content-inner-crs {
    width: auto;
    max-width: 800px;
}';
		}
		$css_inline .= $custom_css;
		wp_add_inline_style( 'woocommerce-boost-sales', $css_inline );


		/*Ajax button*/
		if ( $this->settings->get_option( 'ajax_button' ) && $this->settings->get_option( 'enable_upsell' ) && is_product() ) {
			$product = wc_get_product( get_the_ID() );
			if ( $product && apply_filters( 'wbs_custom_ajax_add_to_cart_button_for_product', ! in_array( $product->get_type(), [ 'assorted_product', 'bundle', 'booking' ] ), $product ) ) {
				if ( is_plugin_active( 'woocommerce-side-cart-premium/xoo-wsc.php' ) && ( is_product() || is_cart() || is_checkout() ) ) {
					$gl_options           = get_option( 'xoo-wsc-gl-options', array() );
					$this->auto_open_cart = isset( $gl_options['sc-auto-open'] ) ? $gl_options['sc-auto-open'] : 1;
				}

				wp_enqueue_style( 'woocommerce-boost-sales-ajax-button', VI_WBOOSTSALES_CSS . 'woocommerce-boost-sales-ajax-button' . $css_ext, array(), VI_WBOOSTSALES_VERSION );
				wp_enqueue_script(
					'woocommerce-boost-sales-ajax-button', VI_WBOOSTSALES_JS . 'woocommerce-boost-sales-ajax-button' . $js_ext, array( 'jquery', 'jquery-vi_flexslider' ), VI_WBOOSTSALES_VERSION, true );
				$product_title = $product->get_title();
				$params        = array(
					'ajax_url'                => WC()->ajax_url(),
					'wc_ajax_url'             => WC_AJAX::get_endpoint( "%%endpoint%%" ),
					'i18n_view_cart'          => esc_attr__( 'View Cart', 'woocommerce-boost-sales' ),
					'cart_url'                => apply_filters( 'woocommerce_add_to_cart_redirect', wc_get_cart_url(), null ),
					'is_cart'                 => is_cart(),
					'cart_redirect_after_add' => get_option( 'woocommerce_cart_redirect_after_add' ),
					'upsell_exclude_products' => $this->settings->get_option( 'upsell_exclude_products' ),
					'product_title'           => $product_title,
					'message_bought'          => str_replace( array(
						'{name_product}',
						'{product_name}',
						'{product_title}'
					), $product_title, $this->settings->get_option( 'message_bought', $wbs_language ) ),
					'auto_open_cart'          => $this->auto_open_cart,
				);
				if ( is_product() ) {
					$params['ajax_button'] = $this->settings->get_option( 'ajax_button' ) ? 1 : 0;
				} else {
					$params['ajax_button'] = 0;
				}
				$product_id = filter_input( INPUT_POST, 'add-to-cart', FILTER_SANITIZE_NUMBER_INT );
				if ( $product_id ) {
					$params['submit'] = 1;
				} else {
					$params['submit'] = 0;
				}

				/*Load data of product*/
				if ( $product->get_type() == 'variable' ) {
					$params['products'] = $product->get_available_variations();
				} else {
					$single_product[]   = array(
						'min_qty'       => $product->get_min_purchase_quantity(),
						'max_qty'       => $product->get_max_purchase_quantity(),
						'display_price' => $product->get_price(),
					);
					$params['products'] = $single_product;
				}
				if ( is_array( $params['products'] ) && count( $params['products'] ) ) {
					for ( $i = 0; $i < count( $params['products'] ); $i ++ ) {
						if ( isset( $params['products'][ $i ]['display_price'] ) && $params['products'][ $i ]['display_price'] ) {
							$params['products'][ $i ]['display_price'] = VI_WBOOSTSALES_Data::convert_price_to_float( $params['products'][ $i ]['display_price'] );
						}
						if ( isset( $params['products'][ $i ]['display_regular_price'] ) && $params['products'][ $i ]['display_regular_price'] ) {
							$params['products'][ $i ]['display_regular_price'] = VI_WBOOSTSALES_Data::convert_price_to_float( $params['products'][ $i ]['display_regular_price'] );
						}
					}
				}
				$params['product_type'] = $product->get_type();
				wp_localize_script( 'woocommerce-boost-sales-ajax-button', 'wbs_add_to_cart_params', $params );
			}

		}
		/*Discount bar*/
		if ( $this->settings->get_option( 'enable_discount' ) && $this->settings->get_option( 'coupon' ) ) {
			wp_enqueue_script(
				'woocommerce-boost-sales-bar', VI_WBOOSTSALES_JS . 'woocommerce-boost-sales-bar' . $js_ext, array(
				'jquery',
				'jquery-vi_flexslider',
			), VI_WBOOSTSALES_VERSION, true
			);
			wp_localize_script( 'woocommerce-boost-sales-bar', 'wbs_discount_bar_params', array(
				'is_checkout' => is_checkout() && ! is_product()
			) );
		}
	}
}