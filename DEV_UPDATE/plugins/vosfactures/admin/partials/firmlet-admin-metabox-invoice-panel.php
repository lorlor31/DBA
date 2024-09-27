<?php
/**
 * NOTICE OF LICENSE
 *
 * This file is licenced under the Software License Agreement.
 * With the purchase or the installation of the software in your application
 * you accept the licence agreement.
 *
 * You must not modify, adapt or create derivative works of this source code
 *
 *  @author    vosfactures.fr
 *  @copyright 2020 vosfactures.fr
 *  @license   LICENSE.txt
*/

if ( Vosfactures::custom_orders_table_usage_is_enabled() ) {
	$post_id = $post->get_id();
} else {
	$post_id = $post->ID;
}

$module   = firmlet_vosfactures();
$api      = new VosfacturesApi();
$this->db = new VosfacturesDatabase();
$order = wc_get_order( $post_id );
$invoice  = $this->db->get_last_invoice( $order->get_id() );
do_action( 'firmlet_meta_box_start', $post_id );
?>
	<div class="wc-firmlet-data-fields">
		<?php if ( $module->is_configured() ) : ?>
			<div>
				<img src="<?php echo esc_html( plugin_dir_url( dirname( __FILE__ ) ) ); ?>logo.png" height="32" width="32">
				<span style="position: absolute; margin-top: 6px">
					<b><?php esc_html_e( 'Integration with', 'firmlet' ); ?></b>
					<a target="_blank" href="<?php echo esc_html( $api->get_api_url() ); ?>"> <?php echo esc_html( $api->get_api_url() ); ?></a>
				</span>
			</div>


			<?php if ( ! empty( $invoice ) && ! empty( $invoice->external_id ) && empty( $invoice->error ) ) : ?>
				<ul>
					<li>
						<a target="_blank" class="button" href="<?php echo esc_html( $api->get_invoice_url( $invoice->external_id ) ); ?>"><?php esc_html_e( 'Show document on account', 'firmlet' ); ?></a>
					</li>
					<li>
						<a target="_blank" class="button" href="<?php echo esc_html( $invoice->view_url ); ?>.pdf"><?php esc_html_e( 'Download document as pdf', 'firmlet' ); ?></a>
					</li>
					<li>
						<a href="" class="button" id="delete"><?php printf( esc_html__( 'Remove invoice from %s', 'firmlet' ), esc_html( $module->display_name ) ); ?></a>
					</li>
					<li>
						<a target="_blank" class="button" href="<?php echo esc_html( $api->get_invoices_url() ); ?>/new?from=<?php echo esc_html( $invoice->external_id ); ?>&kind=correction"><?php esc_html_e( 'Issue correction invoice', 'firmlet' ); ?></a>
					</li>
				</ul>
			<?php else : ?>
				<?php if ( ! empty( $invoice ) && ( empty( $invoice->external_id ) || ! empty( $invoice->error ) ) ) : ?>
					<div class="notice notice-error">
						<p>
							<?php
								esc_html_e( 'Error while generating invoice to order: ', 'firmlet' );
								echo esc_html( $invoice->error );
							?>
						</p>
					</div>
				<?php endif; ?>
				<?php if ( $module->correct_firmlet( 'VF' ) ) : ?>
					<a href="" class="issue button" id="issue"><?php esc_html_e( 'Issue invoice', 'firmlet' ); ?></a><br/>
				<?php else : ?>
					<ul>
						<li>
							<a href="" class="issue" id="auto"><?php esc_html_e( 'Issue VAT/receipt (automatic choice)', 'firmlet' ); ?></a>
						</li>
						<li>
							<a href="" class="issue button" id="vat"><?php esc_html_e( 'Issue VAT', 'firmlet' ); ?></a>
						</li>
						<li>
							<a href="" class="issue button" id="receipt"><?php esc_html_e( 'Issue receipt', 'firmlet' ); ?></a>
						</li>
						<li>
							<a href="" class="issue button" id="proforma"><?php esc_html_e( 'Issue proforma', 'firmlet' ); ?></a>
						</li>
						<li>
							<a href="" class="issue button" id="estimate"><?php esc_html_e( 'Issue estimate', 'firmlet' ); ?></a>
						</li>
						<li>
							<a href="" class="issue button" id="bill"><?php esc_html_e( 'Issue bill', 'firmlet' ); ?></a>
						</li>
					</ul>
				<?php endif; ?>
			<?php endif; ?>
		<?php else : ?>
			<strong style="color: #a00;"><?php echo esc_html( get_option( 'woocommerce_firmlet_settings' )['errors'] ); ?></strong>
		<?php endif; ?>
	</div>

	<script>
		jQuery(document).ready(function () {
			jQuery(".issue").each(function (index) {
				jQuery(this).one("click", function(e) {
					e.preventDefault();
					InvoiceHandlerSendAction(jQuery(this).attr("id"));
				});
			});

			jQuery("#delete").each(function (index) {
				jQuery(this).on("click", function(e) {
					e.preventDefault();
					if (confirm("<?php esc_html_e( 'Are you sure you want to delete this document?', 'firmlet' ); ?>")) {
						InvoiceHandlerSendAction(jQuery(this).attr("id"));
					}
				});
			});

			function InvoiceHandlerSendAction(id) {
				var data = {
					action: "invoice_handler",
					command: id === "delete" ? "delete" : "issue",
					order_id: "<?php echo $post_id; ?>",
					issue_kind: id
				};
				let post = jQuery.post(ajaxurl, data);
				post.always(function () {
					location.reload();
				});
			}
		});
	</script>
<?php
	do_action( 'firmlet_meta_box_end', $post_id );
