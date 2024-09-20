<?php 

	if ( ! function_exists( 'etheme_toggle_block_shortcode' ) ) :
	function etheme_toggle_block_shortcode($atts, $content = null) {
	    $a = shortcode_atts(array(
	        'class' => ''
	    ), $atts);
	    return '<div class="toggle-block '.$a['class'].'">' . do_shortcode($content) . '</div>';
	}
	endif;

?>