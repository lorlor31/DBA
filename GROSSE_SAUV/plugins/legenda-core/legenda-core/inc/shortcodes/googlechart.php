<?php 

	if ( ! function_exists( 'etheme_googlechart_shortcode' ) ) :
		function etheme_googlechart_shortcode($atts, $content = null) {
		$a = shortcode_atts(array(
		        'title' => '',
		        'labels' => '',
		        'data' => '',
		        'type' => 'pie2d',
		        'data_colours' => ''
		    ), $atts);

		    switch($a['type']) {
		        case 'pie':
		            $type = 'p3';
		        break;
		        case 'pie2d':
		            $type = 'p';
		        break;
		        case 'line':
		            $type = 'lc';
		        break;
		        case 'xyline':
		            $type = 'lxy';
		        break;
		        case 'scatter':
		            $type = 's';
		        break;
		    }

		    $output = '';
		    if ($a['title'] != '') $output = '<h2>'. $a['title'] .'</h2>';
		    $output .= '<div class="googlechart">';
		    $output .= '<img src="http://chart.apis.google.com/chart?cht='.$type.'&chd=t:'.$a['data'].'&chtt=&chl='.$a['labels'].'&chs=600x250&chf=bg,s,65432100&chco='.$a['data_colours'].'" />';
		    $output .= '</div>';
		    return $output;
		}
	endif;

	
?>