<?php 

	function etheme_portfolio_shortcode($atts) {
		$a = shortcode_atts( array(
	       'title' => 'Recent Works',
	       'limit' => 12
	   ), $atts );
	   
	   
	   return function_exists('etheme_get_recent_portfolio') ? etheme_get_recent_portfolio($a['limit'], $a['title']) : esc_html__('Please, active legenda theme to use this shortcode', 'legenda-core');
	    
	}
	
?>