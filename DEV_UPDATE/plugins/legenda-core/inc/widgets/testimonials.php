<?php  

	add_action( 'init', 'etheme_register_etheme_testimonials');
	if ( !function_exists('etheme_register_etheme_testimonials') ) {

		function etheme_register_etheme_testimonials () {
			if(!function_exists('vc_map')) {
				return;
			}

		    $testimonials_params = array(
		      'name' => 'Testimonials widget',
		      'base' => 'testimonials',
		      'icon' => 'icon-wpb-etheme',
		      'category' => 'Eight Theme',
		      'params' => array(
		        array(
		          "type" => "textfield",
		          "heading" => __("Limit", 'legenda-core'),
		          "param_name" => "limit",
		          "description" => __('How many testimonials to show? Enter number.', 'legenda-core')
		        ),
		        array(
		          "type" => "dropdown",
		          "heading" => __("Display type", "js_composer"),
		          "param_name" => "type",
		          "value" => array( 
		              "", 
		              __("Slider", 'legenda-core') => 'slider',
		              __("Grid", 'legenda-core') => 'grid'
		            )
		        ),
		        array(
		          "type" => "textfield",
		          "heading" => __("Interval", 'legenda-core'),
		          "param_name" => "interval",
		          "description" => __('Interval between slides. In milliseconds. Default: 10000', 'legenda-core'),
		          "dependency" => array('element' => "type", 'value' => array('slider'))
		        ),
		        array(
		          "type" => "dropdown",
		          "heading" => __("Show Control Navigation", "js_composer"),
		          "param_name" => "navigation",
		          "dependency" => array('element' => "type", 'value' => array('slider')),
		          "value" => array( 
		              "", 
		              __("Hide", 'legenda-core') => 'hide',
		              __("Show", 'legenda-core') => 'show'
		            )
		        ),
		        array(
		          "type" => "textfield",
		          "heading" => __("Category", 'legenda-core'),
		          "param_name" => "category",
		          "description" => __('Display testimonials from category.', 'legenda-core')
		        ),
		      )

		    );  

		    vc_map($testimonials_params);
		    
		}
	}
?>