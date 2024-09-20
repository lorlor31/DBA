<?php 

	if ( ! function_exists( 'etheme_toggle_shortcode' ) ) :
	function etheme_toggle_shortcode($atts, $content = null) {
	    global $tab_count;
	    $a = shortcode_atts(array(
	        'title' => 'Tab',
	        'class' => '',
	        'active' => 0
	    ), $atts);

	    $class = $a['class'];
	    $style = '';

	    $opener = '<div class="open-this">+</div>';

	    if ($a['active'] == 1)  {
	        $style = ' style="display: block;"';
	        $class .= 'opened';
	        $opener = '<div class="open-this">&ndash;</div>';
	    }

	    $tab_count++;

	    return '<div class="toggle-element ' . $class . '"><a href="#" class="toggle-title">' . $opener . $a['title'] . '</a><div class="toggle-content" ' . $style . '>' . do_shortcode($content) . '</div></div>';
	}
	endif;


?>