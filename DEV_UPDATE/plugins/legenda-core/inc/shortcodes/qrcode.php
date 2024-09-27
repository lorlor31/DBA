<?php 
	if ( ! function_exists( 'etheme_qrcode_shortcode' ) ) :
		function etheme_qrcode_shortcode($atts, $content = null) {
		
		$a = shortcode_atts(array(
		        'size' => '128',
		        'self_link' => 0,
		        'title' => 'QR Code',
		        'lightbox' => 0,
		        'class' => ''
		    ), $atts);

		    return generate_qr_code($content,$a['title'],$a['size'],$a['class'],$a['self_link'],$a['lightbox']);
		}
	endif;

?>