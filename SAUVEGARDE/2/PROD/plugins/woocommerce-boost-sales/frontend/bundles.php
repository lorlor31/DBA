<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( 'VI_WBOOSTSALES_Frontend_Bundles' ) ) {
	/**
	 * Frontend class.
	 * The class manage all the Frontend behaviors.
	 *
	 */
	class VI_WBOOSTSALES_Frontend_Bundles {

		/**
		 * Single instance of the class
		 *
		 * @var VI_WBOOSTSALES_Frontend_Bundles
		 * @since 1.0.0
		 */
		protected static $instance;
		private $selected_attributes;
		public $this_is_product = null;

		public $templates = array();

		protected static $settings;
		protected $cart_item_quantity;
		private $cart_item_key = '';
		private $compatible_cart_pop = [];

		/**
		 * Returns single instance of the class
		 *
		 * @return VI_WBOOSTSALES_Frontend_Bundles
		 * @since 1.0.0
		 */
		public static function get_instance() {
			if ( is_null( self::$instance ) ) {
				self::$instance = new self();
			}

			return self::$instance;
		}

		/**
		 * Constructor
		 *
		 * @access public
		 * @since  1.0.0
		 */
		public function __construct() {
			self::$settings = VI_WBOOSTSALES_Data::get_instance();
			if ( ! self::$settings->get_option( 'crosssell_enable' ) ) {
				return;
			}

			add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ) );

			// C A R T
			add_action( 'woocommerce_wbs_bundle_add_to_cart', array( $this, 'woocommerce_wbs_bundle_add_to_cart' ) );
			add_filter( 'woocommerce_add_to_cart_validation', array( $this, 'woocommerce_add_to_cart_validation' ), 10, 6 );
			//add_filter( 'woocommerce_loop_add_to_cart_link', array( $this, 'woocommerce_loop_add_to_cart_link' ), 10, 2 );

			add_filter( 'woocommerce_add_cart_item_data', array( $this, 'woocommerce_add_cart_item_data' ), 10, 2 );

			add_filter( 'woocommerce_cart_item_remove_link', array( $this, 'woocommerce_cart_item_remove_link' ), 10, 2 );

			add_filter( 'woocommerce_cart_item_quantity', array( $this, 'woocommerce_cart_item_quantity' ), 10, 2 );
			add_action( 'woocommerce_after_cart_item_quantity_update', array( $this, 'update_cart_item_quantity' ), 1, 2 );

			add_action( 'woocommerce_before_cart_item_quantity_zero', array( $this, 'update_cart_item_quantity' ), 1 );

			add_filter( 'woocommerce_cart_item_price', array( $this, 'woocommerce_cart_item_price' ), 99, 3 );
			add_filter( 'woocommerce_cart_item_subtotal', array( $this, 'bundles_item_subtotal' ), 99, 3 );
			add_filter( 'woocommerce_checkout_item_subtotal', array( $this, 'bundles_item_subtotal' ), 10, 3 );
			add_filter( 'woocommerce_add_cart_item', array( $this, 'woocommerce_add_cart_item' ), 99, 2 );

			add_action( 'woocommerce_add_to_cart', array( $this, 'woocommerce_add_to_cart' ), 10, 6 );
			add_action( 'woocommerce_cart_item_removed', array( $this, 'woocommerce_cart_item_removed' ), 10, 2 );
			add_action( 'woocommerce_cart_item_restored', array( $this, 'woocommerce_cart_item_restored' ), 10, 2 );

			add_filter( 'woocommerce_cart_contents_count', array( $this, 'woocommerce_cart_contents_count' ) );

			add_filter( 'woocommerce_cart_item_class', array( $this, 'table_item_class_bundle' ), 10, 2 );

			add_filter( 'woocommerce_get_cart_item_from_session', array( $this, 'woocommerce_get_cart_item_from_session' ), 10, 3 );

			// O R D E R
			add_filter( 'woocommerce_order_formatted_line_subtotal', array( $this, 'woocommerce_order_formatted_line_subtotal' ), 10, 3 );
			add_action( 'woocommerce_new_order_item', array( $this, 'woocommerce_new_order_item' ), 10, 3 );
			add_filter( 'woocommerce_order_item_class', array( $this, 'table_item_class_bundle' ), 10, 2 );
			add_filter( 'woocommerce_mini_cart_item_class', array( $this, 'table_item_class_bundle' ), 10, 2 );

			// S H I P P I N G
			add_filter( 'woocommerce_cart_shipping_packages', array( $this, 'woocommerce_cart_shipping_packages' ) );
			add_filter( 'woocommerce_cart_product_subtotal', array( $this, 'woocommerce_cart_product_subtotal' ), 10, 4 );
			/*Compatible with WooCommerce Cart All In One*/
			add_filter( 'vi_wcaio_mini_cart_pd_remove', array( $this, 'woocommerce_cart_item_price' ), 10, 3 );
			add_filter( 'vi_wcaio_mini_cart_pd_qty', array( $this, 'vi_wcaio_mini_cart_pd_qty' ), 10, 3 );

			add_filter( 'woocommerce_get_cart_contents', array( $this, 'woocommerce_get_cart_contents' ), 10, 1 );
			add_filter( 'woocommerce_cart_item_name', array( $this, 'woocommerce_cart_item_name' ), 10, 3 );

			add_filter( 'woocommerce_stock_amount_cart_item', array( $this, 'prevent_changing_bundle_item_quantity' ), 10, 2 );
			add_filter( 'woocommerce_widget_cart_item_quantity', array( $this, 'woocommerce_widget_cart_item_quantity' ), 10, 3 );

			add_action( 'woocommerce_data_stores', [ $this, 'register_data_store' ] );

			/* Compatible */

			add_filter( 'woocommerce_cart_item_product', function ( $product, $cart_item, $cart_item_key ) {
				$this->compatible_cart_pop = $cart_item;
				add_filter( 'cartpops_drawer_cart_item_classes', [ $this, 'add_parent_child_class_bundle' ], 10 );

				return $product;
			}, 10, 3 );

		}

		public function add_parent_child_class_bundle( $classes ) {
			if ( ! empty( $this->compatible_cart_pop ) ) {
				if ( isset( $this->compatible_cart_pop['wbs_bundled_by'] ) ) {
					$classes[] = 'wbs-wcpb-child-of-bundle-table-item';
				} elseif ( isset( $this->compatible_cart_pop['wbs_cartstamp'] ) ) {
					$classes[] = 'wbs-wcpb-bundle-table-item';
				}
			}

			remove_filter( 'cartpops_drawer_cart_item_classes', [ $this, 'add_parent_child_class_bundle' ], 10 );

			return $classes;
		}

		/**
		 * Do not allow to change bundle item quantity via cart update
		 *
		 * @param $quantity
		 * @param $cart_item_key
		 *
		 * @return string
		 */
		public function prevent_changing_bundle_item_quantity( $quantity, $cart_item_key ) {
			$cart_contents = WC()->cart->get_cart_contents();
			if ( ! empty( $cart_contents[ $cart_item_key ] ) ) {
				$cart_content = $cart_contents[ $cart_item_key ];
				$bundle_key   = isset( $cart_content['wbs_bundled_by'] ) ? $cart_content['wbs_bundled_by'] : '';
				if ( $bundle_key && ! empty( $cart_contents[ $bundle_key ] ) ) {
					$wbs_bundled_items = isset( $cart_contents[ $bundle_key ]['wbs_bundled_items'] ) ? $cart_contents[ $bundle_key ]['wbs_bundled_items'] : array();
					if ( in_array( $cart_item_key, $wbs_bundled_items ) && $cart_contents[ $bundle_key ]['data'] && $cart_contents[ $bundle_key ]['data']->is_type( 'wbs_bundle' ) ) {
						$quantity = '';
					}
				}
			}

			return $quantity;
		}

		public function woocommerce_widget_cart_item_quantity( $product_quantity, $cart_item, $cart_item_key ) {
			$cart_contents = WC()->cart->get_cart_contents();
			$bundle_key    = isset( $cart_item['wbs_bundled_by'] ) ? $cart_item['wbs_bundled_by'] : '';
			if ( $bundle_key && ! empty( $cart_contents[ $bundle_key ] ) ) {
				$wbs_bundled_items = isset( $cart_contents[ $bundle_key ]['wbs_bundled_items'] ) ? $cart_contents[ $bundle_key ]['wbs_bundled_items'] : array();
				if ( in_array( $cart_item_key, $wbs_bundled_items ) && $cart_contents[ $bundle_key ]['data'] && $cart_contents[ $bundle_key ]['data']->is_type( 'wbs_bundle' ) ) {
					$product_quantity = '<span class="quantity">' . esc_html__( 'Qty', 'woocommerce' ) . ': ' . $cart_item['quantity'] . '</span>';
				}
			}


			return $product_quantity;
		}

		public function woocommerce_cart_item_name( $name, $cart_item, $cart_item_key ) {
			$this->cart_item_key = $cart_item_key;

			return $name;
		}

		public function woocommerce_get_cart_contents( $cart_contents ) {
			if ( is_array( $cart_contents ) ) {
				foreach ( $cart_contents as $cart_item_key => $cart_item ) {
					if ( isset( $cart_item['wbs_cartstamp'] ) && ! empty( $cart_item['wbs_bundled_items'] ) && $cart_item['quantity'] > 0 ) {
						$bundle_id      = $cart_item['product_id'];
						$dynamic_price  = get_post_meta( $bundle_id, '_wbs_dynamic_price', true );
						$original_price = 0;
						foreach ( $cart_item['wbs_bundled_items'] as $key ) {
							if ( isset( $cart_contents[ $key ] ) ) {
								$original_price += floatval( $cart_contents[ $key ]['wbs_item_price'] );
							}
						}

						if ( $dynamic_price ) {
							$bundle_price    = $original_price;
							$discount_type   = get_post_meta( $bundle_id, '_wbs_discount_type', true );
							$discount_amount = get_post_meta( $bundle_id, '_wbs_discount_amount', true );
							if ( $discount_amount ) {
								if ( $discount_type === 'percent' ) {
									$discount_amount = $discount_amount / 100;
									$bundle_price    = $bundle_price * ( 1 - $discount_amount );
								} else {
									$discount_amount = apply_filters( 'wmc_change_3rd_plugin_price', $discount_amount );
									$bundle_price    = $bundle_price - $discount_amount;
								}
								if ( $bundle_price < 0 ) {
									$bundle_price = 0;
								}
								$bundle_price = VI_WBOOSTSALES_Data::convert_price_to_float( $bundle_price );
							}
							$bundle_price = apply_filters( 'wbs_crossell_recalculated_price_in_cart', $bundle_price, $original_price, $bundle_id, $cart_item['data'] );
							$cart_item['data']->set_price( $bundle_price );
						} else {
//							$bundle_price = wc_get_price_to_display( $cart_item['data'] );
							$bundle_price = self::get_item_price( $cart_item['data'] );
						}

						$cart_contents[ $cart_item_key ]['wbs_original_price'] = $original_price;
						$cart_contents[ $cart_item_key ]['wbs_saved_amount']   = $original_price > $bundle_price ? $original_price - $bundle_price : 0;
					}
				}
			}

			return $cart_contents;
		}

		public function register_data_store( $stores ) {
			if ( ! class_exists( 'WBS_Product_Data_Store' ) ) {
				require_once VI_WBOOSTSALES_INCLUDES . "wbs-product-data-store.php";
			}

			$stores['product-wbs_bundle'] = 'WBS_Product_Data_Store';

			return $stores;
		}

		public function vi_wcaio_mini_cart_pd_qty( $qty, $cart_item_key, $cart_item ) {
			if ( isset( $cart_item['wbs_bundled_by'] ) ) {
				$bundle_cart_key = $cart_item['wbs_bundled_by'];
				if ( isset( WC()->cart->cart_contents[ $bundle_cart_key ] ) ) {
					$qty = '';
				}
			}

			return $qty;
		}

		/**
		 * @param $product_subtotal
		 * @param $product WC_Product_Wbs_Bundle
		 * @param $quantity
		 * @param $wc_cart WC_Cart
		 *
		 * @return string
		 */
		public function woocommerce_cart_product_subtotal( $product_subtotal, $product, $quantity, $wc_cart ) {
			if ( $product && $product->is_type( 'wbs_bundle' ) ) {
				$display_saved_price = intval( self::$settings->get_option( 'display_saved_price' ) );
				if ( in_array( $display_saved_price, array( 0, 1 ) ) ) {
					$cart_contents      = $wc_cart->get_cart_contents();
					$wbs_original_price = isset( $cart_contents[ $this->cart_item_key ]['wbs_original_price'] ) ? VI_WBOOSTSALES_Data::convert_price_to_float( $cart_contents[ $this->cart_item_key ]['wbs_original_price'] ) : 0;
					$saved_amount       = isset( $cart_contents[ $this->cart_item_key ]['wbs_saved_amount'] ) ? VI_WBOOSTSALES_Data::convert_price_to_float( $cart_contents[ $this->cart_item_key ]['wbs_saved_amount'] ) : 0;
					if ( $saved_amount > 0 ) {
						if ( 'incl' === $wc_cart->get_tax_price_display_mode() ) {
							$original_price = wc_get_price_including_tax( $product, array( 'price' => $wbs_original_price, 'qty' => $quantity ) );
						} else {
							$original_price = wc_get_price_excluding_tax( $product, array( 'price' => $wbs_original_price, 'qty' => $quantity ) );
						}
						$product_subtotal = '<del>' . wc_price( $original_price ) . '</del>&nbsp;<ins>' . $product_subtotal . '</ins>';

						switch ( $display_saved_price ) {
							case 1:
								$percent          = round( $saved_amount / $wbs_original_price * 100, 0 );
								$product_subtotal .= '<div class="wbs-cart-product-subtotal-saved-amount"><span>' . esc_html__( 'Save: ', 'woocommerce-boost-sales' ) . $percent . '</span>%</div>';
								break;
							case 0:
								$saved_amount     = $quantity * $saved_amount;
								$product_subtotal .= '<div class="wbs-cart-product-subtotal-saved-amount"><span>' . esc_html__( 'Save: ', 'woocommerce-boost-sales' ) . wc_price( $saved_amount ) . '</span></div>';
								break;
							default:
						}
					}
				}
			}

			return $product_subtotal;
		}

		/**
		 * Edit the count of cart contents
		 * exclude bundled items from the count
		 *
		 * @param $count
		 *
		 * @return int
		 */
		public function woocommerce_cart_contents_count( $count ) {
			$cart_contents = WC()->cart->cart_contents;

			$bundled_items_count = 0;
			foreach ( $cart_contents as $cart_item_key => $cart_item ) {
				if ( ! empty( $cart_item['wbs_bundled_by'] ) ) {
					$bundled_items_count += $cart_item['quantity'];
				}
			}

			return intval( $count - $bundled_items_count );
		}

		/**
		 * add CSS class in table items in checkout and order
		 *
		 * @param $classname
		 * @param $cart_item
		 *
		 * @return string
		 */
		public function table_item_class_bundle( $classname, $cart_item ) {
			if ( isset( $cart_item['wbs_bundled_by'] ) ) {
				return $classname . ' wbs-wcpb-child-of-bundle-table-item';
			} elseif ( isset( $cart_item['wbs_cartstamp'] ) ) {
				return $classname . ' wbs-wcpb-bundle-table-item';
			}

			return $classname;
		}

		/**
		 * create item data [create the wbs_cartstamp if not exist]
		 *
		 * @param $cart_item_data
		 * @param $product_id
		 *
		 * @return mixed
		 */
		public function woocommerce_add_cart_item_data( $cart_item_data, $product_id ) {
			$product = wc_get_product( $product_id );

			if ( ! $product || ! $product->is_type( 'wbs_bundle' ) ) {
				return $cart_item_data;
			}

			/** @var WC_Product_Wbs_Bundle $product */

			if ( isset( $cart_item_data['wbs_cartstamp'] ) && isset( $cart_item_data['wbs_bundled_items'] ) ) {
				return $cart_item_data;
			}

			$bundled_items = $product->get_bundled_items();

			if ( $bundled_items ) {
				$cartstamp  = array();
				$variations = isset( $_POST['vi_chosen_product_variable'] ) ? $_POST['vi_chosen_product_variable'] : array();

				foreach ( $bundled_items as $bundled_item_id => $bundled_item ) {
					$id                   = $bundled_item->product_id;
					$bundled_product_type = $bundled_item->product->get_type();
					if ( $bundled_product_type === 'variation' ) {
						$bundled_product_type                        = 'variation';
						$cartstamp[ $bundled_item_id ]['attributes'] = $bundled_item->product->get_variation_attributes();
					} elseif ( $bundled_product_type === 'variable' && $bundled_item->product->has_child() ) {
						$parent_id = $id;
						if ( isset( $variations[ $parent_id ] ) && is_array( $variations[ $parent_id ] ) && count( $variations[ $parent_id ] ) ) {
							$attributes = array();
							foreach ( $variations[ $parent_id ] as $key => $value ) {
								$attributes[ strtolower( $key ) ] = $value;
							}
							$find_id = VI_WBOOSTSALES_Data::find_matching_product_variation_id( $parent_id, $attributes );
							if ( $find_id ) {
								$id                                          = $find_id;
								$bundled_product_type                        = 'variation';
								$cartstamp[ $bundled_item_id ]['attributes'] = $variations[ $parent_id ];
							}
						}
					}

					$bundled_product_quantity = isset ( $_REQUEST[ apply_filters( 'woocommerce_product_wbs_bundle_field_prefix', '', $product_id ) . 'wbs_bundle_quantity_' . $bundled_item_id ] ) ? absint( $_REQUEST[ apply_filters( 'woocommerce_product_wbs_bundle_field_prefix', '', $product_id ) . 'wbs_bundle_quantity_' . $bundled_item_id ] ) : $bundled_item->get_quantity();

					$cartstamp[ $bundled_item_id ]['product_id'] = $id;
					$cartstamp[ $bundled_item_id ]['type']       = $bundled_product_type;
					$cartstamp[ $bundled_item_id ]['quantity']   = $bundled_product_quantity;
					$cartstamp[ $bundled_item_id ]               = apply_filters( 'woocommerce_wbs_bundled_item_cart_item_identifier', $cartstamp[ $bundled_item_id ], $bundled_item_id );
				}
				$cart_item_data['wbs_cartstamp']     = $cartstamp;
				$cart_item_data['wbs_bundled_items'] = array();
			}

			return $cart_item_data;
		}


		public function woocommerce_add_to_cart( $cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data ) {
			if ( isset( $cart_item_data['wbs_cartstamp'] ) && ! isset( $cart_item_data['wbs_bundled_by'] ) ) {
				$variations = array();
				if ( isset( $_POST['vi_chosen_product_variable'] ) && is_array( $_POST['vi_chosen_product_variable'] ) && count( $_POST['vi_chosen_product_variable'] ) ) {
					$variations = $_POST['vi_chosen_product_variable'];
				}
				$cart_data = WC()->cart->get_cart();

				$bundled_items_cart_data = array( 'wbs_bundled_by' => $cart_item_key );

				foreach ( $cart_item_data['wbs_cartstamp'] as $bundled_item_id => $bundled_item_stamp ) {
					$bundled_item_cart_data                        = $bundled_items_cart_data;
					$bundled_item_cart_data['wbs_bundled_item_id'] = $bundled_item_id;

					$item_quantity = $bundled_item_stamp['quantity'];
					$i_quantity    = $item_quantity * $quantity;
					$prod_id       = $bundled_item_stamp['product_id'];
					$prod_type     = $bundled_item_stamp['type'];
					$attributes    = isset( $bundled_item_stamp['attributes'] ) ? $bundled_item_stamp['attributes'] : array();
					if ( self::$settings->get_option( 'override_products_on_cart' ) ) {
						if ( $prod_type == 'variation' && count( $attributes ) ) {
							foreach ( $cart_data as $key => $product ) {
								$product_quantity = $product['quantity'];
								if ( $product['variation_id'] == $prod_id && ! count( array_diff( $product['variation'], $attributes ) ) ) {
									if ( ! isset( $product['wbs_bundled_item_id'], $product['bundled_item_id'] ) ) {
										WC()->cart->cart_contents[ $key ]['quantity'] = ( $quantity > $product_quantity ) ? ( 0 ) : ( $product_quantity - $quantity );
									}
								}
							}
						} else {
							foreach ( $cart_data as $key => $product ) {
								$product_quantity = $product['quantity'];
								if ( ( $prod_id == $product['product_id'] || $prod_id == $product['variation_id'] ) && ! isset( $product['wbs_bundled_item_id'], $product['bundled_item_id'] ) ) {
									WC()->cart->cart_contents[ $key ]['quantity'] = ( $quantity > $product_quantity ) ? ( 0 ) : ( $product_quantity - $quantity );
								}
							}
						}

					}
					if ( $prod_type == 'variation' ) {
						$parent_id = wp_get_post_parent_id( $prod_id );
						if ( isset( $variations[ $parent_id ] ) ) {
							$variation = $variations[ $parent_id ];
						}
					} else {
						$variation = array();
					}
					$bundled_item_cart_key = $this->bundled_add_to_cart( $product_id, $prod_id, $i_quantity, $variation_id, $variation, $bundled_item_cart_data );

					if ( $bundled_item_cart_key && ! in_array( $bundled_item_cart_key, WC()->cart->cart_contents[ $cart_item_key ]['wbs_bundled_items'] ) ) {
						WC()->cart->cart_contents[ $cart_item_key ]['wbs_bundled_items'][] = $bundled_item_cart_key;
						WC()->cart->cart_contents[ $cart_item_key ]['wbs_parent'][]        = $cart_item_key;
					}
				}
			}

		}

		/**
		 * @param $bundle_id
		 * @param $product_id
		 * @param int $quantity
		 * @param string $variation_id
		 * @param string $variation
		 * @param string $cart_item_data
		 *
		 * @return bool|string
		 */
		public function bundled_add_to_cart( $bundle_id, $product_id, $quantity = 1, $variation_id = '', $variation = '', $cart_item_data = '' ) {
			if ( $quantity <= 0 ) {
				return false;
			}
			$cart_item_data = ( array ) apply_filters( 'woocommerce_add_cart_item_data', $cart_item_data, $product_id, $variation_id, $quantity );
			$cart_id        = WC()->cart->generate_cart_id( $product_id, $variation_id, $variation, $cart_item_data );
			$cart_item_key  = WC()->cart->find_product_in_cart( $cart_id );

			if ( 'product_variation' == get_post_type( $product_id ) ) {
				$variation_id = $product_id;
				$product_id   = wp_get_post_parent_id( $variation_id );
			}

			$product_data = wc_get_product( $variation_id ? $variation_id : $product_id );
			wbs_set_prop( $product_data, 'wbs_wcpb_is_bundled', true );

			if ( ! $cart_item_key ) {

				$cart_item_key                              = $cart_id;
				WC()->cart->cart_contents[ $cart_item_key ] = apply_filters(
					'woocommerce_add_cart_item', array_merge(
					$cart_item_data, array(
						'product_id'   => $product_id,
						'variation_id' => $variation_id,
						'variation'    => $variation,
						'quantity'     => $quantity,
						'data'         => $product_data
					)
				), $cart_item_key
				);
			}

			return $cart_item_key;
		}

		/**
		 * remove 'remove link' for bundled product in cart
		 */
		public function woocommerce_cart_item_remove_link( $link, $cart_item_key ) {
			if ( isset( WC()->cart->cart_contents[ $cart_item_key ]['wbs_bundled_by'] ) ) {
				$bundle_cart_key = WC()->cart->cart_contents[ $cart_item_key ]['wbs_bundled_by'];
				if ( isset( WC()->cart->cart_contents[ $bundle_cart_key ] ) ) {
					return '';
				}
			}

			return $link;
		}

		/**
		 * cart item quantity
		 *
		 */
		public function woocommerce_cart_item_quantity( $quantity, $cart_item_key ) {
			if ( isset( WC()->cart->cart_contents[ $cart_item_key ]['wbs_bundled_by'] ) ) {
				return WC()->cart->cart_contents[ $cart_item_key ]['quantity'];
			}

			return $quantity;
		}

		/**
		 * update cart item quantity
		 *
		 */
		public function update_cart_item_quantity( $cart_item_key, $quantity = 0 ) {
			if ( ! empty( WC()->cart->cart_contents[ $cart_item_key ] ) ) {

				if ( $quantity <= 0 ) {
					$quantity = 0;
				} else {
					$quantity = WC()->cart->cart_contents[ $cart_item_key ]['quantity'];
				}

				if ( ! empty( WC()->cart->cart_contents[ $cart_item_key ]['wbs_cartstamp'] ) && ! isset( WC()->cart->cart_contents[ $cart_item_key ]['wbs_bundled_by'] ) ) {
					$stamp = WC()->cart->cart_contents[ $cart_item_key ]['wbs_cartstamp'];
					foreach ( WC()->cart->cart_contents as $key => $value ) {
						if ( isset( $value['wbs_bundled_by'] ) && $cart_item_key == $value['wbs_bundled_by'] ) {
							$bundle_item_id  = $value['wbs_bundled_item_id'];
							$bundle_quantity = $stamp[ $bundle_item_id ]['quantity'];
							WC()->cart->set_quantity( $key, $quantity * $bundle_quantity, false );
						}
					}
				}
			}
		}

		/**
		 * Remove cart item price for bundled product
		 *
		 * @param $price
		 * @param $cart_item
		 * @param $cart_item_key
		 *
		 * @return string
		 */
		public function woocommerce_cart_item_price( $price, $cart_item, $cart_item_key ) {
			if ( isset( $cart_item['wbs_bundled_by'] ) ) {
				$bundle_cart_key = $cart_item['wbs_bundled_by'];
				if ( isset( WC()->cart->cart_contents[ $bundle_cart_key ] ) ) {
					return '';
				}
			}

			return $price;
		}

		/**
		 * Remove cart item subtotal for bundled product
		 *
		 * @param $subtotal
		 * @param $cart_item
		 * @param $cart_item_key
		 *
		 * @return string
		 */
		public function bundles_item_subtotal( $subtotal, $cart_item, $cart_item_key ) {
			if ( isset( $cart_item['wbs_bundled_by'] ) ) {
				$bundle_cart_key = $cart_item['wbs_bundled_by'];
				if ( isset( WC()->cart->cart_contents[ $bundle_cart_key ] ) ) {
					return '';
				}
			}
			if ( isset( $cart_item['wbs_bundled_items'] ) ) {
				if ( $cart_item['data']->get_price() == 0 ) {
					return '';
				}
			}

			return $subtotal;
		}

		/**
		 * get template for Bundle Product add to cart
		 *
		 */
		public function woocommerce_wbs_bundle_add_to_cart() {
			/** @var WC_Product_Wbs_Bundle $product */
			global $product;
			$bundled_items = $product->get_bundled_items();
			if ( $bundled_items ) {
				wbs_get_template( 'single-product/add-to-cart/wbs-bundle.php', array(), '', VI_WBOOSTSALES_TEMPLATES );
			}
		}

		/**
		 * @param $link
		 * @param $product WC_Product
		 *
		 * @return mixed
		 */
		public function woocommerce_loop_add_to_cart_link( $link, $product ) {
			if ( $product->get_type() == 'wbs_bundle' ) {
				if ( $product->is_in_stock() && $product->all_items_in_stock() && ! $product->has_variables() ) {
					return str_replace( 'product_type_bundle', 'product_type_bundle product_type_simple', $link );
				} else {
					return str_replace( 'add_to_cart_button', '', $link );
				}
			}

			return $link;
		}

		/**
		 * @param $add_flag
		 * @param $product_id
		 * @param $product_quantity
		 * @param string $variation_id
		 * @param array $variations
		 * @param array $cart_item_data
		 *
		 * @return bool
		 */
		public function woocommerce_add_to_cart_validation( $add_flag, $product_id, $product_quantity, $variation_id = '', $variations = array(), $cart_item_data = array() ) {
			/** @var WC_Product_Wbs_Bundle $product */
			$product = wc_get_product( $product_id );
			if ( $product && $product->is_type( 'wbs_bundle' ) && get_option( 'woocommerce_manage_stock' ) == 'yes' ) {
				$bundled_items = $product->get_bundled_items();
				foreach ( $bundled_items as $bundled_item ) {
					/** @var WBS_WC_Bundled_Item $bundled_item */
					$bundled_prod = $bundled_item->get_product();
					if ( ! $bundled_prod->has_enough_stock( intval( $bundled_item->get_quantity() ) * intval( $product_quantity ) ) ) {
						wc_add_notice( __( 'You cannot add this quantity of items, because there are not enough in stock.', 'woocommerce-boost-sales' ), 'error' );

						return false;
					}
				}
			}

			return $add_flag;
		}

		/**
		 * Set bundle items' price to Zero and store their original prices for latter use
		 *
		 * @param $cart_item
		 * @param $cart_key
		 *
		 * @return mixed
		 */
		public function woocommerce_add_cart_item( $cart_item, $cart_key ) {
			$cart_contents = WC()->cart->cart_contents;
			if ( isset( $cart_item['wbs_bundled_by'] ) ) {
				$bundle_cart_key = $cart_item['wbs_bundled_by'];
				if ( isset( $cart_contents[ $bundle_cart_key ] ) ) {
//					$cart_item['wbs_item_price'] = wc_get_price_to_display( $cart_item['data'], array( 'qty' => 1 ) );
					$cart_item['wbs_item_price'] = self::get_item_price( $cart_item['data'] );
					wbs_set_prop( $cart_item['data'], 'price', 0 );
				}
			}

			return $cart_item;
		}

		/**
		 * when a bundle product is removed, its bundled items are removed too.
		 *
		 */
		public function woocommerce_cart_item_removed( $cart_item_key, $cart ) {

			if ( ! empty( $cart->removed_cart_contents[ $cart_item_key ]['wbs_bundled_items'] ) ) {

				$bundled_item_cart_keys = $cart->removed_cart_contents[ $cart_item_key ]['wbs_bundled_items'];

				foreach ( $bundled_item_cart_keys as $bundled_item_cart_key ) {

					if ( ! empty( $cart->cart_contents[ $bundled_item_cart_key ] ) ) {

						$remove                                                = $cart->cart_contents[ $bundled_item_cart_key ];
						$cart->removed_cart_contents[ $bundled_item_cart_key ] = $remove;

						unset( $cart->cart_contents[ $bundled_item_cart_key ] );

						do_action( 'woocommerce_cart_item_removed', $bundled_item_cart_key, $cart );
					}
				}
			}
		}

		/**
		 * when a bundle product is restored, its bundled items are restored too.
		 *
		 * @access public
		 *
		 * @param         $cart_item_key
		 * @param WC_Cart $cart
		 *
		 * @since  1.0.19
		 * @author Leanza Francesco <leanzafrancesco@gmail.com>
		 *
		 */
		public function woocommerce_cart_item_restored( $cart_item_key, $cart ) {
			if ( ! empty( $cart->cart_contents[ $cart_item_key ]['wbs_bundled_items'] ) ) {
				$bundled_item_cart_keys = $cart->cart_contents[ $cart_item_key ]['wbs_bundled_items'];
				foreach ( $bundled_item_cart_keys as $bundled_item_cart_key ) {
					$cart->restore_cart_item( $bundled_item_cart_key );
				}
			}
		}

		/**
		 * @param $cart_item
		 * @param $item_session_values
		 * @param $cart_item_key
		 *
		 * @return mixed
		 */
		public function woocommerce_get_cart_item_from_session( $cart_item, $item_session_values, $cart_item_key ) {
			$cart_contents = ! empty( WC()->cart ) ? WC()->cart->cart_contents : '';
			if ( isset( $item_session_values['wbs_bundled_items'] ) && ! empty( $item_session_values['wbs_bundled_items'] ) ) {
				$cart_item['wbs_bundled_items'] = $item_session_values['wbs_bundled_items'];
			}

			if ( isset( $item_session_values['wbs_cartstamp'] ) ) {
				$cart_item['wbs_cartstamp'] = $item_session_values['wbs_cartstamp'];
			}

			if ( isset( $item_session_values['wbs_bundled_by'] ) ) {
				$cart_item['wbs_bundled_by']      = $item_session_values['wbs_bundled_by'];
				$cart_item['wbs_bundled_item_id'] = isset( $item_session_values['wbs_bundled_item_id'] ) ? $item_session_values['wbs_bundled_item_id'] : $item_session_values['bundled_item_id'];
				$bundle_cart_key                  = $cart_item['wbs_bundled_by'];
				if ( isset( $cart_contents[ $bundle_cart_key ] ) ) {
//					$cart_item['wbs_item_price'] = wc_get_price_to_display( $cart_item['data'], array( 'qty' => 1 ) );
					$cart_item['wbs_item_price'] = self::get_item_price( $cart_item['data'] );
					wbs_set_prop( $cart_item['data'], 'price', 0 );
					if ( isset( $cart_item['data']->subscription_sign_up_fee ) ) {
						wbs_set_prop( $cart_item['data'], 'subscription_sign_up_fee', 0 );
					}
				}
			}

			return $cart_item;
		}

		/* -----------------------------------------
		ORDER
		---------------------------------------- */

		/**
		 * delete subtotal for bundled items in order
		 *
		 */
		public function woocommerce_order_formatted_line_subtotal( $subtotal, $item, $order ) {
			if ( isset( $item['wbs_bundled_by'] ) ) {
				return '';
			}

			return $subtotal;
		}

		/**
		 * @param $item_id
		 * @param $values
		 * @param $cart_item_key
		 *
		 * @throws Exception
		 */
		public function woocommerce_new_order_item( $item_id, $values, $cart_item_key ) {
			if ( isset( $values['wbs_bundled_by'] ) ) {
				wc_add_order_item_meta( $item_id, '_bundled_by', $values['wbs_bundled_by'] );
			} else {
				if ( isset( $values['wbs_cartstamp'] ) ) {
					wc_add_order_item_meta( $item_id, '_cartstamp', $values['wbs_cartstamp'] );
				}
			}
		}

		public function woocommerce_cart_shipping_packages( $packages ) {

			if ( ! empty( $packages ) ) {
				foreach ( $packages as $package_key => $package ) {
					if ( ! empty( $package['contents'] ) ) {
						foreach ( $package['contents'] as $cart_item => $cart_item_data ) {
							if ( isset( $cart_item_data['wbs_bundled_items'] ) ) {
								// SINGULAR SHIPPING
								if ( isset( $cart_item_data['wbs_parent'] ) && is_array( $cart_item_data['wbs_parent'] ) && count( $cart_item_data['wbs_parent'] ) ) {
									foreach ( $cart_item_data['wbs_parent'] as $parent_bundle_key ) {
										if ( isset( $package['contents'][ $parent_bundle_key ] ) ) {
											unset( $packages[ $package_key ]['contents'][ $parent_bundle_key ] );
										}
									}
								}
							}
						}
					}
				}
			}

			return $packages;
		}

		public static function show_crossell_html() {
			wp_enqueue_script( 'wc-add-to-cart-variation' );
			global $product;
			$main_product = $product;
			ob_start();
			if ( $main_product->is_in_stock() ) {
				$bundled_items = $product->get_bundled_items();
				$array_price   = array();
				$disabled      = '';
				if ( self::$settings->get_detect() === 'mobile' && self::$settings->get_option( 'crosssell_mobile_template' ) === 'scroll' ) {
					$out_stock_html = '<div class="wbs-out-stock">' . esc_html__( 'Out of stock', 'woocommerce-boost-sales' ) . '</div>';
					?>
                    <div class="wbs-cross-sells-mobile-items">
						<?php
						if ( count( $bundled_items ) ) {
							?>
                            <div class="wbs-cross-sells wbs-vi-slides"
                                 data-rtl="<?php echo esc_attr( is_rtl() ? 1 : 0 ) ?>">
								<?php
								foreach ( $bundled_items as $bundled_item ) {
									/**
									 * @var WBS_WC_Bundled_Item $bundled_item
									 */
									$bundled_product = $bundled_item->get_product();
									$product_id      = $bundled_product->get_id();
									$array_price[]   = wc_get_price_to_display( $bundled_product, array( 'qty' => $bundled_item->get_quantity() ) );
									?>
                                    <div <?php post_class( 'wbs-product bundle-' . $product_id ) ?>
                                            data-item_quantity="<?php echo esc_attr( $bundled_item->get_quantity() ) ?>"
                                            data-item_image="<?php echo esc_attr( json_encode( wc_get_product_attachment_props( $bundled_product->get_image_id() ) ) ) ?>"
                                            data-item_price="<?php echo esc_attr( VI_WBOOSTSALES_Data::convert_price_to_float( wc_get_price_to_display( $bundled_product, array( 'qty' => 1 ) ) ) ) ?>">
                                        <div class="product-top">
                                            <div class="product-image">
                                                <a href="<?php echo esc_url( $bundled_product->get_permalink() ) ?>">
													<?php do_action( 'woocommerce_boost_sales_before_shop_loop_item_title', $bundled_product ) ?>
                                                </a>
												<?php
												if ( $bundled_item->get_quantity() > 1 ) {
													?>
                                                    <span class="product-quantity"><?php echo 'x' . esc_html( $bundled_item->get_quantity() ); ?></span>
													<?php
												}
												if ( ! $bundled_product->is_in_stock() ) {
													$disabled = 'disabled="true"';
													echo $out_stock_html;
												}
												?>
                                            </div>
                                        </div>
                                        <div class="product-desc">
											<?php
											do_action( 'woocommerce_boost_sales_shop_loop_item_title', $bundled_product );
											do_action( 'woocommerce_boost_sales_after_shop_loop_item_title', $bundled_product );
											if ( $bundled_product->has_child() && $bundled_product->get_type() === 'variable' ) {
												?>
                                                <span class="price wbs-bundle-item-variation-price"></span>
												<?php
												$attributes           = $bundled_product->get_variation_attributes();
												$available_variations = self::get_available_variations( $bundled_product );
												?>
                                                <div class="wbs-variation product">
                                                    <div class="wbs-variations_form cart"
                                                         data-product_id="<?php echo absint( $product_id ); ?>"
                                                         data-product_variations="<?php echo htmlspecialchars( wp_json_encode( $available_variations ) ) ?>">
                                                        <div class="variations">
															<?php
															if ( count( $available_variations ) ) {
																if ( count( $attributes ) ) {
																	$added_variation = self::get_selected_attributes( $product_id );
																	foreach ( $attributes as $attribute_name => $options ) {
																		$selected_attribute_name = VI_WBOOSTSALES_Data::sanitize_taxonomy_name( "attribute_{$attribute_name}" );
																		$selected                = '';
																		if ( isset( $added_variation[ $selected_attribute_name ] ) ) {
																			$selected = $added_variation[ $selected_attribute_name ];
																		}
																		if ( ! $selected ) {
																			$selected = isset( $_REQUEST[ $selected_attribute_name ] ) ? wc_clean( stripslashes( $_REQUEST[ $selected_attribute_name ] ) ) : $bundled_product->get_variation_default_attribute( $attribute_name );
																		}
																		$select_id = "vi-wbs-crs-{$product_id}-$attribute_name";
																		$select_id = function_exists( 'mb_strtolower' ) ? mb_strtolower( $select_id ) : strtolower( $select_id );
																		self::wc_dropdown_variation_attribute_options( array(
																			'options'          => $options,
																			'attribute'        => $attribute_name,
																			'product'          => $bundled_product,
																			'selected'         => $selected,
																			'id'               => $select_id,
																			'name'             => 'vi_chosen_product_variable[' . $product_id . '][' . $selected_attribute_name . ']',
																			'show_option_none' => sprintf( esc_html__( 'Choose %s', 'woocommerce-boost-sales' ), wc_attribute_label( $attribute_name ) )
																		) );
																	}
																} else {
																	ob_end_clean();

																	return false;
																}
															} else {
																ob_end_clean();

																return false;
															}
															?>
                                                        </div>
                                                    </div>
                                                </div>
												<?php
											}
											?>
                                        </div>
                                    </div>
									<?php
								}
								?>
                            </div>
							<?php
						}
						?>
                    </div>
					<?php
				} else {

					?>
                    <div id="flexslider-cross-sells" class="vi-flexslider">
						<?php

						$out_stock_html = '<div class="wbs-out-stock">' . esc_html__( 'Out of stock', 'woocommerce-boost-sales' ) . '</div>';
						if ( ! self::$settings->get_option( 'crosssell_display_on_slide' ) ) {
							$class = 'wbs-products-' . count( $bundled_items );
						} else {
							$class = 'wbs-vi-slides-' . count( $bundled_items );
						}
						if ( count( $bundled_items ) ) {
							?>
                            <div class="wbs-cross-sells wbs-vi-slides <?php echo esc_attr( $class ) ?>"
                                 data-rtl="<?php echo esc_attr( is_rtl() ? 1 : 0 ) ?>">
								<?php
								foreach ( $bundled_items as $bundled_item_k => $bundled_item ) {
									/**
									 * @var WBS_WC_Bundled_Item $bundled_item
									 */
									$bundled_product = $bundled_item->get_product();
									$product_id      = $bundled_product->get_id();
									$array_price[]   = wc_get_price_to_display( $bundled_product, array( 'qty' => $bundled_item->get_quantity() ) );
									?>
                                    <div <?php post_class( 'wbs-product bundle-' . $product_id ) ?>
                                            data-item_quantity="<?php echo esc_attr( $bundled_item->get_quantity() ) ?>"
                                            data-item_image="<?php echo esc_attr( json_encode( wc_get_product_attachment_props( $bundled_product->get_image_id() ) ) ) ?>"
                                            data-item_price="<?php echo esc_attr( VI_WBOOSTSALES_Data::convert_price_to_float( wc_get_price_to_display( $bundled_product, array( 'qty' => 1 ) ) ) ) ?>">
                                        <div class="product-top">
                                            <div class="product-image">
                                                <a href="<?php echo esc_url( $bundled_product->get_permalink() ) ?>">
													<?php do_action( 'woocommerce_boost_sales_before_shop_loop_item_title', $bundled_product ) ?>
                                                </a>
												<?php if ( $bundled_item->get_quantity() > 1 ) {
													?>
                                                    <span class="product-quantity"><?php echo 'x' . esc_html( $bundled_item->get_quantity() ); ?></span>
													<?php
												}
												if ( ! $bundled_product->is_in_stock() ) {
													$disabled = 'disabled="true"';
													echo $out_stock_html;
												}
												?>
                                            </div>
                                        </div>
                                        <div class="product-desc">
											<?php
											do_action( 'woocommerce_boost_sales_shop_loop_item_title', $bundled_product );
											do_action( 'woocommerce_boost_sales_after_shop_loop_item_title', $bundled_product );
											if ( $bundled_product->has_child() && $bundled_product->get_type() === 'variable' ) {
												?>
                                                <span class="price wbs-bundle-item-variation-price"></span>
												<?php
												$attributes           = $bundled_product->get_variation_attributes();
												$available_variations = self::get_available_variations( $bundled_product );
												?>
                                                <div class="wbs-variation product">
                                                    <div class="wbs-variations_form cart"
                                                         data-product_id="<?php echo absint( $product_id ); ?>"
                                                         data-product_variations="<?php echo htmlspecialchars( wp_json_encode( $available_variations ) ) ?>">
                                                        <div class="variations">
															<?php
															if ( count( $available_variations ) ) {
																if ( count( $attributes ) ) {
																	$added_variation = self::get_selected_attributes( $product_id );
																	foreach ( $attributes as $attribute_name => $options ) {
																		$selected_attribute_name = VI_WBOOSTSALES_Data::sanitize_taxonomy_name( "attribute_{$attribute_name}" );
																		$selected                = '';
																		if ( isset( $added_variation[ $selected_attribute_name ] ) ) {
																			$selected = $added_variation[ $selected_attribute_name ];
																		}
																		if ( ! $selected ) {
																			$selected = isset( $_REQUEST[ $selected_attribute_name ] ) ? wc_clean( stripslashes( $_REQUEST[ $selected_attribute_name ] ) ) : $bundled_product->get_variation_default_attribute( $attribute_name );
																		}
																		$select_id = "vi-wbs-crs-{$product_id}-$attribute_name";
																		$select_id = function_exists( 'mb_strtolower' ) ? mb_strtolower( $select_id ) : strtolower( $select_id );
																		self::wc_dropdown_variation_attribute_options( array(
																			'options'          => $options,
																			'attribute'        => $attribute_name,
																			'product'          => $bundled_product,
																			'selected'         => $selected,
																			'id'               => $select_id,
																			'name'             => 'vi_chosen_product_variable[' . $product_id . '][' . $selected_attribute_name . ']',
																			'show_option_none' => sprintf( esc_html__( 'Choose %s', 'woocommerce-boost-sales' ), wc_attribute_label( $attribute_name ) )
																		) );
																	}
																} else {
																	ob_end_clean();

																	return false;
																}
															} else {
																ob_end_clean();

																return false;
															}
															?>
                                                        </div>
                                                    </div>
                                                </div>
												<?php
											}
											?>
                                        </div>
                                    </div>
									<?php
								}
								?>
                            </div>
							<?php
						}

						?>
                    </div>
					<?php

				}

				$sum_pr               = array_sum( $array_price );
				$dynamic_price        = get_post_meta( $main_product->get_id(), '_wbs_dynamic_price', true );
				$has_dynamic_discount = false;
				$percent              = 0;
				$discount_type        = get_post_meta( $main_product->get_id(), '_wbs_discount_type', true );
				if ( $dynamic_price ) {
					$discount_amount = get_post_meta( $main_product->get_id(), '_wbs_discount_amount', true );
					if ( $discount_amount > 0 ) {
						$has_dynamic_discount = true;
						if ( $discount_type === 'percent' ) {
							$percent              = $discount_amount;
							$product_bundle_price = VI_WBOOSTSALES_Data::convert_price_to_float( ( 1 - $percent / 100 ) * $sum_pr );
							$save_price           = $sum_pr - $product_bundle_price;
						} else {
							$discount_amount = apply_filters( 'wmc_change_3rd_plugin_price', $discount_amount );
							if ( $discount_amount > $sum_pr ) {
								$save_price = $sum_pr;
							} else {
								$save_price = $discount_amount;
							}
						}
					} else {
						$save_price = 0;
					}
				} else {
//					$product_bundle_price = wc_get_price_to_display( $product );
					$product_bundle_price = self::get_item_price( $product );
					$save_price           = $sum_pr - $product_bundle_price;
				}
				?>
                <div class="vi-crosssells-atc">
                    <div class="wbs-crosssells-price">
                        <div class="wbs-crs-regular-price">
                            <span class="wbs-new-title"><?php esc_html_e( 'Price: ', 'woocommerce-boost-sales' ) ?></span>
                            <span class="wbs-money"
                                  style="float: none;"><span
                                        class="wbs-crosssells-overall-price"><?php echo wc_price( wc_get_price_to_display( $main_product ) ); ?></span><span
                                        class="wbs-crosssells-atc-price"></span></span>
                        </div>
						<?php
						$display_saved_price = intval( self::$settings->get_option( 'display_saved_price' ) );

						if ( ( $save_price > 0 || $has_dynamic_discount ) && in_array( $display_saved_price, array(
								0,
								1
							) ) ) {
							?>
                            <div class="wbs-crs-save-price">
                                <div class="wbs-total-price">
                                    <span class="wbs-new-title"><?php esc_html_e( 'Total: ', 'woocommerce-boost-sales' ) ?></span>
                                    <del>
                                        <span class="wbs-total-price-origin"><?php echo wc_price( $sum_pr ); ?></span>
                                        <span class="wbs-total-price-current"></span>
                                    </del>
                                </div>
                                <div class="wbs-save-price">
									<?php
									if ( $display_saved_price === 1 && $discount_type === 'percent' ) {
										if ( ! $has_dynamic_discount ) {
											$percent = round( $save_price / $sum_pr * 100, 0 );
										}
										?>
                                        <span class="wbs-new-title"><?php esc_html_e( 'Save: ', 'woocommerce-boost-sales' ) ?></span>
                                        <span class="wbs-saved-price-percent wbs-saved-price-display"><?php echo esc_html( $percent ); ?>%</span>
										<?php
									} elseif ( $display_saved_price !== 2 ) {
										?>
                                        <span class="wbs-new-title"><?php esc_html_e( 'Save: ', 'woocommerce-boost-sales' ) ?></span>
                                        <span class="wbs-save-price-origin"><?php echo $display_saved_price === 0 ? wc_price( $save_price ) : round( $save_price / $sum_pr * 100, 0 ) . '%'; ?></span>
                                        <span class="wbs-save-price-current"></span>
										<?php
									}

									?>
                                </div>
                            </div>
							<?php
						}
						?>
                    </div>
                    <div class="wbs-crosssells-button-atc">
						<?php
						if ( ! $main_product->is_sold_individually() ) {
							woocommerce_quantity_input(
								array(
									'min_value' => apply_filters( 'woocommerce_quantity_input_min', 1, $main_product ),
									'max_value' => apply_filters( 'woocommerce_quantity_input_max', $main_product->backorders_allowed() ? '' : $main_product->get_stock_quantity(), $main_product )
								)
							);
						}
						?>
                        <input type="hidden" name="add-to-cart"
                               value="<?php echo esc_attr( $main_product->get_id() ); ?>"/>
                        <button <?php echo $disabled ?> type="submit"
                                                        class="wbs-single_add_to_cart_button button alt"><?php echo $main_product->single_add_to_cart_text(); ?></button>

                    </div>
                </div>
				<?php
			}

			return ob_get_clean();
		}

		private static function get_available_variations( $product ) {
			$available_variations = array();

			foreach ( $product->get_children() as $child_id ) {
				$variation = wc_get_product( $child_id );

				// Hide out of stock variations if 'Hide out of stock items from the catalog' is checked.
				if ( ! $variation || ! $variation->exists() || ( ! $variation->is_in_stock() && ( 'yes' === get_option( 'woocommerce_hide_out_of_stock_items' ) || self::$settings->get_option( 'hide_out_of_stock' ) ) ) ) {
					continue;
				}

				// Filter 'woocommerce_hide_invisible_variations' to optionally hide invisible variations (disabled variations and variations with empty price).
				if ( apply_filters( 'woocommerce_hide_invisible_variations', true, $product->get_id(), $variation ) && ! $variation->variation_is_visible() ) {
					continue;
				}

				$available_variations[] = $product->get_available_variation( $variation );
			}
			$available_variations = array_values( array_filter( $available_variations ) );

			return $available_variations;
		}

		public static function wc_dropdown_variation_attribute_options( $args = array() ) {
			$args = wp_parse_args( apply_filters( 'wbs_crosssell_woocommerce_dropdown_variation_attribute_options_args', $args ), array(
				'options'          => false,
				'attribute'        => false,
				'product'          => false,
				'selected'         => false,
				'name'             => '',
				'id'               => '',
				'class'            => '',
				'show_option_none' => __( 'Choose an option', 'woocommerce' ),
			) );

			// Get selected value.
			if ( false === $args['selected'] && $args['attribute'] && $args['product'] instanceof WC_Product ) {
				$selected_key     = sanitize_title( $args['attribute'] );
				$args['selected'] = isset( $_REQUEST[ $selected_key ] ) ? wc_clean( wp_unslash( $_REQUEST[ $selected_key ] ) ) : $args['product']->get_variation_default_attribute( $args['attribute'] ); // WPCS: input var ok, CSRF ok, sanitization ok.
			}
			$options               = $args['options'];
			$product               = $args['product'];
			$attribute             = $args['attribute'];
			$name                  = $args['name'] ? $args['name'] : sanitize_title( $attribute );
			$id                    = $args['id'] ? $args['id'] : sanitize_title( $attribute );
			$class                 = $args['class'];
			$show_option_none_text = $args['show_option_none'] ? $args['show_option_none'] : __( 'Choose an option', 'woocommerce' );
			if ( empty( $options ) && ! empty( $product ) && ! empty( $attribute ) ) {
				$attributes = $product->get_variation_attributes();
				$options    = $attributes[ $attribute ];
			}
			$html = '<select id="' . esc_attr( $id ) . '" class="' . esc_attr( $class ) . '" name="' . esc_attr( $name ) . '" data-attribute_name="attribute_' . esc_attr( sanitize_title( $attribute ) ) . '">';
			if ( $args['show_option_none'] ) {
				$html .= '<option value="">' . esc_html( $show_option_none_text ) . '</option>';
			}
			if ( ! empty( $options ) ) {
				if ( $product && taxonomy_exists( $attribute ) ) {
					// Get terms if this is a taxonomy - ordered. We need the names too.
					$terms = wc_get_product_terms( $product->get_id(), $attribute, array(
						'fields' => 'all',
					) );
					$i     = 0;
					foreach ( $terms as $term ) {
						if ( in_array( $term->slug, $options, true ) ) {
							$selected = selected( sanitize_title( $args['selected'] ), $term->slug, false );
							$html     .= '<option value="' . esc_attr( $term->slug ) . '" ' . $selected . '>' . esc_html( apply_filters( 'woocommerce_variation_option_name', $term->name ) ) . '</option>';
						}
						$i ++;
					}
				} else {
					foreach ( $options as $option ) {
						// This handles < 2.4.0 bw compatibility where text attributes were not sanitized.
//						$selected = ( $i = 0 ? 'selected' : '' );
						$selected = sanitize_title( $args['selected'] ) === $args['selected'] ? selected( $args['selected'], sanitize_title( $option ), false ) : selected( $args['selected'], $option, false );
						$html     .= '<option value="' . esc_attr( $option ) . '" ' . $selected . '>' . esc_html( apply_filters( 'woocommerce_variation_option_name', $option ) ) . '</option>';
					}
				}
			}

			$html .= '</select>';
			echo apply_filters( 'wbs_crosssell_woocommerce_dropdown_variation_attribute_options_html', $html, $args ); // WPCS: XSS ok.
		}

		public function enqueue_scripts() {
			if ( self::$settings->get_option( 'enable' ) ) {
				wp_enqueue_style( 'wbs-wcpb-admin-styles', VI_WBOOSTSALES_CSS . 'bundle-frontend.css' );
			}
		}

		public static function get_selected_attributes( $product_id ) {
			$selected_attributes = array();
			$items               = WC()->cart->get_cart_contents();
			if ( count( $items ) ) {
				foreach ( $items as $item ) {
					if ( $item['product_id'] == $product_id ) {
						$selected_attributes = $item['variation'];
						break;
					}
				}
			}

			return $selected_attributes;
		}

		private static function get_item_price( $item, $quantity = 1 ) {
			return wc_prices_include_tax() ? wc_get_price_including_tax( $item, [ 'qty' => $quantity ] ) : wc_get_price_excluding_tax( $item, [ 'qty' => $quantity ] );
		}
	}
}