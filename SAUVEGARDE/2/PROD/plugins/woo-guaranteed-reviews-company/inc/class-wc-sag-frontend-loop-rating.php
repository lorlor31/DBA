<?php

class WC_SAG_Frontend_Loop_Rating {
    /** @var WC_SAG_Settings Plugin settings */
    protected $settings;

    /**
     * Constructor
     */
    public function __construct( $settings ) {
        $this->settings = $settings;

        if ( $this->settings->get( 'enable_loop_rating' ) == 1 ) {
            add_filter( 'woocommerce_after_shop_loop_item_title', array( $this, 'add_ratings' ), 2 );
        }
    }

    /**
     * Add SAG ratings to product loop
     */
    public function add_ratings() {
        global $product;

        $product_id = version_compare( WC_VERSION, '3.0.0', '<' ) ? $product->id : $product->get_id();
        $ratings = wcsag_get_ratings( $product_id );

        if ( $ratings['average'] ) {
            include( WC_SAG_PLUGIN_DIR . 'views/loop-star-rating.php' );
        }
    }
}