<?php 

	if ( ! function_exists( 'etheme_et_section_shortcode' ) ) :
		function etheme_et_section_shortcode($atts, $content = null) {
		    $a = shortcode_atts( array(
		        'el_class' => '',
		        'color_sceheme' => '',
		        'section_border' => '',
		        'padding' => '',
		        'color' => '',
		        'content' => '',
		        'parallax' => 0,
		        'parallax_speed' => 0.05,
		        'video_poster' => '',
		        'video_mp4' => '',
		        'video_webm' => '',
		        'video_ogv' => '',
		        'img' => '',
		        'img_src' => '',
		        'mt' => '',
		        'mb' => '',
		        'pt' => '',
		        'pb' => ''

		    ), $atts);


		    $src = '';
		    $style = 'style="';
		    $video = '';

		    if($a['img'] != '') {
		        $src = wp_get_attachment_image_url( $a['img'], 'medium' );
		    }elseif ($a['img_src'] != '') {
		        $src = do_shortcode($a['img_src']);
		    }

		    if ($src != '') {
		        $style .= 'background-image: url('.$src.');';
		    }
		    if ($a['color'] != '') {
		        $style .= 'background-color: '.$a['color'].';';
		    }

		    if ($a['content'] != '') {
		        $content = $a['content'];
		    }

		    $class = '';

		    if ($a['parallax']) {
		        $class .= 'parallax-section';
		    }

		    if($a['mt'] != '') {
		        $style .= 'margin-top: '.$a['mt'].'px;';
		    }

		    if($a['mb'] != '') {
		        $style .= 'margin-bottom: '.$a['mb'].'px;';
		    }

		    if($a['pt'] != '') {
		        $style .= 'padding-top: '.$a['pt'].'px;';
		    }

		    if($a['pb'] != '') {
		        $style .= 'padding-bottom: '.$a['pb'].'px;';
		    }

		    $style .= '"';
		    $data = '';

		    if ($a['parallax_speed'] != '') {
		      $data = 'data-parallax-speed="'.$a['parallax_speed'].'"';
		    }

		    if($a['video_mp4'] != '' || $a['video_webm'] != '' || $a['video_ogv'] != '') {
		        if($a['video_poster'] != '') {
		            $video_poster = wp_get_attachment_image_url( $a['video_poster'], 'large' );
		            $video .= '
		                <div class="section-video-poster" style="background-image: url('.$video_poster.')"></div>
		            ';
		        }

		        $video .= '
		        <div class="section-back-video hidden-tablet hidden-phone">
		            <video autoplay="autoplay" loop="loop" muted="muted" style="" class="et-section-video">
		                <source src="'.$a['video_mp4'].'" type="video/mp4">
		                <source src="'.$a['video_ogv'].'" type="video/ogv">
		                <source src="'.$a['video_webm'].'" type="video/webm">
		            </video>
		        </div>
		        <div class="section-video-mask"></div>
		        ';
		    }



		    return '<div class="et_section '.$a['padding'].' '.$a['section_border'].' color-scheme-'.$a['color_sceheme'].' '.$class . ' ' . $a['el_class'] . '" '. $style . $data .'>'.$video.'<div class="container">' . do_shortcode($content) . '</div></div>';
		}
	endif;

?>