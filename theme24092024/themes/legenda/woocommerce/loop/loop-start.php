<?php
/**
 * Product Loop Start
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     3.3.0
 */
?>
<?php
global $woocommerce_loop;
// Store column count for displaying the grid

$loop = wc_get_loop_prop( 'columns' );
if(!empty($woocommerce_loop['shortcode_columns'])) {
	$loop_count = $woocommerce_loop['shortcode_columns'];
}

?>
<?php $view_mode = etheme_get_option('view_mode'); ?>
<?php
    if($view_mode == 'list' || $view_mode == 'list_grid') {
        $view_class = 'products-list';
    }else{
        $view_class = 'products-grid';
    }

?>
<div class="product-loop <?php echo esc_attr($view_class); ?> product-count-<?php echo esc_attr($loop); ?>">
