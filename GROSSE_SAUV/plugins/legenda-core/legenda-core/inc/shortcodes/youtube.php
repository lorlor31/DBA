<?php 
	if ( ! function_exists( 'etheme_youtube_shortcode' ) ) :
		function etheme_youtube_shortcode($atts, $content = null) {
		$a = shortcode_atts(array(
		        'src' => '',
		        'height' => '500',
		        'width' => '900'
		    ), $atts);
		    if ($a['src'] == '') return;
		    return '<div class="youtube-video" style="width=:' . $a['width'] . 'px; height:' . $a['height'] . 'px;"><iframe width="' . $a['width'] . '" height="' . $a['height'] . '" src="' . $a['src'] . '" frameborder="0" allowfullscreen></iframe></div>';
		}
	endif;

?>