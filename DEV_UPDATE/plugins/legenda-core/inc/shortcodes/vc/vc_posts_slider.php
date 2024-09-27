<?php 
	$setting_vc_posts_slider = array (
      'params' => array(
    array(
      "type" => "textfield",
      "heading" => __("Widget title", "legenda-core"),
      "param_name" => "title",
      "description" => __("What text use as a widget title. Leave blank if no title is needed.", "legenda-core")
    ),
    array(
      "type" => "textfield",
      "heading" => __("Slides count", "legenda-core"),
      "param_name" => "count",
      "description" => __('How many slides to show? Enter number or word "All".', "legenda-core")
    ),
    array(
      "type" => "posttypes",
      "heading" => __("Post types", "legenda-core"),
      "param_name" => "posttypes",
      "description" => __("Select post types to populate posts from.", "legenda-core")
    ),
    array(
      "type" => 'checkbox',
      "heading" => __("Output post date?", "legenda-core"),
      "param_name" => "slides_date",
      "description" => __("If selected, date will be printed before the teaser text.", "legenda-core"),
      "value" => Array(__("Yes, please", "legenda-core") => true)
    ),
    array(
      "type" => "dropdown",
      "heading" => __("Description", "legenda-core"),
      "param_name" => "slides_content",
      "value" => array(
      		__("No description", "legenda-core") => "", 
      		__("Teaser (Excerpt)", "legenda-core") => "teaser",
  		),
      "description" => __("Some sliders support description text, what content use for it?", "legenda-core"),
    ),
    array(
      "type" => 'checkbox',
      "heading" => __("Output post title?", "legenda-core"),
      "param_name" => "slides_title",
      "description" => __("If selected, title will be printed before the teaser text.", "legenda-core"),
      "value" => Array(__("Yes, please", "legenda-core") => true),
    ),
    array(
      "type" => "textfield",
      "heading" => __("Thumbnail size", "legenda-core"),
      "param_name" => "thumb_size",
      "description" => __('Enter thumbnail size. Example: 200x100 (Width x Height).', "legenda-core")
    ),
    array(
      "type" => "textfield",
      "heading" => __("Post/Page IDs", "legenda-core"),
      "param_name" => "posts_in",
      "description" => __('Fill this field with page/posts IDs separated by commas (,), to retrieve only them. Use this in conjunction with "Post types" field.', "legenda-core")
    ),
    array(
      "type" => "exploded_textarea",
      "heading" => __("Categories", "legenda-core"),
      "param_name" => "categories",
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
      "heading" => __("Order by", "legenda-core"),
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
    vc_map_update('vc_posts_slider', $setting_vc_posts_slider);

    function vc_theme_vc_posts_slider($atts, $content = null) {
      $output = $title = $type = $count = $interval = $slides_content = $link = '';
      $custom_links = $thumb_size = $posttypes = $posts_in = $categories = '';
      $orderby = $order = $el_class = $link_image_start = '';
      extract(shortcode_atts(array(
          'title' => '',
          'type' => 'flexslider_fade',
          'count' => 10,
          'interval' => 3,
          'slides_content' => '',
          'slides_title' => '',
          'link' => 'link_post',
          'more_link' => 1,
          'custom_links' => site_url().'/',
          'thumb_size' => '300x200',
          'posttypes' => '',
          'posts_in' => '',
          'slides_date' => false,
          'categories' => '',
          'orderby' => NULL,
          'order' => 'DESC',
          'el_class' => ''
      ), $atts));

      $gal_images = '';
      $link_start = '';
      $link_end = '';
      $el_start = '';
      $el_end = '';
      $slides_wrap_start = '';
      $slides_wrap_end = '';

      $el_class = ' '.$el_class;

      $query_args = array();

      //exclude current post/page from query
      if ( $posts_in == '' ) {
          global $post;
          $query_args['post__not_in'] = array($post->ID);
      }
      else if ( $posts_in != '' ) {
          $query_args['post__in'] = explode(",", $posts_in);
      }

      // Post teasers count
      if ( $count != '' && !is_numeric($count) ) $count = -1;
      if ( $count != '' && is_numeric($count) ) $query_args['posts_per_page'] = $count;

      // Post types
      $pt = array();
      if ( $posttypes != '' ) {
          $posttypes = explode(",", $posttypes);
          foreach ( $posttypes as $post_type ) {
              array_push($pt, $post_type);
          }
          $query_args['post_type'] = $pt;
      }

      // Narrow by categories
      if ( $categories != '' ) {
          $categories = explode(",", $categories);
          $gc = array();
          foreach ( $categories as $grid_cat ) {
              array_push($gc, $grid_cat);
          }
          $gc = implode(",", $gc);
          ////http://snipplr.com/view/17434/wordpress-get-category-slug/
          $query_args['category_name'] = $gc;

          $taxonomies = get_taxonomies('', 'object');
          $query_args['tax_query'] = array('relation' => 'OR');
          foreach ( $taxonomies as $t ) {
              if ( in_array($t->object_type[0], $pt) ) {
                  $query_args['tax_query'][] = array(
                      'taxonomy' => $t->name,//$t->name,//'portfolio_category',
                      'terms' => $categories,
                      'field' => 'slug',
                  );
              }
          }
      }

      // Order posts
      if ( $orderby != NULL ) {
          $query_args['orderby'] = $orderby;
      }
      $query_args['order'] = $order;

      $thumb_size = explode('x', $thumb_size);
      $width = $thumb_size[0];
      $height = $thumb_size[1];

      $crop = true;

      ob_start();
      etheme_create_posts_slider($query_args, $title, $more_link, $slides_date, $slides_content, $width, $height, $crop );
      $output = ob_get_contents();
      ob_end_clean();

      return $output;
    }
?>