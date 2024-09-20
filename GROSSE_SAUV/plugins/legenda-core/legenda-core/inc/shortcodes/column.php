<?php 

	if ( ! function_exists( 'etheme_column_shortcode' ) ) :
		function etheme_column_shortcode($atts, $content = null) {
		    $a = shortcode_atts( array(
		        'size' => 'one_half',
		        'class' => '',
		    ), $atts);
		    switch($a['size']) {
		        case 'one-half':
		            $class = 'span6 ';
		        break;
		        case 'one-third':
		            $class = 'span4 ';
		        break;
		        case 'two-third':
		            $class = 'span8 ';
		        break;
		        case 'one-fourth':
		            $class = 'span3 ';
		        break;
		        case 'three-fourth':
		            $class = 'span9 ';
		        break;
		        default:
		            $class = $a['size'];
		        }

		        $class .= ' '.$a['class'];

		        return '<div class="' . $class . '">' . do_shortcode($content) . '</div>';
		}
	endif;


?>