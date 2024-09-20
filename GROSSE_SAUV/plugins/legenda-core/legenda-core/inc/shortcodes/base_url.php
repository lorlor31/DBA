<?php 
	
	if ( ! function_exists( 'etheme_base_url_shortcode' ) ) :
		function etheme_base_url_shortcode(){
		    return home_url();
		}
	endif;

?>