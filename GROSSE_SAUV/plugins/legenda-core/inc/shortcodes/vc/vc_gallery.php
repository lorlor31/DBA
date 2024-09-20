<?php 
   $setting_vc_gallery = array(
	  "name" => __("Image Gallery", "legenda-core"),
	  "icon" => "icon-wpb-images-stack",
	  "category" => __('Content', 'legenda-core'),
	  "params" => array(
	    array(
	      "type" => "textfield",
	      "heading" => __("Widget title", "legenda-core"),
	      "param_name" => "title",
	      "description" => __("What text use as a widget title. Leave blank if no title is needed.", "legenda-core")
	    ),
	    array(
	      "type" => "dropdown",
	      "heading" => __("Gallery type", "legenda-core"),
	      "param_name" => "type",
	      "value" => array(__("Flex slider fade", "legenda-core") => "flexslider_fade", __("Flex slider slide", "legenda-core") => "flexslider_slide", __("Nivo slider", "legenda-core") => "nivo", __("Carousel", "legenda-core") => "carousel", __("Image grid", "legenda-core") => "image_grid"),
	      "description" => __("Select gallery type.", "legenda-core")
	    ),
	    array(
	      "type" => "dropdown",
	      "heading" => __("Auto rotate slides", "legenda-core"),
	      "param_name" => "interval",
	      "value" => array(3, 5, 10, 15, __("Disable", "legenda-core") => 0),
	      "description" => __("Auto rotate slides each X seconds.", "legenda-core"),
	      "dependency" => Array('element' => "type", 'value' => array('flexslider_fade', 'flexslider_slide', 'nivo'))
	    ),
	    array(
	      "type" => "attach_images",
	      "heading" => __("Images", "legenda-core"),
	      "param_name" => "images",
	      "value" => "",
	      "description" => __("Select images from media library.", "legenda-core")
	    ),
	    array(
	      "type" => "textfield",
	      "heading" => __("Image size", "legenda-core"),
	      "param_name" => "img_size",
	      "description" => __("Enter image size. Example: thumbnail, medium, large, full or other sizes defined by current theme. Alternatively enter image size in pixels: 200x100 (Width x Height). Leave empty to use 'thumbnail' size.", "legenda-core")
	    ),
	    array(
	      "type" => "dropdown",
	      "heading" => __("On click", "legenda-core"),
	      "param_name" => "onclick",
	      "value" => array(__("Open prettyPhoto", "legenda-core") => "link_image", __("Do nothing", "legenda-core") => "link_no", __("Open custom link", "legenda-core") => "custom_link"),
	      "description" => __("What to do when slide is clicked?", "legenda-core")
	    ),
	    array(
	      "type" => "exploded_textarea",
	      "heading" => __("Custom links", "legenda-core"),
	      "param_name" => "custom_links",
	      "description" => __('Enter links for each slide here. Divide links with linebreaks (Enter).', 'legenda-core'),
	      "dependency" => Array('element' => "onclick", 'value' => array('custom_link'))
	    ),
	    array(
	      "type" => "dropdown",
	      "heading" => __("Custom link target", "legenda-core"),
	      "param_name" => "custom_links_target",
	      "description" => __('Select where to open  custom links.', 'legenda-core'),
	      "dependency" => Array('element' => "onclick", 'value' => array('custom_link')),
	      'value' => $target_arr
	    ),
	    array(
	      "type" => "textfield",
	      "heading" => __("Extra class name", "legenda-core"),
	      "param_name" => "el_class",
	      "description" => __("If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", "legenda-core")
	    )
	  )
	);
    
    vc_map_update('vc_gallery', $setting_vc_gallery);
    
    function vc_theme_vc_gallery($atts, $content = null) {
      $output = $title = $type = $onclick = $custom_links = $img_size = $custom_links_target = $images = $el_class = $interval = '';
      extract(shortcode_atts(array(
          'title' => '',
          'type' => 'flexslider',
          'onclick' => 'link_image',
          'custom_links' => '',
          'custom_links_target' => '',
          'img_size' => 'thumbnail',
          'images' => '',
          'el_class' => '',
          'interval' => '5',
      ), $atts));
      $gal_images = '';
      $link_start = '';
      $link_end = '';
      $el_start = '';
      $el_end = '';
      $slides_wrap_start = '';
      $slides_wrap_end = '';
      $rand = rand(1000,9999);

      $el_class = ' '.$el_class;

      if ( $type == 'nivo' ) {
          $type = ' wpb_slider_nivo theme-default';
          wp_enqueue_script( 'nivo-slider' );
          wp_enqueue_style( 'nivo-slider-css' );
          wp_enqueue_style( 'nivo-slider-theme' );

          $slides_wrap_start = '<div class="nivoSlider">';
          $slides_wrap_end = '</div>';
      } else if ( $type == 'flexslider' || $type == 'flexslider_fade' || $type == 'flexslider_slide' || $type == 'fading' ) {
          $el_start = '<li>';
          $el_end = '</li>';
          $slides_wrap_start = '<ul class="slides">';
          $slides_wrap_end = '</ul>';
      } else if ( $type == 'image_grid' ) {
          wp_enqueue_script( 'isotope' );

          $el_start = '<li class="isotope-item">';
          $el_end = '</li>';
          $slides_wrap_start = '<ul class="wpb_image_grid_ul">';
          $slides_wrap_end = '</ul>';
      } else if ( $type == 'carousel' ) {

          $el_start = '<li class="">';
          $el_end = '</li>';
          $slides_wrap_start = '<ul class="images-carousel carousel-'.$rand.'">';
          $slides_wrap_end = '</ul>';
      }

      $flex_fx = '';
      $flex = false;
      if ( $type == 'flexslider' || $type == 'flexslider_fade' || $type == 'fading' ) {
          $flex = true;
          $type = ' wpb_flexslider'.$rand.' flexslider_fade flexslider';
          $flex_fx = ' data-flex_fx="fade"';
      } else if ( $type == 'flexslider_slide' ) {
          $flex = true;
          $type = ' wpb_flexslider'.$rand.' flexslider_slide flexslider';
          $flex_fx = ' data-flex_fx="slide"';
      } else if ( $type == 'image_grid' ) {
          $type = ' wpb_image_grid';
      }


      /*
       else if ( $type == 'fading' ) {
          $type = ' wpb_slider_fading';
          $el_start = '<li>';
          $el_end = '</li>';
          $slides_wrap_start = '<ul class="slides">';
          $slides_wrap_end = '</ul>';
          wp_enqueue_script( 'cycle' );
      }*/

      //if ( $images == '' ) return null;
      if ( $images == '' ) $images = '-1,-2,-3';

      $pretty_rel_random = 'rel-'.rand();

      if ( $onclick == 'custom_link' ) { $custom_links = explode( ',', $custom_links); }
      $images = explode( ',', $images);
      $i = -1;

      foreach ( $images as $attach_id ) {
          $i++;
          if ($attach_id > 0) {
              $post_thumbnail = wpb_getImageBySize(array( 'attach_id' => $attach_id, 'thumb_size' => $img_size ));
          }
          else {
              $different_kitten = 400 + $i;
              $post_thumbnail = array();
              $post_thumbnail['thumbnail'] = '<img src="http://placekitten.com/g/'.$different_kitten.'/300" />';
              $post_thumbnail['p_img_large'][0] = 'http://placekitten.com/g/1024/768';
          }

          $thumbnail = $post_thumbnail['thumbnail'];
          $p_img_large = $post_thumbnail['p_img_large'];
          $link_start = $link_end = '';

          	$image_alt = get_post_meta( $attach_id, '_wp_attachment_image_alt', true);
			if ( empty( $image_alt ) ) {
				$image_alt = '';
				$attachment = get_post( $attach_id );
				if ( ! empty($attachment) ) {
					$image_alt = trim( strip_tags( $attachment->post_title ) );
				}
			}

          if ( $onclick == 'link_image' ) {
	          if ( is_array($p_img_large) ) {
		          $link_start = '<a rel="lightboxGall" href="' . $p_img_large[0] . '" alt="' . $image_alt . '">';
		          $link_end   = '</a>';
	          }
          }
          else if ( $onclick == 'custom_link' && isset( $custom_links[$i] ) && $custom_links[$i] != '' ) {
              $link_start = '<a href="'.$custom_links[$i].'"' . (!empty($custom_links_target) ? ' target="'.$custom_links_target.'"' : '') . '>';
              $link_end = '</a>';
          }
          $gal_images .= $el_start . $link_start . $thumbnail . $link_end . $el_end;
      }
      $css_class =  apply_filters(VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, 'wpb_gallery wpb_content_element'.$el_class.' clearfix');
      $output .= "\n\t".'<div class="'.$css_class.'">';
      $output .= "\n\t\t".'<div class="wpb_wrapper">';
      $output .= wpb_widget_title(array('title' => $title, 'extraclass' => 'wpb_gallery_heading'));
      $output .= '<div class="wpb_gallery_slides'.$type.'" data-interval="'.$interval.'"'.$flex_fx.'>'.$slides_wrap_start.$gal_images.$slides_wrap_end.'</div>';
      $output .= "\n\t\t".'</div> ';
      $output .= "\n\t".'</div> ';

      if ( $flex ) {
          $output .= '<script type="text/javascript">';
          $output .= 'jQuery(document).ready(function(){';
          $output .= 'var sliderElement = jQuery(".wpb_flexslider'.$rand.'");';
            $output .= 'var sliderSpeed = 800,';
                $output .= "sliderTimeout = parseInt(sliderElement.attr('data-interval'))*1000,";
                $output .= "sliderFx = sliderElement.attr('data-flex_fx'),";
                $output .= "slideshow = true;";
            $output .= "if ( sliderTimeout == 0 ) slideshow = false;";

            $output .= "sliderElement.flexslider({";
                $output .= "animation: sliderFx,";
                $output .= "slideshow: slideshow,";
                $output .= "slideshowSpeed: sliderTimeout,";
                $output .= "sliderSpeed: sliderSpeed,";
                $output .= "smoothHeight: false";
            $output .= "});";
            $output .= "});";
          $output .= "</script>";
      }
      
      if( $type == 'carousel' ) {
      		   $items = '[[0, 1], [479,2], [619,2], [768,4],  [1200, 4], [1600, 4]]';
	           $output .=  '<script type="text/javascript">';
	           //$output .=  '     jQuery(".images-carousel").etFullWidth();';
	           $output .=  '     jQuery(".carousel-'.$rand.'").owlCarousel({';
	           $output .=  '         items:4, ';
	           $output .=  '         nav: true,';
	           $output .=  '         navText:["",""],';
	           $output .=  '         rewindNav: false,';
	           $output .=  '         dots: false,';
	           $output .=  '         itemsCustom: '.$items.'';
	           $output .=  '    });';
	
	           $output .=  ' </script>';
      }

      return $output;
    }
?>