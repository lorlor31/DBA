<?php if ( ! defined( 'ABSPATH' ) ) exit( 'No direct script access allowed' );



$et_info = '';
$result  = '';

if (isset($_GET['et_clear_wc_system_status_theme_info'])){
	delete_transient( 'wc_system_status_theme_info' );
}

ob_start();
    $system = new Etheme_System_Requirements();
    $system->html();
    $result = $system->result();
$system = ob_get_clean();
ob_start();
    $version = new ETheme_Version_Check();
    $version->activation_page();
    ?>

    <h4 class="text-uppercase"><?php esc_html_e('Where can I find my purchase code?', 'legenda'); ?></h4>

    <ul>
        <li><b><?php esc_html_e( 'If you bought theme on  ', 'legenda' ); ?> <a
                        href="https://themeforest.net/">https://themeforest.net/ : </a></b></li>
        <li>1. <?php esc_html_e('Please enter your Envato account and find ', 'legenda'); ?> <a href="https://themeforest.net/downloads"><?php esc_html_e('Downloads tab', 'legenda'); ?></a></li>
        <li>2. <?php esc_html_e('Find Legenda theme in the list and click on the opposite', 'legenda');?> <span><?php echo esc_html__('Download', 'legenda'); ?></span> <?php esc_html_e('button', 'legenda'); ?></li>
        <li>3. <?php esc_html_e('Select', 'legenda'); ?> <span><?php echo esc_html__('License Certificate & Purchase code', 'legenda'); ?></span> <?php esc_html_e('for download', 'legenda'); ?></li>
        <li>4. <?php esc_html_e('Copy the', 'legenda'); ?> <span><?php esc_html_e('Item Purchase Code', 'legenda'); ?> </span><?php esc_html_e('from the downloaded document', 'legenda'); ?></li>
        <br/>
        <li><b><?php esc_html_e( 'If you bought a subscription on  ', 'legenda' ); ?> <a
                        href="https://www.8theme.com/">https://www.8theme.com/ :</a></b>
        </li>
        <li>1. <?php esc_html_e( 'Please enter your 8theme account and find the Subscription License Key section', 'legenda' ); ?>
        </li>
        <li>2. <?php esc_html_e( 'Copy the existing code or generate the new one if you already used previously generated code. You need to generate separate codes for every single activation on different domains.', 'legenda' ); ?>
        </li>
        <li>3. <?php esc_html_e( 'Use it to activate the theme', 'legenda' ); ?>
        </li>
    </ul>
    <br/>

<?php $version = ob_get_clean();

if ( ! class_exists( 'Redux' ) ) {
	$et_info = '<p class="et-message et-error">' . esc_html__('The following required plugin is currently inactive: ', 'legenda') . '<a href="'.admin_url( 'plugins.php' ).'" target="_blank">'.esc_html__('Redux Framework', 'legenda').'</a></p>';
}
if ( ! class_exists('ETheme_Import') ) {
	$et_info = '<p class="et-message et-error">' . esc_html__('The following required plugin is currently inactive: ', 'legenda') . '<a href="'.admin_url( 'plugins.php' ).'" target="_blank">'.esc_html__('Legenda Core', 'legenda').'</a></p>';
}

$checked = (get_option('old_widgets_panel_type', 0)) ? 'checked=checked' : '';

echo '
<div class="et-col-7 etheme-registration">
	'.$et_info.'
	<h3>' . esc_html__( 'Theme Registration', 'legenda' ) . '</h3>
	' . $version . '
	<form action="" class="etheme-options-form" method="post" style="border-top: 1px solid #e1e1e1;">
        <p>
            <label for="old_widgets_panel_type"><input name="old_widgets_panel_type" id="old_widgets_panel_type" type="checkbox" '. $checked .'> Enable old widgets panel</label>
            <input class="hidden" name="etheme-options-save" type="hidden" value="save" />
        </p>
    </form>
</div>
';
echo '
	<div class="et-col-5 etheme-system et-sidebar">
		<h3>' . esc_html__( 'System Requirements', 'legenda' ) . '</h3>
		' . $system . '
		<div class="text-center"><a href="" class="et-button et-button-grey last-button et-loader-on">
		' . esc_html__( 'Check again', 'legenda' ) . '</a>
		
		<a href="?page=et-panel-welcome&et_clear_wc_system_status_theme_info" class="et-button et-button-grey last-button et-loader-on">
            ' . esc_html__( 'Clear WooCommerce system info cache', 'legenda' ) . '
        </a>
		
		</div>';
		if ( ! $result ) {
			echo '<p class="et-message et-error">'.esc_html__( 'Your system does not meet the server requirements. For more efficient result, we strongly recommend to contact your host provider and check the necessary settings.', 'legenda' ).'<p>';
		}

echo '</div>';