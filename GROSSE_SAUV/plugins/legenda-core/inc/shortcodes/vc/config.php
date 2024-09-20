<?php 

	$vc_is_wp_version_3_6_more = version_compare(preg_replace('/^([\d\.]+)(\-.*$)/', '$1', get_bloginfo('version')), '3.6') >= 0;
	// Used in "Button", "Call to Action", "Pie chart" blocks
	$colors_arr = array(__("Grey", "legenda-core") => "wpb_button", __("Blue", "legenda-core") => "btn-primary", __("Turquoise", "legenda-core") => "btn-info", __("Green", "legenda-core") => "btn-success", __("Orange", "legenda-core") => "btn-warning", __("Red", "legenda-core") => "btn-danger", __("Black", "legenda-core") => "btn-inverse");
	
	// Used in "Button" and "Call to Action" blocks
	$size_arr = array(__("Regular size", "legenda-core") => "wpb_regularsize", __("Large", "legenda-core") => "btn-large", __("Small", "legenda-core") => "btn-small", __("Mini", "legenda-core") => "btn-mini");
	
	$target_arr = array(__("Same window", "legenda-core") => "_self", __("New window", "legenda-core") => "_blank");
	
	$add_css_animation = array(
		"type" => "dropdown",
		"heading" => __("CSS Animation", "legenda-core"),
		"param_name" => "css_animation",
		"admin_label" => true,
		"value" => array(__("No", "legenda-core") => '', __("Top to bottom", "legenda-core") => "top-to-bottom", __("Bottom to top", "legenda-core") => "bottom-to-top", __("Left to right", "legenda-core") => "left-to-right", __("Right to left", "legenda-core") => "right-to-left", __("Appear from center", "legenda-core") => "appear"),
		"description" => __("Select type of animation if you want this element to be animated when it enters into the browsers viewport. Note: Works only in modern browsers.", "legenda-core")
	);
	
?>