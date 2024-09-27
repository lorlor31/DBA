<?php
/**
 * Compatibility Class
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\ProductAddOns
 * @version 3.4.0
 */

defined( 'YITH_WAPO' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_WAPO_Compatibility' ) ) {
	/**
	 * Compatibility Class
	 *
	 * @class   YITH_WAPO_Compatibility
	 * @since   3.4.0
	 */
	class YITH_WAPO_Compatibility {

		/**
		 * Single instance of the class
		 *
		 * @var YITH_WAPO_Compatibility
		 */
		protected static $instance;

		/**
		 * Array of compatibilities
		 *
		 * @var array
		 */
		private $compatibilities;

		/**
		 * Returns single instance of the class
		 *
		 * @return YITH_WAPO_Compatibility
		 */
		public static function get_instance() {
			return ! is_null( self::$instance ) ? self::$instance : self::$instance = new self();
		}

		/**
		 * Constructor
		 */
		public function __construct() {
			$this->compatibilities = array(
				'multi-currency-switcher'    => 'WCMCS',
                'wcml-multi-currency'        => 'WCML_Multi_Currency',
				'sitepress-multilingual-cms' => 'WPML',
				'multi-vendor'               => 'WPV',
                'composite'                  => 'Composite',
                'wc-product-bundles'         => 'WC_Product_Bundles',
                'product-bundles'            => 'Product_Bundles',
                'subscription'               => 'Subscription',
			);
			$this->load();
		}

		/**
		 * Load classes
		 */
		private function load() {

			foreach ( $this->compatibilities as $slug => $class_slug ) {
				$filename  = '/class-yith-wapo-' . $slug . '-compatibility.php';
				$classname = 'YITH_WAPO_' . $class_slug . '_Compatibility';

				$var      = str_replace( '-', '_', $slug );
				$filepath = YITH_WAPO_COMPATIBILITY_PATH . $filename;

				if ( ! file_exists( $filepath ) ) {
					$filepath_in_folder = YITH_WAPO_COMPATIBILITY_PATH . '/' . $slug . $filename;
					$filepath           = file_exists( $filepath_in_folder ) ? $filepath_in_folder : false;
				}
				if ( $filepath && $this->has_plugin_or_theme( $slug ) ) {
					require_once $filepath;
					if ( class_exists( $classname ) && method_exists( $classname, 'get_instance' ) ) {
						$classname::get_instance();
					}
				}
			}
		}

		/**
		 * Check if user has a plugin
		 *
		 * @param string $slug Plugin or theme slug.
		 *
		 * @return bool
		 */
		public function has_plugin_or_theme( $slug ) {
			$has = false;
			switch ( $slug ) {
				case 'multi-currency-switcher':
					$has = defined( 'YITH_WCMCS_INIT' );
					break;
                case 'wcml-multi-currency':
                    $has = false;

                    if ( defined( 'WCML_VERSION' ) ) {
                        $wcml_settings = get_option( '_wcml_settings' );
                        if ( $wcml_settings['enable_multi_currency'] > 0 ) {
                            $has = true;
                        }
                    }
                    break;
                case 'sitepress-multilingual-cms':
                    $has = defined( 'ICL_SITEPRESS_VERSION' );
                    break;
                case 'multi-vendor':
                    $has = defined( 'YITH_WPV_PREMIUM' );
                    break;
                case 'composite':
                    $has = defined( 'YITH_WCP_PREMIUM' );
                    break;
                case 'wc-product-bundles':
                    $has = defined( 'WC_PB_VERSION' );
                    break;
                case 'product-bundles':
                    $has = defined( 'YITH_WCPB_PREMIUM' );
                    break;
                case 'subscription':
                    $has = defined( 'YITH_YWSBS_PREMIUM' );
                    break;
			}

			return $has;
		}
	}
}

if ( ! function_exists( 'yith_wapo_compatibility' ) ) {
	/**
	 * Unique access to instance of YITH_WAPO_Compatibility class
	 *
	 * @return YITH_WAPO_Compatibility
	 * @since 3.4.0
	 */
	function yith_wapo_compatibility() {
		return YITH_WAPO_Compatibility::get_instance();
	}
}
