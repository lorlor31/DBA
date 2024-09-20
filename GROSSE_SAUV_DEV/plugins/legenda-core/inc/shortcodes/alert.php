<?php 
	
	if ( ! function_exists( 'etheme_alert_shortcode' ) ) :
		function etheme_alert_shortcode($atts, $content = null) {
		    $a = shortcode_atts( array(
		        'type' => 'success',
		        'title' => '',
		        'close' => 1
		    ), $atts);
		    switch($a['type']) {
		        case 'error':
		            $class = 'error';
		        break;
		        case 'success':
		            $class = 'success';
		        break;
		        case 'info':
		            $class = 'info';
		        break;
		        case 'warning':
		            $class = 'warning';
		        break;
		        default:
		            $class = 'success';
		    }
		    $closeBtn = '';
		    $title = '';
		    if($a['close'] == 1){
		        $closeBtn = '<span class="close-parent">close</span>';
		    }
		    if($a['title'] != '') {
		        $title = '<span class="h3">' . $a['title'] . '</span>';
		    }

		    return '<p class="' . $class . '">' . $title . do_shortcode($content) . $closeBtn . '</p>';
		}
	endif;
	
?>