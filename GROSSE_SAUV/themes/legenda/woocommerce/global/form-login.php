<?php
/**
 * Login form
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     7.1.0
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( is_user_logged_in() ) 
	return;


$woo_new_7_0_1_version = etheme_woo_version_check();
$button_class = '';
if ( $woo_new_7_0_1_version ) {
	$button_class = wc_wp_theme_get_element_class_name( 'button' );
}
?>
<form method="post" class="et_login" <?php if ( $hidden && $hidden != 'no' || $hidden == 'yes') echo 'style="display:none;"'; ?>>

	<?php do_action( 'woocommerce_login_form_start' ); ?>

	<?php if ( $message ) echo wpautop( wptexturize( $message ) ); ?>

	<p class="form-row form-row-first">
		<label for="username"><?php esc_html_e( 'Username or email', 'legenda' ); ?> <span class="required">*</span></label>
		<input type="text" class="input-text" name="username" id="username" />
	</p>
	<p class="form-row form-row-last">
		<label for="password"><?php esc_html_e( 'Password', 'legenda' ); ?> <span class="required">*</span></label>
		<input class="input-text" type="password" name="password" id="password" />
	</p>
	<div class="clear"></div>

	<?php do_action( 'woocommerce_login_form' ); ?>

	<p class="form-row">
		<?php wp_nonce_field( 'woocommerce-login', 'woocommerce-login-nonce' ); ?>
		<button type="submit" class="button <?php echo esc_attr( $button_class ? ' ' . $button_class : '' ); ?>" name="login" value="<?php esc_attr_e( 'Login', 'legenda' ); ?>"><?php esc_html_e( 'Login', 'legenda' ); ?></button>
		<input type="hidden" name="redirect" value="<?php echo esc_url( $redirect ) ?>" />
		<label for="rememberme" class="rememberme" class="woocommerce-form-login__rememberme inline">
			<input name="rememberme" type="checkbox" id="rememberme" value="forever" /> <?php esc_html_e( 'Remember me', 'legenda' ); ?>
		</label>
	</p>
	<p class="lost_password">
		<a href="<?php echo esc_url( wc_lostpassword_url() ); ?>"><?php esc_html_e( 'Lost your password?', 'legenda' ); ?></a>
	</p>

	<div class="clear"></div>

	<?php do_action( 'woocommerce_login_form_end' ); ?>

</form>