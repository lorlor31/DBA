<?php
/**
 * WAPO Premium Class
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\ProductAddOns
 * @version 2.0.0
 */

defined( 'YITH_WAPO' ) || exit; // Exit if accessed directly.

if ( ! class_exists( 'YITH_WAPO_Premium' ) ) {

	/**
	 *  YITH_WAPO Premium Class
	 */
	class YITH_WAPO_Premium extends YITH_WAPO {

		/**
		 * Returns single instance of the class
		 *
		 * @return \YITH_WAPO_Premium
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
		 * Initialize plugin and registers actions and filters to be used
		 *
		 * @since  1.0.0
		 */
		public function __construct() {

			parent::__construct();

            /**
             * Register plugin to licence/update system.
             */

            add_action( 'wp_loaded', array( $this, 'register_plugin_for_activation' ), 99 );
            add_action( 'wp_loaded', array( $this, 'register_plugin_for_updates' ), 99 );

            add_action( 'init', array( $this, 'load_modules' ), 0 );

		}

        /**
         * Register plugins for activation tab
         *
         * @return void
         * @since 2.0.0
         */
        public function register_plugin_for_activation() {
            if ( function_exists( 'YIT_Plugin_Licence' ) ) {
                YIT_Plugin_Licence()->register( YITH_WAPO_INIT, YITH_WAPO_SECRET_KEY, YITH_WAPO_SLUG );
            }
        }

        /**
         * Register plugins for update tab
         *
         * @return void
         * @since 2.0.0
         */
        public function register_plugin_for_updates() {
            if ( function_exists( 'YIT_Upgrade' ) ) {
                YIT_Upgrade()->register( YITH_WAPO_SLUG, YITH_WAPO_INIT );
            }
        }

        /**
         * Load plugin modules
         *
         * @return void
         */
        public function load_modules() {

            // todo:remove yith_wapo_settings_disable_wccl option from the database.

            if ( ! function_exists( 'YITH_WCCL' ) ) {
                require_once YITH_WAPO_DIR . 'modules/color-label-variations/class-yith-wapo-color-label-variations.php';
            }
        }

		/**
		 * Get available addon types
		 *
		 * @return array
		 * @since 2.0.0
		 */
		public function get_available_addon_types() {

            $available_addon_types = array(
                'checkbox',
                'radio',
                'text',
                'textarea',
                'color',
                'number',
                'select',
                'label',
                'product',
                'date',
                'file',
                'colorpicker'
            );

            return $available_addon_types;
		}
	}
}
