<?php

/**
 * Class VI_WBOOSTSALES_Frontend_Single
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VI_WBOOSTSALES_Frontend_Cross_Sells {
	protected static $settings;
	protected static $bundles_from_cart;
	protected $cross_sell_shown;

	public function __construct() {
		self::$settings         = VI_WBOOSTSALES_Data::get_instance();
		$this->cross_sell_shown = false;
		/*Check global enable*/
		if ( self::$settings->enable() ) {
			/*Check cross-sell enable*/
			if ( self::$settings->get_option( 'crosssell_enable' ) ) {
				if ( ! self::$settings->get_option( 'crosssells_hide_on_single_product_page' ) ) {
					switch ( self::$settings->get_option( 'crosssell_display_on' ) ) {
						case 0:
							add_action( 'wp_footer', array( $this, 'show_crosssell_popup' ) );
							break;
						case 1:
							add_action( 'woocommerce_single_product_summary', array(
								$this,
								'show_crosssell_product'
							), 50 );
							break;
						case 2:
							add_action( 'woocommerce_after_single_product_summary', array(
								$this,
								'show_crosssell_product'
							), 9 );
							break;
						case 3:
							add_action( 'woocommerce_after_template_part', array(
								$this,
								'woocommerce_after_template_part'
							) );
							break;
						case 4:
							$crosssell_custom_position = self::$settings->get_option( 'crosssell_custom_position' );
							if ( $crosssell_custom_position ) {
								add_action( $crosssell_custom_position, array(
									$this,
									'show_crosssell_product'
								) );
							}
							break;
						default:
					}
				}
				if ( self::$settings->get_option( 'enable_cart_page' ) ) {
					switch ( self::$settings->get_option( 'crosssell_display_on_cart' ) ) {
						case 'popup':
							add_action( 'wp_footer', array( $this, 'show_crosssell_popup_cart_checkout' ) );
							break;
						case 'before_cart':
							add_action( 'woocommerce_before_cart', array(
								$this,
								'show_crosssell_onpage_cart_checkout'
							) );
							break;
						case 'after_cart':
							add_action( 'woocommerce_after_cart', array(
								$this,
								'show_crosssell_onpage_cart_checkout'
							) );
							break;
						case 'custom_hook':
							$crosssell_custom_position = self::$settings->get_option( 'crosssell_custom_position_cart' );
							if ( $crosssell_custom_position ) {
								add_action( $crosssell_custom_position, array(
									$this,
									'show_crosssell_onpage_cart_checkout'
								) );
							}
							break;
						default:
					}
				}
				if ( self::$settings->get_option( 'enable_checkout_page' ) ) {
					switch ( self::$settings->get_option( 'crosssell_display_on_checkout' ) ) {
						case 'popup':
							add_action( 'wp_footer', array( $this, 'show_crosssell_popup_cart_checkout' ) );
							break;
						case 'before_checkout':
							add_action( 'woocommerce_before_checkout_form', array(
								$this,
								'show_crosssell_onpage_cart_checkout'
							) );
							break;
						case 'after_checkout':
							add_action( 'woocommerce_after_checkout_form', array(
								$this,
								'show_crosssell_onpage_cart_checkout'
							) );
							break;
						case 'custom_hook':
							$crosssell_custom_position = self::$settings->get_option( 'crosssell_custom_position_checkout' );
							if ( $crosssell_custom_position ) {
								add_action( $crosssell_custom_position, array(
									$this,
									'show_crosssell_onpage_cart_checkout'
								) );
							}
							break;
						default:
					}
				}
			}
		}
//		add_filter( 'woocommerce_get_price_html', array( $this, 'woocommerce_get_price_html' ), 10, 2 );
	}

	/**Show original price for WBS bundle products on single page
	 *
	 * @param $price_html
	 * @param $product WC_Product_Wbs_Bundle
	 *
	 * @return float|string
	 */
	public function woocommerce_get_price_html( $price_html, $product ) {
		$original_price = '';
		if ( is_product() && $product && $product->is_type( 'wbs_bundle' ) ) {
			$bundled_items = $product->get_bundled_items();
			if ( count( $bundled_items ) ) {
				$array_price = array();
				foreach ( $bundled_items as $bundled_item ) {
					/**
					 * @var WBS_WC_Bundled_Item $bundled_item
					 */
					$bundled_product = $bundled_item->get_product();
					$price           = wc_get_price_to_display( $bundled_product );
					$array_price[]   = $price;
				}
				$sum_pr               = array_sum( $array_price );
				$product_bundle_price = wc_get_price_to_display( $product );
				$save_price           = $sum_pr - $product_bundle_price;
				if ( $save_price > 0 ) {
					ob_start();
					?>
                    <del>
						<?php echo wc_price( $sum_pr ); ?>
                    </del>
					<?php
					$original_price = ob_get_clean();
				}
			}
		}
		$price_html = $original_price . $price_html;

		return $price_html;
	}

	public function woocommerce_after_template_part( $name ) {
		if ( is_product() && $name === 'single-product/tabs/description.php' ) {
			$this->show_crosssell_product();
		}
	}

	/**
	 * Show bundles below description
	 */
	public function show_crosssell_product() {
		if ( is_product() && ! $this->cross_sell_shown ) {
			$this->cross_sell_shown = true;
			if ( self::$settings->get_option( 'crosssells_hide_on_single_product_page' ) ) {
				return;
			}
			$product_id      = get_the_ID();
			$other_bundle_id = get_post_meta( $product_id, '_wbs_crosssells_bundle', true );
			if ( $other_bundle_id ) {
				if ( get_post_status( $other_bundle_id ) == 'publish' ) {
					if ( self::is_bundle_in_cart( $other_bundle_id ) ) {
						return;
					}
					if ( ( self::$settings->get_option( 'hide_out_of_stock' ) && ! self::is_in_stock( $other_bundle_id ) ) ) {
						return;
					}
					$output = new VI_WBOOSTSALES_Cross_Sells( $other_bundle_id );
					echo $output->show_html( true );
				}
			} else {
				$crosssells = get_post_meta( $product_id, '_wbs_crosssells', true );
				if ( isset( $crosssells[0] ) ) {
					if ( get_post_status( $crosssells[0] ) == 'publish' ) {
						if ( self::is_bundle_in_cart( $crosssells[0] ) ) {
							return;
						}
						if ( ( self::$settings->get_option( 'hide_out_of_stock' ) && ! self::is_in_stock( $crosssells[0] ) ) ) {
							return;
						}
						$output = new VI_WBOOSTSALES_Cross_Sells( $crosssells[0] );
						echo $output->show_html( true );
					}
				}
			}
		}
	}

	public static function is_bundle_in_cart( $bundle_id ) {
		$return       = false;
		$cart_content = WC()->cart->cart_contents;
		if ( is_array( $cart_content ) && count( $cart_content ) ) {
			foreach ( $cart_content as $key => $value ) {
				if ( $value['product_id'] == $bundle_id && ! empty( $value['wbs_bundled_items'] ) ) {
					$return = true;
					break;
				}
			}
		}

		return $return;
	}

	/**
	 * Show HTML cross sells product
	 */
	public function show_crosssell_onpage_cart_checkout() {
		$this->cross_sells_html_for_cart_and_checkout( true );
	}

	public function show_crosssell_popup_cart_checkout() {
		$this->cross_sells_html_for_cart_and_checkout( false );
	}

	public function show_crosssell_popup() {
		if ( is_product() ) {
			$product_id        = filter_input( INPUT_POST, 'add-to-cart', FILTER_SANITIZE_NUMBER_INT );
			$quantity          = filter_input( INPUT_POST, 'quantity', FILTER_SANITIZE_NUMBER_INT );
			$hide_out_of_stock = self::$settings->get_option( 'hide_out_of_stock' );
			if ( $product_id && $quantity ) {
				$other_bundle_id = get_post_meta( $product_id, '_wbs_crosssells_bundle', true );
				if ( $other_bundle_id ) {
					if ( get_post_status( $other_bundle_id ) == 'publish' ) {
						if ( self::is_bundle_in_cart( $other_bundle_id ) ) {
							return;
						}
						if ( ( $hide_out_of_stock && ! self::is_in_stock( $other_bundle_id ) ) ) {
							return;
						}
						$output = new VI_WBOOSTSALES_Cross_Sells( $other_bundle_id );
						echo $output->show_html();
					}
				} else {
					$crosssells = get_post_meta( $product_id, '_wbs_crosssells', true );

					if ( isset( $crosssells[0] ) ) {
						if ( get_post_status( $crosssells[0] ) == 'publish' ) {
							if ( self::is_bundle_in_cart( $crosssells[0] ) ) {
								return;
							}
							if ( ( $hide_out_of_stock && ! self::is_in_stock( $crosssells[0] ) ) ) {
								return;
							}
							$output = new VI_WBOOSTSALES_Cross_Sells( $crosssells[0] );
							echo $output->show_html();
						}
					}
				}
			} else {
				$other_bundle_id = get_post_meta( get_the_ID(), '_wbs_crosssells_bundle', true );
				if ( $other_bundle_id ) {
					if ( get_post_status( $other_bundle_id ) == 'publish' ) {
						if ( self::is_bundle_in_cart( $other_bundle_id ) ) {
							return;
						}
						if ( ( $hide_out_of_stock && ! self::is_in_stock( $other_bundle_id ) ) ) {
							return;
						}
						$output = new VI_WBOOSTSALES_Cross_Sells( $other_bundle_id );
						echo $output->show_html();
					}
				} else {
					$crosssells = get_post_meta( get_the_ID(), '_wbs_crosssells', true );
					if ( isset( $crosssells[0] ) ) {
						if ( get_post_status( $crosssells[0] ) == 'publish' ) {
							if ( self::is_bundle_in_cart( $crosssells[0] ) ) {
								return;
							}
							if ( ( $hide_out_of_stock && ! self::is_in_stock( $crosssells[0] ) ) ) {
								return;
							}
							$output = new VI_WBOOSTSALES_Cross_Sells( $crosssells[0] );
							echo $output->show_html();
						}
					}
				}
			}
		}
	}

	public static function get_cross_sells_from_cart( &$bundle_of ) {
		if ( self::$bundles_from_cart === null ) {
			$bundle_id  = '';
			$bundle_of  = '';
			$crosssells = array();
			$items      = WC()->cart->get_cart_contents();
			if ( count( $items ) ) {
				$p_cart = array();
				foreach ( $items as $item ) {
					if ( ! empty( $item['wbs_bundled_by'] ) ) {
						continue;
					}
					$p_cart[]        = $item['product_id'];
					$other_bundle_id = get_post_meta( $item['product_id'], '_wbs_crosssells_bundle', true );
					if ( $other_bundle_id ) {
						if ( ! self::is_bundle_in_cart( $other_bundle_id ) && get_post_status( $other_bundle_id ) === 'publish' ) {
							if ( apply_filters( 'wbs_valid_bundle_from_cart', true, $other_bundle_id, $item['product_id'] ) ) {
								$crosssells[] = array(
									'quantity'  => $item['quantity'],
									'id'        => $other_bundle_id,
									'bundle_of' => $item['product_id'],
								);
							}
						}
					} else {
						$cross_sell_id = get_post_meta( $item['product_id'], '_wbs_crosssells', true );
						if ( isset( $cross_sell_id[0] ) && ! self::is_bundle_in_cart( $cross_sell_id[0] ) && get_post_status( $cross_sell_id[0] ) === 'publish' ) {
							if ( apply_filters( 'wbs_valid_bundle_from_cart', true, $cross_sell_id[0], $item['product_id'] ) ) {
								$crosssells[] = array(
									'quantity'  => $item['quantity'],
									'id'        => $cross_sell_id[0],
									'bundle_of' => $item['product_id'],
								);
							}
						} else {
							continue;
						}
					}
				}
				$crosssells = array_filter( $crosssells );
				if ( count( $crosssells ) ) {
					$crosssells        = array_values( $crosssells );
					$hide_out_of_stock = self::$settings->get_option( 'hide_out_of_stock' );
					$bundle_added      = self::$settings->get_option( 'bundle_added' );
					$check_opt         = 0;
					if ( is_checkout() ) {
						$check_opt = self::$settings->get_option( 'checkout_page_option' );
					} elseif ( is_cart() ) {
						$check_opt = self::$settings->get_option( 'cart_page_option' );
					}
					switch ( $check_opt ) {
						case  1:
							$bundle_id = self::get_random_bundle_id( $crosssells, $hide_out_of_stock, $bundle_of );
							break;
						case  2:
							$max = 0;
							foreach ( $crosssells as $crosssell ) {
								if ( ( $hide_out_of_stock && ! self::is_in_stock( $crosssell['id'] ) ) || ( $bundle_added && in_array( $crosssell['id'], $p_cart ) ) ) {
									continue;
								}
								$price = wc_get_product( $crosssell['id'] )->get_price();
								if ( $max < $price ) {
									$max       = $price;
									$bundle_id = $crosssell['id'];
									$bundle_of = $crosssell['bundle_of'];
								}
							}

							break;
						default:
							$max = 0;
							foreach ( $crosssells as $crosssell ) {
								if ( ( $hide_out_of_stock && ! self::is_in_stock( $crosssell['id'] ) ) || ( $bundle_added && in_array( $crosssell['id'], $p_cart ) ) ) {
									continue;
								}
								$quantity = $crosssell['quantity'];
								if ( $max < $quantity ) {
									$max       = $quantity;
									$bundle_id = $crosssell['id'];
									$bundle_of = $crosssell['bundle_of'];
								}
							}
					}
				}
			}
			self::$bundles_from_cart = array(
				'id'        => $bundle_id,
				'bundle_of' => $bundle_of,
			);
		} else {
			$bundle_id = self::$bundles_from_cart['id'];
			$bundle_of = self::$bundles_from_cart['bundle_of'];
		}

		return $bundle_id;
	}

	public function cross_sells_html_for_cart_and_checkout( $layout = true ) {
		if ( ! $this->cross_sell_shown ) {
			$enable_cart_page     = self::$settings->get_option( 'enable_cart_page' );
			$enable_checkout_page = self::$settings->get_option( 'enable_checkout_page' );
			if ( ( is_checkout() && $enable_checkout_page ) || ( is_cart() && $enable_cart_page ) ) {
				$this->cross_sell_shown = true;
				$bundle_id              = self::get_cross_sells_from_cart( $bundle_of );
				if ( $bundle_id ) {
					$output = new VI_WBOOSTSALES_Cross_Sells( $bundle_id );
					echo $output->show_html( $layout );
				}
			}
		}
	}

	/**
	 * @param $bundle_id
	 *
	 * @return bool
	 */
	protected static function is_in_stock( $bundle_id ) {
		$instock        = true;
		$product_bundle = wc_get_product( $bundle_id );
		if ( ! $product_bundle ) {
			return false;
		} elseif ( ! $product_bundle->is_type( 'wbs_bundle' ) ) {
			return $product_bundle->is_in_stock();
		}
		$bundled_items = $product_bundle->get_bundled_items();
		if ( ! count( $bundled_items ) ) {
			return false;
		}
		foreach ( $bundled_items as $bundled_item ) {
			if ( ! $bundled_item->is_in_stock() ) {
				$instock = false;
				break;
			}
		}

		return $instock;
	}

	protected static function get_random_bundle_id( $crosssells, $hide_out_of_stock, &$bundle_of ) {
		$bundle_id = '';
		$bundle_of = '';
		if ( count( $crosssells ) ) {
			$index     = rand( 0, count( $crosssells ) - 1 );
			$bundle_id = $crosssells[ $index ]['id'];
			$bundle_of = $crosssells[ $index ]['bundle_of'];
			if ( $hide_out_of_stock && ! self::is_in_stock( $bundle_id ) ) {
				unset( $crosssells[ $index ] );
				$bundle_id = self::get_random_bundle_id( $crosssells, $hide_out_of_stock, $bundle_of );
			}
		}

		return $bundle_id;
	}
}