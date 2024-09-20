<?php 

	if ( ! function_exists( 'etheme_tabs_shortcode' ) ) :
	function etheme_tabs_shortcode($atts, $content = null) {
	    $a = shortcode_atts(array(
	        'class' => ''
	    ), $atts);
	    return '<div class="tabs '.$a['class'].'">' . do_shortcode($content) . '</div>';
	}
	endif;


?>