<?php 
	
	if ( ! function_exists( 'etheme_share_shortcode' ) ) :
		function etheme_share_shortcode($atts, $content = null) {
			extract(shortcode_atts(array(
				'title'  => __('Social', 'legenda-core'),
				'text' => ''
			), $atts));
			global $post;
		    $html = '';
			$permalink = get_permalink($post->ID);
			$image = wp_get_attachment_image_url( get_post_thumbnail_id($post->ID), array( 150, 150 ) );
			$post_title = rawurlencode($post->post_title);
			if($text == '' && $post_title != '') {
				$text = $post_title;
			}
			if($title) $html .= '<span class="share-title">'.$title.'</span>';
			$html .= '
			   <ul class="etheme-social-icons">
		            <li class="share-facebook">
		                <a href="http://www.facebook.com/sharer.php?u='.$permalink.'&amp;images="'.$image.'" target="_blank"><span class="icon-facebook"></span></a>
		            </li>
		            <li class="share-twitter">
		                <a href="https://twitter.com/share?url='.$permalink.'&text='.$text.'" target="_blank"><span class="icon-twitter"></span></a>
		            </li>
		            <li class="share-email">
		                <a href="mailto:enteryour@addresshere.com?subject='.$text.'&amp;body='.__('Check this out: ', 'legenda-core').$permalink.'"><span class="icon-envelope"></span></a>
		            </li>
		            <li class="share-pintrest">
		                <a href="http://pinterest.com/pin/create/button/?url='.$permalink.'&amp;media='.$image.'&amp;description='.$post_title.'" target="_blank"><span class="icon-pinterest"></span></a>
		            </li>
		       </ul>
			';
			return $html;
		}
	endif;


?>