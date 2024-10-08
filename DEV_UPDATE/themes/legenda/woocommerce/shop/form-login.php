<?php
/**
 * Login form
 *
 * @author 		WooThemes
 * @package 	WooCommerce/Templates
 * @version     1.6.4
 */

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

global $woocommerce;

if (is_user_logged_in()) return;
?>
<form method="post" class="et-login">
	<?php if ( $message ) echo wpautop( wptexturize( $message ) ); ?>

	<p class="row-fluid">
		<label class="span4" for="username"><?php esc_html_e( 'Username or email', 'legenda' ); ?> <span class="required">*</span></label>
		<input type="text" class="input-text span8" name="username" id="username" />
	</p>
	<p class="row-fluid">
		<label for="password" class="span4"><?php esc_html_e( 'Password', 'legenda' ); ?> <span class="required">*</span></label>
		<input class="input-text span8" type="password" name="password" id="password" />
	</p>
	<div class="clear"></div>

	<p class="et-login-footer">
		<?php $woocommerce->nonce_field('login', 'login') ?>
		<input type="submit" class="button fl-r active" name="login" value="<?php esc_html_e( 'Login', 'legenda' ); ?>" />
		<input type="hidden" name="redirect" value="<?php echo esc_url( $redirect ) ?>" />
		<a class="lost_password" href="<?php echo esc_url( wp_lostpassword_url( home_url() ) ); ?>"><?php esc_html_e( 'Lost Password?', 'legenda' ); ?></a>
	</p>

	<div class="clear"></div>
</form>