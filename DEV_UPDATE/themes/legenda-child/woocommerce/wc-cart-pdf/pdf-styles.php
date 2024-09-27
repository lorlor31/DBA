<?php
/**
 * PDF styles
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

// Load colors.
$bg              = get_option( 'woocommerce_email_background_color' );
$body            = get_option( 'woocommerce_email_body_background_color' );
$base            = get_option( 'woocommerce_email_base_color' );
$base_text       = wc_light_or_dark( $base, '#202020', '#ffffff' );
$text            = get_option( 'woocommerce_email_text_color' );

// Pick a contrasting color for links.
$link = wc_hex_is_light( $base ) ? $base : $base_text;
if ( wc_hex_is_light( $body ) ) {
	$link = wc_hex_is_light( $base ) ? $base_text : $base;
}

$bg_darker_10    = wc_hex_darker( $bg, 10 );
$body_darker_10  = wc_hex_darker( $body, 10 );
$base_lighter_20 = wc_hex_lighter( $base, 20 );
$base_lighter_40 = wc_hex_lighter( $base, 40 );
$text_lighter_20 = wc_hex_lighter( $text, 20 );

?>
body {
    color: <?php echo esc_attr( $text_lighter_20 ); ?>;
    font-family: "dejavusans", "notosans", ""helveticaneue", helvetica, roboto, arial, sans-serif;
    font-size: 8pt;
	margin: 0;
	padding: 0;
	-webkit-text-size-adjust: none !important;
	width: 100%;
}
a {
    color: <?php echo esc_attr( $base ); ?>;
    text-decoration: none;
}
b, strong, th {
    font-weight: bold;
}
.shop_table {
    width: 100%;
    table-layout: fixed;
    border: 1px solide #ddd;
}
.shop_table .product-name {
    width: 40%;
}
.shop_table .product-tva {
    width: 15%;
    text-align: right;
}
.shop_table .product-quantity {
    width: 15%;
    text-align: right;
}
.shop_table .product-price {
    width: 15%;
    text-align: right;
}
.shop_table .product-subtotal {
    width: 15%;
    text-align: right;
}
.shop_table td,
.shop_table th {
    vertical-align: top;
    padding: .5em .5em 1em;
}
.shop_table dl {
    margin: 0;
    padding: .5em 0;
}
.shop_table dd,
.shop_table dt {
    font-size: 12px;
    display: inline;
    margin: 0;
    padding: 0;
}
.shop_table dt {
    font-weight: bold;
}
.shop_table dd * {
    display: inline;
}
.shop_table dd:after {
    display: block;
    font-size: 0;
    content: "\A";
    clear: both;
    height: 0;
    white-space: pre;
}
.row-subtotal {
    text-transform: capitalize;
    background: #f5f5f5;
    text-align: right;
}
#template_header_image {
    text-align: center;
    padding: 0;
}
#template_footer {
    position: absolute;
    width: 84%;
    left: 8%;
    bottom: 0;
    font-size: 7pt;
    margin-bottom: 30px;
    text-align: center;
}
#template_footer hr {
    border-top: 1px solide #ddd;
    height: 1px;
}
.shop_sign {
    width:300px;
    padding:0;
    margin-top:5px;
    border:1px solid #bbb;
}
.shop_ttc, .suffix_euro {
    display:none;
}