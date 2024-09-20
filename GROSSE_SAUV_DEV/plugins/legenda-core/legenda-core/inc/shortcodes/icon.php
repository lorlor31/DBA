<?php  
	if ( ! function_exists( 'etheme_icon_shortcode' ) ) :
		function etheme_icon_shortcode($atts, $content = null) {
			$a = shortcode_atts(array(
			        'name' => 'circle-blank',
			        'size' => '',
			        'style' => '',
			        'color' => '',
			        'hover' => 0,
			        'class' => ''
			    ), $atts);

		    if($a['hover'] == 1 ) {
		        $a['name'] .= ' hover-icon';
		    }

		    return '<i class="icon-'.$a['name'].' ' . $a['class'] . '" style="color:'.$a['color'].'; font-size:'.$a['size'].'px; '.$a['style'].'"></i>';
		}
	endif;

	if ( !function_exists('etheme_register_icon') ) {

		function etheme_register_icon () {
			if(!function_exists('vc_map')) {
				return;
			}

		    $icon_box_params = array(
		      'name' => 'Awesome Icon',
		      'base' => 'icon',
		      'icon' => 'icon-wpb-etheme',
		      'category' => 'Eight Theme',
		      'params' => array(
		        array(
		          'type' => 'icon',
		          "heading" => __("Icon", 'legenda-core'),
		          "param_name" => "name"
		        ),
		        array(
		          'type' => 'textfield',
		          "heading" => __("Size", 'legenda-core'),
		          "param_name" => "size",
		          "description" => __('For example: 64', 'legenda-core')
		        ),
		        array(
		          'type' => 'colorpicker',
		          "heading" => __("Color", 'legenda-core'),
		          "param_name" => "color"
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