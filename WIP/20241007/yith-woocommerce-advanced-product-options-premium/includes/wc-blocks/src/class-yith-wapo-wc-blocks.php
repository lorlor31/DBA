<?php
/**
 * This class manage the compatibility with Cart and Checkout block
 *
 * @package YITH\Addons\Classes
 * @since 4.8.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class YITH_WAPO_WC_Blocks {
	use YITH_WAPO_Singleton_Trait;


	protected function __construct() {
		add_action( 'wp_enqueue_scripts', array( $this, 'enqueue_scripts' ), 20 );
	}

	/**
	 * Enqueue styles and scripts
	 *
	 * @return void
	 */
	public function enqueue_scripts() {
		global $post;

		if ( has_block( 'woocommerce/checkout', $post ) || has_block( 'woocommerce/cart', $post ) ) {
			$deps = include YITH_WAPO_DIR . 'assets/js/build/wc-blocks/image-replacement/index.asset.php';

			wp_enqueue_script(
				'ywapo-image-replacement-block',
				YITH_WAPO_ASSETS_URL . '/js/build/wc-blocks/image-replacement/index.js',
				$deps['dependencies'],
				$deps['version'],
				true
			);
		}
	}
}