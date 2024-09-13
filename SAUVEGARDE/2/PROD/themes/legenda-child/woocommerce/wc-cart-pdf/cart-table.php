<?php
/**
 * WC Cart PDF template
 * 
 * @package wc-cart-pdf
 */

/**
 * Before template hook
 *
 * @since 1.0.4
 */
do_action( 'wc_cart_pdf_before_template' );
?>
<?php
$mpdf->setAutoTopMargin = 'stretch';
$date = new DateTime();
$date_fin = clone $date;
$date_fin->add(new DateInterval('P1M'));
$mpdf->SetHTMLHeader('
<table style="width:100%;padding:0;margin:0;height:350px;">
        <tr style="width:100%;">
            <td style="width:40%;">
                <a href="https://www.armoireplus.fr"><img width="50%" src="https://www.armoireplus.fr/wp-content/uploads/2020/02/Logo-ArmoirePlus.png" /></a>
            </td>
            <td style="width:60%;padding-top:10px;text-align:right;">
                <p style="font-family:Arial sans-serif;color:black;">
                <h1 style="font-size:12pt;">Devis rapide</h1>
                <h4>Réf. : '. $date->format('YmdHi') .'</h4>
                <span>Date de la proposition : '. $date->format('d/m/Y') .'</span><br>
                <span>Date de la fin de validité : '. $date_fin->format('d/m/Y') .'</span><br>
                </p>
            </td>
        </tr>
</table>');
$mpdf->SetHTMLFooter('
<div id="template_footer">
    <hr>
    Société à responsabilité limitée (SARL) - Capital de 91 000 € - SIRET: 393 708 052 00047<br>
    NAF-APE: 4669 B - RCS/RM: Toulouse 393 708 052 - Numéro TVA: FR 79 393 708 052
</div>');

?>
<div id="template_header_image">
    <table style="width:100%;padding:0;margin:0;">
        <tr style="width:100%;">
            <td style="width:40%;height:180px;">
                <span style="color:black;">Émetteur :</span><br>
                <table style="width:97%;padding:0;margin:0;background-color:#ddd;">
                    <tr>
                        <td style="height:145px;padding:10px;color:black;">
                            <h2 style="font-size:10pt;">Armoire PLUS / D.B.A</h2>
                            <p style="font-size:8pt;">52 Boulevard Gabriel Koenigs<br>
                            31300 Toulouse<br><br>
                            Tél.: 05 31 61 98 32 - Fax: 05 17 47 54 02<br>
                            Email: contact@armoireplus.fr<br>
                            Web: https://www.armoireplus.fr</p>
                        </td>
                    </tr>
                </table>
            </td>
            <td style="width:60%;height:180px;">
                <span style="color:black;">Client :</span><br>
                <table style="width:100%;margin:0;padding:5px 5px 0 5px;border:1px solid #bbb;">
                    <tr style="vertical-align:top;"><td style="height:25px;"><b>Nom : <span style="color:#e2e2e2">........................................</span> Tél. : <span style="color:#e2e2e2">..................................</span></b></td></tr>
                    <tr style="vertical-align:top;"><td style="height:30px;line-height:1.6;"><b>Adresse facturation :
                                <span style="color:#e2e2e2">
                                    ............................................................................................<br>
                                    ............................................................................................
                                </span>
                            </b></td></tr>
                    <tr style="vertical-align:top;"><td style="height:40px;line-height:1.6;"><b>Adresse livraison :
                                <span style="color:#e2e2e2">
                                    ............................................................................................<br>
                                    ............................................................................................
                                </span>
                            </b></td></tr>
                </table>
            </td>
        </tr>
        <tr style="width:100%;">
            <td style="width:100%;height:100px;" colspan="2">
                <span style="color:black;">Livraison :</span><br>
                <table style="width:100%;padding:0;margin-bottom:15px;border:1px solid #bbb;">
                    <tr>
                        <td style="padding:10px;">
                            <?php
                            $max_delai = 0;
                            $html = $method = '';
                            $date_livraison = new DateTime();
                            $date_livraison_max = new DateTime();
                            foreach (WC()->shipping->get_packages() as $k => $v) {
                                foreach($v['rates'] as $rate_id => $rate ){
                                    if (WC()->cart->get_shipping_total() == $rate->get_cost()) {
                                        $method = $rate->get_label();
                                        break;
                                    }
                                }
                            }
                            $cart = WC()->cart->get_cart();
                            foreach ( $cart as $cart_item ) {
                                $is_variation = $cart_item['variation_id'] == 0 ? false : true;
                                $product_id = !$is_variation ? $cart_item['product_id'] : $cart_item['variation_id'];
                                $delai = intval(preg_replace('/[^0-9.]/', '', wp_get_post_terms( $cart_item['product_id'], 'pa_delai-dexpedition', array( 'fields' => 'names' ) )[0] ) );
                                $delai = Livraison::getInstance()->getLivraison($product_id, $is_variation, $cart_item['variation'], $delai, $method);
                                $max_delai = $delai > $max_delai ? $delai : $max_delai;
                            }
                            $date_livraison->add(new DateInterval('P'.($max_delai - 1).'W'));
                            $date_livraison_max->add(new DateInterval('P'.($max_delai - 1).'W'));
                            $date_livraison_max->add(new DateInterval('P10D'));
                            switch ( $method ) {
                                case 'Formule CLÉ EN MAIN':
                                    $html .= '<b style="color:#7f1321;text-transform: uppercase;"><span style="color:#333333;">Délai de livraison estimé de '. $max_delai .' Semaine(s) :</span> Entre le ' . $date_livraison->format('d/m/Y') . ' et le ' . $date_livraison_max->format('d/m/Y') .'</b><br>';
                                    $html .= '<b style="color:gray;">Le prestataire vous contactera pour convenir d\'un rendez-vous n\'hésitez pas à lui imposer vos contraintes.</b><br>';
                                    $html .= '<i class="icon-hand-right" style="color:#2d2477;font-size:12px;padding-right:5px;margin-left:7px;"></i><b>Livraison par véhicule type porteur avec installation complète dans la pièce de votre choix.</b><br>Déballage, mise en place, montage des équipements optionnels / intérieurs et évacuation des emballages pour recyclage. Le mobilier est alors directement prêt à l\'emploi.<br>Livraison en Semaine entre 8h et 16h30.<br>';
                                    $html .= '<span style="color:#560f56;"><i class="icon-exclamation" style="color:#560f56;font-size:12px;padding-right:5px;"></i><i><u>ATTENTION</u> : Ces frais s\'entendent pour un accès "normal" dans les locaux (monte-charge, ascenseur) <u>ou bien escalier large jusqu\'au 1er étage.</u></i></span>';
                                    break;
                                case 'Formule ECO [OFFERTE]':
                                    $html .= '<b style="color:#7f1321;text-transform: uppercase;"><span style="color:#333333;">Délai de livraison estimé de '. $max_delai .' Semaine(s) :</span> Entre le ' . $date_livraison->format('d/m/Y') . ' et le ' . $date_livraison_max->format('d/m/Y') .'</b><br>';
                                    $html .= '<b style="color:gray;">Le transporteur vous contactera pour convenir d\'un rendez-vous n\'hésitez pas à lui imposer vos contraintes.</b><br>';
                                    $html .= '<i class=" icon-hand-right " style="color:#2d2477;font-size:12px;padding-right:5px;margin-left:7px;"></i><b>Livraison par semi-remorque sans HAYON.</b><br>Déchargement par vos soins.<br>Livraison en Semaine entre 8h et 16h30.';
                                    break;
                                case 'Formule ECO Plus':
                                    $html .= '<b style="color:#7f1321;text-transform: uppercase;"><span style="color:#333333;">Délai de livraison estimé de '. $max_delai .' Semaine(s) :</span> Entre le ' . $date_livraison->format('d/m/Y') . ' et le ' . $date_livraison_max->format('d/m/Y') .'</b><br>';
                                    $html .= '<b style="color:gray;">Le transporteur vous contactera pour convenir d\'un rendez-vous n\'hésitez pas à lui imposer vos contraintes. </b><br>';
                                    $html .= '<i class=" icon-hand-right" style="color:#2d2477;font-size:12px;padding-right:5px;margin-left:7px;"></i><b>Livraison par semi-remorque équipé d\'un HAYON.</b><br>Commande déposée au pied du camion.<br>Livraison en Semaine entre 8h et 16h30.<br><i><span style="color:#560f56;"><u>Attention sur devis</u> :</span> Livraison par véhicule type porteur, avec capacité de chargement réduite</i>';
                                    break;
                                case 'Messagerie':
                                    $html .= 'Frais de conditionnement, préparation, traitement et livraison.<br />';
                                    $html .= 'Livraison sous 10 jours.';
                                    break;
                                default:
                                    $html .= 'Nous contacter pour plus d\'information sur les délais de livraison.';
                            }
                            echo '<b style="text-transform:uppercase;color:#7f1321;">' . $method . '<br>Livraison sur le ' . WC()->customer->get_shipping_postcode() . '</b><br>' . $html;
                            ?>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</div>
<table class="shop_table shop_table_responsive cart woocommerce-cart-form__contents" cellspacing="0">
    <thead>
        <tr>
            <th class="product-name"><?php esc_html_e( 'Product', 'woocommerce' ); ?></th>
            <th class="product-tva"><?php esc_html_e( 'Tax', 'woocommerce' ); ?></th>
            <th class="product-price"><?php esc_html_e( 'Price', 'woocommerce' ); ?></th>
            <th class="product-quantity"><?php esc_html_e( 'Quantity', 'woocommerce' ); ?></th>
            <th class="product-subtotal"><?php esc_html_e( 'Total', 'woocommerce' ); ?> HT</th>
        </tr>
    </thead>
    <tbody>
        <?php
        foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
            $_product   = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
            $product_id = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );

            if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
                $product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
                ?>
                <tr class="woocommerce-cart-form__cart-item <?php echo esc_attr( apply_filters( 'woocommerce_cart_item_class', 'cart_item', $cart_item, $cart_item_key ) ); ?>">

                    <td class="product-name" data-title="<?php esc_attr_e( 'Product', 'woocommerce' ); ?>">
                    <?php

                    echo 'Référence : <b>' . $_product->get_sku() . '</b><br>';

                    if ( ! $product_permalink ) {
                        echo wp_kses_post( preg_replace( '/<i class="(.*)">(.*)<\/i>/', '', apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key ) . '&nbsp;' ) );
                    } else {
                        echo wp_kses_post( preg_replace( '/<i class="(.*)">(.*)<\/i>/', '', apply_filters( 'woocommerce_cart_item_name', sprintf( '<a href="%s">%s</a>', esc_url( $product_permalink ), $_product->get_name() ), $cart_item, $cart_item_key ) ) );
                    }

                    do_action( 'woocommerce_after_cart_item_name', $cart_item, $cart_item_key );

                    // Meta data.
                    echo '<span style="font-size:7pt;">' . wc_get_formatted_cart_item_data( $cart_item ) . '</span>';

                    // Backorder notification.
                    if ( $_product->backorders_require_notification() && $_product->is_on_backorder( $cart_item['quantity'] ) ) {
                        echo wp_kses_post( apply_filters( 'woocommerce_cart_item_backorder_notification', '<p class="backorder_notification">' . esc_html__( 'Available on backorder', 'woocommerce' ) . '</p>', $product_id ) );
                    }
                    ?>
                    </td>

                    <td class="product-tva" data-title="<?php esc_attr_e( 'Tax', 'woocommerce' ); ?>">20%</td>

                    <td class="product-price" data-title="<?php esc_attr_e( 'Price', 'woocommerce' ); ?>">
                        <?php
							echo apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key ); // PHPCS: XSS ok.
						?>
                    </td>

                    <td class="product-quantity" data-title="<?php esc_attr_e( 'Quantity', 'woocommerce' ); ?>">
                        <?php print esc_html( $cart_item['quantity'] ); ?>
                    </td>

                    <td class="product-subtotal" data-title="<?php esc_attr_e( 'Total', 'woocommerce' ); ?>">
                        <?php
							echo apply_filters( 'woocommerce_cart_item_subtotal', WC()->cart->get_product_subtotal( $_product, $cart_item['quantity'] ), $cart_item, $cart_item_key ); // PHPCS: XSS ok.
						?>
                    </td>
                </tr>
                <?php
            }
        }
        ?>

				<tr class="cart-subtotal">
					<th class="row-subtotal" colspan="4" style="text-transform: uppercase;">Total HT</th>
					<td class="row-subtotal" data-title="<?php esc_attr_e( 'Subtotal', 'woocommerce' ); ?>"><?php wc_cart_totals_subtotal_html(); ?></td>
				</tr>
				
				<?php if ( 0 < WC()->cart->get_shipping_total() ) : ?>
					<tr class="shipping">
						<th class="row-subtotal" colspan="4">Livraison <?php echo $method; ?></th>
						<td class="row-subtotal" data-title="<?php esc_attr_e( 'Shipping', 'woocommerce' ); ?>"><?php echo WC()->cart->get_cart_shipping_total(); ?></td>
					</tr>
				<?php endif; ?>

				<?php foreach ( WC()->cart->get_fees() as $fee ) : ?>
					<tr class="fee">
						<th class="row-subtotal" colspan="4"><?php echo esc_html( $fee->name ); ?></th>
						<td class="row-subtotal" data-title="<?php echo esc_attr( $fee->name ); ?>"><?php wc_cart_totals_fee_html( $fee ); ?></td>
					</tr>
				<?php endforeach; ?>

				<?php if (WC()->cart->get_discount_total() > 0): ?>
					<tr class="order-save">
						<th class="row-subtotal" colspan="4" style="text-transform:capitalize;">Remise</th>
						<td class="row-subtotal" data-title="Montant Économisé" style="color:#7f1321;"><?php echo '- ' . number_format(WC()->cart->get_discount_total(), 2) . ' &euro;'; ?></td>
					</tr>
				<?php endif; ?>

				<?php if ( wc_tax_enabled() && ! WC()->cart->display_prices_including_tax() ) :
					$taxable_address = WC()->customer->get_taxable_address();
					$estimated_text  = WC()->customer->is_customer_outside_base() && ! WC()->customer->has_calculated_shipping()
							? sprintf( ' <small>' . __( '(estimated for %s)', 'woocommerce' ) . '</small>', WC()->countries->estimated_for_prefix( $taxable_address[0] ) . WC()->countries->countries[ $taxable_address[0] ] )
							: '';

					if ( 'itemized' === get_option( 'woocommerce_tax_total_display' ) ) : ?>
						<?php foreach ( WC()->cart->get_tax_totals() as $code => $tax ) : ?>
							<tr class="tax-rate tax-rate-<?php echo sanitize_title( $code ); ?>">
								<th class="row-subtotal" colspan="4"><?php echo esc_html( $tax->label ) . $estimated_text; ?></th>
								<td class="row-subtotal" data-title="<?php echo esc_attr( $tax->label ); ?>"><?php echo wp_kses_post( $tax->formatted_amount ); ?></td>
							</tr>
						<?php endforeach; ?>
					<?php else : ?>
						<tr class="tax-total">
							<th class="row-subtotal" colspan="4"><?php echo esc_html( WC()->countries->tax_or_vat() ) . $estimated_text; ?></th>
							<td class="row-subtotal" data-title="<?php echo esc_attr( WC()->countries->tax_or_vat() ); ?>"><?php wc_cart_totals_taxes_total_html(); ?></td>
						</tr>
					<?php endif; ?>
				<?php endif; ?>

				<?php //do_action( 'woocommerce_cart_totals_before_order_total' ); ?>

				<tr class="order-total">
					<th class="row-subtotal" colspan="4" style="text-transform: uppercase;color:#7f1321;"><?php _e( 'Total', 'woocommerce' ); ?> TTC</th>
					<td class="row-subtotal" data-title="<?php esc_attr_e( 'Total', 'woocommerce' ); ?>" style="color:#7f1321;"><?php wc_cart_totals_order_total_html(); ?></td>
				</tr>
				<tr style="margin-top:20px;">
					<td colspan="2" style="text-align:justify;">
						<b>Code postal de livraison :</b> <b style="color:#7f1321;"><?= WC()->customer->get_shipping_postcode() ?></b><br>
						<b>Paiement par virement bancaire</b><br><br>
						<small>Vous pouvez vous appuyer sur ce document pour éditer le bon de commande. Veuillez nous retourner par email à <a href="mailto:contact@armoireplus.fr">contact@armoireplus.fr</a> le document entier dûment rempli et signé. Nous vous enverrons nos coordonnées bancaires pour procéder au virement. Pour choisir un autre moyen de paiement ou suivre vos devis/commandes directement en ligne, poursuivez le processus de commande.</small>
					</td>
					<td colspan="3" style="text-align:right;">
						Cachet, Date, Signature et mention "Bon pour Accord"<br>
						<table class="shop_sign"><tr><td><br><br><br><br><br></td></tr></table>
					</td>
				</tr>
                <tr style="margin-top:20px;">
                    <td colspan="5">
                        La signature du présent document vaut acceptation sans réserve par le Client des C.G.V. dont il reconnaît avoir pris connaissance et disponibles à l'adresse <a href="https://www.armoireplus.fr/conditions-generales-de-ventes/">https://www.armoireplus.fr/conditions-generales-de-ventes/</a>
                    </td>
                </tr>
		<?php do_action( 'woocommerce_cart_totals_after_order_total' ); ?>
    </tbody>
</table>

<?php
/**
 * After template hook
 *
 * @since 1.0.4
 */
do_action( 'wc_cart_pdf_after_template' );