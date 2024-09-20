<?php  
	if ( ! function_exists( 'etheme_googlefont_shortcode' ) ) :
		function etheme_googlefont_shortcode($atts, $content = null) {
			global $registerd_fonts;
			$registerd_fonts = is_array($registerd_fonts) ? $registerd_fonts : array();
		    $a = shortcode_atts(array(
		        'name' => 'Open Sans',
		        'size' => '',
		        'color' => '',
		        'class' => ''
		    ),$atts);
		    $google_name = str_replace(" ", "+", $a['name']);
		    if (!in_array($google_name, $registerd_fonts)) {
		    	$registerd_fonts[] = $google_name;
			    ?>
			    <link rel='stylesheet'  href='<?php echo et_http(); ?>fonts.googleapis.com/css?family=<?php echo $google_name; ?>' type='text/css' media='all' />
			    <?php
		    }

		    //wp_enqueue_style($google_name,"http://fonts.googleapis.com/css?family=".$google_name);
		    return '<span class="google-font '.$a['class'].'" style="font-family:'.$a['name'].'; color:'.$a['color'].'; font-size:'.$a['size'].'px;">'.do_shortcode($content).'</span>';
		}
	endif;


?>