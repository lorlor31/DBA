<?php 
	$setting_teaser_grid = array(
	  "params" => array(
	    array(
	      "type" => "textfield",
	      "heading" => __("Widget title", "legenda-core"),
	      "param_name" => "title",
	      "description" => __("What text use as a widget title. Leave blank if no title is needed.", "legenda-core")
	    ),
	    array(
	      "type" => "dropdown",
	      "heading" => __("Columns count", "legenda-core"),
	      "param_name" => "grid_columns_count",
	      "value" => array( 4, 3, 2, 1),
	      "admin_label" => true,
	      "description" => __("Select columns count.", "legenda-core")
	    ),
	    array(
	      "type" => "posttypes",
	      "heading" => __("Post types", "legenda-core"),
	      "param_name" => "grid_posttypes",
	      "description" => __("Select post types to populate posts from.", "legenda-core")
	    ),
	    array(
	      "type" => "textfield",
	      "heading" => __("Teasers count", "legenda-core"),
	      "param_name" => "grid_teasers_count",
	      "description" => __('How many teasers to show? Enter number or word "All".', "legenda-core")
	    ),
	    array(
	      "type" => "dropdown",
	      "heading" => __("Pagination", "legenda-core"),
	      "param_name" => "pagination",
	      "value" => array(__("Show Pagination", "legenda-core") => "show", __("Hide", "legenda-core") => "hide")
	    ),
	    array(
	      "type" => "dropdown",
	      "heading" => __("Content", "legenda-core"),
	      "param_name" => "grid_content",
	      "value" => array(__("Teaser (Excerpt)", "legenda-core") => "teaser", __("Full Content", "legenda-core") => "content"),
	      "description" => __("Teaser layout template.", "legenda-core")
	    ),
	    array(
	      "type" => "dropdown",
	      "heading" => __("'Posted by' block", "legenda-core"),
	      "param_name" => "posted_block",
	      "value" => array(__("Show", "legenda-core") => "show", __("Hide", "legenda-core") => "hide")
	    ),
	    array(
	      "type" => "dropdown",
	      "heading" => __("Hover mask", "legenda-core"),
	      "param_name" => "hover_mask",
	      "value" => array(__("Show", "legenda-core") => "show", __("Hide", "legenda-core") => "hide")
	    ),
	    array(
	      "type" => "dropdown",
	      "heading" => __("Layout", "legenda-core"),
	      "param_name" => "grid_layout",
	      "value" => array(__("Title + Thumbnail + Text", "legenda-core") => "title_thumbnail_text", __("Thumbnail + Title + Text", "legenda-core") => "thumbnail_title_text", __("Thumbnail + Text", "legenda-core") => "thumbnail_text", __("Thumbnail + Title", "legenda-core") => "thumbnail_title", __("Thumbnail only", "legenda-core") => "thumbnail", __("Title + Text", "legenda-core") => "title_text"),
	      "description" => __("Teaser layout.", "legenda-core")
	    ),
	    array(
	      "type" => "dropdown",
	      "heading" => __("Border", "legenda-core"),
	      "param_name" => "border",
	      "value" => array(__("Show border", "legenda-core") => "on", __("Without border", "legenda-core") => "off"),
	      "description" => __("Teaser layout.", "legenda-core")
	    ),
	    array(
	      "type" => "dropdown",
	      "heading" => __("Teaser grid layout", "legenda-core"),
	      "param_name" => "grid_template",
	      "value" => array(__("Grid", "legenda-core") => "grid", __("Grid with filter", "legenda-core") => "filtered_grid"),
	      "description" => __("Teaser layout template.", "legenda-core")
	    ),
	    array(
	      "type" => "taxonomies",
	      "heading" => __("Taxonomies", "legenda-core"),
	      "param_name" => "grid_taxomonies",
	      "dependency" => Array('element' => 'grid_template' /*, 'not_empty' => true*/, 'value' => array('filtered_grid'), 'callback' => 'wpb_grid_post_types_for_taxonomies_handler'),
	      "description" => __("Select taxonomies from.", "legenda-core") //TODO: Change description
	    ),
	    array(
	      "type" => "textfield",
	      "heading" => __("Thumbnail size", "legenda-core"),
	      "param_name" => "grid_thumb_size",
	      "description" => __('Enter thumbnail size. Example: thumbnail, medium, large, full or other sizes defined by current theme. Alternatively enter image size in pixels: 200x100 (Width x Height).', "legenda-core")
	    ),  
	    array(
	      "type" => "textfield",
	      "heading" => __("Post/Page IDs", "legenda-core"),
	      "param_name" => "posts_in",
	      "description" => __('Fill this field with page/posts IDs separated by commas (,) to retrieve only them. Use this in conjunction with "Post types" field.', "legenda-core")
	    ),
	    array(
	      "type" => "textfield",
	      "heading" => __("Exclude Post/Page IDs", "legenda-core"),
	      "param_name" => "posts_not_in",
	      "description" => __('Fill this field with page/posts IDs separated by commas (,) to exclude them from query.', "legenda-core")
	    ),
	    array(
	      "type" => "exploded_textarea",
	      "heading" => __("Categories", "legenda-core"),
	      "param_name" => "grid_categories",
	      "description" => __("If you want to narrow output, enter category names here. Note: Only listed categories will be included. Divide categories with linebreaks (Enter).", "legenda-core")
	    ),
	    array(
	      "type" => "dropdown",
	      "heading" => __("Order by", "legenda-core"),
	      "param_name" => "orderby",
	      "value" => array( "", __("Date", "legenda-core") => "date", __("ID", "legenda-core") => "ID", __("Author", "legenda-core") => "author", __("Title", "legenda-core") => "title", __("Modified", "legenda-core") => "modified", __("Random", "legenda-core") => "rand", __("Comment count", "legenda-core") => "comment_count", __("Menu order", "legenda-core") => "menu_order" ),
	      "description" => sprintf(__('Select how to sort retrieved posts. More at %s.', 'legenda-core'), '<a href="http://codex.wordpress.org/Class_Reference/WP_Query#Order_.26_Orderby_Parameters" target="_blank">WordPress codex page</a>')
	    ),
	    array(
	      "type" => "dropdown",
	      "heading" => __("Order way", "legenda-core"),
	      "param_name" => "order",
	      "value" => array( __("Descending", "legenda-core") => "DESC", __("Ascending", "legenda-core") => "ASC" ),
	      "description" => sprintf(__('Designates the ascending or descending order. More at %s.', 'legenda-core'), '<a href="http://codex.wordpress.org/Class_Reference/WP_Query#Order_.26_Orderby_Parameters" target="_blank">WordPress codex page</a>')
	    ),
	    array(
	      "type" => "textfield",
	      "heading" => __("Extra class name", "legenda-core"),
	      "param_name" => "el_class",
	      "description" => __("If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", "legenda-core")
	    )
	  )
	);

    vc_map_update('vc_posts_grid', $setting_teaser_grid);
?>