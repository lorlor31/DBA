<?php 
	$shortcodes = array(
		'portfolio',
		'portfolio_grid',
	);

	if ( function_exists('etheme_get_option') && etheme_get_option( 'enable_portfolio' ) ) {

		foreach ($shortcodes as $key) {
			require_once( 'portfolio/'.$key.'.php' );
		}

	}
?>