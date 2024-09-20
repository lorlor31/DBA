<?php  
	if ( ! function_exists( 'etheme_teaser_box_shortcodes' ) ) :
		function etheme_teaser_box_shortcodes($atts, $content=null){
			
		    extract(shortcode_atts(array(
		        'title' => '',
		        'heading' => '4',
		        'img' => '',
		        'img_size' => '270x170',
		        'style' => '',
		        'class' => ''
		    ), $atts));


		    //$image ='';

		    $img_size = is_array($img_size) ? explode('x', $img_size[0]) : explode('x', $img_size);

		    $width = $img_size[0];
		    $height = $img_size[1];

		    if($img != '') {
		        $img = new_etheme_get_image( $img, array( $width, $height ) );
		    }

		    if($title != '') {
			    $title = '<h'.$heading.' class="title"><span>'.$title.'</span></h'.$heading.'>';
		    }


		    if($style != '') {
			    $class .= ' style-'.$style;
		    }

		    return '<div class="teaser-box '.$class.'"><div>'. $title . $img . do_shortcode($content) .'</div></div>';

		}
	endif;
    
	if ( !function_exists('etheme_register_teaser_box') ) {

		function etheme_register_teaser_box () {

			if(!function_exists('vc_map')) {
				return;
			}

		    $teaser_box_params = array(
		      'name' => 'Teaser Box',
		      'base' => 'teaser_box',
		      'icon' => 'icon-wpb-etheme',
		      'category' => 'Eight Theme',
		      'params' => array(
		        array(
		          'type' => 'textfield',
		          'heading' => __('Title', 'legenda-core'),
		          'param_name' => 'title'
		        ),
		        array(
		          'type' => 'attach_image',
		          'heading' => __('Image', 'legenda-core'),
		          'param_name' => 'img'
		        ),
		        array(
		          'type' => 'textfield',
		          'heading' => __('Image size', 'legenda-core'),
		          'param_name' => 'img_size',
		          'description' => __('Enter image size. Example in pixels: 200x100 (Width x Height).', 'legenda-core')
		        ),
		        array(
		          'type' => 'textarea_html',
		          'admin_label' => true,
		          'heading' => __('Text', 'legenda-core'),
		          'param_name' => 'content',
		          'value' => __('Click edit button to change this text.', 'legenda-core'),
		          'description' => __('Enter your content.', 'legenda-core')
		        ),
		        array(
		          'type' => 'dropdown',
		          'heading' => __('Style', 'legenda-core'),
		          'param_name' => 'style',
		          'value' => array( __('Default', 'legenda-core') => 'default', __('Bordered', 'legenda-core') => 'bordered')
		        ),
		        array(
		          'type' => 'textfield',
		          'heading' => __('Extra Class', 'legenda-core'),
		          'param_name' => 'class',
		          'description' => __('If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 'legenda-core')
		        )
		      )

		    );  

		    vc_map($teaser_box_params);

		}
	}
?>