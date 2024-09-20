<?php
/**
 * Admin class
 *
 * @author  Cuong Nguyen
 * @package WBS
 * @version 1.0.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
if ( ! class_exists( 'VI_WBOOSTSALES_Admin_Bundles' ) ) {
	/**
	 * Admin class.
	 * The class manage all the admin behaviors.
	 */
	class VI_WBOOSTSALES_Admin_Bundles {

		/**
		 * Single instance of the class
		 *
		 * @since 1.0.0
		 */
		protected static $instance;

		/**
		 * Plugin options
		 *
		 * @var array
		 * @access public
		 * @since  1.0.0
		 */
		public $options = array();


		public $templates = array();

		/**
		 * Returns single instance of the class
		 *
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
			//Add action links
			//add_action('init',array($this,'init'));
			add_action( 'init', array( $this, 'register_wbs_bundle_product_type' ), 1 );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue_scripts' ) );

			add_filter( 'woocommerce_product_data_tabs', array( $this, 'woocommerce_product_data_tabs' ) );
			add_action( 'woocommerce_product_data_panels', array( $this, 'woocommerce_product_data_panels' ) );
			add_action( 'wp_ajax_wbs_wcpb_add_product_in_bundle', array( $this, 'add_product_in_bundle' ) );
			add_action( 'wp_ajax_wbs_search_product_in_bundle', array( $this, 'wbs_search_product_in_bundle' ) );
			add_action( 'wp_ajax_wbs_wcpb_search_item_product', array( $this, 'search_item_product' ) );
			add_action( 'woocommerce_process_product_meta', array( $this, 'woocommerce_process_product_meta' ) );
			add_action( 'woocommerce_process_product_meta_wbs_bundle', array( $this, 'save_price' ) );
			add_action( 'wbs_wcpb_admin_product_bundle_data', array( $this, 'wbs_wcpb_admin_product_bundle_data' ), 10, 4 );
			add_filter( 'product_type_selector', array( $this, 'product_type_selector' ) );

			// Admin ORDER
			add_filter( 'woocommerce_admin_html_order_item_class', array( $this, 'woocommerce_admin_html_order_item_class' ), 10, 2 );
			add_filter( 'woocommerce_admin_order_item_class', array( $this, 'woocommerce_admin_html_order_item_class' ), 10, 2 );
			add_filter( 'woocommerce_admin_order_item_count', array( $this, 'woocommerce_admin_order_item_count' ), 10, 2 );
			add_filter( 'woocommerce_hidden_order_itemmeta', array( $this, 'woocommerce_hidden_order_itemmeta' ) );

		}

		public function init() {
			require_once VI_WBOOSTSALES_INCLUDES . "wbs_bundled_item.php";
		}

		public function register_wbs_bundle_product_type() {
			require_once VI_WBOOSTSALES_INCLUDES . "wbs_bundled_item.php";
			require_once VI_WBOOSTSALES_INCLUDES . "wbs_product_bundle.php";
		}

		/**
		 * Hide wbs_bundled_by meta in admin order
		 *
		 * @param array $hidden
		 *
		 * @access public
		 * @return array
		 * @author Leanza Francesco <leanzafrancesco@gmail.com>
		 * @since  1.0.0
		 */
		public function woocommerce_hidden_order_itemmeta( $hidden ) {
			return array_merge( $hidden, array( '_bundled_by', '_cartstamp' ) );
		}

		/**
		 * add CSS class in admin order bundled items
		 *
		 *
		 * @param string $class
		 * @param array $item
		 *
		 * @access public
		 * @return string
		 * @author Leanza Francesco <leanzafrancesco@gmail.com>
		 * @since  1.0.0
		 */
		public function woocommerce_admin_html_order_item_class( $class, $item ) {
			if ( isset( $item['wbs_bundled_by'] ) ) {
				return $class . ' wbs-wcpb-admin-bundled-item';
			}

			return $class;
		}

		/**
		 * Filter item count in admin orders page
		 *
		 * @param int $count
		 * @param WC_Order $order
		 *
		 * @access public
		 * @return int|string
		 * @author Leanza Francesco <leanzafrancesco@gmail.com>
		 *
		 * @since  1.0.0
		 */
		public function woocommerce_admin_order_item_count( $count, $order ) {
			$counter = 0;
			foreach ( $order->get_items() as $item ) {
				if ( isset( $item['wbs_bundled_by'] ) ) {
					$counter += $item['qty'];
				}
			}
			if ( $counter > 0 ) {
				$non_bundled_count = $count - $counter;

				return sprintf( _n( '%1$s item [%2$s bundled elements]', '%1$s items [%2$s bundled elements]', $non_bundled_count, 'woocommerce-boost-sales' ), $non_bundled_count, $counter );
			}

			return $count;
		}

		/**
		 * add Product Bundle type in product type selector [in product wc-metabox]
		 *
		 * @access public
		 * @since  1.0.0
		 * @author Leanza Francesco <leanzafrancesco@gmail.com>
		 */
		public function product_type_selector( $types ) {
			$types['wbs_bundle'] = _x( 'WBS Product Bundle', 'Admin: type of product', 'woocommerce-boost-sales' );

			return $types;
		}

		/**
		 * bundle items data form
		 *
		 */
		public function wbs_wcpb_admin_product_bundle_data( $metabox_id, $product_id, $item_data, $post_id ) {
			$bp_quantity = isset( $item_data['bp_quantity'] ) ? $item_data['bp_quantity'] : 1;
			?>
            <table>
                <tr>
                    <td><?php echo _ex( 'Quantity', 'Admin: quantity of the bundled product.', 'woocommerce-boost-sales' ); ?></td>
                    <td>
                        <input type="number" size="4" value="<?php echo $bp_quantity ?>"
                               name="_wbs_wcpb_bundle_data[<?php echo $metabox_id; ?>][bp_quantity]"
                               class="wbs-wcpb-bp-quantity short"></td>
                </tr>
            </table>
			<?php
		}

		/**
		 * Ajax Called in bundle_options_metabox.js
		 * return the empty form for the item
		 *
		 * @access public
		 * @since  1.0.0
		 * @author Leanza Francesco <leanzafrancesco@gmail.com>
		 */
		public function add_product_in_bundle() {
			$metabox_id = intval( $_POST['id'] );
			$post_id    = intval( $_POST['post_id'] );
			$product_id = intval( $_POST['product_id'] );

			$title = get_the_title( $product_id );

			ob_start();
			include VI_WBOOSTSALES_TEMPLATES . 'admin/admin-bundled-product-item.php';
			echo ob_get_clean();

			die();
		}

		/**
		 * add Bundle Options Tab [in product wc-metabox]
		 *
		 */
		public function woocommerce_product_data_tabs( $product_data_tabs ) {
			$product_data_tabs['wbs_bundled_options'] = array(
				'label'  => __( 'Bundle Options', 'woocommerce-boost-sales' ),
				'target' => 'wbs_bundled_product_data',
				'class'  => array( 'show_if_wbs_bundle' ),
			);

			return $product_data_tabs;
		}

		/**
		 * add panel for Bundle Options Tab [in product wc-metabox]
		 *
		 */
		public function woocommerce_product_data_panels() {
			global $post;
			$bundle_data     = get_post_meta( $post->ID, '_wbs_wcpb_bundle_data', true );
			$price           = get_post_meta( $post->ID, '_price', true );
			$dynamic_price   = get_post_meta( $post->ID, '_wbs_dynamic_price', true );
			$discount_type   = get_post_meta( $post->ID, '_wbs_discount_type', true );
			$discount_amount = get_post_meta( $post->ID, '_wbs_discount_amount', true );
			?>
            <div id="wbs_bundled_product_data" class="panel woocommerce_options_panel wc-metaboxes-wrapper">

                <div class="options_group wbs-wcpb-bundle-metaboxes-wrapper">

                    <div id="wbs-wcpb-bundle-metaboxes-wrapper-inner">
                        <p class="form-field">
                            <label for="_wbs_dynamic_price"><?php printf( esc_html__( 'Use dynamic price', 'woocommerce-boost-sales' ), get_woocommerce_currency_symbol() ); ?></label>
                            <input id="_wbs_dynamic_price" type="checkbox"
                                   name="_wbs_dynamic_price"
                                   value="1" <?php checked( $dynamic_price, 1 ) ?>>
                        </p>
                        <p class="form-field wbs-price-info">
                            <label for="wbs_bundle_price"><?php printf( esc_html__( 'Price(%s)', 'woocommerce-boost-sales' ), get_woocommerce_currency_symbol() ); ?></label>
                            <input id="wbs_bundle_price" type="text" class="short wc_input_price"
                                   name="wbs_bundle_price"
                                   value="<?php echo esc_attr( $price ) ?>">
                        </p>
                        <p class="form-field wbs-discount-info">
                            <label for="_wbs_discount_type"><?php printf( esc_html__( 'Discount type', 'woocommerce-boost-sales' ), get_woocommerce_currency_symbol() ); ?></label>
                            <select id="_wbs_discount_type" name="_wbs_discount_type">
                                <option value="percent" <?php selected( $discount_type, 'percent' ) ?>><?php esc_html_e( 'Percentage(%)', 'woocommerce-boost-sales' ); ?></option>
                                <option value="fixed" <?php selected( $discount_type, 'fixed' ) ?>><?php esc_html_e( 'Fixed amount', 'woocommerce-boost-sales' ); ?></option>
                            </select>
                        </p>
                        <p class="form-field wbs-discount-info">
                            <label for="_wbs_discount_amount"><?php printf( esc_html__( 'Discount amount', 'woocommerce-boost-sales' ), get_woocommerce_currency_symbol() ); ?></label>
                            <input type="number" min="0" step="any" id="_wbs_discount_amount" name="_wbs_discount_amount"
                                   value="<?php echo esc_attr( $discount_amount ) ?>">
                        </p>
                        <p class="toolbar">
                            <a href="#" class="close_all"><?php _e( 'Close all', 'woocommerce-boost-sales' ); ?></a>
                            <a href="#" class="expand_all"><?php _e( 'Expand all', 'woocommerce-boost-sales' ); ?></a>
                        </p>

                        <div class="wbs-wcpb-bundled-items wc-metaboxes ui-sortable">
							<?php
							if ( ! empty( $bundle_data ) ) {
								$i = 0;
								foreach ( $bundle_data as $item_id => $item_data ) {
									//$metabox_id     = $item_data[ 'bundle_order' ];
									$i ++;
									$metabox_id = $i;
									$post_id    = $post->ID;
									$product_id = $item_data['product_id'];

									$title       = get_the_title( $product_id );
									$open_closed = 'closed';
									ob_start();
									include VI_WBOOSTSALES_TEMPLATES . 'admin/admin-bundled-product-item.php';
									echo ob_get_clean();
								}
							}
							?>
                        </div>
                        <p class="wbs-wcpb-bundled-prod-toolbar toolbar">
                            <span class="wbs-wcpb-bundled-prod-toolbar-wrapper">
                                <span class="wbs-wcpb-bundled-prod-selector">
                                    <select type="hidden" class="wc-product-search" style="width: 250px;"
                                            id="wbs-wcpb-bundled-product"
                                            name="wbs_wcpb_bundled_product"
                                            data-action="wbs_wcpb_search_item_product"
                                            data-placeholder="<?php _e( 'Search for a product&hellip;', 'woocommerce-boost-sales' ); ?>"
                                            data-exclude="<?php echo $post->ID; ?>">
									</select>
                                </span>
                                <button type="button" id="wbs-wcpb-add-bundled-product"
                                        class="button button-primary"><?php _e( 'Add Product', 'woocommerce-boost-sales' ); ?></button>
                            </span>
                        </p>
                    </div>
                </div>
            </div>
			<?php
		}

		public function search_item_product() {
			add_filter( 'woocommerce_json_search_found_products', [ $this, 'remove_variable_product' ] );
			WC_AJAX::json_search_products_and_variations();
		}

		public function remove_variable_product( $search_results ) {
			if ( ! empty( $search_results ) ) {
				foreach ( $search_results as $pid => $title ) {
					$product = wc_get_product( $pid );
					if ( $product->is_type( 'variable' ) ) {
						unset( $search_results[ $pid ] );
					}
				}
			}

			return $search_results;
		}

		public function wbs_search_product_in_bundle() {
			if ( ! current_user_can( 'manage_options' ) ) {
				return;
			}
			ob_start();

			$keyword = filter_input( INPUT_GET, 'keyword', FILTER_SANITIZE_STRING );

			if ( empty( $keyword ) ) {
				die();
			}
			$found_products = array();
			$args           = array(
				'post_type'      => 'product',
				'post_status'    => VI_WBOOSTSALES_Data::search_product_statuses(),
				'posts_per_page' => 100,
				's'              => $keyword
			);
			$products       = get_posts( $args );
			if ( count( $products ) ) {
				foreach ( $products as $pas ) {
					$asb = wc_get_product( $pas );
					if ( $asb->get_type() != 'wbs_bundle' ) {
						if ( $asb->get_type() == 'variable' && $asb->has_child() ) {
							$product          = array(
								'id'   => $asb->get_id(),
								'text' => $asb->get_title() . ' (#' . $asb->get_id() . ') #PARENT'
							);
							$found_products[] = $product;
							$get_ch           = $asb->get_children();
							foreach ( $get_ch as $child ) {
								$child_var        = wc_get_product( $child );
								$chi_id           = $child_var->get_id();
								$chi_name         = $child_var->get_name();
								$product          = array(
									'id'   => $chi_id,
									'text' => $chi_name . ' (#' . $chi_id . ')'
								);
								$found_products[] = $product;
							}
						} else {
							$sing_id          = $asb->get_id();
							$sing_name        = $asb->get_name();
							$product          = array(
								'id'   => $sing_id,
								'text' => $sing_name . ' (#' . $sing_id . ')'
							);
							$found_products[] = $product;
						}
					}
				}
			}

			wp_send_json( $found_products );
			die;

		}

		/**
		 * @param $post_id
		 */
		public function woocommerce_process_product_meta( $post_id ) {
			$bundle_data = isset( $_POST['_wbs_wcpb_bundle_data'] ) ? $_POST['_wbs_wcpb_bundle_data'] : false;
			update_post_meta( $post_id, '_wbs_wcpb_bundle_data', $bundle_data );
		}

		public function save_price( $post_id ) {
			$bundle_price = isset( $_POST['wbs_bundle_price'] ) ? $_POST['wbs_bundle_price'] : '';
			update_post_meta( $post_id, '_price', $bundle_price );
			update_post_meta( $post_id, '_regular_price', $bundle_price );
			update_post_meta( $post_id, '_wbs_dynamic_price', isset( $_POST['_wbs_dynamic_price'] ) ? sanitize_text_field( $_POST['_wbs_dynamic_price'] ) : '' );
			update_post_meta( $post_id, '_wbs_discount_type', isset( $_POST['_wbs_discount_type'] ) ? sanitize_text_field( $_POST['_wbs_discount_type'] ) : '' );
			update_post_meta( $post_id, '_wbs_discount_amount', isset( $_POST['_wbs_discount_amount'] ) ? sanitize_text_field( $_POST['_wbs_discount_amount'] ) : '' );
		}

		public function admin_enqueue_scripts() {
			wp_enqueue_script( 'jquery-ui-tabs' );

			$screen = get_current_screen();
			if ( 'product' == $screen->id ) {
				wp_enqueue_style( 'wbs-wcpb-admin-styles', VI_WBOOSTSALES_CSS . 'bundle-admin.css' );
				wp_enqueue_script( 'jquery-ui-sortable' );
				wp_enqueue_script( 'woocommerce-boost-sales', VI_WBOOSTSALES_JS . 'bundles.js', array( 'jquery' ) );
			}
		}

	}
}

/**
 * Unique access to instance of VI_WBOOSTSALES_Admin_Bundles class
 *
 * @return \VI_WBOOSTSALES_Admin_Bundles
 * @since 1.0.0
 */
function VI_WBOOSTSALES_Admin_Bundles() {
	return VI_WBOOSTSALES_Admin_Bundles::get_instance();
}
