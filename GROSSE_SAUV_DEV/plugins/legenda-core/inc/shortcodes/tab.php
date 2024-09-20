<?php 

	if ( ! function_exists( 'etheme_tab_shortcode' ) ) :
		function etheme_tab_shortcode($atts, $content = null) {
		    global $tab_count;
		    $a = shortcode_atts(array(
		        'title' => 'Tab',
		        'class' => '',
		        'active' => 0
		    ), $atts);

		    $class = $a['class'];
		    $style = '';

		    if ($a['active'] == 1)  {
		        $style = ' style="display: block;"';
		        $class .= 'opened';
		    }

		    $tab_count++;

		    return '<a href="#tab_'.$tab_count.'" id="tab_'.$tab_count.'" class="tab-title ' . $class . '">' . $a['title'] . '</a><div id="content_tab_'.$tab_count.'" class="tab-content" ' . $style . '>' . do_shortcode($content) . '</div>';
		}
	endif;

	
 ?>