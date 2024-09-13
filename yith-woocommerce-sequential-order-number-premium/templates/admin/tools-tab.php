<?php
/**
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 *
 * @package YITH WooCommerce Sequential Order Number Premium
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$datepicker_placeholder = __( 'Select a date', 'yith-woocommerce-sequential-order_number' );
?>
<div id="yith_wc_sequential_order_number_panel_tools" class="yith-plugin-fw  yit-admin-panel-container">
	<div class="yit-admin-panel-content-wrap">
		<form id="plugin-fw-wc" method="post">

			<table class="form-table">
				<h2><?php esc_html_e( 'Import Settings', 'yith-woocommerce-sequential-order-number' ); ?></h2>
				<tbody>
				<tr valign="top" class="yith-plugin-fw-panel-wc-row buttons">
					<th scope="row" class="titledesc">
						<label for="ywson_import_from_woocommerce_son"><?php esc_html_e( 'Import from WooCommerce Sequential Order Number', 'yith-woocommerce-sequential-order-number' ); ?></label>
					</th>
					<td class="forminp frominp-buttons">
						<input id="ywson_import_order_numbers" type="button" class="button button-primary"
							value="<?php esc_attr_e( 'Import', 'yith-woocommerce-sequential-order-number' ); ?>">
						<span class="description"><?php esc_html_e( 'This tool allows importing all existing sequential order numbers created by WooCommerce Sequential Order Number. Otherwise, order numbers are assigned by the plugin.', 'yith-woocommerce-sequential-order-number' ); ?></span>
						<input type="hidden" id="ywson_nonce" value="<?php echo esc_attr( wp_create_nonce( 'ywson-import-numbers' ) ); ?>">
					</td>
				</tr>
				</tbody>
			</table>
		</form>
	</div>
</div>
