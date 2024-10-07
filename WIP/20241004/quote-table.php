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
		

        if( ! empty( $items ) ):
			foreach( $shipping_methods as $shipping_method ):
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
				$max_delai = 0;
				$method_name = $shipping_method->get_name();
				$method_id = $shipping_method->get_id();
				$all_meta = $shipping_method->get_meta_data();
				$shipping_item = '';
				foreach ($all_meta as $meta) {
					if ($meta->key == 'Articles' && !empty($meta->value)) {
						$shipping_item = $meta->value;
						break;
					}
				}
				?>
				<tr> // Ligne Description Shipping methode
					<td colspan="6" style="border:solid 1px #bbb">
						<?php 
                        foreach ($items as $item) {
                            if (strpos($shipping_item, $item->get_name()) !== false) {
								
								$is_variation = isset($item['variation_id']) && $item['variation_id'] ? true : false;
							
								$parent_product_id = $is_variation ? wp_get_post_parent_id($item['variation_id']) : $item['product_id'];
								$_product_id = $is_variation ? $item['variation_id'] : $item['product_id'];
								$_product = wc_get_product( $_product_id );			

// BRAND / DESCRIPTION SUPPLIER 
								$supplier_meta = get_post_meta($parent_product_id, 'supplier', true);
								$brand_meta = wp_get_post_terms($parent_product_id, 'brand')[0]->name;
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
                                // break;
                            }
                        }
								
// LIVRAISON DELAI
							$attributes = $is_variation ? $_product->get_variation_attributes() : [];							
							$delai = intval(preg_replace('/[^0-9.]/', '', wp_get_post_terms( $parent_product_id, 'pa_delai-dexpedition', array( 'fields' => 'names' ) )[0] ) );
							$delai = Livraison::getInstance()->getLivraison($_product_id, $is_variation, $attributes, $delai, $method_name, $order_id);
							$max_delai = $delai > $max_delai ? $delai : $max_delai;					
							// NXOVER [[
							if (function_exists('nxover_order_options_shipping_delay') && 
									($o_delay = nxover_order_options_shipping_delay($item, $method_name, $order->get_id())) !== false)
								$max_delai = max($max_delai, $o_delay);
							// ]] NXOVER
			
							Livraison::getInstance()->fourchette($date_livraison, $date_livraison_max,$max_delai,$supplier_meta,$order);
							
							// Définissez et formatez $formattedCustomDate ici
							$custom_shipping_date = $order->get_meta('custom_shipping_date_' . $method_id, true);
							$custom_shipping_text = $order->get_meta('custom_shipping_text_' . $method_id, true);
							if (!empty($custom_shipping_date)) {
								$dateObject = DateTime::createFromFormat('Y-m-d', $custom_shipping_date);
								if ($dateObject) {
									$formatter = new IntlDateFormatter('fr_FR', IntlDateFormatter::FULL, IntlDateFormatter::NONE, 'Europe/Paris', IntlDateFormatter::GREGORIAN, 'EEEE d MMMM yyyy');
									$formattedCustomDate = ucwords($formatter->format($dateObject));
								}
							}
							$date_livraison = ucwords(IntlDateFormatter::formatObject( $date_livraison, 'eeee d MMM', 'fr' ));
							$date_livraison_max = ucwords(IntlDateFormatter::formatObject( $date_livraison_max, 'eeee d MMM', 'fr' ));

							$html = '<hr style="margin:3px 0;color:#ffffff;"><div>';
							switch ( $method_name ) {
								case 'Formule ECOCEAN':
									 break;
								case 'Formule CLÉ EN MAIN':
									if (!empty($custom_shipping_date)) {
										$html .= '<b style="color:#007f15;text-transform:uppercase;font-size:0.7em;">Livraison le ' . $formattedCustomDate . ' *</b><br>';
									}elseif (!empty($custom_shipping_text))  {
										$html .= '<b style="color:#007f15;text-transform:uppercase;font-size:0.7em;">Livraison ' . $custom_shipping_text . ' *</b><br>';
										$html .= '<b style="color:gray;font-size:0.8em;">Le prestataire vous contactera pour convenir d\'un rendez-vous n\'hésitez pas à lui imposer vos contraintes.</b><br>';
									}else{
										$html .= '<b style="color:#007f15;text-transform:uppercase;font-size:0.7em;">Délai de livraison estimé entre le ' . $date_livraison . ' et ' . $date_livraison_max .' *</b><br>';
										$html .= '<b style="color:gray;font-size:0.8em;">Le prestataire vous contactera pour convenir d\'un rendez-vous n\'hésitez pas à lui imposer vos contraintes.</b><br>';
									}   
									
									$html .= '<span style="font-size:0.8em;"><b>Livraison par véhicule type porteur avec installation complète dans la pièce de votre choix.</b><br>Déballage, mise en place, montage des équipements optionnels / intérieurs et évacuation des emballages pour recyclage. Le mobilier est alors directement prêt à l\'emploi.<br>Livraison en Semaine entre 8h et 16h30.</span><br>';
									$html .= '<span style="color:#560f56;font-size:0.8em;"><i><u>ATTENTION</u> : Ces frais s\'entendent pour un accès "normal" dans les locaux (monte-charge, ascenseur) <u>ou bien escalier large jusqu\'au 1er étage seulement.</u></i></span><br>';
									$html .= '<small><span style="color:#007f15;">(*)</span> Estimation de la livraison calculée sur la date du devis. Une nouvelle estimation sera calculée à la validation du paiement ou du mandat.</small>';
									break;					
								case 'Formule CLÉ EN MAIN HARTMANN':
									if (!empty($custom_shipping_date)) {
										$html .= '<b style="color:#007f15;text-transform:uppercase;font-size:0.7em;">Livraison le ' . $formattedCustomDate . ' *</b><br>';
									}elseif (!empty($custom_shipping_text))  {
										$html .= '<b style="color:#007f15;text-transform:uppercase;font-size:0.7em;">Livraison ' . $custom_shipping_text . ' *</b><br>';
										$html .= '<b style="color:gray;font-size:0.8em;">Le prestataire vous contactera pour convenir d\'un rendez-vous n\'hésitez pas à lui imposer vos contraintes.</b><br>';
									}else{
										$html .= '<b style="color:#007f15;text-transform:uppercase;font-size:0.7em;">Délai de livraison estimé entre le ' . $date_livraison . ' et ' . $date_livraison_max .' *</b><br>';
										$html .= '<b style="color:gray;font-size:0.8em;">Le prestataire vous contactera pour convenir d\'un rendez-vous n\'hésitez pas à lui imposer vos contraintes.</b><br>';
									}    
									
									$html .= '<span style="font-size:0.8em;"><b>Livraison par véhicule 19 Tonnes sur Rendez-vous avec mise en place dans la pièce de votre choix (hors accès par escaliers)</b><br>Déballage, mise en place, montage des équipements optionnels / intérieurs et évacuation des emballages pour recyclage. Mise en place en RDC avec 2 marches maximum ou étage avec ascenseur. Maximum de 20 mètres de roulage.<br>Livraison en Semaine entre 8h et 16h30.</span><br>';
									$html .= '<span style="color:#560f56;font-size:0.8em;"><i><u>ATTENTION</u> : Prix sur devis pour accès difficile (forte pente, roulage supérieur à 20 mètre, accès par escalier; fixation au sol par technicien agréé)</i></span><br>';
									$html .= '<small><span style="color:#007f15;">(*)</span> Estimation de la livraison calculée sur la date du devis. Une nouvelle estimation sera calculée à la validation du paiement ou du mandat.</small>';
									break;
									case 'Formule Messagerie HARTMANN':
									if (!empty($custom_shipping_date)) {
										$html .= '<b style="color:#007f15;text-transform:uppercase;font-size:0.7em;">Livraison le ' . $formattedCustomDate . ' *</b><br>';
									}elseif (!empty($custom_shipping_text))  {
										$html .= '<b style="color:#007f15;text-transform:uppercase;font-size:0.7em;">Livraison ' . $custom_shipping_text . ' *</b><br>';
									}else{
										$html .= '<b style="color:#007f15;text-transform:uppercase;font-size:0.7em;">Délai de livraison estimé entre le ' . $date_livraison . ' et ' . $date_livraison_max .' *</b><br>';
									}  
									$html .= '<span style="font-size:0.8em;"><b>Livraison par véhicule 19 Tonnes sans Rendez-vous. Manutention par vos soins.<br>Livraison en Semaine entre 8h et 16h30.</span><br>';
									$html .= '<span style="color:#560f56;font-size:0.8em;"><i><u>ATTENTION</u> : Prix sur devis pour accès difficile (forte pente, roulage suppérieur à 20 mètre, accès par escalier; fixation au sol par technicien agréé)</i></span><br>';
									$html .= '<small><span style="color:#007f15;">(*)</span> Estimation de la livraison calculée sur la date du devis. Une nouvelle estimation sera calculée à la validation du paiement ou du mandat.</small>';
									break;
								case 'Formule ECO [OFFERTE]':
								case 'Formule ECO':
									if (!empty($custom_shipping_date)) {
										$html .= '<b style="color:#007f15;text-transform:uppercase;font-size:0.7em;">Livraison le ' . $formattedCustomDate . ' *</b><br>';
									}elseif (!empty($custom_shipping_text))  {
										$html .= '<b style="color:#007f15;text-transform:uppercase;font-size:0.7em;">Livraison ' . $custom_shipping_text . ' *</b><br>';
										$html .= '<b style="color:gray;font-size:0.8em;">Le transporteur vous contactera pour convenir d\'un rendez-vous n\'hésitez pas à lui imposer vos contraintes.</b><br>';
									}else{
										$html .= '<b style="color:#007f15;text-transform:uppercase;font-size:0.7em;">Délai de livraison estimé entre le ' . $date_livraison . ' et ' . $date_livraison_max .' *</b><br>';
										$html .= '<b style="color:gray;font-size:0.8em;">Le transporteur vous contactera pour convenir d\'un rendez-vous n\'hésitez pas à lui imposer vos contraintes.</b><br>';
									}  
									
									$html .= '<span style="font-size:0.8em;"><b>Livraison par semi-remorque sans HAYON.</b><br>Déchargement par vos soins.<br>Livraison en Semaine entre 8h et 16h30.</span><br>';
									$html .= '<small><span style="color:#007f15;">(*)</span> Estimation de la livraison calculée sur la date du devis. Une nouvelle estimation sera calculée à la validation du paiement ou du mandat.</small>';
									break;
								case 'Formule ECO Plus':
									if (!empty($custom_shipping_date)) {
										$html .= '<b style="color:#007f15;text-transform:uppercase;font-size:0.7em;">Livraison le ' . $formattedCustomDate . ' *</b><br>';
									}elseif (!empty($custom_shipping_text))  {
										$html .= '<b style="color:#007f15;text-transform:uppercase;font-size:0.7em;">Livraison ' . $custom_shipping_text . ' *</b><br>';
										$html .= '<b style="color:gray;font-size:0.8em;">Le transporteur vous contactera pour convenir d\'un rendez-vous n\'hésitez pas à lui imposer vos contraintes. </b><br>';
									}else{
										$html .= '<b style="color:#007f15;text-transform:uppercase;font-size:0.7em;">Délai de livraison estimé entre le ' . $date_livraison . ' et ' . $date_livraison_max .' *</b><br>';
										$html .= '<b style="color:gray;font-size:0.8em;">Le transporteur vous contactera pour convenir d\'un rendez-vous n\'hésitez pas à lui imposer vos contraintes. </b><br>';
									}   
									
									$html .= '<span style="font-size:0.8em;"><b>Livraison par semi-remorque équipé d\'un HAYON.</b><br>Commande déposée au pied du camion.<br>Livraison en Semaine entre 8h et 16h30.<br><i><span style="color:#560f56;"><u>Attention sur devis</u> :</span> Livraison par véhicule type porteur, avec capacité de chargement réduite</i></span><br>';
									$html .= '<small><span style="color:#007f15;">(*)</span> Estimation de la livraison calculée sur la date du devis. Une nouvelle estimation sera calculée à la validation du paiement ou du mandat.</small>';
									break;
								case 'Messagerie':
									$html .= 'Frais de conditionnement, préparation, traitement et livraison.<br />';
									$html .= 'Livraison sous 10 jours.';
									break;
								default:
									$html .= 'Nous contacter pour plus d\'information sur les délais de livraison.';
							}
							$html .= '</div>';
							echo '<div><b style="text-transform:uppercase;">Livraison ' . $count . ', Expédiés par ' . strtoupper($supplier_meta) . ' : </b> ' . $method_name . '</span></div>' . $html ;							
							echo '<hr style="margin:7px 0;color:#f26d00;width:10%;"><div> <span style="color:#f26d00">&Agrave; Propos de ' . $brand . ' : </span>' . $description_supplier . '</div><hr style="margin:7px 0;color:#ffffff;">';
							
							$count += 1;
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
							if ( $is_variation && empty( $image ) ) {
								$parent_id = wp_get_post_parent_id( $_product_id );
								$image = wp_get_attachment_image_src( get_post_thumbnail_id( $parent_id ), 'thumbnail' );
							}
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
								$sku       = sprintf( '<br><small class="wc-item-sku">Réf. %s', $_product->get_sku() );
								$title .=  apply_filters( 'ywraq_sku_label_html', $sku, $_product ) . $garantie . $french_supplier . '</small>'; //phpcs:ignore
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
													echo ' <span class="cart-fabrication"><strong>Livré Monobloc</strong> <i class="icon-info-sign tooltip1" style="color:#0000ce;"><i class="tooltiptext1">Meuble Assemblé et Soudé en Usine</i></i></span>';
													break;
												case 'mixte':
													echo ' <span class="cart-fabrication"><strong>Livré Monobloc, à équiper</strong> <i class="icon-info-sign tooltip1" style="color:#0000ce;"><i class="tooltiptext1">Meuble Assemblé et Soudé en Usine, Equipement livré séparement à monter par vos soins</i></i></span>';;
													break;
												case 'caisson':
													echo ' <span class="cart-fabrication"><strong>Livré Partiellement Monobloc</strong> <i class="icon-info-sign tooltip1" style="color:#0000ce;"><i class="tooltiptext1">Caisson Monobloc, Bureau à monter</i></i></span>';;
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
										// if (function_exists('nxover_filter_qpdf_item'))
											// $display = nxover_filter_qpdf_item($item, $display);
										// ]] NXOVER
										$display = str_replace( '<p', '<span', $display );
										$display = str_replace( '</p>', '</span>', $display );
										$display = str_replace( 'supplier', 'Expéditeur', $display );
										// Ligne pour remplacer le '(+' dans le prix de l'option par un '(inclus' // Version cracra
										$display = str_replace( '(+', '(inclus ', $display );
										$excerpt = get_the_excerpt($item['product_id']);
										
										// print_r($excerpt,true);
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
											$excerpt = str_replace('<a href="#tab_description">Plus de Caractéristiques [...]</a>', '', $excerpt );
											// enlever le + avant le prix
											$excerpt = str_replace('+', '', $excerpt);
											// rajouter "inclus" après le prix
											$excerpt = str_replace('€', '€ inclus', $excerpt);
										}
										$supplier_meta = get_post_meta($item['product_id'], 'supplier', true);
										// if ($supplier_meta =="Vinco"){
											$display .= '<b>Caractéristiques : </b>' . $excerpt;
										// }
										echo wp_kses_post( $display);
										?>
									</small>
								</td>
							</tr>
							<tr>
								<td colspan="6" style="height :50px">
								</td>
							</tr>
							
						<?php
					endif;
				endforeach; // Items boucle 2
			endforeach;  // Shipping methodes
        endif; ?>
        </tbody>
		</table>
        <table class="quote-total"  cellspacing="0"  style="page-break-inside: avoid; break-inside: avoid;">
        <?php
        if ( 'no' === get_option( 'ywraq_pdf_hide_total_row', 'no' ) ) {
			$total_ht = 0;
			$tva_value = 0;
			$tte = $order->get_order_item_totals();
			foreach ( $order->get_order_item_totals() as $key => $total ) {
				if ( $key === 'cart_subtotal' || $key === 'shipping' || strpos($key, 'fee') !== false) {
					$total_ht += floatval(preg_replace('/[^0-9\.]/', '', $total['value']));
					$formatted_total_ht = number_format($total_ht, 2, '.', ',');
				}elseif ($key === 'discount'){
					$total_ht -= floatval(preg_replace('/[^0-9\.]/', '', $total['value']));
					$formatted_total_ht = number_format($total_ht, 2, '.', ',');
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
                            <td scope="col" colspan="2" class="last-col" style="text-align:right;color:#7f1321;"><?php echo wp_kses_post( $formatted_total_ht ) . ' €';?></td>
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
		
			<table class="ywraq-buttons pdf-button accept">
				<tr>
					<?php if ( get_option( 'ywraq_show_accept_link' ) !== 'no' ): ?>
						<td>
							<span style='text-transform:uppercase;font-size:15px;'>Validez votre devis en ligne</span><br><br>
							<hr style="margin:10px 0; color:#0a5699;">
							<p style="margin-top:8px;"><strong>Carte Bleue | 3x sans frais | Chèque | Virement | Mandat Administratif | Conditions de règlement personnalisées</strong></p>
							<hr style="margin:5px 0; color:#ffffff;">
							<p style="font-size:11px;">Notre service client est disponible pour trouver la modalité de règlement la plus adaptée à vos besoins.</p>
						</td>
					<?php endif; ?>
				</tr>
				<tr>
					<td>
						<hr style="margin:10px 0; color:#ffffff;">
					</td>
				</tr>
				<tr>
					<td>
						<a href="https://www.armoireplus.fr/mon-compte/quotes/" style="text-transform:capitalize;font-size:18px;">
							Je valide mon devis en ligne
						</a>
					</td>
				</tr>
				<tr>
					<td>
						<hr style="margin:10px 0; color:#ffffff;">
					</td>
				</tr>
				<tr>
					<td>
						<div>
							<p style="font-size:12px;font-weight: normal;"><b>Nos coordonnées bancaires :</b><br>
								IBAN : FR76 3000 4010 1600 0101 2462 666 <br> BIC : BNPAFRPPXXX
							</p>
						</div>
					</td>
				</tr>
			</table>
		
    </div>
<?php endif ?>
  
<?php do_action( 'yith_ywraq_email_after_raq_table', $order ); ?>
