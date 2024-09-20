<?php 
	
	if ( ! function_exists( 'etheme_row_shortcode' ) ) :
		function etheme_row_shortcode($atts, $content = null) {
		    $a = shortcode_atts( array(
		        'class' => '',
		        'fluid' => 1
		    ), $atts);

		    $class = '';

		    if ($a['fluid'] == 1) {
		        $class = '-fluid';
		    }
		    return '<div class="row'.$class . ' ' . $a['class'] . '">' . do_shortcode($content) . '</div>';
		}
	endif;


?>