<?php
/**
 * HTML Template Quote table
 *
 * @package YITH Woocommerce Request A Quote
 * @since   1.0.0
 * @version 2.2.7
 * @author  YITH
 *
 * @var WC_Order $order
 */

$border   = true;
$order_id = $order->get_id();

if ( function_exists( 'icl_get_languages' ) ) {
    global $sitepress;
    $lang = $order->get_meta( 'wpml_language' );
    YITH_Request_Quote_Premium()->change_pdf_language( $lang );
}
add_filter( 'woocommerce_is_attribute_in_product_name', '__return_false' );

$after_list = $order->get_meta( '_ywcm_request_response' );
if ( '' !== $after_list ): ?>
<div class="after-list">
    <p><?php echo wp_kses_post( apply_filters( 'ywraq_quote_before_list', nl2br( $after_list ), $order_id ) ); ?></p>
</div>
<?php endif; ?>

<?php do_action( 'yith_ywraq_email_before_raq_table', $order ); ?>

<?php
$columns = get_option( 'ywraq_pdf_columns', 'all' );
if ( ! is_array( $columns ) ) {
    $columns = array( $columns );
}
?>
<div class="table-wrapper">
    <div class="mark"></div>
    <table class="quote-table" cellspacing="0" cellpadding="6" style="width:100%;" border="0">
        <tbody>
        <?php
        $items = $order->get_items();
        $currency = $order->get_currency();
		$count = 1;
		$drop_order = w_c_dropshipping()->orders->get_order_info($order);
		$shipping_methods = $drop_order['order']->get_shipping_methods();
		
		// Récupérer la date à laquelle le devis a pris le statut "devis en attente"
		$date_devis_en_attente = $order->get_meta('ywraq_pending_status_date');
		if (!empty($date_devis_en_attente)) {
			$date_livraison = new DateTime($date_devis_en_attente);
			$date_livraison_max = new DateTime($date_devis_en_attente);
		} else {
			// Sinon, utiliser la date de création du devis
			$date_livraison = new DateTime($order->get_date_created());
			$date_livraison_max = new DateTime($order->get_date_created());
		}

        if( ! empty( $items ) ):
			foreach( $shipping_methods as $shipping_method ):
				$max_delai = 0;
				$method_name = $shipping_method->get_name();
				$all_meta = $shipping_method->get_meta_data();
				$shipping_item = '';
				foreach ($all_meta as $meta) {
					if ($meta->key == 'Articles' && !empty($meta->value)) {
						$shipping_item = $meta->value;
						break;
					}
				}
				?>
				<tr class="spaced-row"> // Ligne Description Shipping methode
					<td colspan="6" style="border:solid 1px #bbb">
						<?php 
						
                        foreach ($items as $item) {
                            if (strpos($shipping_item, $item->get_name()) !== false) {
								
								$_product_id = isset($item['variation_id']) && $item['variation_id'] ? wp_get_post_parent_id($item['variation_id']) : $item['product_id'];
								
								$supplier_meta = get_post_meta($_product_id, 'supplier', true);
								$brand_meta = wp_get_post_terms($_product_id, 'brand')[0]->name;
								$description_supplier='';
								$brand ='';
								
								if ($brand_meta=='DBA'){
									$description_supplier = '<span style="font-size:0.9em;">Depuis 1994, en tant qu\'entreprise DBA, nous avons acquis une expertise inégalée dans la sélection de mobilier et d\'atelier. Nous avons établi des partenariats de longue date avec des fournisseurs soigneusement choisis, ce qui nous permet de vous offrir une vaste gamme de produits. Ainsi, vous pouvez bénéficier de l\'offre optimale, sans compromis entre le design, l\'ergonomie, la fonctionnalité et le prix.</span>';
									$brand ='DBA';
								}else{
									$brand = $supplier_meta;
									if($supplier_meta=='Vinco'){
										$description_supplier = '<span style="font-size:0.9em;">Pensé, dessiné et fabriqué par un Groupe familial fondé en 1945 en Normandie, leader dans les métiers du rangement métallique. Vinco s\'est engagé très tôt dans la démarche de certification environnementale avec la certification NF Environnemental ameublement géré par le FCBA. Aujourd\'hui, Vinco est titulaire de 15 certifications pour l\'ensemble des gammes (NF Office Excellence Certifié / Certification PEFC, GS…).</span>';
									}elseif($supplier_meta=='Hartmann'){
										$description_supplier = '<span style="font-size:0.9em;">Depuis plus d\'un siècle, votre sécurité et celle de vos biens, la maison Hartmann Tresore excelle dans l\'art de fabriquer des coffres-forts, devenus la référence européenne en raison de leur grande fiabilité. Le choix du meilleur de la qualité allemande avec des produits sont certifiés ISO 9001 / ISO 14001 et ils sont testés conformément aux normes Européennes et reconnus par les compagnies d\'assurances.</span>';
									}elseif($supplier_meta=='GGI FRANCE'){
										$description_supplier = '<span style="font-size:0.9em;">Depuis deux décennies, GGI capitalise son expérience dans la conception et le développement de fauteuils de bureau. Fabricant français du Tarn-et-Garonne. GGI utilise des Colles 100 % base aqueuse permettent de proposer des produits NF Environnement, ISO, Garantie 3 ans.</span>';
									}elseif($supplier_meta=='Caray'){
										$description_supplier = '<span style="font-size:0.9em;">Entreprise familiale créée en 1948, CARAY est aujourd\'hui un des leaders Français du Mobilier de Bureau. Depuis près de 70 ans, il sélectionne des produits dans le monde entier pour vous offrir un large choix de mobiliers qui répondent tous à trois critères : ERGONOMIE, DESIGN, FONCTIONNALITÉ. L\'objectif est simple : vendre du mobilier ergonomique haut de gamme à prix doux. </span>';
									}elseif($supplier_meta=='Burocean'){
										$description_supplier = '<span style="font-size:0.9em;">Fabricant français engagé en Nouvelle Aquitaine, membre fondateur d’Actinéo, l\'observatoire de la qualité de vie au bureau. Depuis 2005, accompagné par le FCBA, l\'ensemble du process industriel Burocean intègre les exigences d\'éco-conception. Cette démarche globale permet d\'obtenir la marque NF environnement. Les panneaux bois sont certifiés PEFC. Une garantie 5 ans reflète la qualité de fabrication Burocean.</span>';
									}elseif($supplier_meta=='Akaze'){
										$description_supplier = '<span style="font-size:0.9em;">Avec AKAZE, vous profitez de l\'expérience d\'un savoir-faire artisanal Français depuis 40 ans, situé en Anjou, près de Cholet (49), vous optez pour un équipement fonctionnel, solide et durable de votre environnement de travail.</span>';
									}
								}								
								echo '<b style="text-transform:uppercase;">Livraison ' . $count . ', Expédié par ' . strtoupper($supplier_meta) . ' : </b> ' . $method_name . '</span><br>' ;
                                echo '<p> <span style="color:#ea911c">&Agrave; Propos de ' . $brand . ' : </span>' . $description_supplier . '</p>';
                                break;
                            }
                        }
						?>
					</td>
				</tr>
				<tr class="spaced-row">
					<td colspan="2" style="text-align:center;font-weight:bold;">Produit</td>
					<td style="text-align:center;font-weight:bold;width:25px;">TVA</td>
					<td style="text-align:center;font-weight:bold;width:75px;">Prix</td>
					<td style="text-align:center;font-weight:bold;width:50px;">Qté.</td>
					<td style="text-align:right;font-weight:bold;width:75px">Total</td>
				</tr>
					<?php				
					foreach( $items as $item ):
						
						if ( strpos( $shipping_item, $item->get_name() ) !== false ):
							if ( isset( $item['variation_id'] ) && $item['variation_id'] ) {
								$_product_id = $item['variation_id'];
								$is_variation = true ;
							} else {
								$_product_id = $item['product_id'];
								$is_variation = false ;
							}
							
							$_product = wc_get_product( $_product_id );

							if ( ! $_product ) {
								continue;
							}

							$title = $_product->get_title();
							$attributes = $is_variation ? $_product->get_variation_attributes() : [];
							$image = wp_get_attachment_image_src( get_post_thumbnail_id( $_product_id ), 'thumbnail' );
							$image_dir = str_replace(site_url() . '/', get_home_path(), $image[0]);
							$image_tag = '<img src="' . $image_dir . '" width="50px" height="50px" style="float:left;">';
							$origin_meta = get_post_meta($item['product_id'], 'country_origin', true );
							
							$garantie = $is_variation ? get_post_meta( $_product->get_parent_id(), 'label_garantie', true ) : get_post_meta( $_product_id, 'label_garantie', true );
							switch ( $garantie ) {
								case '1':
									$garantie = ' - Garantie fabricant 1 an';
									break;
								case '2':
									$garantie = ' - Garantie fabricant 2 ans';
									break;
								case '3':
									$garantie = ' - Garantie fabricant 3 ans';
									break;
								case '5':
									$garantie = ' - Garantie fabricant 5 ans';
									break;
								case '10':
									$garantie = ' - Garantie fabricant 10 ans';
									break;
								default:
									$garantie = ' - Garantie fabricant 1 an';
									break;
							}
							
							if (empty($origin_meta) || $origin_meta == 'fr' ){
								$french_supplier = '<img height="10px" style="margin-left:10px;" src="https://www.armoireplus.fr/wp-content/uploads/2022/10/drapeau_francais-12.png">';
							}else{
								$french_supplier='';
							}

							if ( $_product->get_sku() !== '' && ywraq_show_element_on_list( 'sku' ) ) {
								$sku_label = apply_filters( 'ywraq_sku_label', __( ' SKU:', 'yith-woocommerce-request-a-quote' ) );
								$sku       = sprintf( '<br><small class="wc-item-sku">%s</small>', $_product->get_sku() );
								$title .=  apply_filters( 'ywraq_sku_label_html', $sku, $_product ) . $garantie . $french_supplier;; //phpcs:ignore
							}

							$subtotal   = wc_price( $item['line_total'], array( 'currency' => $currency ) );
							$unit_price = wc_price( $item['line_total'] / $item['qty'], array( 'currency' => $currency ) );

							if ( get_option( 'ywraq_show_old_price' ) === 'yes' ) {
								$subtotal   = ( $item['line_subtotal'] !== $item['line_total'] ) ? '<small><del>' . wc_price( $item['line_subtotal'], array( 'currency' => $currency ) ) . '</del></small><br> ' . wc_price( $item['line_total'], array( 'currency' => $currency ) ) : wc_price( $item['line_subtotal'], array( 'currency' => $currency ) );
								$unit_price = ( $item['line_subtotal'] !== $item['line_total'] ) ? '<small><del>' . wc_price( $item['line_subtotal'] / $item['qty'], array( 'currency' => $currency ) ) . '</del></small><br> ' . wc_price( $item['line_total'] / $item['qty'] ) : wc_price( $item['line_subtotal'] / $item['qty'], array( 'currency' => $currency ) );
							}
							
							

							?>
							<tr class="spaced-row">
								<td  colspan="2" >
									<h4><?php echo wp_kses_post( $title ); ?></h4>
								</td>
								<td style="text-align:center;">20%</td>
								<td style="text-align:center;"><?php echo wp_kses_post( $unit_price ); ?></td>
								<td style="text-align:center;"><?php echo esc_html( $item['qty'] ); ?></td>
								<td class="last-col" style="text-align:right;"><?php echo wp_kses_post( $subtotal ); ?></td>
							</tr>
							
							<tr>

								<td style="width:70px;">
									<?php echo $image_tag; ?>
								</td>
								<td colspan="4">
									<small>
									<div id="addinfo_attribu">
											<?php  
											$monobloc = get_post_meta($item['product_id'], 'monobloc', true);
										if ( !empty($monobloc) ) {
											switch ($monobloc) {
												case 'oui':
													echo ' <span class="cart-fabrication"><strong>Livré Monobloc</strong> <i class="icon-info-sign tooltip1" style="color:#d24f08;"><i class="tooltiptext1">Meuble Assemblé et Soudé en Usine</i></i></span>';
													break;
												case 'mixte':
													echo ' <span class="cart-fabrication"><strong>Livré Monobloc, à équiper</strong> <i class="icon-info-sign tooltip1" style="color:#d24f08;"><i class="tooltiptext1">Meuble Assemblé et Soudé en Usine, Equipement livré séparement à monter par vos soins</i></i></span>';;
													break;
												case 'caisson':
													echo ' <span class="cart-fabrication"><strong>Livré Partiellement Monobloc</strong> <i class="icon-info-sign tooltip1" style="color:#d24f08;"><i class="tooltiptext1">Caisson Monobloc, Bureau à monter</i></i></span>';;
													break;
												case 'non':
													echo ' <span class="cart-fabrication"><strong>Livré démonté</strong></span>';
													break;
											}
										} 
										 ?> 
										</div>
										<?php
										$display = wc_display_item_meta(
											$item,
											array(
												'before'       => '<div class="wc-item-meta"><span>',
												'after'        => '</span></div>',
												'separator'    => '<br>',
												'echo'         => false,
												'autop'        => false,
												'label_before' => '<strong class="wc-item-meta-label">',
												'label_after'  => ' :</strong> ',
											)
										);
										// NXOVER [[
										if (function_exists('nxover_filter_qpdf_item'))
											$display = nxover_filter_qpdf_item($item, $display);
										// ]] NXOVER
										$display = str_replace( '<p', '<span', $display );
										$display = str_replace( '</p>', '</span>', $display );
										$display = str_replace( 'supplier', 'Expéditeur', $display );
										$excerpt = get_the_excerpt($item['product_id']);
										$product_id = $item->get_product_id();
										
										$posLesPlus = strpos($excerpt, '<div class="bloc_lesplus_serieplus">');
										$posAuDela = strpos($excerpt, '<div class="bloc_audela_standard">');
										$posPlusCar = strpos($excerpt, '<a href="#tab_description">Plus de Caractéristiques [...]</a>');
										if ($posLesPlus !== false || $posAuDela !== false) {
											if ($posLesPlus !== false && $posAuDela !== false) {
												$excerpt = substr($excerpt, 0, min($posLesPlus, $posAuDela));
											} elseif ($posLesPlus !== false) {
												$excerpt = substr($excerpt, 0, $posLesPlus);
											} else {
												$excerpt = substr($excerpt, 0, $posAuDela);
											}
										}

										if ($posPlusCar !== false) {
											$excerpt = str_replace('<a href="#tab_description">Plus de Caractéristiques [...]</a>', '', $excerpt);
										}
										$supplier_meta = get_post_meta($item['product_id'], 'supplier', true);
										// if ($supplier_meta =="Vinco"){
											$display .= '<b>Caractéristiques : </b>' . $excerpt;
										// }
										echo wp_kses_post( $display );
										?>
									</small>
								</td>
							</tr>
						<?php
					endif;
				endforeach;
				$count += 1;
			endforeach; 
        endif; ?>
        </tbody>
		</table>
        <table class="quote-total"  cellspacing="0"  style="page-break-inside: avoid; break-inside: avoid;">
        <?php
        if ( 'no' === get_option( 'ywraq_pdf_hide_total_row', 'no' ) ) {
			$total_ht = 0;
			$tva_value = 0;
			foreach ( $order->get_order_item_totals() as $key => $total ) {
				if ( $key === 'cart_subtotal' || $key === 'shipping' || strpos($key, 'fee') !== false) {
					$total_ht += floatval(preg_replace('/[^0-9\.]/', '', $total['value']));
				}elseif ($key === 'discount'){
					$total_ht -= floatval(preg_replace('/[^0-9\.]/', '', $total['value']));
				}elseif (($key === 'tva-1') || ($key === 'fr-tva-1')) {
					$tva_value = floatval(preg_replace('/[^0-9\.]/', '', $total['value']));
				}
			}
            foreach ( $order->get_order_item_totals() as $key => $total ) {
                if ( $key != 'payment_method' ): ?>
                    <?php
				  $type = '(HT)';
					if ($key === 'order_total') {
						$type = $tva_value == 0 ? '(HT)' : '(TTC)';
					} elseif (($key === 'tva-1') || ($key === 'fr-tva-1')) {
						$type = '';  // Pas de suffixe pour la TVA  
					}
                    $label = preg_replace('/^é/', 'É', $total['label']);
                    $label = trim($label, " \t\n\r\0\x0B\xC2\xA0:");
                    ?>
				<?php if(($key === 'tva-1') || ($key === 'fr-tva-1')): ?>		
						<tr>
							<th scope="col" colspan="3" style="text-align:right;color:#7f1321;font-weight:bold;"><?php echo 'Total (HT)'; ?></th>						 
                            <td scope="col" colspan="2" class="last-col" style="text-align:right;color:#7f1321;"><?php echo wp_kses_post( $total_ht ) . ' €';?></td>
						</tr>			
						<tr>
							<th scope="col" colspan="3" style="text-align:right;"><?php echo trim($label) . ' ' . $type; ?></th>
                            <td scope="col" colspan="2" class="last-col" style="text-align:right;<?= ($key == 'order_total') ? 'color:#7f1321;font-weight:bold;' : '' ?>"><?php echo wp_kses_post( $total['value'] );?></td>
						</tr>
				<?php else: ?>	
				<tr>
					<th scope="col" colspan="3" style="text-align:right;"><?php echo trim($label) . ' ' . $type; ?></th>
					<td scope="col" colspan="2" class="last-col" style="text-align:right;">
						<?php
						$int_var = preg_replace('/[^0-9]/', '', $total['value']);
						$value = preg_replace('#&nbsp;<small class="shipped_via">.*</small>#', '', $total['value']);
						echo (!$int_var) ? 'Offert' : $value;
						?>
					</td>
				</tr>
				<?php endif; ?>
                <?php endif; ?>
            <?php
            }
        }
        ?>
        </table>
</div>
<?php if ( get_option( 'ywraq_pdf_link' ) === 'yes' ) : ?>
    <div style="margin-top:20px;">
        <table class="ywraq-buttons">
            <tr>
                <?php if ( get_option( 'ywraq_show_accept_link' ) !== 'no' ): ?>
                    <td>Validez votre devis en ligne :
                        <a href="https://www.armoireplus.fr/mon-compte/quotes/" class="pdf-button accept">&nbsp;Je valide en ligne&nbsp;</a>
                    </td>
                <?php endif;
                echo ( get_option( 'ywraq_show_accept_link' ) !== 'no' && get_option( 'ywraq_show_reject_link' ) !== 'no' ) ? '<td><span style="color: #666666">|</span></td>' : '';
                if ( get_option( 'ywraq_show_reject_link' ) !== 'no' ): ?>
                    <td><a href="<?php echo esc_url( ywraq_get_rejected_quote_page( $order ) ); ?>" class="pdf-button">&nbsp;<?php ywraq_get_label( 'reject', true ); ?>&nbsp;</a></td>
                <?php endif ?>
            </tr>
        </table>
    </div>
	<div style="margin-top: 20px; text-align: center;font-size:12px;font-weight: normal;">
		<p  style="margin-top:8px;">Moyens de paiement disponibles en ligne :</p>
		<p style="margin-top:8px;"><strong>Carte Bleue | 3x sans frais | Chèque | Virement | Mandat Administratif | Conditions de règlement personnalisées</strong></p>
		<p style="margin-top:8px;">Notre service client est disponible pour trouver la modalité de règlement la plus adaptée à vos besoins.</p>
	</div>
    <div style="margin-top:20px;">
        <p style="font-size:12px;font-weight: normal;">Voici notre relevé d'identité bancaire / IBAN : <br>
            IBAN : FR76 3000 4010 1600 0101 2462 666 / BIC : BNPAFRPPXXX
        </p>
    </div>
<?php endif ?>
  
<?php do_action( 'yith_ywraq_email_after_raq_table', $order ); ?>
