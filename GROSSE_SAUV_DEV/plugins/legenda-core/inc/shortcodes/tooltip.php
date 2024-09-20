<?php  

	if ( ! function_exists( 'etheme_tooltip_shortcode' ) ) :
		function etheme_tooltip_shortcode($atts,$content=null){
		    $a = shortcode_atts( array(
		       'position' => 'top',
		       'text' => '',
		       'class' => '',
		       'link' => '#'
		   ), $atts );

		    return '<a href="'.$a['link'].'" class="'.$a['class'].'" rel="tooltip" data-placement="'.$a['position'].'" data-original-title="'.$a['text'].'">'.$content.'</a>';
		}
	endif;

	
?>