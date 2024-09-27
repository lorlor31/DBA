<?php
/**
 * YITH_WAPO_Cart_Premium Class
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\ProductAddOns
 * @version 4.11.0
 */

defined( 'YITH_WAPO' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_WAPO_Cart_Premium' ) ) {

    /**
     *  Addon class.
     *  The class manage all the Addon behavior in the cart.
     */
    class YITH_WAPO_Cart_Premium extends YITH_WAPO_Cart
    {

        /**
         * Returns single instance of the class
         *
         * @return \YITH_WAPO_Cart_Premium
         * @since 4.11.0
         */
        public static function get_instance() {
            if ( is_null( self::$instance ) ) {
                self::$instance = new self();
            }

            return self::$instance;
        }

        /**
         *  Constructor
         *
         * @param array $args The args to instantiate the class.
         */
        public function __construct() {
            parent::__construct();

            // Load gallery scripts on product pages only if supported.
            add_action( 'template_redirect', array( $this, 'maybe_load_photoswipe' ) );
            // Add templates to the footer.
            add_action( 'wp_footer', array( $this, 'load_template' ) );

            // Display options in cart and checkout page.
            add_action( 'woocommerce_after_cart_item_name', array( $this, 'maybe_display_edit_product_link' ), 27, 2 );

            // Change add to cart text on cart.
            add_filter( 'woocommerce_product_single_add_to_cart_text', array( $this, 'add_to_cart_text_on_cart' ), 10, 2 );

            // Ajax loading product template.
            add_action( 'wp_ajax_ywapo_load_product_template', array( $this, 'load_product_template' ) );
            add_action( 'wp_ajax_nopriv_ywapo_load_product_template', array( $this, 'load_product_template' ) );

            // Ajax updating addons on save add-ons action.
            add_action( 'wp_ajax_ywapo_update_addons_on_cart_item', array( $this, 'update_addons_cart_item' ) );
            add_action( 'wp_ajax_nopriv_ywapo_update_addons_on_cart_item', array( $this, 'update_addons_cart_item' ) );

        }

        /**
         * Load photoswipe template if it is in cart.
         */
        public function maybe_load_photoswipe() {
            if ( is_cart() || has_block( 'woocommerce/cart' ) ) {
                add_action( 'wp_footer', 'woocommerce_photoswipe' );
            }
        }

        /**
         * Change add to cart text on cart modal.
         * @param string $text The add to cart text.
         * @param WC_Product $product The product object.
         * @return mixed|string|null
         */
        public function add_to_cart_text_on_cart( $text, $product ) {

            if ( isset( $_REQUEST['action'] ) && 'ywapo_load_product_template' === $_REQUEST['action'] ) {
                check_ajax_referer( 'addons-nonce', 'security' );

                // translators: Changed add to cart button text when editing add-ons in the cart page.
                $text = __( 'Update', 'yith-woocommerce-product-add-ons' );
            }

            return $text;

        }

        /**
         * Display the edit product link if the add-on has blocks in the cart page. For individual add-ons it displays a hidden input with information.
         *
         * @param array $cart_item The product in the cart.
         * @param string $cart_item_key Key for the product in the cart.
         * @return void
         */
        public function maybe_display_edit_product_link( $cart_item, $cart_item_key, $ob = false ) {

            $show_in_cart_opt = wc_string_to_bool( get_option( 'yith_wapo_show_options_in_cart', 'yes' ) === 'yes' );
            $allow_edit_opt   = wc_string_to_bool( get_option( 'yith_wapo_allow_edit_in_cart', 'yes' ) === 'yes' );

            $display   = apply_filters( 'yith_wapo_display_edit_product_link', true, $cart_item, $cart_item_key );

            if ( $show_in_cart_opt && $allow_edit_opt && $display ) {
                $product_id   = $cart_item['product_id'] ?? 0;
                $variation_id = $cart_item['variation_id'] ?? 0;
                $addons       = wp_json_encode($cart_item['yith_wapo_options'] ?? '' ) ?? 0;

                if ( $ob ) {
                    ob_start();
                    wc_get_template('edit-product-link.php',
                        compact('product_id', 'variation_id', 'addons', 'cart_item', 'cart_item_key'),
                        '',
                        YITH_WAPO_TEMPLATE_PATH . '/front/cart/');
                    $html = ob_get_clean();

                    return $html;
                } else {
                    wc_get_template('edit-product-link.php',
                        compact('product_id', 'variation_id', 'addons', 'cart_item', 'cart_item_key'),
                        '',
                        YITH_WAPO_TEMPLATE_PATH . '/front/cart/');
                }
            }
        }

        /**
         * Load the cart popup in the footer of the page.
         *
         * @return void
         */
        public function load_template() {

            if ( is_cart() ) {
                $args = array();
                wc_get_template( 'cart-popup.php', $args, '', YITH_WAPO_TEMPLATE_PATH . '/front/cart/' );
            }

        }

        /**
         * Ajax that return the product/variation template HTML.
         *
         * @return void
         */
        public function load_product_template() {

            check_ajax_referer( 'addons-nonce', 'security' );

            $product_id    = absint( $_REQUEST['product_id'] ) ?? 0;
            $variation_id  = absint( $_REQUEST['variation_id'] ) ?? 0;
            $cart_item_key = $_REQUEST['cart_item_key'] ?? 0;

            $product   = wc_get_product( $product_id );
            $variation = '';

            $quantity = 0;

            if ( $cart_item_key > 0 ) {
                $cart_item         = WC()->cart->get_cart_item( $cart_item_key );
                $addon_qty_options = $cart_item['yith_wapo_qty_options'] ?? array();

                // Get add-on image.
                $addon_image_id    = $cart_item['yith_wapo_product_img'] ?? '';
                $addon_image       = wp_get_attachment_image_url( $addon_image_id, 'full' );

                $addons = yith_wapo_get_addons_by_cart_item( $cart_item_key, true );

                // Quantity of current cart item.
                $quantity = $cart_item['quantity'] ?? $quantity;
            }


            // Remove actions to not show woocommerce tabs, related products, etc.
            remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_product_data_tabs', 10 );
            remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_upsell_display', 15 );
            remove_action( 'woocommerce_after_single_product_summary', 'woocommerce_output_related_products', 20 );

            // Remove product thumbnails gallery.
            if ( apply_filters( 'yith_wapo_remove_product_gallery_in_cart_modal', false ) ) {
                remove_all_actions( 'woocommerce_product_thumbnails' );
            }

            $html = do_shortcode( '[product_page id=' . $product_id . ']' );

            if ( $product instanceof WC_Product_Variable ) {
                $variation    = $variation_id > 0 ? $product->get_available_variation( $variation_id ) : false;
            }

            if ( defined( 'YITH_WCCL_PREMIUM' ) ) {
                $wccl_data = YITH_WCCL_Frontend::get_instance()->create_attributes_json( $product_id, true );
            } else {
                $wccl_data = class_exists( 'YITH_WAPO_Color_Label_Variations_Frontend' ) ? YITH_WAPO_Color_Label_Variations_Frontend::get_instance()->create_attributes_json( $product_id ) : '';
            }

            // phpcs:enable WordPress.Security.NonceVerification.Recommended
            wp_send_json(
                array(
                    'html'                 => $html,
                    'variation'            => $variation,
                    'quantity'             => $quantity,
                    'wccl-data'            => $wccl_data,
                    'addon_image'          => $addon_image ?? '',
                    'quantities'           => $addon_qty_options ?? array(),
                    'addons'               => $addons ?? array()
                )
            );

        }

        /**
         * Ajax that update the add-ons in the cart page after modal saving.
         *
         * @return void
         */
        public function update_addons_cart_item() {

            check_ajax_referer( 'addons-nonce', 'security' );

            $cart_item_key       = $_REQUEST['cart_item_key'] ?? '';

            if ( ! $cart_item_key || ! WC()->cart instanceof WC_Cart ) {
                wp_send_json( array(
                        'error' => 'There was an error with the cart item key'
                ) );
            }

           if ( isset( WC()->cart->cart_contents[$cart_item_key] ) ) {

               $serialized          = $_REQUEST['addons'] ?? '';
               $product_id          = $_REQUEST['product_id'] ?? '';
               $variation_id        = $_REQUEST['variation_id'] ?? '';
               $individual_item_key = $_REQUEST['individual_item_key'] ?? '';

                if ( $serialized ) {

                    // Format add-ons.
                    $formatted_addons = yith_wapo_format_addons( $serialized );

                    $addons            = $formatted_addons['yith_wapo_options']['addons'] ?? array();
                    $individual_addons = $formatted_addons['yith_wapo_options']['individual'] ?? array();
                    $qty_addons        = $formatted_addons['yith_wapo_qty_options'] ?? array();

                    // Get add-on image to replace in the cart.
                    $product_image    = yith_wapo_get_value_from_serialized_form( 'yith_wapo_product_img', $serialized );

                    // Update item quantity.
                    $current_quantity = WC()->cart->cart_contents[$cart_item_key]['quantity'];
                    $item_quantity    = yith_wapo_get_value_from_serialized_form( 'quantity', $serialized );

                    if ( $item_quantity !== $current_quantity ) {
                        WC()->cart->cart_contents[$cart_item_key]['quantity'] = $item_quantity;
                    }

                    if ( ! preg_match("~^(?:f|ht)tps?://~i", $product_image ) ) {
                        $product_image = "http:" . $product_image;
                    }

                    $product_image = attachment_url_to_postid( $product_image ); // Image ID.

                    // Product variation.
                    if ( intval( $variation_id ) > 0 && WC()->cart->cart_contents[$cart_item_key]['variation_id'] > 0
                        &&
                        $variation_id !== WC()->cart->cart_contents[$cart_item_key]['variation_id'] ) {

                        $cart_content = WC()->cart->cart_contents;
                        $cloned_item  = WC()->cart->cart_contents[$cart_item_key];

                        $variation = wc_get_product( $variation_id );
                        $attrs     = yith_wapo_get_attributes_selected( $serialized, $variation );

                        // Clone the item to replace it in the cart by the current one.
                        $cloned_item['variation_id']          = $variation_id;
                        $cloned_item['variation']             = $attrs;
                        $cloned_item['data_hash']             = wc_get_cart_item_data_hash($variation);
                        $cloned_item['data']                  = $variation;
                        $cloned_item['yith_wapo_options']     = $addons;
                        $cloned_item['yith_wapo_product_img'] = $product_image;

                        $cart_content[$cart_item_key] = $cloned_item;

                        WC()->cart->set_cart_contents( $cart_content ); // Update the full cart item.

                    } else {
                        WC()->cart->cart_contents[$cart_item_key]['yith_wapo_options']     = $addons; // Update add-ons for the main product.
                        WC()->cart->cart_contents[$cart_item_key]['yith_wapo_qty_options'] = $qty_addons; // Update add-ons for the main product.
                        WC()->cart->cart_contents[$cart_item_key]['yith_wapo_product_img'] = $product_image;
                    }

                    // Create new product with individual add-on.
                    $sold_individually_product = get_option( 'yith_wapo_sold_individually_product_id', false );
                    $quantity                  = apply_filters( 'yith_wapo_sold_individually_quantity', 1 );

                    $cart_item_data_sold_individually = array(
                        'yith_wapo_individual_addons' => true,
                        'yith_wapo_addons_parent_key' => $cart_item_key,
                        'yith_wapo_product_id'        => $product_id,
                        'yith_wapo_variation_id'      => $variation_id,
                        'yith_wapo_qty_options'       => $qty_addons,
                    );

                    // Remove individual products if there are more than individual add-ons.
                    $item_count = 1;
                    foreach ( WC()->cart->cart_contents as $item_id => $item ) {
                        if ( isset( $item['yith_wapo_individual_addons'] ) ) {
                            if ( $item_count > count( $individual_addons ) ) {
                                // Remove product.
                                unset( WC()->cart->cart_contents[$item_id] );
                            }
                            $item_count++;
                        }
                    }

                    if ( apply_filters( 'yith_wapo_split_addons_individually_on_cart', false ) ) { // Split individual add-ons.
                        for ( $i = 0; $i < count( $individual_addons ); $i++ ) {
                            if ( isset( $individual_item_key[$i] ) && isset( WC()->cart->cart_contents[$individual_item_key[$i]] ) ) {
                                WC()->cart->cart_contents[$individual_item_key[$i]]['yith_wapo_options']     = array( $individual_addons[$i] );
                                WC()->cart->cart_contents[$individual_item_key[$i]]['yith_wapo_qty_options'] = $qty_addons;
                            } else {
                                $cart_item_data_sold_individually['yith_wapo_options']           = array( $individual_addons[$i] );
                                $cart_item_data_sold_individually['yith_wapo_qty_options']       = $qty_addons;
                                WC()->cart->add_to_cart( $sold_individually_product, $quantity, 0, array(), $cart_item_data_sold_individually );
                            }
                        }

                    } else { // Non splitted individual add-ons.
                        if ( is_array( $individual_item_key ) && isset( WC()->cart->cart_contents[$individual_item_key[0]] ) ) {
                            WC()->cart->cart_contents[$individual_item_key[0]]['yith_wapo_options']     = $individual_addons;
                            WC()->cart->cart_contents[$individual_item_key[0]]['yith_wapo_qty_options'] = $qty_addons;
                        } else {
                            if ( ! empty( $individual_addons ) ) {
                                $cart_item_data_sold_individually['yith_wapo_options']           = $individual_addons;
                                $cart_item_data_sold_individually['yith_wapo_qty_options']       = $qty_addons;
                                WC()->cart->add_to_cart( $sold_individually_product, $quantity, 0, array(), $cart_item_data_sold_individually );
                            }
                        }
                    }

                    WC()->cart->set_session();

                    // Send success response.
                    wp_send_json(
                        array(
                            'success' => true,
                        )
                    );
                }
            }
        }

    }

}