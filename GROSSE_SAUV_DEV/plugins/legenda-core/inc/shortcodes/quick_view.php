<?php 

if ( ! function_exists( 'etheme_quick_view_shortcodes' ) ) {
	function etheme_quick_view_shortcodes($atts, $content=null){
	    extract(shortcode_atts(array(
	        'id' => '',
	        'class' => ''
	    ), $atts));
	    return '<div class="show-quickly-btn '.$class.'" data-prodid="'.$id.'">'. do_shortcode($content) .'</div>';
	}
}

?>