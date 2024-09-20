<?php  

add_action( 'cmb2_admin_init', 'etheme_base_metaboxes');
/**
 * Define the metabox and field configurations.
 *
 * @param  array $meta_boxes
 * @return array
 */

if(!function_exists('etheme_base_metaboxes')) {
	function etheme_base_metaboxes() {

	    $cmb = new_cmb2_box( array(
			'id'         => 'page_layout',
			'title'      => esc_html__( '[8theme] Layout Options', 'legenda' ),
			'object_types'      => array( 'page', 'post'), // Post type
			'context'    => 'normal',
			'priority'   => 'low',
			'show_names' => true, // Show field names on the left
	        // 'cmb_styles' => false, // false to disable the CMB stylesheet
	        // 'closed'     => true, // Keep the metabox closed by default
	    ) );

	    $cmb->add_field( 
	        array(
	            'id'          => 'sidebar_state',
	            'name'       => esc_html__('Sidebar Position', 'legenda'),
	            'type'        => 'select',
	            'options'     => array(
	            	'' => __('Default' , 'legenda'),
                    'no_sidebar' => __('Without Sidebar', 'legenda'),
                    'left' => __('Left Sidebar', 'legenda'),
                    'right' => __('Right Sidebar' , 'legenda')
	            )
	        )
    	);

	    $cmb->add_field( 
	        array(
	            'id'          => 'widget_area',
	            'name'        => esc_html__('Widget Area', 'legenda'),
	            'type'        => 'select',
	            'options'     => etheme_get_sidebars()
	        )
    	);

	    $cmb->add_field( 
	        array(
	            'id'          => 'sidebar_width',
	            'name'       => esc_html__('Sidebar width', 'legenda'),
	            'type'        => 'select',
	            'options'     => array(
                    '' => 'Default',
                    2 => '1/6',
                    3 => '1/4',
                    4 => '1/3'
	            )
	        )
    	);

	    $cmb->add_field( 
	        array(
	            'id'          => 'custom_header',
	            'name'       => esc_html__('Custom header', 'legenda'),
	            'type'        => 'select',
	            'options'     => array(
                    'inherit'   => 'Inherit',
                    1   => 'Default',
                    2   => 'Variant 2',
                    3   => 'Variant 3',
                    4   => 'Variant 4',
                    5   => 'Variant 5',
                    6   => 'Variant 6',
                    7   => 'Default',
                    9   => 'Transparent',
                    8   => 'Vertical',
	            )
	        )
    	);

	    $cmb->add_field( 
	        array(
	            'id'          => 'page_heading',
	            'name'       => esc_html__('Show Page Heading', 'legenda'),
	            'type'        => 'select',
	            'options'     => array(
	                'enable' => 'Enable',
	                'disable' => 'Disable'
	            )
	        )
    	);

		$cmb->add_field(
			array(
				'id'          => 'page_color_scheme',
				'name'       => esc_html__('Page color scheme', 'legenda'),
				'type'        => 'select',
				'options'     => array(
					'inherit'   => 'Inherit',
					'light' => 'Light',
					'dark' => 'Dark'
				)
			)
		);

	    $cmb->add_field( 
	    	array(
	            'id'          => 'custom_logo',
	            'name'        => esc_html__('Custom logo for this page/post', 'legenda'),
			    'desc' => esc_html__('Upload an image or enter an URL.', 'legenda'),
			    'type' => 'file',
			    'allow' => array( 'url', 'attachment' ) // limit to just attachments with array( 'attachment' )
        	)
    	);

	    $cmb->add_field( 
    	array(
                'id'          => 'custom_nav',
                'name'        => esc_html__('Custom navigation for this page', 'legenda'),
                'type'        => 'select',
                'options'     => et_get_menus_options()
            )
    	);

        if(class_exists('RevSliderAdmin')) {

    	    $cmb->add_field( 
		        array(
		            'id'          => 'page_slider',
		            'name'       => esc_html__('Show revolution slider instead of breadcrumbs and page title', 'legenda'),
		            'type'        => 'select',
		            'options'     => etheme_get_revsliders()
		        )
			);
        }

		$static_blocks = array();
		$static_blocks[] = "--choose--";
		
		if ( etheme_get_option('enable_static_blocks') ) {
			foreach (et_get_static_blocks() as $block) {
				$static_blocks[$block['value']] = $block['label'];
			}
		}

	    $cmb = new_cmb2_box( array(
			'id'         => 'product_options',
			'title'      => esc_html__( '[8theme] Product Options', 'legenda' ),
			'object_types' => array( 'product' ), // Post type
			'context'    => 'normal',
			'priority'   => 'low',
			'show_names' => true, // Show field names on the left
	        // 'cmb_styles' => false, // false to disable the CMB stylesheet
	        // 'closed'     => true, // Keep the metabox closed by default
	    ) );

	    $cmb->add_field( 
	        array(
	            'id'          => 'additional_block',
	            'name'        => esc_html__('Additional information block', 'legenda'),
	            'type'        => 'select',
	            'options'     => $static_blocks
	        )
		);

	    $cmb->add_field( 
	        array(
	            'id'          => 'product_new',
	            'name'        => esc_html__('Mark product as "New"', 'legenda'),
	            'type'        => 'select',
	            'options'     => array(
	            	'disable' => esc_html__('No', 'legenda'),
	            	'enable' => esc_html__('Yes', 'legenda')
	            )
	        )
		);

	    $cmb->add_field( array(
	            'id'          => 'size_guide_img',
	            'name'        => esc_html__('Size Guide img', 'legenda'),
			    'desc' => 'Upload an image or enter an URL.',
			    'type' => 'file',
			    'allow' => array( 'url', 'attachment' ) // limit to just attachments with array( 'attachment' )
	        )
		);

		$cmb->add_field( array(
	            'id'          => 'hover_img',
	            'name'        => esc_html__('Hover img', 'legenda'),
			    'desc' => 'Upload an image or enter an URL.',
			    'type' => 'file',
			    'allow' => array( 'url', 'attachment' ) // limit to just attachments with array( 'attachment' )
	        )
		);

	    $cmb->add_field( 
			array(
			    'name' => esc_html__( 'Title for custom tab', 'legenda' ),
			    'id' => 'custom_tab1_title',
			    'type' => 'text',
			)
		);

		$cmb->add_field( 
			array(
			    'name' => esc_html__( 'Text for custom tab', 'legenda' ),
			    'id' => 'custom_tab1',
			    'type' => 'textarea',
			)
		);
		
	}
}
?>