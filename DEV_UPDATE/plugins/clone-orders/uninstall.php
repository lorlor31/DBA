<?php
/**
 * Clone Orders uninstall
 *
 * Uninstalling deletes all options.
 */

// Exit if not called from WordPress
defined( 'WP_UNINSTALL_PLUGIN' ) || exit;

global $wpdb;

// Delete all options
$wpdb->query( "DELETE FROM $wpdb->options WHERE option_name LIKE 'vibe_clone_orders_%';" );
