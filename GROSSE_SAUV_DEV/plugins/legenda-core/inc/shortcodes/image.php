<?php  
	if ( ! function_exists( 'etheme_image_shortcode' ) ) :
		function etheme_image_shortcode($atts, $content = null) {
		$a = shortcode_atts(array(
		        'src' => '',
		        'alt' => '',
		        'height' => '',
		        'width' => '',
		        'class' => ''
		    ), $atts);

		    return '<img src="'.$a['src'].'" alt="'.$a['alt'].'" height="'.$a['height'].'" width="'.$a['width'].'" class="'.$a['class'].'" />';
		}
	endif;

?>