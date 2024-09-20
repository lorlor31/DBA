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
	 * Fired during plugin activation
	 *
	 * @link  http://example.com
	 * @since 1.0.0
	 *
	 * @package    firmlet
	 * @subpackage firmlet/includes
	 */

	/**
	 * Fired during plugin activation.
	 *
	 * This class defines all code necessary to run during the plugin's activation.
	 *
	 * @since      1.0.0
	 * @package    firmlet
	 * @subpackage firmlet/includes
	 * @author     VosFactures
	 */
class VosfacturesActivator {


	/**
	 * Short Description. (use period)
	 *
	 * Long Description.
	 *
	 * @since 1.0.0
	 */


	public function activate() {
		include_once plugin_dir_path( dirname( __FILE__ ) ) . 'includes/class-firmlet-database.php';
		$this->db = new VosfacturesDatabase();
		$this->db->install_database();
	}
}
