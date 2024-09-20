<?php

/**
 * Class VI_WBOOSTSALES_Frontend_Upsells
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VI_WBOOSTSALES_Frontend_Upsells {
	protected $settings;
	public static $added_to_cart;
	public static $cart_item_key;
	public static $grouped_quantity = 0;
	public static $grouped_total;

	public function __construct() {
		$this->settings      = VI_WBOOSTSALES_Data::get_instance();
		self::$cart_item_key = '';
		self::$added_to_cart = array();
		if ( $this->settings->enable() ) {
			/*Add to cart template*/
			add_action( 'woocommerce_boost_sales_single_product_summary', array( $this, 'add_to_cart_template' ) );
			add_action( 'woocommerce_boost_sales_single_product_summary_mobile', array( $this, 'add_to_cart_template_mobile' ) );
			add_action( 'woocommerce_boost_sales_simple_add_to_cart', array( $this, 'woocommerce_boost_sales_simple_add_to_cart' ) );
			add_action( 'woocommerce_boost_sales_variable_add_to_cart', array( $this, 'woocommerce_boost_sales_variable_add_to_cart' ) );
			add_action( 'woocommerce_boost_sales_single_variation', array( $this, 'woocommerce_boost_sales_single_variation' ) );

			add_action( 'woocommerce_boost_sales_simple_add_to_cart_mobile', array( $this, 'woocommerce_boost_sales_simple_add_to_cart_mobile' ) );
			add_action( 'woocommerce_boost_sales_variable_add_to_cart_mobile', array( $this, 'woocommerce_boost_sales_variable_add_to_cart_mobile' ) );
			add_action( 'woocommerce_boost_sales_single_variation_mobile', array( $this, 'woocommerce_boost_sales_single_variation_mobile' ) );


			add_action( 'woocommerce_boost_sales_single_product_summary', array( $this, 'product_link' ) );
			add_action( 'woocommerce_boost_sales_single_product_summary_mobile', array( $this, 'product_link' ) );
			/**
			 * woocommerce_before_shop_loop_item_title hook.
			 *
			 * @hooked woocommerce_template_loop_product_thumbnail - 10
			 */
			add_action( 'woocommerce_boost_sales_before_shop_loop_item_title', array( $this, 'woocommerce_template_loop_product_thumbnail' ) );
			/**
			 * woocommerce_shop_loop_item_title hook.
			 *
			 * @hooked woocommerce_template_loop_product_title - 10
			 */
			add_action( 'woocommerce_boost_sales_shop_loop_item_title', array( $this, 'woocommerce_template_loop_product_title' ) );

			/**
			 * woocommerce_after_shop_loop_item_title hook.
			 *
			 * @hooked woocommerce_template_loop_rating - 5
			 * @hooked woocommerce_template_loop_price - 10
			 */
			add_action( 'woocommerce_boost_sales_after_shop_loop_item_title', array( $this, 'product_rate' ), 5 );
			add_action( 'woocommerce_boost_sales_after_shop_loop_item_title', array( $this, 'product_price' ), 10 );
			if ( $this->settings->get_option( 'enable_upsell' ) ) {
				add_action( 'woocommerce_add_to_cart', array( $this, 'woocommerce_add_to_cart' ), 999, 6 );
			}
		}
	}

	public static function disable_cart_redirect_after_add() {
		return 'no';
	}

	/**
	 * @param $cart_item_key
	 * @param $product_id
	 * @param $quantity
	 * @param $variation_id
	 * @param $variation
	 * @param $cart_item_data
	 */
	public function woocommerce_add_to_cart( $cart_item_key, $product_id, $quantity, $variation_id, $variation, $cart_item_data ) {
		if ( $this->settings->get_option( 'ajax_button' ) || $this->settings->get_option( 'show_if_empty' ) || self::get_upsells_ids( $product_id ) ) {
			add_filter( 'option_woocommerce_cart_redirect_after_add', array( __CLASS__, 'disable_cart_redirect_after_add' ) );
		}

		$product = wc_get_product( $product_id );
		if ( $product ) {
			if ( $product->is_type( 'bundle' ) ) {
				self::$cart_item_key = $cart_item_key;
			}

			if ( $cart_item_key ) {
				self::$added_to_cart[ $product_id ] = array(
					'quantity'        => $quantity,
					'variation_id'    => $variation_id,
					'variation'       => array(),
					'variation_image' => '',
					'price'           => false
				);

				if ( isset( WC()->cart->cart_contents[ $cart_item_key ] ) ) {
					$cart_product = WC()->cart->cart_contents[ $cart_item_key ]['data'];

					if ( $cart_product ) {
						if ( $cart_product->is_taxable() ) {
							self::$added_to_cart[ $product_id ]['price'] = wc_get_price_to_display( $cart_product, array( 'qty' => 1 ) );
						} else {
							self::$added_to_cart[ $product_id ]['price'] = $cart_product->get_price();
						}

						$p_price = $cart_product->is_taxable() ? wc_get_price_to_display( $cart_product ) : $cart_product->get_price();

						self::$grouped_quantity += $quantity;
						self::$grouped_total    += $p_price * $quantity;

						self::$added_to_cart[ $product_id ]['formatted_price'] = $p_price;
//						self::$added_to_cart[ $product_id ]['added_product']   = "<div class='wbs-added-product-row'><div class='wbs-added-product-title'>{$cart_product->get_name()}</div><div class='wbs-added-product-price'> x {$quantity} = $p_price</div></div>";
					}
				}

				if ( is_array( $variation ) && count( $variation ) ) {
					self::$added_to_cart[ $product_id ]['variation'] = array_map( 'urldecode', $variation );
				}

				if ( $variation_id ) {
					$variation_obj = wc_get_product( $variation_id );
					if ( $variation_obj ) {
						$variation_image = VI_WBOOSTSALES_Upsells::get_product_image( $variation_obj );
						if ( $variation_image ) {
							self::$added_to_cart[ $product_id ]['variation_image'] = $variation_image;
						}
					}
				}
			}
		}
	}

	/**
	 * @param $product WC_Product
	 */
	public function woocommerce_template_loop_product_title( $product ) {
		echo '<span class="woocommerce-loop-product__title">' . $product->get_title() . '</span>';
	}

	/**
	 * @param $product WC_Product
	 */
	public function woocommerce_template_loop_product_thumbnail( $product ) {
		echo VI_WBOOSTSALES_Upsells::get_product_image( $product ); // WPCS: XSS ok.
	}

	/**
	 * @param $product
	 */
	public function woocommerce_boost_sales_single_variation( $product ) {
		echo '<div class="woocommerce-variation single_variation"></div>';
		wbs_get_template( 'single-product/add-to-cart/variation-add-to-cart-button.php', array(
			'product'       => $product,
			'hide_quantity' => $this->settings->get_option( 'hide_quantity' )
		), '', VI_WBOOSTSALES_TEMPLATES );
	}

	public function woocommerce_boost_sales_single_variation_mobile( $product ) {
		echo '<div class="woocommerce-variation single_variation"></div>';
		wbs_get_template( 'single-product/add-to-cart/variation-add-to-cart-button-mobile.php', array(
			'product'       => $product,
			'hide_quantity' => $this->settings->get_option( 'hide_quantity' )
		), '', VI_WBOOSTSALES_TEMPLATES );
	}

	/**
	 * @param $product WC_Product_Variable
	 */
	public function woocommerce_boost_sales_variable_add_to_cart( $product ) {
		// Enqueue variation scripts.
		wp_enqueue_script( 'wc-add-to-cart-variation' );

		// Get Available variations?
		$get_variations = count( $product->get_children() ) <= apply_filters( 'woocommerce_ajax_variation_threshold', 30, $product );

		$tmpl       = $this->settings->get_option( 'upsells_select_option_template' );
		$get_detect = $this->settings->get_detect();
		$atc_style  = $get_detect === 'mobile' ? $this->settings->get_option( 'add_to_cart_style_mobile' ) : $this->settings->get_option( 'add_to_cart_style' );

		if ( $tmpl == 'button' && 'visible' == $atc_style ) {
			$product_type = $product->get_type();
			if ( $product_type === 'variable' ) {
				wbs_get_template(
					'single-product/add-to-cart/upsell-variable-swatches.php', array(
					'available_variations' => $get_variations ? $product->get_available_variations() : false,
					'attributes'           => $product->get_variation_attributes(),
					'selected_attributes'  => $product->get_default_attributes(),
					'product'              => $product,
				), '', VI_WBOOSTSALES_TEMPLATES
				);
			}
		} else {
			// Load the template.
			wbs_get_template(
				'single-product/add-to-cart/variable.php', array(
				'available_variations' => $get_variations ? $product->get_available_variations() : false,
				'attributes'           => $product->get_variation_attributes(),
				'selected_attributes'  => $product->get_default_attributes(),
				'product'              => $product,
			), '', VI_WBOOSTSALES_TEMPLATES
			);
		}
	}

	/**
	 * @param $product WC_Product_Variable
	 */
	public function woocommerce_boost_sales_variable_add_to_cart_mobile( $product ) {
		// Enqueue variation scripts.
		wp_enqueue_script( 'wc-add-to-cart-variation' );

		// Get Available variations?
		$get_variations = count( $product->get_children() ) <= apply_filters( 'woocommerce_ajax_variation_threshold', 30, $product );

		// Load the template.
		wbs_get_template(
			'single-product/add-to-cart/variable-mobile.php', array(
			'available_variations' => $get_variations ? $product->get_available_variations() : false,
			'attributes'           => $product->get_variation_attributes(),
			'selected_attributes'  => $product->get_default_attributes(),
			'product'              => $product,
		), '', VI_WBOOSTSALES_TEMPLATES
		);
	}

	/**
	 * @param $product
	 */
	public function woocommerce_boost_sales_simple_add_to_cart( $product ) {
		wbs_get_template( 'single-product/add-to-cart/simple.php', array(
			'product'       => $product,
			'hide_quantity' => $this->settings->get_option( 'hide_quantity' )
		), '', VI_WBOOSTSALES_TEMPLATES );
	}

	public function woocommerce_boost_sales_simple_add_to_cart_mobile( $product ) {
		wbs_get_template( 'single-product/add-to-cart/simple-mobile.php', array(
			'product'       => $product,
			'hide_quantity' => $this->settings->get_option( 'hide_quantity' )
		), '', VI_WBOOSTSALES_TEMPLATES );
	}

	/**
	 * @param $product WC_Product
	 */
	public function add_to_cart_template( $product ) {
		$get_detect = $this->settings->get_detect();
		$atc_style  = $get_detect === 'mobile' ? $this->settings->get_option( 'add_to_cart_style_mobile' ) : $this->settings->get_option( 'add_to_cart_style' );
		if ( $atc_style === 'theme_default' ) {
			echo do_shortcode( '[add_to_cart id="' . $product->get_id() . '" show_price="false" style="font-size:.8em;margin:0;" class="wbs-upsell-atc-button-theme_default"] ' );
		} else {
			$required_addon = false;
			if ( class_exists( 'WC_Product_Addons_Helper' ) ) {
				$addons = WC_Product_Addons_Helper::get_product_addons( $product->get_id(), false, false, true );
				if ( $addons && ! empty( $addons ) ) {
					foreach ( $addons as $addon ) {
						if ( '1' == $addon['required'] ) {
							$required_addon = true;
							break;
						}
					}
				}
			}
			if ( ! $required_addon ) {
				do_action( 'woocommerce_boost_sales_' . $product->get_type() . '_add_to_cart', $product );
			} elseif ( ! $this->settings->get_option( 'hide_view_more_button' ) && $atc_style === 'hover' ) {
				?>
                <a href="<?php echo $product->get_permalink() ?>"
                   class="wbs-product-link"><?php esc_html_e( 'View more', 'woocommerce-boost-sales' ) ?></a>
				<?php
			}
		}
	}

	/**
	 * @param $product WC_Product
	 */
	public function add_to_cart_template_mobile( $product ) {
		$required_addon = false;
		if ( class_exists( 'WC_Product_Addons_Helper' ) ) {
			$addons = WC_Product_Addons_Helper::get_product_addons( $product->get_id(), false, false, true );
			if ( $addons && ! empty( $addons ) ) {
				foreach ( $addons as $addon ) {
					if ( '1' == $addon['required'] ) {
						$required_addon = true;
						break;
					}
				}
			}
		}
		if ( ! $required_addon ) {
			do_action( 'woocommerce_boost_sales_' . $product->get_type() . '_add_to_cart_mobile', $product );
		} elseif ( ! $this->settings->get_option( 'hide_view_more_button' ) ) {
			?>
            <a href="<?php echo $product->get_permalink() ?>"
               class="wbs-product-link"><?php esc_html_e( 'View more', 'woocommerce-boost-sales' ) ?></a>
			<?php
		}
	}

	/**
	 * @param $product WC_Product
	 */
	public function product_price( $product ) {
		if ( $price_html = $product->get_price_html() ) {
			?>
            <span class="price"><?php echo $price_html; ?></span>
			<?php
		}
	}

	/**
	 * @param $product WC_Product
	 */
	public function product_rate( $product ) {
		if ( get_option( 'woocommerce_enable_review_rating' ) === 'no' ) {
			return;
		}
		$rating = $product->get_average_rating();
		if ( $rating > 0 ) {
			echo wc_get_rating_html( $rating );
		}
	}

	/**
	 * @param $product WC_Product
	 */
	public function product_link( $product ) {
		$get_detect = $this->settings->get_detect();
		$atc_style  = $get_detect === 'mobile' ? $this->settings->get_option( 'add_to_cart_style_mobile' ) : $this->settings->get_option( 'add_to_cart_style' );
		if ( ! $this->settings->get_option( 'hide_view_more_button' ) && $atc_style === 'hover' ) {
			?>
            <a href="<?php echo $product->get_permalink() ?>"
               class="wbs-product-link"><?php esc_html_e( 'View more', 'woocommerce-boost-sales' ) ?></a>
			<?php
		}
	}

	/**
	 * @param $categories
	 *
	 * @return array
	 */
	public static function get_products_from_categories( $categories ) {
		$products = array();
		if ( is_array( $categories ) && count( $categories ) ) {
			$args     = array(
				'post_status'      => 'publish',
				'post_type'        => 'product',
				'posts_per_page'   => 50,
				'suppress_filters' => true,
				'fields'           => 'ids',
				'tax_query'        => array(
					'relation' => 'AND',
					array(
						'taxonomy' => 'product_cat',
						'field'    => 'ID',
						'terms'    => $categories,
						'operator' => 'IN'
					),
				),
			);
			$settings = VI_WBOOSTSALES_Data::get_instance();
			switch ( $settings->get_option( 'sort_product' ) ) {
				case 1:
					$args['orderby'] = 'title';
					$args['order']   = 'desc';
					break;
				case 2;
					$args['orderby']  = 'meta_value_num';
					$args['meta_key'] = '_price';
					$args['order']    = 'desc';
					break;
				case 3;
					$args['orderby']  = 'meta_value_num';
					$args['meta_key'] = '_price';
					$args['order']    = 'asc';
					break;
				case 4;
					$args['orderby'] = 'rand';
					break;
				case 5;
					$args['orderby']  = 'meta_value_num';
					$args['meta_key'] = 'total_sales';
					$args['order']    = 'desc';
					break;
				default;
					$args['orderby'] = 'title';
					$args['order']   = 'asc';
			}
			$the_query = new WP_Query( $args );

			if ( $the_query->have_posts() ) {
				$products = $the_query->posts;
			}
			wp_reset_postdata();
		}

		return $products;
	}

	public static function get_products_from_tags( $tags, $exclude_categories, $tag_field = 'ID' ) {
		$products = array();
		if ( is_array( $tags ) && count( $tags ) ) {
			$args = array(
				'post_status'      => 'publish',
				'post_type'        => 'product',
				'posts_per_page'   => 50,
				'suppress_filters' => true,
				'tax_query'        => array(
					'relation' => 'AND',
					array(
						'taxonomy' => 'product_tag',
						'field'    => $tag_field,
						'terms'    => $tags,
						'operator' => 'IN',
					),
				),
				'fields'           => 'ids',
			);
			if ( count( $exclude_categories ) ) {
				$args['tax_query'][] = array(
					'taxonomy' => 'product_cat',
					'field'    => 'ID',
					'terms'    => $exclude_categories,
					'operator' => 'NOT IN'
				);
			}
			$the_query = new WP_Query( $args );

			if ( $the_query->have_posts() ) {
				$products = $the_query->posts;
			}
			wp_reset_postdata();
		}

		return $products;
	}

	/**
	 * @param $product WC_Product
	 * @param $show_with_subcategory
	 * @param $exclude_categories
	 * @param $upsell_exclude_categories
	 * @param $sort_product
	 *
	 * @return array
	 */
	protected static function get_product_in_category( $product, $show_with_subcategory, $exclude_categories, $upsell_exclude_categories, $sort_product ) {
		$products     = array();
		$category_ids = $product->get_category_ids();
		if ( count( array_intersect( $category_ids, $upsell_exclude_categories ) ) ) {
			return $products;
		}
		if ( count( $category_ids ) ) {
			$categories = $category_ids;
			if ( $show_with_subcategory ) {
				$count      = count( get_ancestors( $category_ids[0], 'product_cat', 'taxonomy' ) );
				$cates_temp = array( $category_ids[0] );
				foreach ( $category_ids as $cate ) {
					$parents = get_ancestors( $cate, 'product_cat', 'taxonomy' );
					if ( $count < count( $parents ) ) {
						$count      = count( $parents );
						$cates_temp = array( $cate );
					} elseif ( $count == count( $parents ) ) {
						$cates_temp[] = $cate;
					}
				};
				$categories = $cates_temp;
			}
			$categories = array_unique( $categories );
			$u_args     = array(
				'post_status'      => 'publish',
				'post_type'        => 'product',
				'posts_per_page'   => 50,
				'suppress_filters' => true,
				'fields'           => 'ids',
				'tax_query'        => array(
					'relation' => 'AND',
					array(
						'taxonomy' => 'product_cat',
						'field'    => 'ID',
						'terms'    => $categories,
						'operator' => 'IN'
					),
					array(
						'taxonomy' => 'product_type',
						'field'    => 'slug',
						'terms'    => 'wbs_bundle',
						'operator' => 'NOT IN'
					),
				),
			);
			if ( count( $exclude_categories ) ) {
				$u_args['tax_query'][] = array(
					'taxonomy' => 'product_cat',
					'field'    => 'ID',
					'terms'    => $exclude_categories,
					'operator' => 'NOT IN'
				);
			}

			switch ( $sort_product ) {
				case 1:
					$u_args['orderby'] = 'title';
					$u_args['order']   = 'desc';
					break;
				case 2;
					$u_args['orderby']  = 'meta_value_num';
					$u_args['meta_key'] = '_price';
					$u_args['order']    = 'desc';
					break;
				case 3;
					$u_args['orderby']  = 'meta_value_num';
					$u_args['meta_key'] = '_price';
					$u_args['order']    = 'asc';
					break;
				case 4;
					$u_args['orderby'] = 'rand';
					break;
				case 5;
					$u_args['orderby']  = 'meta_value_num';
					$u_args['meta_key'] = 'total_sales';
					$u_args['order']    = 'desc';
					break;
				default;
					$u_args['orderby'] = 'title';
					$u_args['order']   = 'asc';
			}
			$the_query = new WP_Query( $u_args );

			if ( $the_query->have_posts() ) {
				$products = $the_query->posts;
			}
			wp_reset_postdata();
		}

		return $products;
	}

	public static function filter_upsells_ids( $upsells, $exclude ) {

		return array_values( array_diff( array_filter( array_unique( $upsells ) ), $exclude ) );
	}

	public static function get_upsells_ids( $product_id ) {
		$settings   = VI_WBOOSTSALES_Data::get_instance();
		$item_limit = $settings->get_option( 'limit' );
		$product    = wc_get_product( $product_id );
		if ( ! $product ) {
			return '';
		}
		/*Get product in cart*/
		$products_added = array( $product_id );
		if ( $settings->get_option( 'hide_products_added' ) ) {
			$cart_items = WC()->cart->get_cart();
			if ( is_array( $cart_items ) && count( $cart_items ) ) {
				foreach ( $cart_items as $cart_item ) {
					$products_added[] = $cart_item['product_id'];
				}
			}
		}

		$products_added = array_merge( $products_added, $settings->get_option( 'exclude_product' ) );

		/*Get upsells added manually*/
		$upsells = get_post_meta( $product_id, '_wbs_upsells', true );
		if ( ! is_array( $upsells ) ) {
			$upsells = array();
		}

		if ( $item_limit ) {
			$upsells            = self::filter_upsells_ids( $upsells, $products_added );
			$upsells_categories = get_post_meta( $product_id, '_wbs_upsells_categories', true );
			if ( count( $upsells ) < $item_limit && $upsells_categories ) {
				$upsells = array_merge( $upsells, VI_WBOOSTSALES_Frontend_Upsells::get_products_from_categories( $upsells_categories ) );
				$upsells = self::filter_upsells_ids( $upsells, $products_added );
			}

			/*Get Recently Viewed Products*/
			if ( $settings->get_option( 'show_recently_viewed_products' ) && count( $upsells ) < $item_limit ) {
				$viewed_products = ! empty( $_COOKIE['woocommerce_recently_viewed'] ) ? (array) explode( '|', wp_unslash( $_COOKIE['woocommerce_recently_viewed'] ) ) : array(); // @codingStandardsIgnoreLine
				$viewed_products = array_reverse( array_filter( array_map( 'absint', $viewed_products ) ) );
				$upsells         = array_merge( $upsells, $viewed_products );
				$upsells         = self::filter_upsells_ids( $upsells, $products_added );
			}
			/*Get upsells from same categories*/
			if ( $settings->get_option( 'show_with_category' ) && count( $upsells ) < $item_limit ) {
				if ( get_transient( 'vi_woocommerce_boost_sales_product_in_category_ids_' . $product_id ) ) {
					$p_upsells = get_transient( 'vi_woocommerce_boost_sales_product_in_category_ids_' . $product_id );
				} else {
					$show_with_subcategory     = $settings->get_option( 'show_with_subcategory' );
					$exclude_categories        = $settings->get_option( 'exclude_categories' );
					$upsell_exclude_categories = $settings->get_option( 'upsell_exclude_categories' );
					$sort_product              = $settings->get_option( 'sort_product' );
					$p_upsells                 = self::get_product_in_category( $product, $show_with_subcategory, $exclude_categories, $upsell_exclude_categories, $sort_product );
					set_transient( 'vi_woocommerce_boost_sales_product_in_category_ids_' . $product_id, $p_upsells, DAY_IN_SECONDS );
				}
				$upsells        = array_merge( $upsells, $p_upsells );
				$upsells        = self::filter_upsells_ids( $upsells, $products_added );
			}
			/*Get upsells from same tags*/
			if ( $settings->get_option( 'show_with_tags' ) && count( $upsells ) < $item_limit ) {
				if ( get_transient( 'vi_woocommerce_boost_sales_product_in_tags_ids_' . $product_id ) ) {
					$p_upsells = get_transient( 'vi_woocommerce_boost_sales_product_in_tags_ids_' . $product_id );
				} else {
					$tag_ids = $product->get_tag_ids();
					if ( $tag_ids ) {
						$p_upsells = self::get_products_from_tags( $tag_ids, $settings->get_option( 'exclude_categories' ) );
						set_transient( 'vi_woocommerce_boost_sales_product_in_tags_ids_' . $product_id, $p_upsells, DAY_IN_SECONDS );
					} else {
						$p_upsells = array();
					}
				}
				$upsells = array_merge( $upsells, $p_upsells );
				$upsells = self::filter_upsells_ids( $upsells, $products_added );
			}
			$upsells = array_slice( $upsells, 0, $item_limit );
		} else {
			$upsells_categories = get_post_meta( $product_id, '_wbs_upsells_categories', true );
			$upsells            = array_merge( $upsells, self::get_products_from_categories( $upsells_categories ) );
			/*Get Recently Viewed Products*/
			if ( $settings->get_option( 'show_recently_viewed_products' ) ) {
				$viewed_products = ! empty( $_COOKIE['woocommerce_recently_viewed'] ) ? (array) explode( '|', wp_unslash( $_COOKIE['woocommerce_recently_viewed'] ) ) : array(); // @codingStandardsIgnoreLine
				$viewed_products = array_reverse( array_filter( array_map( 'absint', $viewed_products ) ) );
				$upsells         = array_merge( $upsells, $viewed_products );
			}
			/*Get upsells from same categories*/
			if ( $settings->get_option( 'show_with_category' ) ) {
				if ( get_transient( 'vi_woocommerce_boost_sales_product_in_category_ids_' . $product_id ) ) {
					$p_upsells = get_transient( 'vi_woocommerce_boost_sales_product_in_category_ids_' . $product_id );
				} else {
					$show_with_subcategory     = $settings->get_option( 'show_with_subcategory' );
					$exclude_categories        = $settings->get_option( 'exclude_categories' );
					$upsell_exclude_categories = $settings->get_option( 'upsell_exclude_categories' );
					$sort_product              = $settings->get_option( 'sort_product' );
					$p_upsells                 = self::get_product_in_category( $product, $show_with_subcategory, $exclude_categories, $upsell_exclude_categories, $sort_product );
					set_transient( 'vi_woocommerce_boost_sales_product_in_category_ids_' . $product_id, $p_upsells, DAY_IN_SECONDS );
				}
				$upsells        = array_merge( $upsells, $p_upsells );
			}
			/*Get upsells from same tags*/
			if ( $settings->get_option( 'show_with_tags' ) ) {
				if ( get_transient( 'vi_woocommerce_boost_sales_product_in_tags_ids_' . $product_id ) ) {
					$p_upsells = get_transient( 'vi_woocommerce_boost_sales_product_in_tags_ids_' . $product_id );
				} else {
					$tag_ids = $product->get_tag_ids();
					if ( $tag_ids ) {
						$p_upsells = self::get_products_from_tags( $tag_ids, $settings->get_option( 'exclude_categories' ) );
						set_transient( 'vi_woocommerce_boost_sales_product_in_tags_ids_' . $product_id, $p_upsells, DAY_IN_SECONDS );
					} else {
						$p_upsells = array();
					}
				}
				$upsells = array_merge( $upsells, $p_upsells );
			}
			$upsells = array_values( array_diff( array_filter( array_unique( $upsells ) ), $products_added ) );
		}

		return apply_filters( 'woocommerce_boost_sales_upsells_items_ids', $upsells, $product_id );
	}
}