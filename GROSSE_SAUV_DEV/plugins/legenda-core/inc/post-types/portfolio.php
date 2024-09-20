<?php  

	if (!function_exists('etheme_portfolio_init')) {

		add_action('init', 'etheme_portfolio_init', 1); 

		function etheme_portfolio_init(){

			if ( function_exists('etheme_get_option') && !etheme_get_option('enable_portfolio') ) {
				return;
			}

			$labels = array(
				'name' => _x('Projects', 'post type general name', 'legenda-core'),
				'singular_name' => _x('Project', 'post type singular name', 'legenda-core'),
				'add_new' => _x('Add New', 'project', 'legenda-core'),
				'add_new_item' => __('Add New Project', 'legenda-core'),
				'edit_item' => __('Edit Project', 'legenda-core'),
				'new_item' => __('New Project', 'legenda-core'),
				'view_item' => __('View Project', 'legenda-core'),
				'search_items' => __('Search Projects', 'legenda-core'),
				'not_found' =>  __('No projects found', 'legenda-core'),
				'not_found_in_trash' => __('No projects found in Trash', 'legenda-core'),
				'parent_item_colon' => '',
				'menu_name' => 'Portfolio'
			
			);
			
			$args = array(
				'labels' => $labels,
				'public' => true,
				'publicly_queryable' => true,
				'show_ui' => true,
				'show_in_menu' => true,
				'query_var' => true,
				'rewrite' => true,
				'capability_type' => 'post',
				'has_archive' => true,
				'hierarchical' => false,
				'menu_position' => null,
				'supports' => array('title','editor','author','thumbnail','excerpt','comments'),
				'rewrite' => array('slug' => 'project'),
				'show_in_rest' => true
			);
			
			register_post_type('etheme_portfolio',$args);

			$labels = array(
				'name' => _x( 'Tags', 'taxonomy general name', 'legenda-core' ),
				'singular_name' => _x( 'Tag', 'taxonomy singular name', 'legenda-core' ),
				'search_items' =>  __( 'Search Types', 'legenda-core' ),
				'all_items' => __( 'All Tags', 'legenda-core' ),
				'parent_item' => __( 'Parent Tag', 'legenda-core' ),
				'parent_item_colon' => __( 'Parent Tag:', 'legenda-core' ),
				'edit_item' => __( 'Edit Tags', 'legenda-core' ),
				'update_item' => __( 'Update Tag', 'legenda-core' ),
				'add_new_item' => __( 'Add New Tag', 'legenda-core' ),
				'new_item_name' => __( 'New Tag Name', 'legenda-core' ),
			);
			
			// Custom taxonomy for Project Tags
			/*register_taxonomy('tag',array('etheme_portfolio'), array(
				'hierarchical' => false,
				'labels' => $labels,
				'show_ui' => true,
				'query_var' => true,
				'rewrite' => array( 'slug' => 'tag' ),
			));*/
			
			$labels2 = array(
				'name' => _x( 'Portfolio Categories', 'taxonomy general name', 'legenda-core' ),
				'singular_name' => _x( 'Category', 'taxonomy singular name', 'legenda-core' ),
				'search_items' =>  __( 'Search Types', 'legenda-core' ),
				'all_items' => __( 'All Categories', 'legenda-core' ),
				'parent_item' => __( 'Parent Category', 'legenda-core' ),
				'parent_item_colon' => __( 'Parent Category:', 'legenda-core' ),
				'edit_item' => __( 'Edit Categories', 'legenda-core' ),
				'update_item' => __( 'Update Category', 'legenda-core' ),
				'add_new_item' => __( 'Add New Category', 'legenda-core' ),
				'new_item_name' => __( 'New Category Name', 'legenda-core' ),
			);
			
			
			register_taxonomy('categories',array('etheme_portfolio'), array(
				'hierarchical' => true,
				'labels' => $labels2,
				'show_ui' => true,
				'query_var' => true,
				'rewrite' => array( 'slug' => 'category' ),
			));

		}

	}
?>