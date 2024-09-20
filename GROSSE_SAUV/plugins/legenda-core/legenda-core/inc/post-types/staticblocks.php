<?php 

	add_action('init', 'et_register_static_blocks');

	add_filter( 'manage_staticblocks_posts_columns', 'et_staticblocks_columns' );
	add_action( 'manage_staticblocks_posts_custom_column', 'et_staticblocks_columns_val', 10, 2 );

	if(!function_exists('et_register_static_blocks')) {
	    function et_register_static_blocks() {
	            if ( function_exists('etheme_get_option') && !etheme_get_option('enable_static_blocks') ) {
	                return;
	            }
	            $labels = array(
	                'name' => _x( 'Static Blocks', 'post type general name', 'legenda-core' ),
	                'singular_name' => _x( 'Block', 'post type singular name', 'legenda-core' ),
	                'add_new' => _x( 'Add New', 'static block', 'legenda-core' ),
	                'add_new_item' => sprintf( __( 'Add New %s', 'legenda-core' ), __( 'Static Blocks', 'legenda-core' ) ),
	                'edit_item' => sprintf( __( 'Edit %s', 'legenda-core' ), __( 'Static Blocks', 'legenda-core' ) ),
	                'new_item' => sprintf( __( 'New %s', 'legenda-core' ), __( 'Static Blocks', 'legenda-core' ) ),
	                'all_items' => sprintf( __( 'All %s', 'legenda-core' ), __( 'Static Blocks', 'legenda-core' ) ),
	                'view_item' => sprintf( __( 'View %s', 'legenda-core' ), __( 'Static Blocks', 'legenda-core' ) ),
	                'search_items' => sprintf( __( 'Search %s', 'legenda-core' ), __( 'Static Blocks', 'legenda-core' ) ),
	                'not_found' =>  sprintf( __( 'No %s Found', 'legenda-core' ), __( 'Static Blocks', 'legenda-core' ) ),
	                'not_found_in_trash' => sprintf( __( 'No %s Found In Trash', 'legenda-core' ), __( 'Static Blocks', 'legenda-core' ) ),
	                'parent_item_colon' => '',
	                'menu_name' => __( 'Static Blocks', 'legenda-core' )

	            );
	            $args = array(
	                'labels' => $labels,
	                'public' => true,
	                'publicly_queryable' => true,
	                'show_ui' => true,
	                'show_in_menu' => true,
	                'query_var' => true,
	                'rewrite' => array( 'slug' => 'staticblocks' ),
	                'capability_type' => 'post',
	                'has_archive' => false,
	                'hierarchical' => false,
	                'supports' => array( 'title', 'editor', 'thumbnail', 'page-attributes' ),
	                'menu_position' => 8,
	                'show_in_rest' => true
	            );
	            register_post_type( 'staticblocks', $args );
	    }
	}

	function et_staticblocks_columns($defaults) {
	    return array(
	    	'cb'               => '<input type="checkbox" />',
	        'title'            => esc_html__( 'Title', 'legenda-core' ),
	        'shortcode_column' => esc_html__( 'Shortcode', 'legenda-core' ),
	        'date'             => esc_html__( 'Date', 'legenda-core' ),
	    );
	}
	 
	function et_staticblocks_columns_val($column_name, $post_ID) {
	   if ($column_name == 'shortcode_column') {
            echo '[block id="'.$post_ID.'"]';
	   }
	}
?>