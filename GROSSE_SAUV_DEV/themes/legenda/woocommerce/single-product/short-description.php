<?php
/**
 * Single product short description
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     3.3.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $post;

$short_description = apply_filters( 'woocommerce_short_description', $post->post_excerpt );
if ( ! $short_description ) {
	return;
}
?>
<?php echo '<div itemprop="description" class="short-description">' . $short_description . '</div>'; ?>