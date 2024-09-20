<?php

/**
 * Class WCCT_Compatibility_WCABE
 * This Class is responsive for controlling the behaviour of saving product using woocommerce advanced bulk edit plugin.
 */
if ( class_exists( 'W3ExAdvancedBulkEditMain' ) ) {
	class WCCT_Compatibility_WCABE {
		private static $ins = null;

		public function __construct() {
			add_action( 'woocommerce_update_product', array( $this, 'wcct_finale_product_save_wcabe' ), 10, 1 );
		}

		public static function get_instance() {
			if ( null === self::$ins ) {
				self::$ins = new self;
			}

			return self::$ins;
		}

		public function wcct_finale_product_save_wcabe( $product_id ) {
			delete_post_meta( $product_id, '_wcct_product_taxonomy_term_ids' );
		}
	}

	WCCT_Compatibility_WCABE::get_instance();
}
