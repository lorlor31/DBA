<?php 

	function etheme_portfolio_grid_shortcode() {
		$a = shortcode_atts( array(
	       'categories' => '',
	       'limit' => -1,
	   		'show_pagination' => 1
	   ), $atts );
	   
	   
	   return function_exists('get_etheme_portfolio') ? get_etheme_portfolio($a['categories'], $a['limit'], $a['show_pagination']) : esc_html__('Please, active legenda theme to use this shortcode', 'legenda-core');
	    
	}

?>