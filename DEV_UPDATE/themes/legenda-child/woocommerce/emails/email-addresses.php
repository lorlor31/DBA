<?php
/**
 * Email Addresses
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/email-addresses.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates\Emails
 * @version 8.6.0
 */

/** 
 * EDIT NOTES FOR KADENCE WOOMAIL DESIGNER
 *
 * Add support for responsive email.
 */



if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$text_align       = is_rtl() ? 'right' : 'left';
$address          = $order->get_formatted_billing_address();
$shipping         = $order->get_formatted_shipping_address();
$responsive_check = Kadence_Woomail_Customizer::opt( 'responsive_mode' );
if ( true == $responsive_check ) {
	?>
<table id="addresses" cellspacing="0" cellpadding="0" style="width: 100%; vertical-align: top; margin-bottom: 40px; padding:0;" border="0">
	<tr>
		<td class="address-container" style="text-align:<?php echo esc_attr( $text_align ); ?>; border:0; padding:0;" valign="top" width="48%">
			<h2><?php esc_html_e( 'Billing address', 'kadence-woocommerce-email-designer' ); ?></h2>

			<address class="address">
			<table cellspacing="0" cellpadding="0" style="width: 100%; padding:0;" border="0">
						<tr>
							<td class="address-td" valign="top">
				<?php echo wp_kses_post( $address ? $address : esc_html__( 'N/A', 'kadence-woocommerce-email-designer' ) ); ?>
				<?php
								// Adds in support for plugin.
								if ( ! class_exists( 'APG_Campo_NIF' ) ) {
									if ( $order->get_billing_phone() ) :
										?>
					<br/><?php echo wc_make_phone_clickable( $order->get_billing_phone() ); ?>
				<?php endif; ?>
				<?php if ( $order->get_billing_email() ) : ?><a href="mailto:<?php echo esc_attr( $order->get_billing_email() ); ?>"><?php echo esc_html( $order->get_billing_email() ); ?></a>										<?php
									endif;
								}
								?>
							</td>
						</tr>
					</table>
				/**
				 * Fires after the core address fields in emails.
				 *
				 * @since 8.6.0
				 *
				 * @param string $type Address type. Either 'billing' or 'shipping'.
				 * @param WC_Order $order Order instance.
				 * @param bool $sent_to_admin If this email is being sent to the admin or not.
				 * @param bool $plain_text If this email is plain text or not.
				 */
				do_action( 'woocommerce_email_customer_address_section', 'billing', $order, $sent_to_admin, false );
				?>
			</address>
		</td>
		<?php if ( ! wc_ship_to_billing_address_only() && $order->needs_shipping_address() && $shipping ) : ?>
			<td class="shipping-address-space" style="padding:0; min-width:10px; width:4%;"></td>
			<td class="shipping-address-container" style="text-align:<?php echo esc_attr( $text_align ); ?>width:48%;" valign="top">
					<h2><?php esc_html_e( 'Shipping address', 'kadence-woocommerce-email-designer' ); ?></h2>

					<address class="address">
						<table cellspacing="0" cellpadding="0" style="width: 100%; padding:0;" border="0">
							<tr>
								<td class="address-td" valign="top">
									<?php echo wp_kses_post( $shipping ); ?>
									<?php if ( method_exists( $order, 'get_shipping_phone' ) && $order->get_shipping_phone() ) : ?>
										<br /><?php echo wc_make_phone_clickable( $order->get_shipping_phone() ); ?>
									<?php endif; ?>
								</td>
							</tr>
						</table>
					</address>
				</td>

		<?php endif; ?>
		</tr>
	</table>
	<?php
} else {
	?>
	<table id="addresses" cellspacing="0" cellpadding="0" style="width: 100%; vertical-align: top; margin-bottom: 40px; padding:0;" border="0">
		<tr>
			<td class="address-container" style="text-align:<?php echo esc_attr( $text_align ); ?>; padding:0; border:0;" valign="top" width="50%">
				<h2><?php esc_html_e( 'Billing address', 'kadence-woocommerce-email-designer' ); ?></h2>

				<address class="address">
				<table cellspacing="0" cellpadding="0" style="width: 100%; padding:0;" border="0">
						<tr>
							<td class="address-td" valign="top">
								<?php echo wp_kses_post($address ? $address : esc_html__('N/A', 'kadence-woocommerce-email-designer')); ?>
								<?php
								// Adds in support for plugin.
								if (! class_exists('APG_Campo_NIF')) {
									if ($order->get_billing_phone()) :
								?>
										<br /><?php echo wc_make_phone_clickable($order->get_billing_phone()); ?>
									<?php endif; ?>
									<?php if ($order->get_billing_email()) : ?>
										<a href="mailto:<?php echo esc_attr($order->get_billing_email()); ?>"><?php echo esc_html($order->get_shipping_email()); ?></a>
								<?php
									endif;
								}
								?>
							</td>
						</tr>
					</table>
					</address>
			</td>
			<?php if (! wc_ship_to_billing_address_only() && $order->needs_shipping_address() && $shipping) : ?>
				<td class="shipping-address-container" style="text-align:<?php echo esc_attr($text_align); ?>; padding:0 0 0 20px;" valign="top" width="50%">
					<h2><?php esc_html_e('Shipping address', 'kadence-woocommerce-email-designer'); ?></h2>
					<address class="address">
						<table cellspacing="0" cellpadding="0" style="width: 100%; padding:0;" border="0">
							<tr>
								<td class="address-td" valign="top">
									<?php echo wp_kses_post($shipping); ?>
									<?php if (method_exists($order, 'get_shipping_phone') && $order->get_shipping_phone()) : ?>
										<br /><?php echo wc_make_phone_clickable($order->get_shipping_phone()); ?>
									<?php endif; ?>
								</td>
							</tr>
						</table>

						
					<?php
					do_action( 'woocommerce_email_customer_address_section', 'shipping', $order, $sent_to_admin, false );
					?>
				</address>
			</td>
		<?php endif; ?>
	</tr>
</table>
<?php
}