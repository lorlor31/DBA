<?php 

	if ( ! function_exists( 'etheme_hr_shortcode' ) ) :
		function etheme_hr_shortcode($atts) {
		    $a = shortcode_atts(array(
		        'class' => '',
		        'height' => ''
		    ),$atts);

		    return '<hr class="divider '.$a['class'].'" style="height:'.$a['height'].'px;"/>';
		}
	endif;

?>