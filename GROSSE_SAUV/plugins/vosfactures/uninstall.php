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
	 * Fired when the plugin is uninstalled.
	 *
	 * When populating this file, consider the following flow
	 * of control:
	 *
	 * - This method should be static
	 * - Check if the $_REQUEST content actually is the plugin name
	 * - Run an admin referrer check to make sure it goes through authentication
	 * - Verify the output of $_GET makes sense
	 * - Repeat with other user roles. Best directly by using the links/query string parameters.
	 * - Repeat things for multisite. Once for a single site in the network, once sitewide.
	 *
	 * This file may be updated more in future version of the Boilerplate; however, this is the
	 * general skeleton and outline for how the file should work.
	 *
	 * For more information, see the following discussion:
	 * https://github.com/tommcfarlin/WordPress-Plugin-Boilerplate/pull/123#issuecomment-28541913
	 *
	 * @link  http://example.com
	 * @since 1.0.0
	 *
	 * @package firmlet
	 */

// If uninstall not called from WordPress, then exit.
if ( ! defined( 'WP_UNINSTALL_PLUGIN' ) ) {
	exit;
}
