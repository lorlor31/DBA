<?php  

	if ( ! function_exists( 'etheme_banner_shortcode' ) ) :
		function etheme_banner_shortcode($atts, $content) {
			
		    $image = $mask = '';
		    $a = shortcode_atts(array(
		        'align'  => 'left',
		        'valign'  => 'top',
		        'class'  => '',
		        'link'  => '',
		        'hover'  => '',
		        'content'  => '',
		        'font_style'  => '',
		        'banner_style'  => '',
		        'img' => '',
		        'img_src' => '',
		        'img_size' => '270x170',
		        'css'=> ''
		    ), $atts);

		    $image = '';

		    $img_size = explode('x', $a['img_size']);

		    if (!empty($a['css'])) {
				$Design = explode("{", $a['css']);
				$Design = $Design[0];
				$Design = explode(".", $Design);
				$Design = $Design[1];
		    } else {
		    	$Design = '';
		    }

		    $width = isset( $img_size[0] ) ? $img_size[0] : 250;
		    $height = isset( $img_size[1] ) ? $img_size[1] : $width;
		    if($a['img'] != '') {
		        $image = new_etheme_get_image( $a['img'], array( $width, $height ) );
		    }

		    if ($a['banner_style'] != '') {
		      $a['class'] .= ' style-'.$a['banner_style'];
		    }

		    if ($a['align'] != '') {
		      $a['class'] .= ' align-'.$a['align'];
		    }

		    if ($a['valign'] != '') {
		      $a['class'] .= ' valign-'.$a['valign'];
		    }

		    $onclick = '';
		    if($a['link'] != '') {
		        $a['class'] .= ' cursor-pointer';
		        $onclick = 'onclick="window.location=\''.$a['link'].'\'"';
		    }

		    return '<div class="banner '.$a['class'].' banner-font-'.$a['font_style'].' hover-'.$a['hover'].' '.$Design.'" '.$onclick.'><div class="banner-content"><div class="banner-inner">'.do_shortcode($content).'</div></div>' . $image . '</div>';
		}
	endif;

	if ( !function_exists('etheme_register_banner') ) {

		function etheme_register_banner () {

			if(!function_exists('vc_map')) {
				return;
			}

		    $banner_params = array(
		      'name' => 'Banner',
		      'base' => 'banner',
		      'icon' => 'icon-wpb-etheme',
		      'category' => 'Eight Theme',
		      'params' => array(
		        array(
		          'type' => 'attach_image',
		          "heading" => __("Banner Image", 'legenda-core'),
		          "param_name" => "img"
		        ),
		        array(
		          "type" => "textfield",
		          "heading" => __("Banner size", "js_composer"),
		          "param_name" => "img_size",
		          "description" => __("Enter image size. Example in pixels: 200x100 (Width x Height).", "js_composer")
		        ),
		        array(
		          "type" => "textfield",
		          "heading" => __("Link", "js_composer"),
		          "param_name" => "link"
		        ),
		        array(
		          "type" => "textarea_html",
		          "holder" => "div",
		          "heading" => "Banner Mask Text",
		          "param_name" => "content",
		          "value" => "Some promo text"
		        ),
		        /*array(
		          "type" => "dropdown",
		          "heading" => __("Vertical Align", 'legenda-core'),
		          "param_name" => "valign",
		          "value" => array( "", __("top", 'legenda-core') => "top", __("center", 'legenda-core') => "center",__("bottom", 'legenda-core') => "bottom")
		        ),*/
		        array(
		          "type" => "dropdown",
		          "heading" => __("Vertical text centering", 'legenda-core'),
		          "param_name" => "valign",
		          "value" => array( "", __("Enable", 'legenda-core') => "middle", __("Disable", 'legenda-core') => "top")
		        ),
		        array(
		          "type" => "dropdown",
		          "heading" => __("Banner style", 'legenda-core'),
		          "param_name" => "banner_style",
		          "value" => array( "", __("dark with border", 'legenda-core') => "dark_border")
		        ),
		        array(
		          "type" => "dropdown",
		          "heading" => __("Font style", 'legenda-core'),
		          "param_name" => "font_style",
		          "value" => array( "", __("light", 'legenda-core') => "light", __("dark", 'legenda-core') => "dark")
		        ),
		        array(
		          "type" => "dropdown",
		          "heading" => __("Hover effect", 'legenda-core'),
		          "param_name" => "hover",
		          "value" => array( "", __("zoom", 'legenda-core') => "zoom", __("fade", 'legenda-core') => "faded")
		        ),
		        array(
		          "type" => "textfield",
		          "heading" => __("Extra Class", 'legenda-core'),
		          "param_name" => "class",
		          "description" => __('If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 'legenda-core')
		        ),
		        array(
					'type' => 'css_editor',
					'heading' => __( 'CSS box', 'js_composer' ),
					'param_name' => 'css',
					'group' => __( 'Design Options', 'js_composer' ),
				),
		      )
		    );  
		
		    vc_map($banner_params);
		}
		}
?>