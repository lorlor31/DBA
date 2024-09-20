<?php

	add_action( 'init', 'etheme_register_recent_comments');

	if ( !function_exists('etheme_register_recent_comments') ) {
		function etheme_register_recent_comments () {

			if(!function_exists('vc_map')) {
				return;
			}

	    	$recent_comments_params = array(
		      'name' => 'Recent comments widget',
		      'base' => 'et_recent_comments',
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
		          "description" => __('How many comments to show? Enter number.', 'legenda-core')
		        )
		      )
		
		    );  
		
		    vc_map($recent_comments_params);
		}
	}
?>