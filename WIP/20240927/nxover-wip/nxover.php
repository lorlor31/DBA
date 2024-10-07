<?php

/*
Plugin Name: Nx Override
Plugin URI:  http://web.nexilogic.eu/wp-plugins/nxover/
Description: Custom overrides
Version:     1.0.2
Author:      Nexilogic
Author URI:  http://web.nexilogic.eu/contact
License:     Commercial
License URI: http://web.nexilogic.eu/module/LICENSE.txt
Text Domain: nxover
Domain Path: /languages
*/

if (! function_exists('add_action'))
{
	echo 'Hi there !  I\'m just a plugin, not much I can do when called directly.';
	exit;
}

define('NXOVER_PLUGIN_ID', basename(__FILE__, '.php'));

define('NXOVER_PLUGIN_URL', plugin_dir_url(__FILE__));
define('NXOVER_PLUGIN_DIR', plugin_dir_path(__FILE__));

register_activation_hook(__FILE__, 'nxover_install');
register_deactivation_hook(__FILE__, 'nxover_uninstall');

require_once NXOVER_PLUGIN_DIR.'inc/setup.php';
