<?php 

	if ( ! function_exists( 'etheme_blockquote_shortcode' ) ) :
		function etheme_blockquote_shortcode($atts, $content = null) {
		    $a = shortcode_atts( array(
		        'align' => 'left',
		        'class' => ''
		    ), $atts);
		    switch($a['align']) {

		        case 'right':
		            $align = 'fl-r';
		        break;
		        case 'center':
		            $align = 'fl-none';
		        break;
		        default:
		            $align = 'fl-l';
		    }
		    $content = wpautop(trim($content));
		    return '<blockquote class="' . $align .' '. $a['class'] . '">' . $content . '</blockquote>';
		}
	endif;

?>