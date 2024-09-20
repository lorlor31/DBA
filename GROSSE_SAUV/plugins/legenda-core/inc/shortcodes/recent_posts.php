<?php 

	if ( ! function_exists( 'etheme_recent_posts_shortcode' ) ) :
	function etheme_recent_posts_shortcode($atts){
			
	    $a = shortcode_atts( array(
	       'title' => 'Recent Posts',
	       'limit' => 10,
	       'cat' => '',
	       'imgwidth' => 300,
	       'imgheight' => 200,
	       'date' => 0,
	       'excerpt' => 0,
	       'more_link' => 1
	   ), $atts );


	    $args = array(
	        'post_type'             => 'post',
	        'ignore_sticky_posts'   => 1,
	        'no_found_rows'         => 1,
	        'posts_per_page'        => $a['limit'],
	        'cat'                   => $a['cat']
	    );

	    ob_start();
	    etheme_create_posts_slider( $args, $a['title'], $a['more_link'], $a['date'], $a['excerpt'], $a['imgwidth'], $a['imgheight'] );
	    $output = ob_get_contents();
	    ob_end_clean();

	    return $output;

	}
	endif;


?>