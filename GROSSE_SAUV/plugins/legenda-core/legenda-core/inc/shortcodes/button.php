<?php 
	
	if ( ! function_exists( 'etheme_btn_shortcode' ) ) :
		function etheme_btn_shortcode($atts){
		    $target = '';
		    $a = shortcode_atts( array(
		       'title' => 'Button',
		       'url' => '#',
		       'icon' => '',
		       'target' => '',
		       'style' => ''
		   ), $atts );
		    $icon = '';
		    if($a['icon'] != '') {
		        $icon = '<i class="icon-'.$a['icon'].'"></i>';
		    }
		    if($a['target'] != '') {
		        $target = ' target="' . $a['target'] . '"';
		    }
		    return '<a class="button ' . $a['style'] . '" href="' . $a['url'] . '"' . $target . '><span>'. $icon . $a['title'] . '</span></a>';
		}
	endif;

?>