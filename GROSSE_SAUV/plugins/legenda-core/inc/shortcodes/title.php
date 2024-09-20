<?php 

	if ( ! function_exists( 'etheme_title_shortcode' ) ) :
	function etheme_title_shortcode($atts, $content = null) {
	    $a = shortcode_atts( array(
	        'heading' => '2',
	        'subtitle' => '',
	        'align' => 'center',
	        'subtitle' => '',
	        'line' => 1
	    ), $atts);
	    $subtitle = '';
	    $class = 'title';
	    $class .= ' a-'.$a['align'];
	    if(!$a['line']) {
	        $class .= ' without-line';
	    }
	    if($a['subtitle'] != '') {
	        $class .= ' with-subtitle';
	        $subtitle = '<span class="subtitle a-'.$a['align'].'">'.$a['subtitle'].'</span>';
	    }

	    return '<h'.$a['heading'].' class="'.$class.'"><span>'.$content.'</span></h'.$a['heading'].'>'.$subtitle;
	}
	endif;
	
?>