<?php
/**
 * Single Product tabs
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     3.8.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

/**
 * Filter tabs and allow third parties to add their own
 *
 * Each tab is an array containing title, callback and priority.
 * @see woocommerce_default_product_tabs()
 */
$product_tabs = apply_filters( 'woocommerce_product_tabs', array() );
if(!empty($product_tabs['additional_information'])) {
    $product_tabs['additional_information']['title'] = __('More Info', 'legenda');
}
?>
	<div class="tabs clearfix <?php etheme_option('tabs_type'); ?>">
		<?php if ( ! empty( $product_tabs ) ) : ?>
			<?php foreach ( $product_tabs as $key => $product_tab ) : ?>
				<a href="#tab_<?php echo esc_attr($key); ?>" id="tab_<?php echo esc_attr($key); ?>" class="tab-title"><?php echo apply_filters( 'woocommerce_product_' . $key . '_tab_title', $product_tab['title'], $key ) ?></a>
				<div class="tab-content" id="content_tab_<?php echo esc_attr($key); ?>">
					<?php call_user_func( $product_tab['callback'], $key, $product_tab ) ?>
				</div>
			<?php endforeach; ?>
		<?php endif; ?>
		
        <?php if (etheme_get_custom_field('custom_tab1_title') && etheme_get_custom_field('custom_tab1_title') != '' ) : ?>
            <a href="#tab_7" id="tab_7" class="tab-title"><?php etheme_custom_field('custom_tab1_title'); ?></a>
            <div id="content_tab_7" class="tab-content">
        		<?php echo do_shortcode(etheme_get_custom_field('custom_tab1')); ?>
            </div>
        <?php endif; ?>	 
        
        <?php if (etheme_get_option('custom_tab_title') && etheme_get_option('custom_tab_title') != '' ) : ?>
            <a href="#tab_9" id="tab_9" class="tab-title"><?php etheme_option('custom_tab_title'); ?></a>
            <div id="content_tab_9" class="tab-content">
        		<?php echo do_shortcode(etheme_get_option('custom_tab')); ?>
            </div>
        <?php endif; ?>	
	</div>
