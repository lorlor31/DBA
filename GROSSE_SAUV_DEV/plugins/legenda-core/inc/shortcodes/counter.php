<?php 

	if ( ! function_exists( 'etheme_counter_shortcode' ) ) :
	function etheme_counter_shortcode($atts, $content = null) {
	    $a = shortcode_atts( array(
	        'init_value' => 1,
	        'final_value' => 100,
	        'class' => ''
	    ), $atts);

	    return '<span id="animatedCounter" class="animated-counter '.$a['class'].'" data-value='.$a['final_value'].'>'.$a['init_value'].'</span>';
	}
	endif;

?>