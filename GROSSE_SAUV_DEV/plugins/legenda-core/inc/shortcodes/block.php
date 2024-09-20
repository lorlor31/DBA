<?php  
	if ( ! function_exists( 'etheme_block_shortcode' ) ) :
		function etheme_block_shortcode($atts) {
			
		    $a = shortcode_atts(array(
		        'class' => '',
		        'id' => ''
		    ),$atts);

		    return et_get_block($a['id']);
		}
	endif;

?>