<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VI_WBOOSTSALES_Cross_Sells {
	private $settings;
	private $bundle_id;

	/**
	 * VI_WBOOSTSALES_Cross_Sells constructor.
	 *
	 * @param $cross_sells
	 */
	public function __construct( $cross_sells ) {
		$this->settings  = VI_WBOOSTSALES_Data::get_instance();
		$this->bundle_id = $cross_sells;
	}

	/**
	 * @param bool $layout
	 *
	 * @return bool|false|string
	 */
	public function show_html( $layout = false ) {
		global $wbs_language;
		/*Check product bundles*/
		if ( ! $this->bundle_id ) {
			return false;
		}
		$product = wc_get_product( $this->bundle_id );

		if ( $product->get_type() !== 'wbs_bundle' || $product->get_status() !== 'publish' ) {
			return false;
		}

		$crosssell_description     = $this->settings->get_option( 'crosssell_description', $wbs_language );
		$crosssell_template        = $this->settings->get_option( 'crosssell_template' );
		$crosssell_mobile_template = $this->settings->get_option( 'crosssell_mobile_template' );
		$detect                    = $this->settings->get_detect();
		$display_saved_price       = $this->settings->get_option( 'display_saved_price' );
		if ( $detect === 'mobile' ) {
			if ( $crosssell_mobile_template === 'vertical' ) {
				$crosssell_template = $crosssell_mobile_template;
			} else {
				$crosssell_template = 'slider';
			}
		}
		$dynamic_price   = get_post_meta( $this->bundle_id, '_wbs_dynamic_price', true );
		$discount_type   = get_post_meta( $this->bundle_id, '_wbs_discount_type', true );
		$discount_amount = get_post_meta( $this->bundle_id, '_wbs_discount_amount', true );
		if ( $discount_type !== 'percent' && $discount_amount ) {
			$discount_amount = apply_filters( 'wmc_change_3rd_plugin_price', $discount_amount );
		}
		$wbs_class = array( 'wbs-crosssells' );
		if ( ! $layout ) {
			$icon_position = $this->settings->get_option( 'icon_position' );
			$init_delay    = $this->settings->get_option( 'init_delay' );
			$open          = $this->settings->get_option( 'enable_cross_sell_open' );
			$added         = filter_input( INPUT_POST, 'add-to-cart', FILTER_SANITIZE_NUMBER_INT );
			$quantity      = filter_input( INPUT_POST, 'quantity', FILTER_SANITIZE_NUMBER_INT );
			if ( $open && is_product() && $added && $quantity ) {
				$open = 0;
			}
			$init_random = array();
			if ( $init_delay ) {
				$init_random = array_filter( explode( ',', $init_delay ) );
			}
			if ( count( $init_random ) == 2 ) {
				$init_delay = rand( $init_random[0], $init_random[1] );
			}

			if ( count( $product->bundle_data ) ) {
				$class = 'woocommerce-boost-sales';
				if ( $detect === 'mobile' ) {
					$class .= ' woocommerce-boost-sales-mobile';
				}
				ob_start();
				if ( ! $this->settings->get_option( 'hide_gift' ) ) {
					switch ( $this->settings->get_option( 'icon' ) ) {
						case 1:
							$icon = ' wbs-icon-gift';
							break;
						case 2:
							$icon = ' wbs-icon-custom';
							break;
						default:
							$icon = ' wbs-icon-font';
					}
					?>
                    <div id="gift-button"
                         class="gift-button animated <?php echo $icon_position == 0 ? 'gift_right' : 'gift_left';
					     echo $icon; ?>" style="display: none;"">
                    </div>
					<?php
				}
				?>
                <div id="wbs-content-cross-sells" class="<?php echo esc_attr( $class ) ?>" style="display: none"
                     data-initial_delay="<?php echo esc_attr( $init_delay ); ?>"
                     data-open="<?php echo esc_attr( $open ); ?>">
                    <div class="wbs-overlay"></div>
                    <div class="wbs-wrapper">
                        <div class="wbs-content-crossell <?php echo $icon_position == 0 ? 'gift_right' : 'gift_left'; ?>">
                            <div class="wbs-content-inner wbs-content-inner-crs">
                                <span class="wbs-close"
                                      title="<?php esc_html_e( 'Close', 'woocommerce-boost-sales' ) ?>"><span>X</span></span>
                                <div class="wbs-added-to-cart-overlay">
                                    <div class="wbs-loading"></div>
                                    <div class="wbs-added-to-cart-overlay-content">
                                        <span class="wbs-icon-added"></span>
										<?php esc_html_e( 'Added to cart', 'woocommerce-boost-sales' ) ?>
                                    </div>
                                </div>
                                <div class="wbs-bottom">
									<?php
									if ( $crosssell_description ) {
										?>
                                        <div class="crosssell-title"><?php echo esc_html( $crosssell_description ) ?></div>
										<?php
									}
									if ( $crosssell_template !== 'slider' ) {
										VI_WBOOSTSALES_Frontend_Frequently_Product::$is_cross_sells = true;
										echo do_shortcode( '[wbs_frequently_product product_id="" source="cross_sells" style="' . $crosssell_template . '" show_attribute="click" message="" hide_if_added=""]' );
									} else {
										?>
                                        <form class="woocommerce-boost-sales-cart-form" method="post"
                                              enctype='multipart/form-data'>
                                            <div class="<?php echo esc_attr( implode( ' ', $wbs_class ) ) ?>"
                                                 data-dynamic_price="<?php echo esc_attr( $dynamic_price ) ?>"
                                                 data-fixed_price="<?php echo esc_attr( wc_get_price_to_display( $product ) ) ?>"
                                                 data-saved_type="<?php echo esc_attr( $display_saved_price ) ?>"
                                                 data-discount_type="<?php echo esc_attr( $discount_type ) ?>"
                                                 data-discount_amount="<?php echo esc_attr( $discount_amount ) ?>">
												<?php
												wc_setup_product_data( $product->get_id() );
												$return = VI_WBOOSTSALES_Frontend_Bundles::show_crossell_html();
												if ( false === $return ) {
													ob_end_clean();

													return '';
												} else {
													echo $return;
												}
												wp_reset_postdata();
												?>
                                            </div>
                                        </form>
										<?php
									}
									?>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
				<?php
				return ob_get_clean();
			} else {
				return false;
			}
		} else {
			if ( $crosssell_template !== 'slider' ) {
				VI_WBOOSTSALES_Frontend_Frequently_Product::$is_cross_sells = true;
				echo do_shortcode( '[wbs_frequently_product product_id="" source="cross_sells" style="' . $crosssell_template . '" show_attribute="click" message="' . $crosssell_description . '" hide_if_added=""]' );
			} else {
				$class = 'wbs-content-cross-sells-product-single-container';
				if ( $detect === 'mobile' ) {
					$class .= ' woocommerce-boost-sales-mobile';
				}
				ob_start();
				?>
                <div class="<?php echo esc_attr( $class ) ?>">
					<?php
					$class = '';
					if ( $this->settings->get_option( 'crosssell_display_on_slide' ) ) {
						$class = 'crosssell-display-on-slide';
					}
					?>
                    <div id="wbs-content-cross-sells-product-single" class="<?php echo esc_attr( $class ) ?>">
						<?php if ( $crosssell_description ) { ?>
                            <div class="crosssell-title"><?php echo esc_html( $crosssell_description ) ?></div>
						<?php } ?>
                        <form class="woocommerce-boost-sales-cart-form" method="post" enctype='multipart/form-data'>
                            <div class="<?php echo esc_attr( implode( ' ', $wbs_class ) ) ?>"
                                 data-dynamic_price="<?php echo esc_attr( $dynamic_price ) ?>"
                                 data-fixed_price="<?php echo esc_attr( wc_get_price_to_display( $product ) ) ?>"
                                 data-saved_type="<?php echo esc_attr( $display_saved_price ) ?>"
                                 data-discount_type="<?php echo esc_attr( $discount_type ) ?>"
                                 data-discount_amount="<?php echo esc_attr( $discount_amount ) ?>">
								<?php
								wc_setup_product_data( $product->get_id() );
								$return = VI_WBOOSTSALES_Frontend_Bundles::show_crossell_html();
								if ( false === $return ) {
									ob_end_clean();

									return '';
								} else {
									echo $return;
								}
								wp_reset_postdata();
								?>
                            </div>
                        </form>
                    </div>
                    <div class="woocommerce-message">
						<?php
						echo wc_add_to_cart_message( $product->get_id(), false, true )
						?>
                    </div>
                </div>
				<?php
			}

			return ob_get_clean();
		}
	}
}