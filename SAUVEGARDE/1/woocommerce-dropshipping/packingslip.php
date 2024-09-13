<?php
    global $wpdb;
    setlocale(LC_ALL, 'fr_FR');
	
	$options = get_option( 'wc_dropship_manager' );
    $logo_url = $options['packing_slip_url_to_logo'];
	
    $order_id = $order_info['id'];
    $order = wc_get_order( $order_id );
	$item_order = $order->get_items();
    $num_order = get_post_meta($order->get_id(), '_ywson_custom_number_order_complete', true);
    $shipping_email = get_post_meta($order->get_id(), '_shipping_email', true);
    $shipping_phone = get_post_meta($order->get_id(), '_shipping_phone', true);
    $supplier_name = ucfirst($supplier_info['slug']);
    $is_email = debug_backtrace()[3]['function'] == '_send_order' ? true : false;
?>

<div style="background-color:#fff;<?php if ($is_email) { echo 'padding:15px;margin:15px 15%;'; } ?>">

    <!-- Info -->
    <table cellpadding="0" cellspacing="0" style="width:100%;margin:0 0 20px;padding:0;">
        <tr>
            <td style="width:48%;vertical-align:top;">
                <table cellpadding="0" cellspacing="0" style="width:100%;">
                    <tr>
                        <td style="height:125px;vertical-align:top;border:1px solid #e7e7e7;padding:15px;">
                            <span style="font-size:5pt;color:#777;font-style:italic;text-transform:uppercase;">Émetteur</span><br>
                            <strong style="font-size:9pt;">Armoire PLUS / D.B.A</strong>
                            <p style="font-size:7pt;">
                                9 ch de Rebel<br>
                                31180 Castelmaurou<br><br>
                                Tél.: 05 31 61 98 32 - Fax: 05 17 47 54 02<br>
                                Email: contact@armoireplus.fr<br>
                                Web: https://www.armoireplus.fr
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
            <td style="width:4%;"></td>
            <td style="width:48%;vertical-align:top;">
                <table cellpadding="0" cellspacing="0" style="width:100%;">
                    <tr>
                        <td style="height:125px;vertical-align:top;border:1px solid #e7e7e7;padding:15px;">
                            <span style="font-size:5pt;color:#777;font-style:italic;text-transform:uppercase;">Client</span><br>
                            <strong style="font-size:9pt;"><?= $order->get_shipping_last_name() .' '. $order->get_shipping_first_name(); ?></strong>
                            <p style="font-size:7pt;">
                                <?= $order->get_shipping_company() != '' ? 'Entreprise : ' . $order->get_shipping_company() . '<br>' : ''; ?>
                                <?= $order->get_shipping_address_1() .' '. $order->get_shipping_address_2(); ?><br>
                                <?= $order->get_shipping_postcode() .' '. $order->get_shipping_city(); ?><br><br>
                                Tél.: <?= $shipping_phone ?: $order->get_billing_phone(); ?><br>
                                Email: <?= $shipping_email ?: $order->get_billing_email(); ?>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Livraison -->
    <table cellpadding="0" cellspacing="0" style="width:100%;margin:0 0 20px;padding:0;">
        <tr>
            <td style="width:100%;vertical-align:top;border:1px solid #e7e7e7;padding:15px;">
                <span style="font-size:5pt;color:#777;font-style:italic;text-transform:uppercase;">Livraison</span><br>
                <?php
                $html = '';
                $current_shipping_method = null;
                $order_items = $order->get_items();
                foreach ( $order->get_shipping_methods() as $shipping_method ) {
					$meta_data = $shipping_method->get_meta_data();
					$articles_value = '';
					foreach ($meta_data as $meta) {
						if ($meta->key === 'Articles' && !empty($meta->value)) {
							$articles_value = $meta->value;
							break;
						}
					}
                    foreach ( $order_items as $item ) {
                         if (strpos($articles_value, $item->get_name()) !== false) {
                            $supplier_id = get_post_meta($item['product_id'], 'supplier', true);
							$formatted_supplier_id = strtolower(str_replace('-', ' ', $supplier_id));
							$formatted_supplier_name = strtolower(str_replace('-', ' ', $supplier_name));
                            if ( $formatted_supplier_id == $formatted_supplier_name ) {
                                $current_shipping_method = $shipping_method->get_name();
                                break 2;
                            }
                        }
                    }
                }
                $is_drop = false;
                switch ( $current_shipping_method ) {
                    case 'Formule CLÉ EN MAIN':
                        $is_drop = true;
                        $html .= '<b>Le prestataire vous contacte pour convenir d\'un RDV.<br>Déballage, mise en place, montage des équipements optionnels / intérieurs et évacuation des emballages pour recyclage.</b><br>Livraison par véhicule type porteur avec installation complète dans la pièce de votre choix.</b><br>Déballage, mise en place, montage des équipements optionnels / intérieurs et évacuation des emballages pour recyclage. Le mobilier est alors directement prêt à l\'emploi.<br>Livraison en Semaine entre 8h et 16h30.';
                        break;  
					case 'Formule CLÉ EN MAIN HARTMANN':
                        $html .= '<b>Le prestataire vous contacte pour convenir d\'un RDV.<br>Déballage, mise en place, montage des équipements optionnels / intérieurs et évacuation des emballages pour recyclage.</b>';
                        break;
					case 'Formule Messagerie HARTMANN':
                        $html .= '<b>Livraison sans rendez-vous par poids lourd. Manutention à partir du camion.<br>Livraison en Semaine entre 8h et 16h30.';
                        break;
                    case 'Formule ECO [OFFERTE]':
                    case 'Formule ECO':
                        $html .= '<b>Le prestataire vous contacte pour convenir d\'un RDV.<br>Merci de vous assurez que le contact chargé de receptionner soit présent.</b><br>Livraison par semi-remorque sans HAYON. Déchargement par vos soins.<br>Livraison en Semaine entre 8h et 16h30.';
                        break;
                    case 'Formule ECO Plus':
                        $html .= '<b>Le prestataire vous contacte pour convenir d\'un RDV.<br>Merci de vous assurez que le contact chargé de receptionner soit présent.</b><br>Livraison par semi-remorque équipé d\'un HAYON. Commande déposée au pied du camion.<br>Livraison en Semaine entre 8h et 16h30.';
                        break;
                    case 'Messagerie':
                        $html .= '<b>Frais de conditionnement, préparation, traitement et livraison.</b>';
                        break;
                    default:
                        $html .= 'Nous contacter pour plus d\'information sur les conditions de livraison.';
                }
                if ( $is_email ) {
                    $supplier_method = '';
                    switch ( $current_shipping_method ) {
                        case 'Formule CLÉ EN MAIN':
                            $supplier_method = 'DROP';
                            break;
                        case 'Formule CLÉ EN MAIN HARTMANN':
                            $supplier_method = 'Prestation Installation';
                            break;
                        case 'Formule Messagerie HARTMANN':
                            $supplier_method = 'Prestation Messagerie';
                            break;
                        case 'Formule ECO [OFFERTE]':
                        case 'Formule ECO':
                            $supplier_method = 'SD RDV 48H AV';
                            break;
                        case 'Formule ECO Plus':
                            $supplier_method = 'SD+HAYON RDV 48H AV';
                            break;
                        case 'Messagerie':
                            $supplier_method = 'Messagerie';
                            break;
                    }
                    $current_shipping_method = $supplier_method;
                }
                ?>
                <b style="font-size:8pt;color:#7f1321;"><?= $current_shipping_method ?></b><br>
                <p style="font-size:7pt;"><?= $html ?></p>
            </td>
        </tr>
    </table>

    <!-- Note -->
    <?php if ( $order->get_customer_note() != '' ): ?>
    <table cellpadding="0" cellspacing="0" style="width:100%;margin:0 0 20px;padding:0;">
        <tr>
            <td style="width:100%;vertical-align:top;border:1px solid #e7e7e7;padding:15px;">
                <span style="font-size:5pt;color:#777;font-style:italic;text-transform:uppercase;">Note</span><br>
                <p style="font-size:7pt;"><?= $order->get_customer_note() ?></p>
            </td>
        </tr>
    </table>
    <?php endif; ?>
    <!-- Note au Fournisseur -->
    <?php 
    $note_fournisseur = get_post_meta( $order->get_id(), 'note_fournisseur', true );
    if ( !empty($note_fournisseur) ): ?>
    <table cellpadding="0" cellspacing="0" style="width:100%;margin:0 0 20px;padding:0;">
        <tr>
            <td style="width:100%;vertical-align:top;border:1px solid #e7e7e7;padding:15px;">
                <span style="font-size:5pt;color:#777;font-style:italic;text-transform:uppercase;">Note au Fournisseur</span><br>
                <p style="font-size:7pt;"><?= esc_html( $note_fournisseur ) ?></p>
            </td>
        </tr>
    </table>
    <?php endif; ?>
    <!-- Référence -->
    <?= '<h2 style="font-size:9pt;color:#222;">Référence commande : <span style="color:#7f1321;">' . $num_order . '</span></h2>' ?>
	<!-- Produits -->
<?php
// main_file.php

include 'functions.php'; // Inclure le fichier contenant les fonctions

?>

<!-- Produits -->
<table cellpadding="0" cellspacing="0" style="width:100%;margin:0 0 20px;padding:0;">
    <thead>
    <tr style="text-align:left;">
        <th style="font-size:7pt;color:#777;background:#f7f7f7;padding:10px;"><?php esc_html_e('Product', 'woocommerce'); ?></th>
        <th style="font-size:7pt;color:#777;background:#f7f7f7;padding:10px;"><?php esc_html_e('SKU', 'woocommerce'); ?></th>
        <th style="font-size:7pt;color:#777;background:#f7f7f7;padding:10px;"><?php esc_html_e('Quantity', 'woocommerce'); ?></th>
        <?= $is_email ? '<th style="font-size:7pt;color:#777;background:#f7f7f7;padding:10px;">Achat HT</th>' : '' ?>
    </tr>
    </thead>
    <tbody>
    <?php
		$total_order = $total_line = 0;
	// Ajoutez l'ID de la ligne de commande à chaque produit dans order_info
	foreach ($item_order as $order_item) {
		$product_id = $order_item->get_product_id();
		$variation_id = $order_item->get_variation_id();
		$order_item_id = $order_item->get_id();
		
foreach ($order_info[$supplier_info['slug']] as $index => $prod_info) {
    if (!isset($prod_info['order_item_id']) && 
        ($product_id == $prod_info['id'] || $variation_id == $prod_info['id'])) {
        $order_info[$supplier_info['slug']][$index]['order_item_id'] = $order_item_id;
        break;
    }
}

	}
		$addons_result = process_addons_for_order_items($item_order);
		error_log("addons_result: " . print_r($addons_result, true));

foreach ($order_info[$supplier_info['slug']] as $index => $prod_info) :
    if (!isset($order_info[$supplier_info['slug']][$index]['order_item_id'])) {
        continue;
    }
    $order_item_id = $order_info[$supplier_info['slug']][$index]['order_item_id'];

    if (isset($addons_result[$order_item_id])) {
        $addons_simple = $addons_result[$order_item_id]['addons_simple'];
        $addons_product = $addons_result[$order_item_id]['addons_product'];

    } else {
        $addons_simple = [];
        $addons_product = [];
    }
	

    $product_data = product_info_promo($order_info[$supplier_info['slug']][$index], $supplier_info, $wpdb, $order_id, $is_email, $promo_ids_string, $promo_dates_string);
    $result = identify_attributes_and_addons($order_info[$supplier_info['slug']][$index]['order_item_meta']);
	error_log("result: " . print_r($result, true));
    $attributes = $result['attributes'];
?>
        <tr style="text-align:left;">
            <td style="font-size:8pt;border-bottom:1px solid #b8b8b8;padding:10px;">
                <h3 style="font-size:8pt;"><?= $prod_info['variation_name'] ?><?php if ($product_data['is_promo'] && $is_email): ?><br><strong style="color:#7f1321;text-transform:uppercase;">En promotion <?= $product_data['promo_date_readable']; ?></strong><?php endif; ?></h3>
            <?php
			foreach ($attributes as &$attribute) {
				$attribute['name'] = get_attribute_name($attribute['key']);
				echo '<p style="font-size:7pt;">- ' . $attribute['name'] . ' : <b>' . $attribute['value'] . '</b></p>';
			}
            foreach ($addons_simple  as $addon) {
                // if (!isset($addon['product_id'])) {
                    echo '<p style="font-size:7pt;color:blue;">- ' . $addon['title'] . ' : <b>' . $addon['value'] . '</b></p>';
                // }
            }
            foreach ($addons_product  as $addon) {
                // if (isset($addon['product_id'])) {
                    echo '<p style="font-size:7pt;color:red;">- ' . $addon['title'] . ' : <b>' . $addon['info'] . ' (' . $addon['product_mpn'] . ', ' . $addon['product_cog'] . ')</b></p>';
                // }
            }
            ?>
            </td>
			<?php $product_mpn = get_product_ugs($product_data['product_id'], $wpdb); ?>
            <td style="font-size:8pt;border-bottom:1px solid #b8b8b8;padding:10px;">
				<?php
				echo $product_mpn;
				$addon_tval = 0;
				foreach ($addons_product  as $addon) {
					if (isset($addon['product_id'])) {
						echo '<p style="font-size:7pt;color:red;">+ ' . $addon['product_mpn'] . '(x' . $addon['quantity'] . ')</b></p>';
					}
					$addon_val = $addon['product_cog'] * $addon['quantity'];
					$addon_tval += $addon_val;
				}
				?>
			</td>
            <td style="font-size:8pt;border-bottom:1px solid #b8b8b8;padding:10px;text-align:center;">x<?= $prod_info['qty'] ?></td>
            <?php
				$prod_cog = get_post_meta($prod_info['id'], '_wc_cog_cost', true);
            if (!empty($prod_cog)) {
                $total_line = $product_data['category'] == 'bureau-plus' && $is_drop ? $prod_info['qty'] * ( ($prod_cog / 0.35 * 0.40) + $addon_tval) : $prod_info['qty'] * ($prod_cog + $addon_tval);
                $total_order += $total_line;
            }
            ?>
            <?= $is_email ? '<td style="font-size:8pt;border-bottom:1px solid #b8b8b8;padding:10px;">' . number_format($total_line, 2, '.', ' ') . ' &euro;</td>' : '' ?>
        </tr>
    <?php endforeach; ?>
    <?php if ($is_email): ?>
        <tr>
            <td colspan="4" style="font-size:9pt;font-weight:bold;padding:10px;text-align:right;">Total achat commande : <span style="color:#7f1321;"><?= number_format($total_order, 2, '.', ' '); ?> &euro; HT</span></td>
        </tr>
    <?php endif; ?>
    </tbody>
</table>



</div>
