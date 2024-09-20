<?php
/**
 * The template for the header sticky bar.
 * Override this template by specifying the path where it is stored (templates_path) in your Redux config.
 *
 * @author        Redux Framework
 * @package       ReduxFramework/Templates
 * @version:      4.0.0
 */

?>

<?php
$activated_data = '';
if ( etheme_is_activated() ):
	
	$activated_data = get_option( 'etheme_activated_data' );
	$activated_data = ( isset( $activated_data['purchase'] ) && ! empty( $activated_data['purchase'] ) ) ? $activated_data['purchase'] : '';

endif; ?>

<div id="redux-sticky">
    <div id="info_bar">
        <div class="etheme-button-sets">
        </div>
        <div class="redux-action_bar">
            <span class="spinner"><div class="et-loader "><svg class="loader-circular" viewBox="25 25 50 50"><circle class="loader-path" cx="50" cy="50" r="12" fill="none" stroke-width="2" stroke-miterlimit="10"></circle></svg></div></span>
			<?php
			if ( false === $this->parent->args['hide_save'] ) {
				submit_button( esc_attr__( 'Save Changes', 'legenda' ), 'primary', 'redux_save', false, array( 'id' => 'redux_top_save' ) );
			}
			
			if ( false === $this->parent->args['hide_reset'] ) {
				submit_button( esc_attr__( 'Reset Section', 'legenda' ), 'secondary', $this->parent->args['opt_name'] . '[defaults-section]', false, array( 'id' => 'redux-defaults-section-top' ) );
				submit_button( esc_attr__( 'Reset All', 'legenda' ), 'secondary', $this->parent->args['opt_name'] . '[defaults]', false, array( 'id' => 'redux-defaults-top' ) );
			}
			?>
        </div>
        <div class="redux-ajax-loading" alt="<?php esc_attr_e( 'Working...', 'legenda' ); ?>">&nbsp;</div>
    </div>

    <!-- Notification bar -->
    <div id="redux_notification_bar">
		<?php $this->notification_bar(); ?>
    </div>
</div>
