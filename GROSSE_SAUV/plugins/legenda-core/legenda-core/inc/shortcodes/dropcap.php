<?php 
	if ( ! function_exists( 'etheme_dropcap_shortcode' ) ) :
		function etheme_dropcap_shortcode($atts,$content=null){
		    $a = shortcode_atts( array(
		       'style' => ''
		   ), $atts );

		    return '<span class="dropcap ' . $a['style'] . '">' . $content . '</span>';
		}
	endif;

	
?>