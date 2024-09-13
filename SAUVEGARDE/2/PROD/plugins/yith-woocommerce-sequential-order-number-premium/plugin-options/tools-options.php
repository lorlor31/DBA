<?php
/**
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @package YITH WooCommerce Sequential Order Number Premium
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly


$settings = array(
	'tools' => array(
		'tools-action' => array(
			'type'   => 'custom_tab',
			'action' => 'ywson_tools_tab',
		),
	),
);

return $settings;
