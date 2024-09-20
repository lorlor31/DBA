<?php 
	if ( ! function_exists( 'etheme_template_url_shortcode' ) ) :
		function etheme_template_url_shortcode(){
		    return get_template_directory_uri();
		}
	endif;
	
?>