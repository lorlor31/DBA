<?php 
	if ( ! function_exists( 'etheme_gmaps_shortcode' ) ) :
		function etheme_gmaps_shortcode($atts, $content = null) {
		    $a = shortcode_atts(array(
		            'title' => '',
		            'address' => 'London',
		            'height' => 400,
		            'width' => 800,
		            'type' => 'roadmap',
		            'zoom' => 14,
		            'api' => ''
		        ), $atts);
		        if ($a['address'] == '') return;
		        $rand = rand(100,1000);
		        $api = ( ! empty( $a['api'] ) ) ? $a['api'] : etheme_option( 'google_map_api' );
		        wp_enqueue_script( 'google.maps', 'http://maps.google.com/maps/api/js?key=' . $api . '' );
		        wp_enqueue_script( 'gmap', get_template_directory_uri().'/js/libs/jquery.gmap.min.js' );

		        $output = '';

		        if ($a['title'] != '') $output = '<h2>'. $a['title'] .'</h2>';

		        $output .= '<div id="map'.$rand.'" style="height:'.$a['height'].'px;" class="gmap">'."\r\n";
		        $output .= '<p>Enable your JavaScript!</p>';
		        $output .= '</div>';
		        $output .= '<script type="text/javascript">'."\r\n";
		        $output .= 'jQuery(document).ready(function(){'."\r\n";
		        $output .= 'var $map = jQuery("#map'.$rand.'");'."\r\n";
		        $output .= 'if( $map.length ) {'."\r\n";
		        $output .= '$map.gMap({'."\r\n";
		        $output .= 'address: "'.$a['address'].'",'."\r\n";
		        $output .= 'maptype: "'.$a['type'].'",'."\r\n";
		        $output .= 'zoom: '.$a['zoom'].','."\r\n";
		        $output .= 'markers: [';
		        $output .= '{ "address" : "'.$a['address'].'" }'."\r\n";
		        $output .= ']'."\r\n";
		        $output .= '});'."\r\n";
		        $output .= '}'."\r\n";
		        $output .= '});'."\r\n";
		        $output .= '</script>';

		        return $output;
		}
	endif;