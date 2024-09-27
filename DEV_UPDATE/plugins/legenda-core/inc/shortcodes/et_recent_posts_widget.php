<?php  
	if ( ! function_exists( 'etheme_recent_posts_widget_shortcode' ) ) :
		function etheme_recent_posts_widget_shortcode($atts, $content = null) {

			if ( !class_exists( 'Etheme_Recent_Posts_Widget' ) ) {
				legenda_theme_activation_text();
				return;
			}

		    $a = shortcode_atts(array(
		        'title' => '',
		        'number' => 5
		    ),$atts);

		    $widget = new Etheme_Recent_Posts_Widget();

		    $args = array(
		        'before_widget' => '<div class="sidebar-widget etheme_widget_recent_entries">',
		        'after_widget' => '</div><!-- //sidebar-widget -->',
		        'before_title' => '<h4 class="widget-title">',
		        'after_title' => '</h4>',
		        'widget_id' => 'etheme_widget_recent_entries',
		    );
		    $instance = array(
		        'title' => $a['title'],
		        'number' => $a['number']
		    );

		    ob_start();
		    $widget->widget($args, $instance);
		    $output = ob_get_contents();
		    ob_end_clean();

		    return $output;
		}
	endif;

?>