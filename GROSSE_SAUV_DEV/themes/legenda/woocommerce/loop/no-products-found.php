<?php
/**
 * Displayed when no products are found matching the current query.
 *
 * Override this template by copying it to yourtheme/woocommerce/loop/no-products-found.php
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     7.8.0
 */

defined( 'ABSPATH' ) || exit;
?>
<div class="empty-category-block">
	
	<?php etheme_option( 'empty_category_content' ); ?>	
	<p><a class="button active arrow-left" href="<?php echo get_permalink( wc_get_page_id( 'shop' ) ); ?>"><span><?php esc_html_e( 'Return To Shop', 'legenda' ) ?></span></a></p>
	
</div>