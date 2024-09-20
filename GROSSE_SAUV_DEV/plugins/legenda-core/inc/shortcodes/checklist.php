<?php 

	if ( ! function_exists( 'etheme_checklist_shortcode' ) ) :
		function etheme_checklist_shortcode($atts, $content = null) {
		    $a = shortcode_atts( array(
		        'style' => 'arrow'
		    ), $atts);
		    switch($a['style']) {
		        case 'arrow':
		            $class = 'arrow';
		        break;
		        case 'circle':
		            $class = 'circle';
		        break;
		        case 'star':
		            $class = 'star';
		        break;
		        case 'square':
		            $class = 'square';
		        break;
		        case 'dash':
		            $class = 'dash';
		        break;
		        default:
		            $class = 'arrow';
		    }
		    return '<div class="list list-' . $class . '">' . do_shortcode($content) . '</div	>';
		}
	endif;


?>