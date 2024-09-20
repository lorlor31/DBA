<?php 
	if ( ! function_exists( 'etheme_featured_shortcodes' ) ) :
		function etheme_featured_shortcodes($atts, $content=null){
		    global $wpdb;
		    if ( !class_exists('Woocommerce') ) return false;

		    extract(shortcode_atts(array(
		        'shop_link' => 1,
		        'limit' => 50,
		        'categories' => '',
		        'title' => __('Featured Products', 'legenda-core')
		    ), $atts));

		    $args = apply_filters('woocommerce_related_products_args', array(
		        'post_type'             => 'product',
		        'meta_value'            => 'yes',
		        'ignore_sticky_posts'   => 1,
		        'no_found_rows'         => 1,
		        'posts_per_page'        => $limit
		    ) );

		    $args['tax_query'][] = array(
		      'taxonomy' => 'product_visibility',
		      'field'    => 'name',
		      'terms'    => 'featured',
		      'operator' => 'IN',
		    );

		      // Narrow by categories
		      if ( $categories != '' ) {
		          $categories = explode(",", $categories);
		          $gc = array();
		          foreach ( $categories as $grid_cat ) {
		              array_push($gc, $grid_cat);
		          }
		          $gc = implode(",", $gc);
		          ////http://snipplr.com/view/17434/wordpress-get-category-slug/
		          $args['category_name'] = $gc;
		          $pt = array('product');

		          $taxonomies = get_taxonomies('', 'object');
		          $args['tax_query'] = array('relation' => 'OR');
		          foreach ( $taxonomies as $t ) {
		              if ( in_array($t->object_type[0], $pt) ) {
		                  $args['tax_query'][] = array(
		                      'taxonomy' => $t->name,//$t->name,//'portfolio_category',
		                      'terms' => $categories,
		                      'field' => 'id',
		                  );
		              }
		          }
		      }

		    ob_start();
		    etheme_create_slider($args,$title, $shop_link);
		    $output = ob_get_contents();
		    ob_end_clean();

		    return $output;
		}
	endif;
?>