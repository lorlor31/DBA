<?php

namespace Vibe\Clone_Orders;

defined( 'ABSPATH' ) || exit; // Exit if accessed directly

/**
 * Sets up Settings page and provides access to setting values
 *
 * @since 1.4.0
 */
class Settings {

	/**
	 * Creates an instance and sets up the hooks to integrate with the admin
	 */
	public function __construct() {
		add_filter( 'woocommerce_get_sections_advanced', array( __CLASS__, 'add_settings_page' ) );
		add_filter( 'woocommerce_get_settings_advanced', array( __CLASS__, 'add_settings' ), 10, 2 );
		add_filter( 'plugin_action_links', array( __CLASS__, 'add_settings_link' ), 10, 2 );
	}

	/**
	 * Adds a section to the advanced tab
	 *
	 * @param array $sections The existing settings sections on the advanced tab
	 *
	 * @return array The sections with the clone-orders settings section added
	 */
	public static function add_settings_page( array $sections ) {
		$sections['clone-orders'] = __( 'Clone orders', 'clone-orders' );

		return $sections;
	}

	/**
	 * Adds setting fields to the clone-orders section of the settings
	 *
	 * @param array  $settings        The current settings
	 * @param string $current_section The name of the current section of settings
	 *
	 * @return array The settings fields including clone orders settings if the current section is 'clone-orders'
	 */
	public static function add_settings( array $settings, $current_section ) {
		if ( 'clone-orders' != $current_section ) {
			return $settings;
		}

		$settings[] = array(
			'name' => __( 'Clone orders', 'clone-orders' ),
			'type' => 'title',
			'desc' => __( 'The following options are used to configure the Clone Orders extension.', 'clone-orders' )
		);

		$settings[] = array(
			'name'     => __( 'Clone quantity', 'clone-orders' ),
			'desc'     => __( 'Available stock only', 'clone-orders' ),
			'desc_tip' => __( 'Only clone items that there is sufficient stock for, or items on backorder', 'clone-orders' ),
			'id'       => Clone_Orders::hook_prefix( 'clone_instock_only' ),
			'type'     => 'checkbox'
		);

		$settings[] = array(
			'name'     => __( 'Additional fields', 'clone-orders' ),
			'desc_tip' => __( 'These fields will be included in any clone in addition to the standard fields.<br /><br />
							   Input each field on a new line, or separated by a comma.', 'clone-orders' ),
			'id'       => Clone_Orders::hook_prefix( 'meta_fields' ),
			'type'     => 'textarea',
			'css'      => 'min-width: 50%; height: 100px;'
		);

		$settings[] = array( 'type' => 'sectionend', 'id' => 'clone-orders' );

		return $settings;
	}

	/**
	 * Fetches and returns the clone quantity setting. Defaults to disabled.
	 *
	 * @return bool Whether to only clone the quantity available in stock
	 */
	public static function enable_cloning_instock_only() {
		return get_option( Clone_Orders::hook_prefix( 'clone_instock_only' ), false ) == 'yes';
	}

	/**
	 * Fetches and returns the meta fields setting after cleaning it and splitting into an array
	 *
	 * @return array The meta fields setting cleaned up
	 */
	public static function meta_fields() {
		$option = get_option( Clone_Orders::hook_prefix( 'meta_fields' ), '' );

		// Split at commas and new-line characters
		$fields = preg_split( '/[\n,]/', $option );

		// Trim
		$fields = array_map( 'trim', $fields );

		// Remove blanks
		$fields = array_filter( $fields );

		// Remove any duplicates
		$fields = array_unique( $fields );

		return array_values( $fields );
	}

	/**
	 * Adds a Settings link to the plugin list
	 *
	 * @return array The plugin's action links with a link to the plugin settings added
	 */
	public static function add_settings_link( $plugin_actions, $plugin_file ) {
		$new_actions = array();

		if ( 'clone-orders.php' === basename( $plugin_file ) ) {
			/* translators: %s: Settings */
			$new_actions['settings'] = sprintf( __( '<a href="%s">Settings</a>', 'clone-orders' ), esc_url( admin_url( 'admin.php?page=wc-settings&tab=advanced&section=clone-orders' ) ) );
		}

		return array_merge( $new_actions, $plugin_actions );
	}
}
