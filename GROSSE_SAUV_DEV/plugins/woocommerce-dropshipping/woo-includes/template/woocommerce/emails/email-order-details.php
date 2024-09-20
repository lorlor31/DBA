<?php

/**
 * Order details table shown in emails.
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/emails/email-order-details.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://docs.woocommerce.com/document/template-structure/
 * @package WooCommerce\Templates\Emails
 * @version 3.7.0
 */

defined( 'ABSPATH' ) || exit;


$text_align = is_rtl() ? 'right' : 'left';
global  $woocommerce;
do_action( 'woocommerce_email_before_order_table', $order, $sent_to_admin, $plain_text, $email ); ?>

<h2>
	<?php
	if ( $sent_to_admin ) {
		$before = '<a class="link" href="' . esc_url( $order->get_edit_order_url() ) . '">';
		$after  = '</a>';
	} else {
		$before = '';
		$after  = '';
	}
	/* translators: %s: Order ID. */
	echo wp_kses_post( $before . sprintf( __( '[Order #%s]', 'woocommerce-dropshipping' ) . $after . ' (<time datetime="%s">%s</time>)', $order->get_order_number(), $order->get_date_created()->format( 'c' ), wc_format_datetime( $order->get_date_created() ) ) );
	?>
</h2>

<div style="margin-bottom: 40px;">
	<?php
		$options = get_option( 'wc_dropship_manager' );
		$split_gst_amount = $options['show_gst_supplier_email'];
	?>
	<table class="td" cellspacing="0" cellpadding="6" style="width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; text-align:center;" border="1">
		<thead>
			<tr>
				<?php if ( 0 == $split_gst_amount ) { ?>
					<th class="td" scope="col" style="text-align:<?php echo esc_attr( $text_align ); ?>; width:30%;"><?php esc_html_e( 'Product', 'woocommerce-dropshipping' ); ?></th>

					<th class="td" scope="col" style="text-align:<?php echo esc_attr( $text_align ); ?>; width:20%;"><?php esc_html_e( 'Quantity', 'woocommerce-dropshipping' ); ?></th>
				<?php } else { ?>
					<th class="td" scope="col" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php esc_html_e( 'Product', 'woocommerce-dropshipping' ); ?></th>

					<th class="td" scope="col" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php esc_html_e( 'Quantity', 'woocommerce-dropshipping' ); ?></th>

				<?php } ?>


				<?php if ( 1 == $split_gst_amount ) { ?>

				<th class="td" scope="col" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php esc_html_e( 'Price (excl GST)', 'woocommerce-dropshipping' ); ?></th>

				<th class="td" scope="col" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php esc_html_e( 'Sub Total (excl GST)', 'woocommerce-dropshipping' ); ?></th>

				<th class="td" scope="col" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php esc_html_e( 'GST Amount', 'woocommerce-dropshipping' ); ?></th>

				<th class="td" scope="col" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php esc_html_e( 'Amount Payable (Incl GST)', 'woocommerce-dropshipping' ); ?></th>

				<?php } elseif ( 0 == $split_gst_amount ) { ?>

				<th class="td" scope="col" style="text-align:<?php echo esc_attr( $text_align ); ?>; width:40%;"><?php esc_html_e( 'Price', 'woocommerce-dropshipping' ); ?></th>

				<?php } else { ?>
					<th class="td" scope="col" style="text-align:<?php echo esc_attr( $text_align ); ?>; "><?php esc_html_e( 'Price', 'woocommerce-dropshipping' ); ?></th>

					<?php } ?>


			</tr>
		</thead>
		<tbody>


			<?php

					$items = $order->get_items();
			foreach ( $items as $item_id => $item ) {
				$product = $item->get_product();
				$sub_total = $item->get_quantity() * wc_get_price_excluding_tax( $product );
				$total_gst = $item->get_subtotal_tax();

				?>
		 <tr>
			 <td><?php echo $item->get_name(); ?><br/><?php echo $product->get_sku(); ?></td>
			<td><?php echo $item->get_quantity(); ?></td>
			<td><?php echo wc_price( wc_get_price_excluding_tax( $product ) ); ?></td>

				<?php if ( 1 == $split_gst_amount ) { ?>
				<td><?php echo wc_price( $item->get_quantity() * wc_get_price_excluding_tax( $product ) ); ?></td>
				<td><?php echo  wc_price( $item->get_subtotal_tax() ); ?></td>
				<td><?php echo wc_price( ( $item->get_quantity() * wc_get_price_excluding_tax( $product ) ) + $item->get_total_tax() ); ?></td>
			<?php } else { ?>
				<td style="display:none"><?php echo wc_price( $item->get_quantity() * wc_get_price_excluding_tax( $product ) ); ?></td>
				<td style="display:none"><?php echo  wc_price( $item->get_subtotal_tax() ); ?></td>
				<td style="display:none"><?php echo wc_price( ( $item->get_quantity() * wc_get_price_excluding_tax( $product ) ) + $item->get_total_tax() ); ?></td>
			<?php } ?>

		</tr>
				<?php

			}





			/*
			  echo wc_get_email_order_items( // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
				$order,
				array(
					'show_sku'      => $sent_to_admin,
					'show_image'    => false,
					'image_size'    => array( 32, 32 ),
					'plain_text'    => $plain_text,
					'sent_to_admin' => $sent_to_admin,
				)
			);*/
			?>

			<tr style="font-weight:bold;">
				 <td>Subtotal</td>
				<td>&nbsp;</td>
				<?php if ( 1 == $split_gst_amount ) { ?>
					<td>&nbsp;</td>
				<?php } else { ?>
					<td style="display:none">&nbsp;</td>
				<?php } ?>
				<td><?php echo wc_price( floatval( $sub_total ) ); ?></td>

				<?php if ( 1 == $split_gst_amount ) { ?>

					<td><?php echo  wc_price( floatval( $total_gst ) ); ?></td>
					<td><?php echo wc_price( floatval( ( ( $sub_total + $total_gst ) * 100 ) ) / 100 ); ?></td>
				<?php } else { ?>
					<td style="display:none"><?php echo  wc_price( floatval( $total_gst ) ); ?></td>
					<td style="display:none"><?php echo wc_price( floatval( ( ( $sub_total + $total_gst ) * 100 ) ) / 100 ); ?></td>
				<?php } ?>
			</tr>


		</tbody>
		<tfoot>
		<?php if ( 0 == $split_gst_amount ) { ?>
			<?php
			$item_totals = $order->get_order_item_totals();

			if ( $item_totals ) {
				$i = 0;
				foreach ( $item_totals as $total ) {
					$i++;
					if ( wp_kses_post( $total['label'] ) != 'Subtotal:' ) {
						?>

					<tr>
						<th class="td" scope="row" colspan="2" style="text-align:<?php echo esc_attr( $text_align ); ?>; <?php echo ( 1 === $i ) ? 'border-top-width: 4px;' : ''; ?>"><?php echo wp_kses_post( $total['label'] ); ?></th>
						<td class="td"  colspan="2" style="text-align:<?php echo esc_attr( $text_align ); ?>; <?php echo ( 1 === $i ) ? 'border-top-width: 4px;' : ''; ?>"><?php echo wp_kses_post( $total['value'] ); ?></td>
					</tr>
						<?php
					}
				}
			}
			if ( $order->get_customer_note() ) {
				?>
				<tr>
					<th class="td" scope="row" colspan="2" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php esc_html_e( 'Note:', 'woocommerce-dropshipping' ); ?></th>
					<td class="td" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php echo wp_kses_post( nl2br( wptexturize( $order->get_customer_note() ) ) ); ?></td>
				</tr>
				<?php
			}
		} else {
			?>
				<?php
				$item_totals = $order->get_order_item_totals();

				if ( $item_totals ) {
					$i = 0;
					foreach ( $item_totals as $total ) {
						$i++;
						if ( wp_kses_post( $total['label'] ) != 'Subtotal:' ) {
							?>

					<tr>
						<th class="td" scope="row" colspan="4" style="text-align:<?php echo esc_attr( $text_align ); ?>; <?php echo ( 1 === $i ) ? 'border-top-width: 4px;' : ''; ?>"><?php echo wp_kses_post( $total['label'] ); ?></th>
						<td class="td"  colspan="2" style="text-align:<?php echo esc_attr( $text_align ); ?>; <?php echo ( 1 === $i ) ? 'border-top-width: 4px;' : ''; ?>"><?php echo wp_kses_post( $total['value'] ); ?></td>
					</tr>
							<?php
						}
					}
				}
				if ( $order->get_customer_note() ) {
					?>
				<tr>
					<th class="td" scope="row" colspan="2" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php esc_html_e( 'Note:', 'woocommerce-dropshipping' ); ?></th>
					<td class="td" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php echo wp_kses_post( nl2br( wptexturize( $order->get_customer_note() ) ) ); ?></td>
				</tr>
					<?php
				}
				?>
					<?php } ?>
		</tfoot>
	</table>
</div>

<?php do_action( 'woocommerce_email_after_order_table', $order, $sent_to_admin, $plain_text, $email ); ?>
