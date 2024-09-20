<?php 
	
if(!function_exists('et_brands')) {
	function et_brands($atts) {
			
		extract( shortcode_atts( array(
			'title' => '',
			'limit' => 10,
			'display_type' => 'slider',
			'columns' => 3,
			'class' => ''
		), $atts ) );

		$output = '';

		$args = array( 'hide_empty' => false, 'number' => $limit );

		$terms = get_terms('brand', $args);

		$count = count($terms); $i=0;
		if ($count > 0) {
			$output .= '<div class="et-brands-'.$display_type.' '.$class.' columns-number-'.$columns.'">';
			if($title != '') {
				$output .= '<h2 class="brands-title title"><span>'.$title.'</span></h2>';
			}

			if ( !etheme_get_option('enable_brands') ) {
                $output .= '<p>' . esc_html__( 'To use this widget enable "brands" in the module section of theme options', 'legenda-core' ) . '</p>';
	            $output .= '</div>';
	            return $output;
	        }

			$output .= '<ul class="et-brands">';

		    foreach ($terms as $term) {
		        $i++;
		        $thumbnail_id 	= absint( get_term_meta( $term->term_id, 'thumbnail_id', true ) );
				$output .= '<li class="et-brand">';
				if($thumbnail_id) {
					$output .= '<a href="' . get_term_link( $term ) . '" title="' . sprintf(__('View all products from %s', 'legenda-core'), $term->name) . '">' . new_etheme_get_image( $thumbnail_id, 'medium' ) . '</a>';
				} else {
					$output .= '<h3><a href="' . get_term_link( $term ) . '" title="' . sprintf(__('View all products from %s', 'legenda-core'), $term->name) . '">' . $term->name . '</a></h3>';
				}
				$output .= '</li>';
		    }

		    $output .= '</ul>';
			$output .= '</div>';

			if($display_type == 'slider') {
				$items = '[[0, 1], [479,2], [619,3], [768,3],  [1200, 4], [1600, 4]]';
				$output .=  '<script type="text/javascript">';
				$output .=  '     jQuery(".et-brands").owlCarousel({';
				$output .=  '         items:1, ';
				$output .=  '         nav: true,';
				$output .=  '         navText:["",""],';
				$output .=  '         dots: false,';
				$output .=  '         rewindNav: false,';
				$output .=  '         itemsCustom: '.$items.'';
				$output .=  '    });';

				$output .=  ' </script>';
			}

		}

		return $output;
	}
}

if ( !function_exists('etheme_register_et_brands') ) {

	function etheme_register_et_brands () {
		if(!function_exists('vc_map')) {
			return;
		}

		$brands_params = array(
		  'name' => 'Brands',
		  'base' => 'brands',
		  'icon' => 'icon-wpb-etheme',
		  'category' => 'Eight Theme',
		  'params' => array(
		    array(
		      "type" => "textfield",
		      "heading" => __("Title", 'legenda-core'),
		      "param_name" => "title"
		    ),
		    array(
		      "type" => "dropdown",
		      "heading" => __("Display type", 'legenda-core'),
		      "param_name" => "display_type",
		      "value" => array( 
		          __("Slider", 'legenda-core') => 'slider',
		          __("Grid", 'legenda-core') => 'grid'
		        )
		    ),
		    array(
		      "type" => "dropdown",
		      "heading" => __("Number of columns", 'legenda-core'),
		      "param_name" => "columns",	          
		      "dependency" => Array('element' => "display_type", 'value' => array('grid')),
		      "value" => array( 
		          '2' => 2,
		          '3' => 3,
		          '4' => 4,
		          '5' => 5,
		          '6' => 6,
		        )
		    ),
		    array(
		      "type" => "textfield",
		      "heading" => __("Maximum number of brands to display", 'legenda-core'),
		      "param_name" => "limit"
		    ),
		    array(
		      "type" => "textfield",
		      "heading" => __("Extra Class", 'legenda-core'),
		      "param_name" => "class"
		    )
		  )

		);

		vc_map($brands_params);

	}
}

?>