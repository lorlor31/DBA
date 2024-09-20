<?php
if(!function_exists('et_twitter_slider')) {
	function et_twitter_slider($atts) {
			
		extract( shortcode_atts( array(
			'title' => '',
			'user' => '',
			'consumer_key' => '',
			'consumer_secret' => '',
			'user_token' => '',
			'user_secret' => '',
			'style' =>'black',
			'limit' => 10,
			'class' => ''
		), $atts ) );

		if(empty($consumer_key) || empty($consumer_secret) || empty($user)) {
			return __('Not enough information', 'legenda-core');
		}

		$atts['usernames'] = $atts['user'];
		if (!isset($atts['limit'])) $atts['limit'] = 10;

		$tweets_array = get_tweets($atts);
		$output = '';
		$box_id = rand(1000,9999);

		$output .= '<div class="et-twitter-slider style-' . esc_attr($style) . ' ' . esc_html($class) . '">';
		if($title != '') {
			$output .= '<h2 class="twitter-slider-title"><span>'.$title.'</span></h2>';
		}


		$output .= '<ul class="et-tweets owl-carousel owl-theme slider-'.$box_id.'">';


		if(!empty($tweets_array['errors']) && count($tweets_array['errors']) > 0) {
			foreach($tweets_array['errors'] as $error) {
				$output .= '<li class="et-tweet error">';
				$output .= $error['message'];
				$output .= '</li>';
			}
		} else {
			foreach($tweets_array as $tweet) {
				$output .= '<li class="et-tweet">';
				$output .= $tweet['text'];
				$output .= '</li>';
			}
		}



		$output .= '</ul>';

		$output .= '</div>';

		$items = '[[0, 1], [479,1], [619,1], [768,1],  [1200, 1], [1600, 1]]';
		$output .=  '<script type="text/javascript">';
		$output .=  '     jQuery(".slider-'.$box_id.'").owlCarousel({';
		$output .=  '         items:1, ';
		$output .=  '         nav: true,';
		$output .=  '         navText:["",""],';
		$output .=  '         rewindNav: false,';
		$output .=  '         dots: false,';
		$output .=  '         itemsCustom: '.$items.'';
		$output .=  '    });';
		$output .=  ' </script>';

		return $output;

	}
}

function get_tweets($settings) {

	if ( !$settings['consumer_key'] || !$settings['consumer_secret'] ) {
		echo '<p>'.
		     sprintf(esc_html__('Please, enter %1$sConsumer key%2$s and %1$sConsumer secret%2$s', 'legenda-core'), '<strong>', '</strong>') .
		     '</p>';
		return;
	}

	$connection = new \TwitterOAuth(
		$settings['consumer_key'],    // Consumer key
		$settings['consumer_secret']  // Consumer secret
	);

	$settings['tweets_type'] = 'account';

	$posts_data_transient_name = 'etheme-twitter-feed-widget-posts-data-' . sanitize_title_with_dashes( $settings['tweets_type'] . $settings['usernames'] . $settings['limit'] );
	$readyTweets = maybe_unserialize( base64_decode( get_transient( $posts_data_transient_name ) ) );

	if ( ! $readyTweets || isset($_GET['et_clear_twitter_catch']) ) {

		if ( empty($settings['usernames']) ) {
			echo '<p>'.
			     esc_html__('Please, enter Username', 'legenda-core') .
			     '</p>';
			return false;
		}

		$readyTweets = $connection->get(
			'statuses/user_timeline',
			array(
				'count' => $settings['limit'],
				'screen_name' => $settings['usernames']
			)
		);

		if ( $connection->http_code != 200 ) {
			echo '<p class="elementor-panel-alert elementor-panel-alert-danger">'.
			     esc_html__('Twitter not return 200', 'legenda-core') .
			     '</p>';
			return false;
		}

		$encode_posts = base64_encode( maybe_serialize( $readyTweets ) );
		set_transient( $posts_data_transient_name, $encode_posts, apply_filters( 'etheme_twitter_feed_cache_time', HOUR_IN_SECONDS * 2 ) );
	}

	if ( ! $readyTweets ) {
		echo '<p class="elementor-panel-alert elementor-panel-alert-warning">'.
		     esc_html__('Twitter did not return any data', 'legenda-core') .
		     '</p>';
		return false;
	}

	$tweets = array();

	foreach ($readyTweets as $tweet) {
		$screen_name = $tweet->user->screen_name;
		$text = parse_tweet( $tweet, $settings );

		$id_str = $tweet->id_str;
		$permalink = 'https://twitter.com/' . $screen_name . '/status/' . $id_str;

		$tweets[] = array(
			'text'      => $text,
			'name'      => $tweet->user->name,
			'screen_name'      => $screen_name,
			'verified' => $tweet->user->verified,
			'id_str' => $id_str,
			'permalink' => $permalink,
			'time'      => false,
			'favorite_count' => $tweet->favorite_count,
			'retweet_count' => $tweet->retweet_count,
		);
	}

	return $tweets;
}

function parse_tweet($tweet, $settings) {
	// If the Tweet a ReTweet - then grab the full text of the original Tweet
	if( isset( $tweet->retweeted_status ) ) {
		// Split it so indices count correctly for @mentions etc.
		$rt_section = current( explode( ":", $tweet->text ) );
		$text = $rt_section.": ";
		// Get Text
		$text .= $tweet->retweeted_status->text;
	} else {
		// Not a retweet - get Tweet
		$text = $tweet->text;
	}

	// Link Creation from clickable items in the text
	$text = preg_replace( '/((http)+(s)?:\/\/[^<>\s]+)/i', '<a href="$0" target="_blank" rel="nofollow noopener">$0</a>', $text );
	// Clickable Twitter names
	$text = preg_replace( '/[@]+([A-Za-z0-9-_]+)/', '<a href="https://twitter.com/$1" target="_blank" rel="nofollow noopener">@\\1</a>', $text );
	// Clickable Twitter hash tags
	$text = preg_replace( '/[#]+([A-Za-z0-9-_]+)/', '<a href="https://twitter.com/hashtag/$1?src=hashtag_click" target="_blank" rel="nofollow noopener">$0</a>', $text );
	return $text;
}

if ( !function_exists('etheme_register_et_twitter_slider') ) {

	function etheme_register_et_twitter_slider () {
		if(!function_exists('vc_map')) {
			return;
		}

	    $twitter_params = array(
	      'name' => 'Twitter Slider',
	      'base' => 'twitter_slider',
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
	          "heading" => __("User account name", 'legenda-core'),
	          "param_name" => "user"
	        ),
	        array(
	          "type" => "textfield",
	          "heading" => __("Consumer Key", 'legenda-core'),
	          "param_name" => "consumer_key"
	        ),
	        array(
	          "type" => "textfield",
	          "heading" => __("Consumer Secret", 'legenda-core'),
	          "param_name" => "consumer_secret"
	        ),
	        array(
	          "type" => "textfield",
	          "heading" => __("Limit", 'legenda-core'),
	          "param_name" => "limit"
	        ),
	        array(
	          "type" => "dropdown",
	          "heading" => __("Choose color scheme ", 'legenda-core'),
	          "param_name" => "style",
	          "value" => array( 
	          	__("Dark", 'legenda-core') => "dark", 
				__("Light", 'legenda-core') => "white",
	            )
	        ),
	        array(
	          "type" => "textfield",
	          "heading" => __("Extra Class", 'legenda-core'),
	          "param_name" => "class"
	        ),
	      )
	
	    );  
	
	    vc_map($twitter_params);
	}
}

?>