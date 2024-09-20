<?php
/**
 * Loop Rating
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     3.6.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $product;

if ( ! wc_review_ratings_enabled() ) {
	return;
}
?>

<?php if ( $rating_html = wc_get_rating_html( $product->get_average_rating() ) ) : ?>
	<div class="woocommerce-product-rating">
		<span class="rating-label"><?php esc_html_e( 'Rating:', 'legenda' ); ?></span>
		<?php echo wc_get_rating_html( $product->get_average_rating() ); // WordPress.XSS.EscapeOutput.OutputNotEscaped. ?>
	</div>
<?php endif; ?>