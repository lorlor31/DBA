<?php
/**
 * Request Quote PDF Header
 *
 * @package YITH Woocommerce Request A Quote
 * @since   1.0.0
 * @version 2.2.7
 * @author  YITH
 */

/**
 * @var $order    WC_Order
 * @var $raq_data array
 */

if ( function_exists( 'icl_get_languages' ) ) {
	global $sitepress;
	$lang = yit_get_prop( $order, 'wpml_language', true );
	if( function_exists('wc_switch_to_site_locale' ) ) {
		wc_switch_to_site_locale();
	}
	YITH_Request_Quote_Premium()->change_pdf_language( $lang );
}

$order_id = yit_get_prop( $order, 'id', true );
$logo_url = get_option( 'ywraq_pdf_logo' );

$logo_attachment_id = apply_filters('yith_pdf_logo_id', get_option( 'ywraq_pdf_logo-yith-attachment-id' ));
if ( ! $logo_attachment_id && $logo_url ) {
	$logo_attachment_id = attachment_url_to_postid( $logo_url );
}

$logo_attachment_id = apply_filters( 'yith_pdf_logo_id', get_option( 'ywraq_pdf_logo-yith-attachment-id' ) );
if ( ! $logo_attachment_id && $logo_url ) {
    $logo_attachment_id = attachment_url_to_postid( $logo_url );
}

$logo = $logo_attachment_id ? get_attached_file( $logo_attachment_id ) : $logo_url;

$image_type         = wp_check_filetype( $logo );
$mime_type          = array( 'image/jpeg', 'image/png' );
$logo               = apply_filters( 'ywraq_pdf_logo', ( isset( $image_type['type'] ) && in_array( $image_type['type'], $mime_type ) ) ? $logo : '' );

$user_name         = yit_get_prop( $order, 'ywraq_customer_name', true );
$user_email        = yit_get_prop( $order, 'ywraq_customer_email', true );
$formatted_address_form = $order->get_formatted_billing_address();
$formatted_address = $order->get_billing_company();
if ($order->get_billing_company()!=''){$formatted_address .='<br>';}
$formatted_address .= $order->get_billing_first_name().' '.$order->get_billing_last_name().'<br>';
$formatted_address .= $order->get_billing_address_1().'<br>';
$formatted_address .= $order->get_billing_address_2();
if ($order->get_billing_address_2()!=''){$formatted_address .='<br>';}
$formatted_address .= $order->get_billing_postcode().' '.$order->get_billing_city().'<br>';
// Obtention de la liste des pays de WooCommerce
$countries = WC()->countries->get_countries();
// Récupération du code du pays de la commande
$billing_country = $order->get_billing_country();
// Vérification et affichage du pays
if (isset($billing_country) && isset($countries[$billing_country])) {
    $formatted_address .= $countries[$billing_country];
} else {
    $formatted_address .= '';
}

$shipping_address  = $order->get_formatted_shipping_address();
// Split Asreesse de livraison 
$shipping_address_split = $order->get_shipping_company();
if ($order->get_shipping_company()!=''){$shipping_address_split .='<br>';}
$shipping_address_split .= $order->get_shipping_first_name().' '.$order->get_shipping_last_name().'<br>';
$shipping_address_split .= $order->get_shipping_address_1().'<br>';
$shipping_address_split .= $order->get_shipping_address_2();
if ($order->get_shipping_address_2()!=''){$shipping_address_split .='<br>';}
$shipping_address_split .= $order->get_shipping_postcode().' '.$order->get_shipping_city().'<br>';
// Récupération du code du pays de la commande
$shipping_country = $order->get_shipping_country();
// Vérification et affichage du pays
if (isset($shipping_country) && isset($countries[$shipping_country])) {
    $shipping_address_split .= $countries[$shipping_country];
} else {
    $shipping_address_split .= '';
}

$billing_name      = yit_get_prop( $order, '_billing_first_name', true );
$billing_surname   = yit_get_prop( $order, '_billing_last_name', true );
$billing_phone     = yit_get_prop( $order, 'ywraq_billing_phone', true );
$billing_phone     = empty( $billing_phone ) ? yit_get_prop( $order, '_billing_phone', true ) : $billing_phone;
$billing_vat       = yit_get_prop( $order, 'ywraq_billing_vat', true );
$order_id          = yit_get_prop( $order, 'id', true );
$order_message     = $order->get_customer_note();

$exdata            = yit_get_prop( $order, '_ywcm_request_expire', true );
error_log('Exdata (WC_DateTime): ' . print_r($exdata, true));
// error_log('order: ' . print_r($order, true));
$expiration_data   = '';
if ( function_exists( 'wc_format_datetime' ) ) {
    $order_date = wc_format_datetime( $order->get_date_created() );
    if ( ! empty( $exdata ) ) {
        $exdata          = new WC_DateTime( $exdata, new DateTimeZone( 'UTC' ) );
        $expiration_data = wc_format_datetime( $exdata );
    }
} else {
    $date_format     = isset( $raq_data['lang'] ) ? ywraq_get_date_format( $raq_data['lang'] ) : wc_date_format();
    $order_date      = date_i18n( $date_format, strtotime( yit_get_prop( $order, 'date_created', true ) ) );
    $expiration_data = empty( $exdata ) ? '' : date_i18n( $date_format, strtotime( $exdata ) );
}
$user = new WC_Customer( yit_get_prop( $order, '_ywraq_author', true ) );

?>
<div class="logo">
    <img src="<?= $logo ?>" style="width:200px;" alt="Logo Armoire Plus">
</div>
<div class="order_info" style="margin-top:15px;">
    <h1>Devis</h1>
    <h2><?= apply_filters( 'ywraq_quote_number', $order_id ) ?></h2>
    <span>Date de la proposition : <?= $order_date ?></span><br>
    <?php if ( $expiration_data != '' ): ?><span>Date de la fin de validité : <?= $expiration_data ?></strong></span><?php endif; ?>
</div>
<div class="info">
    <div class="info_emetteur">
        <span class="label">Émetteur :</span>
        <div class="content">
            <h3>Armoire PLUS / D.B.A</h3>
            <p>
                9 Chemin de Rebel<br>
                31180 Castelmaurou<br><br>
                Tél. : 05 31 61 98 32<br>
                Email : contact@armoireplus.fr<br>
                Web : https://www.armoireplus.fr
            </p>
        </div>
    </div>
    <div class="info_client">
        <span class="label">Client :</span>
        <div class="content">
            <?php if ( empty( $billing_name ) && empty( $billing_surname ) ): ?>
                <strong><?php echo $user_name ?></strong><br>
            <?php endif;
            echo $formatted_address . '<br><br>';
            if ( $billing_phone != '' ) {
                echo 'Tél. : ' . $billing_phone . '<br>';
            }
            echo 'Email : ' . $user_email . '<br>';
            if ( $billing_vat != '' ) {
                echo 'VAT : ' . $billing_vat . '<br>';
            }
            ?>
        </div>
    </div>
   <?php if (($formatted_address_form !== $shipping_address) && ('' !== $shipping_address)) {
		$order_livraison = str_replace(['<br>', '<br/>', '<br />'], ', ', $shipping_address) . '<br>';
	}	
	else {
		$order_livraison = str_replace(['<br>', '<br/>', '<br />'], ' ', $shipping_address_split) . '<br>';
	}
	?>
    <div class="info_livraison">
        <span class="label"><u>Adresse de Livraison :</u> <?= $order_livraison ?></span>
    </div>
    <?php if ($order_message != ''): ?>
        <div class="note_livraison">
            <span class="label">Note client :</span>
            <div class="content"><?= $order_message; ?></div>
        </div>
    <?php endif; ?>
</div>
<div class="clear"></div>
