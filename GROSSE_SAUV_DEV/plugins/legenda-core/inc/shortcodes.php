<?php 
	
	if ( legenda_theme_old() ) return;

	function legenda_load_shortcodes() {
		$shortcodes = array(
			'contact_form',
			'et_brands',
			'etheme_search',
			'et_twitter_slider',
			'teaser_box',
			'quick_view',
			'etheme_products',
			'etheme_product_categories',
			'etheme_featured',
			'etheme_new',
			'template_url',
			'base_url',
			'recent_posts',
			'button',
			'alert',
			'title',
			'counter',
			'callto',
			'dropcap',
			'blockquote',
			'checklist',
			'et_section',
			'row',
			'column',
			'toggle_block',
			'toggle',
			'tabs',
			'tab',
			'hr',
			'countdown',
			'tooltip',
			'vimeo',
			'youtube',
			'gmaps',
			'qrcode',
			'share',
			'image',
			'team_member',
			'googlefont',
			'block',
			'et_recent_posts_widget',
			'et_recent_comments',
			'ptable',
			'single_post',
			'banner',
			'icon',
			'icon_box',
			'googlechart',
			'testimonials',
			// 'et_instagram',
			'portfolio_global',
		);
			
		foreach ($shortcodes as $key) {
			require_once( 'shortcodes/'.$key.'.php' );
		}
	}

	// vc functions, shortcodes
	require_once( 'shortcodes/vc_global.php' );

	$shortcodes_init = array(
		'legenda_load_shortcodes',
		'etheme_register_et_brands',
		'etheme_register_etheme_search',
		'etheme_register_et_twitter_slider',
		'etheme_register_team_member',
		'etheme_register_icon',
		'etheme_register_icon_box',
		'etheme_register_banner',
		'etheme_register_ptable',
		'etheme_register_single_post',
		'etheme_register_countdown',
		'etheme_register_etheme_product_categories',
		'etheme_register_teaser_box',
		'etheme_register_etheme_products',
		// 'etheme_instagram_shortcode'
	);

	foreach ($shortcodes_init as $key) {
		add_action( 'init', $key, 999 );
	}
	
	add_action( 'init', 'legenda_shortcodes_init' );
	if( ! function_exists( 'legenda_shortcodes_init' ) ) {
	    function legenda_shortcodes_init() {

    		$shortcodes = array(
    			// 'et_instagram' => 'etheme_instagram_shortcode',
				'contact_form' => 'et_contact_form',
				'brands' => 'et_brands',
				'teaser_box' => 'etheme_teaser_box_shortcodes',
				'etheme_search' => 'etheme_search',
				'twitter_slider' => 'et_twitter_slider',
				'quick_view' => 'etheme_quick_view_shortcodes',
				'etheme_products' => 'etheme_products_shortcodes',
				'etheme_product_categories' => 'etheme_product_categories',
				'etheme_featured' => 'etheme_featured_shortcodes',
				'etheme_new' => 'etheme_new_shortcodes',
				'template_url' => 'etheme_template_url_shortcode',
				'base_url' => 'etheme_base_url_shortcode',
				'recent_posts' => 'etheme_recent_posts_shortcode',
				'button' => 'etheme_btn_shortcode',
				'alert' => 'etheme_alert_shortcode',
				'title' => 'etheme_title_shortcode',
				'counter' => 'etheme_counter_shortcode',
				'callto' => 'etheme_callto_shortcode',
				'dropcap' => 'etheme_dropcap_shortcode',
				'blockquote' => 'etheme_blockquote_shortcode',
				'checklist' => 'etheme_checklist_shortcode',
				'et_section' => 'etheme_et_section_shortcode',
				'row' => 'etheme_row_shortcode',
				'column' => 'etheme_column_shortcode',
				'toggle_block' => 'etheme_toggle_block_shortcode',
				'toggle' => 'etheme_toggle_shortcode',
				'tabs' => 'etheme_tabs_shortcode',
				'tab' => 'etheme_tab_shortcode',
				'hr' => 'etheme_hr_shortcode',
				'countdown' => 'etheme_countdown_shortcode',
				'tooltip' => 'etheme_tooltip_shortcode',
				'vimeo' => 'etheme_vimeo_shortcode',
				'youtube' => 'etheme_youtube_shortcode',
				'gmaps' => 'etheme_gmaps_shortcode',
				'qrcode' => 'etheme_qrcode_shortcode',
				'share' => 'etheme_share_shortcode',
				'image' => 'etheme_image_shortcode',
				'team_member' => 'etheme_team_member_shortcode',
				'googlefont' => 'etheme_googlefont_shortcode',
				'block' => 'etheme_block_shortcode',
				'et_recent_posts_widget' => 'etheme_recent_posts_widget_shortcode',
				'et_recent_comments' => 'etheme_recent_comments_shortcode',
				'ptable' => 'etheme_ptable_shortcode',
				'single_post' => 'etheme_featured_post_shortcode',
				'banner' => 'etheme_banner_shortcode',
				'icon' => 'etheme_icon_shortcode',
				'icon_box' => 'etheme_icon_box_shortcode',
				'googlechart' => 'etheme_googlechart_shortcode',

				'portfolio_grid' => 'etheme_portfolio_grid_shortcode',
				'portfolio' => 'etheme_portfolio_shortcode'

			);

	    	foreach ($shortcodes as $key => $value) {
		    	add_shortcode( $key, $value );	
	    	}

	    }
	}

?>