<?php
/**
 * NOTICE OF LICENSE
 *
 * This file is licenced under the Software License Agreement.
 * With the purchase or the installation of the software in your application
 * you accept the licence agreement.
 *
 * You must not modify, adapt or create derivative works of this source code
 *
 *  @author    vosfactures.fr
 *  @copyright 2020 vosfactures.fr
 *  @license   LICENSE.txt
*/

use Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController;
use Automattic\WooCommerce\Utilities\OrderUtil;

/**
	 * The metabox-specific functionality of the plugin.
	 *
	 * @package    firmlet
	 * @subpackage firmlet/admin
	 * @author     VosFactures
	 * //
	 */
class VosfacturesAdmin_Metaboxes {


	/**
	 * The post meta data
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    string $meta The post meta data.
	 */
	private $meta;

	/**
	 * The ID of this plugin.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since  1.0.0
	 * @access private
	 * @var    string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since 1.0.0
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;

		$this->set_meta();
	}

	/**
	 * Registers metaboxes with WordPress
	 *
	 * @since  1.0.0
	 * @access public
	 */
	public function add_metaboxes() {
		$screen = class_exists( '\Automattic\WooCommerce\Internal\DataStores\Orders\CustomOrdersTableController' ) && wc_get_container()->get( CustomOrdersTableController::class )->custom_orders_table_usage_is_enabled()
			? wc_get_page_screen_id( 'shop-order' )
			: 'shop_order';

		add_meta_box(
			'firmlet-invoice-panel',
			apply_filters( 'firmlet-metabox-title-invoice-panel', 'VosFactures' ),
			array( $this, 'metabox' ),
			$screen,
			'normal',
			'high',
			array(
				'file' => 'invoice-panel',
			)
		);
	} // add_metaboxes()

	/**
	 * Check each nonce. If any don't verify, $nonce_check is increased.
	 * If all nonces verify, returns 0.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return int        The value of $nonce_check
	 */

	/**
	 * Sets the class variable $options
	 */
	public function set_meta() {
		global $post;

		if ( empty( $post ) ) {
			return;
		}

		if ( Vosfactures::custom_orders_table_usage_is_enabled() ) {
			$post_id = $post->get_id();
			$type    = OrderUtil::get_order_type( $post_id );
		} else {
			$post_id = $post->ID;
			$type    = $post->post_type;
		}

		if ( 'job' != $type ) {
			return;
		}

		$this->meta = get_post_custom( $post_id );
	} // set_meta()

	/**
	 * Calls a metabox file specified in the add_meta_box args.
	 *
	 * @since  1.0.0
	 * @access public
	 * @return void
	 */
	public function metabox( $post, $params ) {
		if ( ! is_admin() ) {
			return;
		}

		if ( Vosfactures::custom_orders_table_usage_is_enabled() ) {
			$not_order = ! OrderUtil::is_order( $post->get_id(), wc_get_order_types() );
		} else {
			$not_order = 'shop_order' !== $post->post_type;
		}

		if ( $not_order ) {
			return;
		}

		if ( ! empty( $params['args']['classes'] ) ) {
			$classes = 'repeater ' . $params['args']['classes'];
		}

		include plugin_dir_path( __FILE__ ) . 'partials/firmlet-admin-metabox-' . $params['args']['file'] . '.php';
	} // metabox()
}
