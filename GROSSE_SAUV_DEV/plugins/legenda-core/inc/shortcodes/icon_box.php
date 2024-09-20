<?php  
	if ( ! function_exists( 'etheme_icon_box_shortcode' ) ) :
		function etheme_icon_box_shortcode($atts, $content = null) {
		    $a = shortcode_atts(array(
		        'title' => '',
		        'icon' => 'bolt',
		        'icon_position' => 'left',
		        'icon_style' => '',
		        'color' => '',
		        'bg_color' => '',
		        'color_hover' => '',
		        'bg_color_hover' => '',
		        'text' => ''
		    ),$atts);

		    $box_id = rand(1000,10000);

		    $output = '';
		    $output .= '<div class="block-with-ico ico-box-'.$box_id.' ico-position-'.$a['icon_position'].' ico-style-'.$a['icon_style'].'">';
		        $output .= '<i class="fa fa-'.$a['icon'].'" ></i>';
		        $output .= '<div class="ico-box-content">';
		        $output .= '<h5>'.$a['title'].'</h5>';
		        $output .= do_shortcode($content).do_shortcode($a['text']);
		        $output .= '</div>';
		    $output .= '</div>';
		    $output .= '<style>';
		    $output .= '.ico-box-'.$box_id.' i {';
		    if($a['color'] != '') {
			    $output .= 'color:'.$a['color'].'!important;';
		    }
		    if($a['bg_color'] != '') {
			    $output .= 'background:'.$a['bg_color'].'!important;';
		    }
		    $output .= '}';
		    $output .= '.ico-box-'.$box_id.':hover i {';
		    if($a['color_hover'] != '') {
		    	$output .= 'color:'.$a['color_hover'].'!important;';
		    }
		    if($a['bg_color_hover'] != '') {
		    	$output .= 'background:'.$a['bg_color_hover'].'!important;';
		    }
		    $output .= '}';
		    $output .= '</style>';

		    return $output;
		}
	endif;

	if ( !function_exists('etheme_register_icon_box') ) {
		function etheme_register_icon_box () {
			if(!function_exists('vc_map')) {
				return;
			}

		    $icon_box_params = array(
		      'name' => 'Icon Box',
		      'base' => 'icon_box',
		      'icon' => 'icon-wpb-etheme',
		      'category' => 'Eight Theme',
		      'params' => array(
		        array(
		          'type' => 'textfield',
		          "heading" => __("Box title", 'legenda-core'),
		          "param_name" => "title"
		        ),
		        array(
		          'type' => 'icon',
		          "heading" => __("Icon", 'legenda-core'),
		          "param_name" => "icon"
		        ),
		        array(
		          'type' => 'colorpicker',
		          "heading" => __("Icon Color", 'legenda-core'),
		          "param_name" => "color"
		        ),
		        array(
		          'type' => 'colorpicker',
		          "heading" => __("Background Color", 'legenda-core'),
		          "param_name" => "bg_color"
		        ),
		        array(
		          'type' => 'colorpicker',
		          "heading" => __("Icon Color [HOVER]", 'legenda-core'),
		          "param_name" => "color_hover"
		        ),
		        array(
		          'type' => 'colorpicker',
		          "heading" => __("Background Color [HOVER]", 'legenda-core'),
		          "param_name" => "bg_color_hover"
		        ),
		        array(
		          "type" => "textarea_html",
		          'admin_label' => true,
		          "heading" => __("Text", 'legenda-core'),
		          "param_name" => "content",
		          "value" => __("Click edit button to change this text.", 'legenda-core'),
		          "description" => __("Enter your content.", 'legenda-core')
		        ),
		        array(
		          "type" => "dropdown",
		          "heading" => __("Icon Position", 'legenda-core'),
		          "param_name" => "icon_position",
		          "value" => array( 
		              "", 
		              __("Top", 'legenda-core') => 'top',
		              __("Left", 'legenda-core') => 'left'
		            )
		        ),
		        array(
		          "type" => "dropdown",
		          "heading" => __("Icon Style", 'legenda-core'),
		          "param_name" => "icon_style",
		          "value" => array( 
		              __("Encircled", 'legenda-core') => 'encircled',
		              __("Small", 'legenda-core') => 'small',
		              __("Large", 'legenda-core') => 'large'
		            )
		        ),
		        array(
		          "type" => "textfield",
		          "heading" => __("Extra Class", 'legenda-core'),
		          "param_name" => "class",
		          "description" => __('If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 'legenda-core')
		        )
		      )
		    );  
		
		    vc_map($icon_box_params);
		}
	}

?>