<?php 
define('PARENT_DIR', get_template_directory());
define('ETHEME_THEME_NAME', 'Legenda');
define('THEME_LOGO', 'Legenda');
define('ETHEME_CODE_DIR', trailingslashit(PARENT_DIR).'framework');

define('PARENT_URL', get_template_directory_uri());
define('ETHEME_CODE_URL', trailingslashit(PARENT_URL).'framework');
define('ETHEME_CODE_IMAGES_URL', trailingslashit(ETHEME_CODE_URL).'css/images');
define('ETHEME_CODE_JS_URL', trailingslashit(ETHEME_CODE_URL).'js');
define('ETHEME_CODE_CSS_URL', trailingslashit(ETHEME_CODE_URL).'css');
define('CHILD_URL', get_stylesheet_directory_uri());
define('ETHEME_API', 'https://www.8theme.com/themes/api/');
define('ETHEME_BASE_URI', PARENT_URL .'/');

define('ET_CODE', 'framework/');
define('ET_CODE_3D', ET_CODE .'thirdparty/');
define('ET_BASE_URI', get_template_directory_uri() .'/');
define('ET_CODE_3D_URI', ET_BASE_URI.ET_CODE .'thirdparty/');

// add_editor_style();
add_action('after_setup_theme', 'etheme_theme_setup');
function etheme_theme_setup(){
	load_theme_textdomain( ETHEME_DOMAIN, get_template_directory() . '/languages' );

	$locale = get_locale();
	$locale_file = get_template_directory() . "/languages/$locale.php";
	if ( is_readable( $locale_file ) )
		require_once( $locale_file );

	add_theme_support( 'title-tag' );
}

add_action( 'admin_notices', 'legenda_core_is_inactive_notice', 8 );

function legenda_core_is_inactive_notice(){
	if ( !function_exists('legenda_theme_old')) {
		echo '<div class="wrap">
			<div class="et-message et-error">
				<p>'. sprintf( esc_html__('%1s Install and activate %2s plugin to use full theme functionality.', 'legenda'), '<b>'.esc_html__('IMPORTANT:', 'legenda').'</b>', '<a href="'.admin_url( 'plugins.php' ).'">'.esc_html__('Legenda Core', 'legenda').'</a>') . '</p>
			</div>
		</div>';
	}
}

if ( get_option('option_tree') && (!get_option( 'legenda_theme_migrated', false ) || isset($_GET['legenda_theme_migrate_options'])) ) {

	if ( class_exists('ReduxFramework') ) {
		if (isset($_GET['legenda_theme_migrate_options'])) {
			require_once( trailingslashit(ETHEME_CODE_DIR). 'migrator.php' );
			new Etheme_Legenda_Options_Migrator();
		}
		else {
			add_action( 'admin_notices', function() {

				echo '<div class="wrap">
		                <div class="et-message et-info notice">
		                    '.sprintf(esc_html__('%1s %2s To finish migration from Option Tree (old Theme Options) to Redux Framework (new Theme Options) we have to update your database to the newest version. %3s %4s', 'legenda'), '<strong>'.esc_html__('Legenda database update required.', 'legenda').'</strong><br/>', '<p>', '</p>', '<a href="'.add_query_arg( 'legenda_theme_migrate_options', 'true',  admin_url() ) . '" class="etheme-migrator et-button et-button-green">' . esc_html__('Please update now', 'legenda') . '</a>') . '
		                </div>
		            </div>
		        ';
			}, 10 );
		}
	}
	else {
		add_action( 'admin_notices', function() {
			echo '<div class="wrap">
				<div class="et-message et-error">
					<p>'.sprintf(esc_html__('%1s Install and activate %2s plugin to use Theme Options.', 'legenda'), '<b>'.esc_html__('IMPORTANT:', 'legenda').'</b>', '<a href="'.admin_url( 'themes.php?page=install-required-plugins&plugin_status=install' ).'"><b>'.esc_html__('Redux Framework', 'legenda').'</b></a>') . '</p>
				</div>
			</div>';
		}, 10);
	}
}

// **********************************************************************// 
// ! Notice "Plugin version"
// **********************************************************************// 
add_action( 'admin_notices', 'etheme_required_core_notice', 50 );

function etheme_required_core_notice(){
	$file = ABSPATH . 'wp-content/plugins/legenda-core/legenda-core.php';

	if ( ! file_exists($file) ) return;

	$plugin = get_plugin_data( $file, false, false );

	if ( version_compare( '1.0.5', $plugin['Version'], '>' ) ) {
		echo '
		<div class="et-message et-error">
			<p>This theme version requires the following plugin <strong>Legenda Core</strong> to be updated up to 1.0.5 version</p>
		</div>
	';
	}
}
require_once( trailingslashit( ETHEME_CODE_DIR) . 'system-requirements.php' );
require_once( trailingslashit(ETHEME_CODE_DIR). 'options.php' );
require_once( trailingslashit(ETHEME_CODE_DIR). 'inc/taxonomy-metadata.php' );
require_once( trailingslashit(ETHEME_CODE_DIR). 'theme-functions.php' );
require_once( trailingslashit(ETHEME_CODE_DIR). 'custom-styles.php' );
require_once( trailingslashit(ETHEME_CODE_DIR). 'shortcodes.php' );
if(class_exists('WooCommerce'))
	require_once( trailingslashit(ETHEME_CODE_DIR). 'woo.php' );

if( etheme_is_activated() ) {
	/* extension loader */
	require_once( trailingslashit(ETHEME_CODE_DIR). 'custom-metaboxes.php');
}
require_once( apply_filters('etheme_file_url', trailingslashit(ETHEME_CODE_DIR) . 'thirdparty/options-framework/loader.php') );
require_once( trailingslashit(ETHEME_CODE_DIR). 'theme-options.php');

if ( file_exists(trailingslashit(ETHEME_CODE_DIR). 'thirdparty/envato_setup/envato_setup.php') ){
	require_once( trailingslashit(ETHEME_CODE_DIR). 'thirdparty/envato_setup/envato_setup.php' );
}

if ( is_admin() ) {
    require_once( trailingslashit(ETHEME_CODE_DIR) . 'plugins.php' );
    require_once( get_template_directory() . '/framework/thirdparty/menu-images/nav-menu-images.php');
	require_once( trailingslashit(ETHEME_CODE_DIR). 'version-check.php');
}

if ( etheme_get_option( 'enable_portfolio' ) ) {
	require_once( trailingslashit(ETHEME_CODE_DIR). 'portfolio.php' );
}

require_once( trailingslashit(ETHEME_CODE_DIR). 'panel/panel.php');

require_once( get_template_directory() . '/framework/walkers.php' );

require_once( apply_filters('etheme_file_url', ETHEME_CODE_DIR . '/admin/widgets/class-admin-sidebasr.php') );

require_once( get_template_directory() . '/framework/class-account-register-ajax.php' );