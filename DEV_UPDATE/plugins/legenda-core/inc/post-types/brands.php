<?php 
	add_action( 'init', 'et_create_brand_taxonomies' );
	if(!function_exists('et_create_brand_taxonomies')) {
		function et_create_brand_taxonomies() {
			if ( function_exists('etheme_get_option') && !etheme_get_option('enable_brands') ) {
				return;
			}
			$labels = array(
				'name'              => _x( 'Brands', '', 'legenda-core' ),
				'singular_name'     => _x( 'Brand', '', 'legenda-core' ),
				'search_items'      => __( 'Search Brands', 'legenda-core' ),
				'all_items'         => __( 'All Brands', 'legenda-core' ),
				'parent_item'       => __( 'Parent Brand', 'legenda-core' ),
				'parent_item_colon' => __( 'Parent Brand:', 'legenda-core' ),
				'edit_item'         => __( 'Edit Brand', 'legenda-core' ),
				'update_item'       => __( 'Update Brand', 'legenda-core' ),
				'add_new_item'      => __( 'Add New Brand', 'legenda-core' ),
				'new_item_name'     => __( 'New Brand Name', 'legenda-core' ),
				'menu_name'         => __( 'Brands', 'legenda-core' ),
			);

			$args = array(
				'hierarchical'      => true,
				'labels'            => $labels,
				'show_ui'           => true,
				'show_admin_column' => true,
				'query_var'         => true,
	            'capabilities'			=> array(
	            	'manage_terms' 		=> 'manage_product_terms',
					'edit_terms' 		=> 'edit_product_terms',
					'delete_terms' 		=> 'delete_product_terms',
					'assign_terms' 		=> 'assign_product_terms',
	            ),
	            'show_in_rest'       => false,
				'rewrite'           => array( 'slug' => 'brand' ),
			);

			register_taxonomy( 'brand', array( 'product' ), $args );
		}
	}
?>