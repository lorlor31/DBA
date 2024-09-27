<?php
/**
 * WAPO Frontend Premium Class
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\ProductAddOns
 * @version 4.15.0
 */

defined( 'YITH_WAPO' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_WAPO_Front_Premium' ) ) {

    /**
     *  YITH_WAPO_Front_Premium Class
     */
    class YITH_WAPO_Front_Premium extends YITH_WAPO_Front {
        /**
         * Returns single instance of the class
         *
         * @return \YITH_WAPO_Front_Premium
         * @since 4.15.0
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
         * Initialize plugin and registers actions and filters to be used
         *
         * @since 4.15.0
         */
        public function __construct() {
            parent::__construct();

            add_action( 'wp_footer', array( $this, 'yith_wapo_uploaded_file_template' ) );

        }

        /**
         * Front enqueue scripts
         */
        public function enqueue_scripts() {

            if ( apply_filters( 'yith_wapo_enqueue_front_scripts', true ) ) {

                global $post;

                $suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';

                // CSS.
                wp_enqueue_style( 'yith_wapo_front',
                    YITH_WAPO_URL . 'assets/css/front.css',
                    array(
                        'photoswipe-default-skin'
                    ),
                    YITH_WAPO_SCRIPT_VERSION );
                wp_enqueue_style( 'yith_wapo_jquery-ui', YITH_WAPO_URL . 'assets/css/jquery/jquery-ui-1.13.2.css', false, YITH_WAPO_SCRIPT_VERSION ); // jQuery UI.
                wp_enqueue_style( 'dashicons' );

                if ( ! wp_script_is( 'yith-plugin-fw-icon-font', 'registered' ) ) {
                    wp_register_style( 'yith-plugin-fw-icon-font', YIT_CORE_PLUGIN_URL . '/assets/css/yith-icon.css', array(), YITH_WAPO_SCRIPT_VERSION );
                }
                wp_enqueue_style( 'yith-plugin-fw-icon-font' );

                // ColorPicker with Iris library.
                wp_enqueue_style( 'wp-color-picker' );
                wp_enqueue_script(
                    'iris',
                    admin_url( 'js/iris.min.js' ),
                    array( 'jquery-ui-draggable', 'jquery-ui-slider', 'jquery-touch-punch' ),
                    YITH_WAPO_SCRIPT_VERSION,
                    true
                );

                // We make sure our wp-color-picker script is enqueued, not from third parties.
                if( wp_script_is( 'wp-color-picker', 'registered' ) ) {
                    wp_deregister_script( 'wp-color-picker' );
                }

                wp_enqueue_script(
                    'wp-color-picker',
                    admin_url( 'js/color-picker.min.js' ),
                    array( 'iris', 'wp-i18n' ),
                    YITH_WAPO_SCRIPT_VERSION,
                    true
                );

                // CSS - WC blocks.
                if ( has_block( 'woocommerce/checkout', $post ) || has_block( 'woocommerce/cart', $post ) ) {
                    wp_enqueue_style('yith_wapo_frontend_blocks',
                        YITH_WAPO_URL . 'assets/css/wc-blocks/frontend.css',
                        array(),
                        YITH_WAPO_SCRIPT_VERSION);
                }

                // JS.
                wp_register_script( 'yith_wapo_front', YITH_WAPO_URL . 'assets/js/front' . $suffix . '.js',
                    $this->get_enqueue_script_dependencies(),
                    YITH_WAPO_SCRIPT_VERSION,
                    true );

                $front_localize = array(
                    'dom'                            => array(
                        'single_add_to_cart_button' => '.single_add_to_cart_button',
                    ),
                    'i18n'                           => array(
                        // translators: Label printed in the add-on type Date, when activating the timepicker.
                        'datepickerSetTime'          => __( 'Set time', 'yith-woocommerce-product-add-ons' ),
                        // translators: Label printed in the add-on type Date, when activating the timepicker.
                        'datepickerSaveButton'       => __( 'Save', 'yith-woocommerce-product-add-ons' ),
                        // translators: Label printed if minimum value is 1.
                        'selectAnOption'             => __( 'Please, select an option', 'yith-woocommerce-product-add-ons' ),
                        // translators: Label printed if minimum value is more than 1. %d is the number of options.
                        'selectAtLeast'              => __( 'Please, select at least %d options', 'yith-woocommerce-product-add-ons' ),
                        // translators: Label printed for exact selection value. %d is the number of options.
                        'selectOptions'              => __( 'Please, select %d options', 'yith-woocommerce-product-add-ons' ),
                        // translators: [FRONT] Error when the user select more than allowed options ( min/max feature ).
                        'maxOptionsSelectedMessage'  => __( 'More options than allowed have been selected', 'yith-woocommerce-product-add-ons' ),
                        'uploadPercentageDoneString' => _x( 'done', '[FRONT] Percentage done when uploading a file on an add-on type File.', 'yith-woocommerce-product-add-ons' ),
                    ),
                    'ajaxurl'                        => admin_url( 'admin-ajax.php' ),
                    'addons_nonce'                   => wp_create_nonce( 'addons-nonce' ),
                    'upload_allowed_file_types'      => get_option( 'yith_wapo_upload_allowed_file_types', '.jpg, .jpeg, .pdf, .png, .rar, .zip' ),
                    'upload_max_file_size'           => get_option( 'yith_wapo_upload_max_file_size', '5' ),
                    'total_price_box_option'         => get_option( 'yith_wapo_total_price_box', 'all' ),
                    'replace_product_price'          => yith_wapo_should_replace_product_price( $post ),
                    'woocommerce_currency'           => esc_attr( get_woocommerce_currency() ),
                    'currency_symbol'                => get_woocommerce_currency_symbol( get_woocommerce_currency() ),
                    'currency_position'              => get_option( 'woocommerce_currency_pos', ',' ),
                    'total_thousand_sep'             => get_option( 'woocommerce_price_thousand_sep', ',' ),
                    'decimal_sep'                    => get_option( 'woocommerce_price_decimal_sep', '.' ),
                    'number_decimals'                => absint( get_option( 'woocommerce_price_num_decimals', 2 ) ),
                    'priceSuffix'                    => wc_tax_enabled() ? get_option( 'woocommerce_price_display_suffix', '' ) : '',
                    'includeShortcodePriceSuffix'    => apply_filters( 'yith_wapo_include_shortcode_price_suffix',
                        wc_tax_enabled() &&
                        ( strpos(get_option( 'woocommerce_price_display_suffix', '' ), '{price_including_tax}') !== false ||
                            strpos(get_option( 'woocommerce_price_display_suffix', '' ), '{price_excluding_tax}') !== false )
                    ),
                    'replace_image_path'             => $this->get_product_gallery_image_path(),
                    'replace_product_price_class'    => $this->get_product_price_class(),
                    'hide_button_required'           => get_option( 'yith_wapo_hide_button_if_required', 'no' ),
                    'messages'                       => array(
                        // translators: [FRONT] Message error when the value of the add-on type Number is below minimum accepted.
                        'lessThanMin'         => __( 'The value is less than the minimum. The minimum value is:', 'yith-woocommerce-product-add-ons' ),
                        // translators: [FRONT] Message error when total of add-ons type numbers does not exceeds the minimum set in the configuration
                        'moreThanMax'         => __( 'The value is greater than the maximum. The maximum value is:', 'yith-woocommerce-product-add-ons' ),
                        // translators: [FRONT] Message error when total of add-ons type numbers does not exceeds the minimum set in the configuration
                        'minErrorMessage'         => __( 'The sum of the numbers is below the minimum. The minimum value is:', 'yith-woocommerce-product-add-ons' ),
                        // translators: [FRONT] Message error when total of add-ons type numbers exceeds the maximum set in the configuration
                        'maxErrorMessage'         => __( 'The sum of the numbers exceeded the maximum. The maximum value is:', 'yith-woocommerce-product-add-ons' ),
                        // translators: [FRONT] Message giving the error after checking minimum and maximum quantity when adding to cart
                        'checkMinMaxErrorMessage' => __( 'Please, select an option', 'yith-woocommerce-product-add-ons' ),
                        // translators: [FRONT] [FRONT] Text to show when an option is required
                        'requiredMessage'         => get_option( 'yith_wapo_required_option_text', __( 'This option is required.', 'yith-woocommerce-product-add-ons' ) ),
                        // translators: [FRONT] Text to show for maximum files allowed on add-on type Upload
                        'maxFilesAllowed'         => __( 'Maximum uploaded files allowed. The maximum number of files allowed is: ', 'yith-woocommerce-product-add-ons' ),
                        // translators: [FRONT] Message giving the error when the file upload is not a supported extension
                        'noSupportedExtension'    => __( 'Error - not supported extension!', 'yith-woocommerce-product-add-ons' ),
                        // translators: [FRONT] Message giving the error when the file has a hight size
                        'maxFileSize'             => __( 'Error - file size for %s - max %d MB allowed!', 'yith-woocommerce-product-add-ons' ),

                    ),
                    'productQuantitySelector'          => apply_filters(
                        'yith_wapo_product_quantity_selector',
                        'form.cart .quantity input.qty:not(.wapo-product-qty)'
                    ),
                    'enableGetDefaultVariationPrice'   => apply_filters( 'yith_wapo_get_default_variation_price_calculation', true ),
                    'currentLanguage'                  => '',
                    'conditionalDisplayEffect'         => apply_filters( 'yith_wapo_conditional_display_effect', 'fade' ),
                    'preventAjaxCallOnUnchangedTotals' => apply_filters( 'yith_wapo_prevent_ajax_call_on_unchanged_totals', true ),
                    'wc_blocks'                        => array(
                        'has_cart_block' => has_block( 'woocommerce/cart' )
                    ),
                    'loader'                           => apply_filters( 'yith_wapo_loader_gif', YITH_WAPO_ASSETS_URL . '/img/loader.gif' ),
                    'isMobile'                         => wp_is_mobile(),
                    'hide_order_price_if_zero'         => apply_filters( 'yith_wapo_hide_order_price_if_zero', false ),
                    'disableCurrentDayBasedOnTime'     => apply_filters( 'yith_wapo_datepicker_disable_current_day_based_on_time', false ),
                    'datepickerHourToCheck'                      => apply_filters( 'yith_wapo_datepicker_hour_to_check', 13 ),

                );

                $front_localize = apply_filters( 'yith_wapo_frontend_localize_args', $front_localize );

                wp_localize_script( 'yith_wapo_front', 'yith_wapo', $front_localize );
                wp_enqueue_script( 'yith_wapo_front' );


            }

        }

        /**
         * Get the main product image classes on the product page to replace the image.
         * @return string
         */
        protected function get_product_gallery_image_path() {

            $image_class = '.woocommerce-product-gallery .woocommerce-product-gallery__wrapper .woocommerce-product-gallery__image:first-child img.zoomImg,
            .woocommerce-product-gallery .woocommerce-product-gallery__wrapper .woocommerce-product-gallery__image:first-child source,
            .yith_magnifier_zoom img, .yith_magnifier_zoom_magnifier,
            .owl-carousel .woocommerce-main-image,
            .woocommerce-product-gallery__image .wp-post-image,
            .dt-sc-product-image-gallery-container .wp-post-image,
            elementor-widget-theme-post-featured-image'; // TODO: Elementor integration

            // Is using WC Blocks.
            if ( yith_plugin_fw_wc_is_using_block_template_in_single_product() ) {
                $image_class = '.wp-block-woocommerce-product-image-gallery .woocommerce-product-gallery__wrapper .wp-post-image';
            }

            return apply_filters( 'yith_wapo_additional_replace_image_path', esc_attr( $image_class ) );
        }

        /** Get the enqueue script dependencies */
        protected function get_enqueue_script_dependencies() {
            global $post;

            $deps = parent::get_enqueue_script_dependencies();

            if ( is_cart() || has_block( 'woocommerce/checkout', $post ) ) {
                array_push( $deps, 'zoom', 'flexslider', 'photoswipe-ui-default' );
            }

            return $deps;
        }

        /**
         * Script with the template for the Upload type add-on.
         *
         * @return void
         */
        public function yith_wapo_uploaded_file_template() {
            if ( is_product() || is_cart() || is_checkout() ) {

                global $product, $variation;
                $has_upload_addon = YITH_WAPO_DB()->product_has_addon_type( $product, $variation, 'file' );

                if ( $has_upload_addon ) {
                    ?>
                    <script type="text/html" id="tmpl-yith-wapo-uploaded-file-template">

                        <div class="yith-wapo-uploaded-file-element uploaded-file-{{{data.fileIndex}}} completed" data-index="{{{data.fileIndex}}}">
                            <div class="yith-wapo-uploaded-file-info">
                        <span class="info">
                            <label class="file-name">
                                <span><?php echo esc_html( '{{{data.fileName}}}' ); ?></span>
                            </label>
                            <span class="file-size"><?php echo esc_html( '{{{data.fileSize}}}' ); ?></span>
                            <img src="{{{data.image}}}" alt="Image uploaded to add-on file input" class="yith-wapo-img-uploaded">
                        </span>
                                <i class="remove yith-plugin-fw__action-button__icon yith-icon yith-icon-trash"></i>
                            </div>
                            <div class="yith-wapo-loader-container" id="progressbar{{{data.fileIndex}}}">
                                <div class="yith-wapo-loader-label"></div>
                                <div class="yith-wapo-loader" role="progressbar"></div>
                            </div>
                            <input type="hidden" id="yith-wapo-{{{data.optionId}}}" class="option yith-wapo-option-value" name="yith_wapo[][{{{data.optionId}}}][]" value="{{{data.addonVal}}}" >
                        </div>
                    </script>
                    <?php
                }
            }
        }

    }
}
