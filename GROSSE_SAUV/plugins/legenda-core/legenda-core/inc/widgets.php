<?php  
	if ( legenda_theme_old() ) return; 

	add_action( 'widgets_init', 'etheme_register_general_widgets' );

	if ( !function_exists('etheme_register_general_widgets')) {

		function etheme_register_general_widgets() {

			$widgets = array(
				'config',
				'et_recent_posts_widget',
				'et_recent_comments',
				'etheme_twitter',
				'etheme_flickr-widget',
				'etheme_widget_recent_entries',
				'etheme_widget_recent_comments',
				'etheme_widget_satick_block',
				'etheme_widget_qr_code',
				'etheme_widget_search',
				'etheme_widget_brands',

			);
			foreach ($widgets as $key) {
				require_once( 'widgets/'.$key.'.php' );
			}

			$widgets = array(
				'Etheme_Twitter_Widget',
				'Etheme_Recent_Posts_Widget',
				'Etheme_Recent_Comments_Widget',
				'Etheme_Flickr_Widget',
				'Etheme_StatickBlock_Widget',
				'Etheme_QRCode_Widget',
				'Etheme_Search_Widget',
				'Etheme_Brands_Widget',
			);
		    foreach ($widgets as $key) {
			    register_widget($key);
			}
		}
	}
?>