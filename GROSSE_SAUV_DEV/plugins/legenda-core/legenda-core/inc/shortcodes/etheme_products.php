<?php 
	if ( ! function_exists( 'etheme_products_shortcodes' ) ) :
		function etheme_products_shortcodes($atts, $content=null) {
		    global $wpdb;
		    if ( !class_exists('Woocommerce') ) return false;

		    extract(shortcode_atts(array(
		        'ids' => '',
		        'skus' => '',
		        'columns' => 4,
		        'shop_link' => 1,
		        'limit' => 20,
		        'categories' => '',
		        'block_id' => false,
		        'type' => 'slider',
		        'style' => 'default',
		        'products' => '', //featured new sale bestsellings recently_viewed
		        'title' => '',
		        'desktop' => 4,
		        'notebook' => 4,
		        'tablet' => 3,
		        'phones' => 2,
		        'orderby' => 'name',
			    'order' => 'ASC',
		    ), $atts));

		    $args = array(
		        'post_type'             => 'product',
		        'ignore_sticky_posts'   => 1,
		        'no_found_rows'         => 1,
		        'posts_per_page'        => $limit,
		        'orderby'   => $order,
		        'order'     => $orderby,
		        'meta_query'     => array(),

		    );

		    $args['tax_query'][] = array(
		        'taxonomy' => 'product_visibility',
		        'field'    => 'name',
		        'terms'    => 'hidden',
		        'operator' => 'NOT IN',
		    );

		    if ($products == 'new') {
		        $args['meta_key'] = 'product_new';
		        $args['meta_value'] = 'enable';
		    }

		    if ($products == 'featured') {
		        $args['tax_query'][] = array(
			        'taxonomy' => 'product_visibility',
			        'field'    => 'name',
			        'terms'    => 'featured',
			        'operator' => 'IN',
			    );
		    }

		    if ($products == 'sale') {
		        $product_ids_on_sale = wc_get_product_ids_on_sale();
		        $args['post__in'] = $product_ids_on_sale;
		    }

		    if ($products == 'bestsellings') {
		        $args['meta_key'] = 'total_sales';
		        $args['orderby'] = 'meta_value_num';
		    }

		    if ($products == 'recently_viewed') {
		        $viewed_products = ! empty( $_COOKIE['woocommerce_recently_viewed'] ) ? (array) explode( '|', $_COOKIE['woocommerce_recently_viewed'] ) : array();
		        $viewed_products = array_filter( array_map( 'absint', $viewed_products ) );

		        if ( empty( $viewed_products ) )
		          return;
		        $args['post__in'] = $viewed_products;
		        $args['orderby'] = 'rand';
		    }



			if ( ! empty( $orderby ) && $args['orderby'] != 'meta_value_num' ) {
			 	$args['orderby'] = esc_attr($orderby);
			}

			if (!empty($orderby)) {
				$args['order'] = esc_attr($order);
			}
		    if($skus != ''){
		        $skus = explode(',', $atts['skus']);
		        $skus = array_map('trim', $skus);
		        $args['meta_query'][] = array(
		            'key'       => '_sku',
		            'value'     => $skus,
		            'compare'   => 'IN'
		        );
		    }

		    if($ids != ''){
		        $ids = explode(',', $atts['ids']);
		        $ids = array_map('trim', $ids);
		        $args['post__in'] = $ids;
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
		      //$args['category_name'] = $gc;
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

		    $customItems = array(
		        'desktop' => $desktop,
		        'notebook' => $notebook,
		        'tablet' => $tablet,
		        'phones' => $phones
		    );

		    if ($type == 'slider') {
		    	$slider_args = array(
		    		'title' => $title,
		    		'shop_link' => $shop_link,
		    		'slider_type' => false,
		    		'items' => $customItems,
		    		'style' => $style,
		    	);
		        ob_start();
		        etheme_create_slider($args, $slider_args);
		        $output = ob_get_contents();
		        ob_end_clean();
		    } elseif($type == 'full-width') {
		    	$slider_args = array(
		    		'title' => $title,
		    		'shop_link' => $shop_link,
		    		'slider_type' => false,
		    		'customItems' => $customItems,
		    		'style' => $style,
		    		'block_id' => $block_id,
				    'full_width' => true
		    	);
		        ob_start();
		        etheme_create_slider($args, $slider_args);
		        $output = ob_get_contents();
		        ob_end_clean();
		    } else {
		        $output = etheme_products($args, $title, $columns);
		    }

		    return $output;
		}
	endif;
    
	if ( !function_exists('etheme_register_etheme_products') ) {

		function etheme_register_etheme_products () {

			if(!function_exists('vc_map') ) {
				return;
			}
			
			if(!function_exists('et_get_static_blocks')) {
				return;
			}
    
		    $static_blocks = array('--choose--' => '');
		    $et_static_blocks = et_get_static_blocks();
		    
		    foreach($et_static_blocks as $value) {
			    $static_blocks[$value['label']] = $value['value'];
		    }
			$order_by_values = array(
			'',
				__( 'Date', 'legenda-core' ) => 'date',
				__( 'ID', 'legenda-core' ) => 'ID',
				__( 'Author', 'legenda-core' ) => 'author',
				__( 'Title', 'legenda-core' ) => 'title',
				__( 'Modified', 'legenda-core' ) => 'modified',
				__( 'Random', 'legenda-core' ) => 'rand',
				__( 'Comment count', 'legenda-core' ) => 'comment_count',
				__( 'Menu order', 'legenda-core' ) => 'menu_order',
			);

			$order_way_values = array(
			'',
				__( 'Descending', 'legenda-core' ) => 'DESC',
				__( 'Ascending', 'legenda-core' ) => 'ASC',
			);
		    
		    $fpost_params = array(
		      'name' => 'Products',
		      'base' => 'etheme_products',
		      'icon' => 'icon-wpb-etheme',
		      'category' => 'Eight Theme',
		      'params' => array(
		        array(
		          "type" => "textfield",
		          "heading" => __("Title", 'legenda-core'),
		          "param_name" => "title"
		        ),
		        array(
		          "type" => "textfield",
		          "heading" => __("IDs", 'legenda-core'),
		          "param_name" => "ids"
		        ),
		        array(
		          "type" => "textfield",
		          "heading" => __("SKUs", 'legenda-core'),
		          "param_name" => "skus"
		        ),
			    array(
		        	'type' => 'dropdown',
		        	'heading' => __( 'Order by', 'legenda-core' ),
		        	'param_name' => 'orderby',
		        	'value' => $order_by_values,
		        	'description' => sprintf( __( 'Select how to sort retrieved products. More at %s.', 'legenda-core' ), '<a href="http://codex.wordpress.org/Class_Reference/WP_Query#Order_.26_Orderby_Parameters" target="_blank">WordPress codex page</a>' )
		        ),
		        array(
		        	'type' => 'dropdown',
		        	'heading' => __( 'Order way', 'legenda-core' ),
		        	'param_name' => 'order',
		        	'value' => $order_way_values,
		        	'description' => sprintf( __( 'Designates the ascending or descending order. More at %s.', 'legenda-core' ), '<a href="http://codex.wordpress.org/Class_Reference/WP_Query#Order_.26_Orderby_Parameters" target="_blank">WordPress codex page</a>' )
		        ),
		        array(
		          "type" => "dropdown",
		          "heading" => __("Display Type", 'legenda-core'),
		          "param_name" => "type",
		          "value" => array( __("Slider", 'legenda-core') => 'slider',__("Slider full width (LOOK BOOK)", 'legenda-core') => 'full-width', __("Grid", 'legenda-core') => 'grid')
		        ),
		        array(
		          "type" => "dropdown",
		          "dependency" => array('element' => "type", 'value' => array('full-width')),
		          "heading" => __("Static block for the first slide of the LOOK BOOK", 'legenda-core'),
		          "param_name" => "block_id",
		          "value" => $static_blocks
		        ),
		        array(
		          "type" => "textfield",
		          "heading" => __("Columns", 'legenda-core'),
		          "param_name" => "columns",
		          "dependency" => array('element' => "type", 'value' => array('grid'))
		        ),
		        array(
		          "type" => "dropdown",
		          "heading" => __("Product view", 'legenda-core'),
		          "param_name" => "style",
		          "dependency" => array('element' => "type", 'value' => array('slider')),
		          "value" => array( __("Default", 'legenda-core') => 'default', __("Advanced", 'legenda-core') => 'advanced')
		        ),
		        array(
		          "type" => "textfield",
		          "heading" => __("Number of items on desktop", 'legenda-core'),
		          "param_name" => "desktop",
		          "dependency" => array('element' => "type", 'value' => array('slider'))
		        ),
		        array(
		          "type" => "textfield",
		          "heading" => __("Number of items on notebook", 'legenda-core'),
		          "param_name" => "notebook",
		          "dependency" => array('element' => "type", 'value' => array('slider'))
		        ),
		        array(
		          "type" => "textfield",
		          "heading" => __("Number of items on tablet", 'legenda-core'),
		          "param_name" => "tablet",
		          "dependency" => array('element' => "type", 'value' => array('slider'))
		        ),
		        array(
		          "type" => "textfield",
		          "heading" => __("Number of items on phones", 'legenda-core'),
		          "param_name" => "phones",
		          "dependency" => array('element' => "type", 'value' => array('slider'))
		        ),
		        array(
		          "type" => "dropdown",
		          "heading" => __("Products type", 'legenda-core'),
		          "param_name" => "products",
		          "value" => array( __("All", 'legenda-core') => '', __("Featured", 'legenda-core') => 'featured', __("New", 'legenda-core') => 'new', __("Sale", 'legenda-core') => 'sale', __("Recently viewed", 'legenda-core') => 'recently_viewed', __("Bestsellings", 'legenda-core') => 'bestsellings')
		        ),
		        array(
		          "type" => "textfield",
		          "heading" => __("Limit", 'legenda-core'),
		          "param_name" => "limit"
		        ),
		        array(
		          "type" => "textfield",
		          "heading" => __("Categories IDs", 'legenda-core'),
		          "param_name" => "categories"
		        )
		      )
		
		    );  
		
		    vc_map($fpost_params);
		}
	}
	
?>