<?php
/**
 * Add description field to add/edit products attribute
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH Composite Products for WooCommerce Premium
 * @version 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}
?>

<div id="ywcp_tab_dependecies" class="ywcp_panel panel woocommerce_options_panel wc-metaboxes-wrapper">
    <?php

    /**
     * yith_woocommerce_component_edit_admin_html hook.
     *
     * @hooked yith-wcp-edit-depenedencies-list-options - 15
     */
    do_action( 'yith_woocommerce_dependencies_edit_admin_html', $wcp_data , $wcp_data_dependencies, $wcp_data_dependencies_component_list_data, $post->ID , $wpdb );

    ?>
</div>