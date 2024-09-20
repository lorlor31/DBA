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

/**
	 * Fired during plugin deactivation
	 *
	 * @package    Firmlet
	 * @subpackage Firmlet/includes
	 * @link  http://example.com
	 * @since 1.0.0
	 */

	/**
	 * Fired during plugin deactivation.
	 *
	 * This class defines all code necessary to run during the plugin's deactivation.
	 *
	 * @since      1.0.0
	 * @package    firmlet
	 * @subpackage firmlet/includes
	 * @author     VosFactures
	 */
class VosfacturesDeactivator {


	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since 1.0.0
	 */
	public static function deactivate() {
		delete_option( 'vosfactures' );
		delete_option( 'firmlet_deferred_admin_notices' );
	}
}
