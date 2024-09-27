<?php 

	if ( ! function_exists( 'etheme_callto_shortcode' ) ) :
		function etheme_callto_shortcode($atts, $content = null) {
		    $a = shortcode_atts( array(
		        'btn' => '',
		        'style' => '',
		        'btn_position' => 'right',
		        'link' => ''
		    ), $atts);
		    $btn = '';
		    $class = '';
		    $btnClass = '';

		    if($a['style'] == 'filled') {
		        $btnClass = 'active filled';
		    } else if($a['style'] == 'dark') {
		        $btnClass = 'white';
		    }

		    if($a['btn'] != '') {
		        $btn = '<a href="'.$a['link'].'" class="button '.$btnClass.'">' . $a['btn'] . '</a>';
		    }

		    if($a['style'] != '') {
		        $class = 'style-'.$a['style'];
		    }

		    $output = '';

		    $output .= '<div class="cta-block '.$class.'"><div class="table-row">';
		        if($a['btn'] != '') {

		                if ($a['btn_position'] == 'left') {
		                    $output .= '<div class="table-cell button-left">'.$btn.'</div>';
		                }
		                $output .= '<div class="table-cell">'. do_shortcode($content) .'</div>';

		                if ($a['btn_position'] == 'right') {
		                    $output .= '<div class="table-cell button-right">'.$btn.'</div>';
		                }

		        } else{
		            $output .= '<div class="table-cell">'. do_shortcode($content) .'</div>';
		        }
		    $output .= '</div></div>';

		    return $output;
		}
	endif;


?>