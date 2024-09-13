<?php

/**
 * Class VI_WBOOSTSALES_Frontend_Notify
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VI_WBOOSTSALES_Frontend_Archive_Upsells {
	protected $settings;

	public function __construct() {
		$this->settings = VI_WBOOSTSALES_Data::get_instance();
		if ( $this->settings->enable() && $this->settings->get_option( 'enable_upsell' ) ) {
			add_action( 'wp_footer', array( $this, 'init_boost_sales' ) );
			add_action( 'wp_ajax_wbs_get_product', array( $this, 'product_html' ) );
			add_action( 'wp_ajax_nopriv_wbs_get_product', array( $this, 'product_html' ) );
			add_filter( 'woocommerce_add_to_cart_fragments', array(
				$this,
				'save_added_to_cart'
			) );
		}
	}

	public function save_added_to_cart( $fragments ) {
		global $wbs_language;
		$wbs_language = '';
		$wc_ajax      = isset( $_GET['wc-ajax'] ) ? sanitize_text_field( $_GET['wc-ajax'] ) : '';
		$product_id   = isset( $_POST['product_id'] ) ? sanitize_text_field( $_POST['product_id'] ) : '';
		if ( in_array( $wc_ajax, array(
				'xoo_wsc_add_to_cart',
				'viwcaio_add_to_cart',
				'wpvs_add_to_cart',
				'add_to_cart'
			) ) && count( VI_WBOOSTSALES_Frontend_Upsells::$added_to_cart ) && $product_id ) {
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
			$added_to_cart           = VI_WBOOSTSALES_Frontend_Upsells::$added_to_cart;
			$upsell_exclude_products = $this->settings->get_option( 'upsell_exclude_products' );
			if ( ! empty( $added_to_cart[ $product_id ] ) && ! in_array( $product_id, $upsell_exclude_products ) ) {
				$fragments['wbs_added_to_cart'] = $added_to_cart;
				$upsells                        = VI_WBOOSTSALES_Frontend_Upsells::get_upsells_ids( $product_id );
				$quantity                       = $added_to_cart[ $product_id ]['quantity'];
				$obj_upsell                     = new VI_WBOOSTSALES_Upsells( $product_id, $quantity, $upsells, $product_id, VI_WBOOSTSALES_Frontend_Upsells::$cart_item_key );
				$fragments['wbs_upsells_html']  = $obj_upsell->show_html();
			}
		}

		return $fragments;
	}

	/**
	 * Show HTML on front end
	 */
	public function product_html() {
		VI_WBOOSTSALES_Frontend_Upsells::$added_to_cart = isset( $_POST['added_to_cart'] ) ? wc_clean( $_POST['added_to_cart'] ) : array();
		$enable                                         = $this->settings->get_option( 'enable' );
		$upsell_exclude_products                        = $this->settings->get_option( 'upsell_exclude_products' );
		$product_id                                     = filter_input( INPUT_POST, 'id', FILTER_SANITIZE_NUMBER_INT );
		$upsells_html                                   = '';
		if ( $enable && $product_id && ! in_array( $product_id, $upsell_exclude_products ) ) {
			$upsells_html = $this->show_product( $product_id );
		}
		$discount_bar = new VI_WBOOSTSALES_Discount_Bar();
		wp_send_json( array(
			'upsells_html'      => $upsells_html,
			'discount_bar_html' => $discount_bar->show_html(),
		) );
	}


	/**
	 * Show HTML code
	 */
	public function init_boost_sales() {
		wp_enqueue_script( 'wc-add-to-cart-variation' );
		if ( is_cart() && $this->settings->get_option( 'hide_on_cart_page' ) ) {
			return;
		}
		if ( is_checkout() && $this->settings->get_option( 'hide_on_checkout_page' ) ) {
			return;
		}
		if ( ! is_single() ) {
			echo $this->show_product();
		}
	}

	/**
	 * @param $product WC_Product
	 *
	 * @return array
	 */
	protected function get_product_in_category( $product ) {
		$only_sub_category         = $this->settings->get_option( 'show_with_subcategory' );
		$exclude_categories        = $this->settings->get_option( 'exclude_categories' );
		$upsell_exclude_categories = $this->settings->get_option( 'upsell_exclude_categories' );
		$products                  = array();
		$category_ids              = $product->get_category_ids();
		if ( count( array_intersect( $category_ids, $upsell_exclude_categories ) ) ) {
			return $products;
		}
		if ( count( $category_ids ) ) {
			$categories = $category_ids;
			if ( $only_sub_category ) {
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
			$categories = array_diff( $categories, $exclude_categories );
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
					array(
						'taxonomy' => 'product_type',
						'field'    => 'slug',
						'terms'    => array(
							'simple',
							'variable',
							'external',
							'subscription',
							'variable-subscription',
							'member'
						),
						'operator' => 'IN'
					),
				),
			);
			switch ( $this->settings->get_option( 'sort_product' ) ) {
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

	/**
	 * @param null $product_id
	 *
	 * @return false|string
	 */
	protected function show_product( $product_id = null ) {
		if ( ! $product_id ) {
			ob_start();
			?>
            <div id="wbs-content-upsells"
                 class="woocommerce-boost-sales wbs-content-up-sell wbs-archive-page" style="display: none;"></div>
			<?php
			return ob_get_clean();
		} else {
			$upsells  = VI_WBOOSTSALES_Frontend_Upsells::get_upsells_ids( $product_id );
			$quantity = filter_input( INPUT_POST, 'quantity', FILTER_SANITIZE_NUMBER_INT );

			$obj_upsell = new VI_WBOOSTSALES_Upsells( $product_id, $quantity, $upsells, $product_id, VI_WBOOSTSALES_Frontend_Upsells::$cart_item_key );

			return $obj_upsell->show_html();
		}
	}
}