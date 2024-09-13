<?php
/**
 * Output a single payment method
 *
 * This template can be overridden by copying it to yourtheme/woocommerce/checkout/payment-method.php.
 *
 * HOWEVER, on occasion WooCommerce will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see         https://docs.woocommerce.com/document/template-structure/
 * @package     WooCommerce\Templates
 * @version     3.5.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>
<li class="wc_payment_method payment_method_<?php echo esc_attr( $gateway->id ); ?> payment_method_hero">
	<input id="payment_method_<?php echo esc_attr( $gateway->id ); ?>" type="radio" class="input-radio" name="payment_method" value="<?php echo esc_attr( $gateway->id ); ?>" <?php checked( $gateway->chosen, true ); ?> data-order_button_text="<?php echo esc_attr( $gateway->order_button_text ); ?>" />

	<label for="payment_method_<?php echo esc_attr( $gateway->id ); ?>" style="display:flex;align-items: center;">
		<?php
        $out = '';
        $selectFirst = false;
        $out .= '';
        $checked = '';
        $code = $gateway->code;
        $title = $gateway->method_title;
        $strlen = strlen($title);
        $margin = '0';
        if ($strlen < 28){
            $margin ='8.5px';
        }
        $imgUrl = plugins_url('woocommerce-gateway-hero/assets/images/');
        $out .= '';
        $out .= '<span class="herosublabel">';
        $out .=   '<img src="' . $imgUrl . $code . '.svg" alt="' . $title . '" class="payment-icon"/>';
        $out .=   '<span class="herolabeltext">';
        $out .=      $title;
        $out .=   '</span>';
        $out .=   '<span class="cardtypes">';
        $out .=       '<img src="' . $imgUrl . 'VISA.svg" alt="VISA"/>';
        $out .=       '<img src="' . $imgUrl . 'MASTERCARD.svg" alt="MASTERCARD"/>';
        $out .=       '<img src="' . $imgUrl . 'AMEX.svg" alt="AMEX"/>';
        $out .=       '<img src="' . $imgUrl . 'CB.svg" alt="CB"/>';
        $out .=   '</span>';
        $out .= '</span>';
        echo $out;
        ?>
	</label>
</li>