<?php  

	add_action( 'init', 'etheme_register_recent_posts');

	if ( !function_exists('etheme_register_recent_posts') ) {
		function etheme_register_recent_posts () {

			if(!function_exists('vc_map')) {
				return;
			}

		    $recent_posts_params = array(
		      'name' => 'Recent posts widget',
		      'base' => 'et_recent_posts_widget',
		      'icon' => 'icon-wpb-etheme',
		      'category' => 'Eight Theme',
		      'params' => array(
		        array(
		          'type' => 'textfield',
		          "heading" => __("Widget title", 'legenda-core'),
		          "param_name" => "title",
		          "description" => __("What text use as a widget title. Leave blank if no title is needed.", 'legenda-core')
		        ),
		        array(
		          "type" => "textfield",
		          "heading" => __("Limit", 'legenda-core'),
		          "param_name" => "number",
		          "description" => __('How many posts to show? Enter number.', 'legenda-core')
		        )
		      )

		    );  

		    vc_map($recent_posts_params);
		}
	}

?>