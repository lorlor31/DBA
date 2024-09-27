<?php
/**
 * Template for variation support opt-in.
 *
 * @package  woocommerce-gpf
 */

?>
<h3><?php esc_html_e( 'Other settings', 'woocommerce_gpf' ); ?></h3>
<p>
	<input type="checkbox" class="woocommerce_gpf_field_selector" name="woocommerce_gpf_config[include_variations]" id="woocommerce_gpf_config[include_variations]" {include_variations_selected}>
	<label for="woocommerce_gpf_config[include_variations]"><?php
	esc_html_e( __( 'Include variations in your feed.', 'woocommerce_gpf' ) );
        ?></label><br>
	<strong><?php esc_html_e( _x( 'Note:', 'Introduction to description for "include variations" option', 'woocommerce_gpf' ) ); ?></strong>
    <?php echo wp_kses( sprintf( __( 'We strongly recommend leaving this enabled. If you feel you may need to disable this, please reach out to our <a href="%s" rel="noopener noreferrer">support team</a> to discuss your options first.', 'woocommerce_gpf' ), 'https://woocommerce.com/my-account/contact-support/' ), ['a' => [ 'href' => true, 'rel' => true ] ] ); ?>
</p>
