<?php
// Plugins activation
require_once('plugins/class-tgm-plugin-activation.php');
global $pagenow;

if ($pagenow!='plugins.php'){
	add_action('tgmpa_register', 'etheme_register_required_plugins');
}

function etheme_register_required_plugins() {
	if( ! etheme_is_activated() ) return;
	$activated_data = get_option( 'etheme_activated_data' );
	$key = $activated_data['api_key'];
	if( ! $key || empty( $key ) ) return;

	$plugins = get_transient( 'etheme_plugins_info' );
	if (! $plugins || empty( $plugins ) || isset($_GET['et_clear_plugins_transient'])){
		$plugins_dir = ETHEME_API . 'files/get/';
		$token = '?token=' . $key;
		$url = 'https://8theme.com/import/xstore-demos/1/plugins/?plugins_dir=' . $plugins_dir . '&token=' .$token . '&project=legenda';
		$response = wp_remote_get( $url );
		$response = json_decode(wp_remote_retrieve_body( $response ), true);
		$plugins = $response;
		set_transient( 'etheme_plugins_info', $plugins, 24 * HOUR_IN_SECONDS );
	}

	if ( ! $plugins || ! is_array($plugins) || ! count($plugins) ){
		$plugins = array();
	}

	// Change this to your theme text domain, used for internationalising strings

	/**
	 * Array of configuration settings. Amend each line as needed.
	 * If you want the default strings to be available under your own theme domain,
	 * leave the strings uncommented.
	 * Some of the strings are added into a sprintf, so see the comments at the
	 * end of each line for what each argument will be.
	 */
	$config = array(
		'domain'       		=> 'legenda',         	// Text domain - likely want to be the same as your theme.
		'default_path' 		=> '',                         	// Default absolute path to pre-packaged plugins
		'menu_slug' 	=> 'themes.php', 				// Default parent menu slug
		'parent_slug' 	=> 'themes.php', 				// Default parent URL slug
		'menu'         		=> 'install-required-plugins', 	// Menu slug
		'has_notices'      	=> true,                       	// Show admin notices or not
		'is_automatic'    	=> true,					   	// Automatically activate plugins after installation or not
		'message' 			=> '',							// Message to output right before the plugins table
		'strings'      		=> array(
			'page_title'                       			=> esc_html__( 'Install Required Plugins', 'legenda'),
			'menu_title'                       			=> esc_html__( 'Install Plugins', 'legenda' ),
			'installing'                       			=> esc_html__( 'Installing Plugin: %s', 'legenda' ), // %1$s = plugin name
			'oops'                             			=> esc_html__( 'Something went wrong with the plugin API.', 'legenda' ),
			'notice_can_install_required'     			=> _n_noop( 'This theme requires the following plugin: %1$s.', 'This theme requires the following plugins: %1$s.', 'legenda' ), // %1$s = plugin name(s)
			'notice_can_install_recommended'			=> _n_noop( 'This theme recommends the following plugin: %1$s.', 'This theme recommends the following plugins: %1$s.', 'legenda' ), // %1$s = plugin name(s)
			'notice_cannot_install'  					=> _n_noop( 'Sorry, but you do not have the correct permissions to install the %s plugin. Contact the administrator of this site for help on getting the plugin installed.', 'Sorry, but you do not have the correct permissions to install the %s plugins. Contact the administrator of this site for help on getting the plugins installed.', 'legenda' ), // %1$s = plugin name(s)
			'notice_can_activate_required'    			=> _n_noop( 'The following required plugin is currently inactive: %1$s.', 'The following required plugins are currently inactive: %1$s.', 'legenda' ), // %1$s = plugin name(s)
			'notice_can_activate_recommended'			=> _n_noop( 'The following recommended plugin is currently inactive: %1$s.', 'The following recommended plugins are currently inactive: %1$s.', 'legenda' ), // %1$s = plugin name(s)
			'notice_cannot_activate' 					=> _n_noop( 'Sorry, but you do not have the correct permissions to activate the %s plugin. Contact the administrator of this site for help on getting the plugin activated.', 'Sorry, but you do not have the correct permissions to activate the %s plugins. Contact the administrator of this site for help on getting the plugins activated.', 'legenda' ), // %1$s = plugin name(s)
			'notice_ask_to_update' 						=> _n_noop( 'The following plugin needs to be updated to its latest version to ensure maximum compatibility with this theme: %1$s.', 'The following plugins need to be updated to their latest version to ensure maximum compatibility with this theme: %1$s.', 'legenda' ), // %1$s = plugin name(s)
			'notice_cannot_update' 						=> _n_noop( 'Sorry, but you do not have the correct permissions to update the %s plugin. Contact the administrator of this site for help on getting the plugin updated.', 'Sorry, but you do not have the correct permissions to update the %s plugins. Contact the administrator of this site for help on getting the plugins updated.', 'legenda' ), // %1$s = plugin name(s)
			'install_link' 					  			=> _n_noop( 'Begin installing plugin', 'Begin installing plugins', 'legenda' ),
			'activate_link' 				  			=> _n_noop( 'Activate installed plugin', 'Activate installed plugins', 'legenda' ),
			'return'                           			=> esc_html__( 'Return to Required Plugins Installer', 'legenda' ),
			'plugin_activated'                 			=> esc_html__( 'Plugin activated successfully.', 'legenda' ),
			'complete' 									=> esc_html__( 'All plugins installed and activated successfully. %s', 'legenda' ), // %1$s = dashboard link
			'nag_type'									=> 'updated' // Determines admin notice type - can only be 'updated' or 'error'
		)
	);

	tgmpa($plugins, $config);
}
