<?php
class null_instagram_widget extends WP_Widget {

    function __construct() {
		parent::__construct(
			'null-instagram-feed',
			__( 'Instagram', 'legenda-core' ),
			array(
				'classname' => 'null-instagram-feed',
				'description' => esc_html__( 'Displays your latest Instagram photos', 'legenda-core' ),
				'customize_selective_refresh' => true,
			)
		);
	}

	function widget($args, $instance) {

		extract($args, EXTR_SKIP);

		$title = empty( $instance['title'] ) ? '' : apply_filters( 'widget_title', $instance['title'] );
        $user     = empty( $instance['user'] ) ? '' : $instance['user'];
        $username = empty( $instance['username'] ) ? '' : $instance['username'];
		$limit = empty( $instance['number'] ) ? 9 : $instance['number'];
		$columns = empty($instance['columns']) ? 3 : (int) $instance['columns'];
		$size = empty( $instance['size'] ) ? 'large' : $instance['size'];
        $target = empty( $instance['target'] ) ? '_self' : $instance['target'];
		$link = empty( $instance['link'] ) ? '' : $instance['link'];
		$filter = empty($instance['filter_img']) ? '' : $instance['filter_img'];
		$info = empty($instance['info']) ? false : true;
		$slider = empty($instance['slider']) ? false : true;
		$spacing = empty($instance['spacing']) ? false : true;

		// slider args
		$large = empty($instance['large']) ? 4 : $instance['large'];
		$notebook = empty($instance['notebook']) ? 3 : $instance['notebook'];
		$tablet_land = empty($instance['tablet_land']) ? 2 : $instance['tablet_land'];
        $tablet_portrait = empty($instance['tablet_portrait']) ? 2 : $instance['tablet_portrait'];
        $mobile = empty($instance['mobile']) ? 1 : $instance['mobile'];
        $slider_autoplay = empty($instance['slider_autoplay']) ? false : true;
        $slider_speed = empty($instance['slider_speed']) ? 10000 : $instance['slider_speed'];
        $pagination_type = empty($instance['pagination_type']) ? 'hide' : $instance['pagination_type'];
        $default_color = empty($instance['default_color']) ? '#e6e6e6' : $instance['default_color'];
        $active_color = empty($instance['active_color']) ? '#b3a089' : $instance['active_color'];
        $hide_fo = empty($instance['hide_fo']) ? '' : $instance['hide_fo'];
        $hide_buttons = empty($instance['hide_buttons']) ? false : true;

		echo $args['before_widget'];

		if ( ! empty( $title ) ) { echo $args['before_title'] . wp_kses_post( $title ) . $args['after_title']; };

		do_action( 'wpiw_before_widget', $instance );

        $instagram['data'] = array();
        $tag = $username;

		if ( $user ) {

            $instagram = $this->et_get_instagram($limit, $tag, '', $user);

			if ( is_wp_error($instagram) ) {

				echo wp_kses_post( $instagram->get_error_message() );

			} else {

                // slice list down to required limit.
				$instagram['data'] = array_slice( $instagram['data'], 0, $limit );

				// filters for custom classes
                $ulclass = apply_filters( 'wpiw_list_class', 'instagram-pics instagram-size-' . $size );
				$liclass = apply_filters( 'wpiw_item_class', '' );
				$aclass = apply_filters( 'wpiw_a_class', '' );
				$imgclass = apply_filters( 'wpiw_img_class', '' );
				$box_id = rand(1000,10000);

				?><ul class="<?php if( $slider ) { echo "owl-carousel"; } ?> instagram-pics instagram-size-<?php echo esc_attr( $size ); ?> instagram-columns-<?php echo esc_attr( $columns ); ?> <?php if($spacing) echo 'instagram-no-space'; ?> <?php if($slider) echo 'instagram-slider instagram-slider-'.$box_id.''; ?> <?php if ($hide_buttons == true) echo 'navigation_off';  ?> clearfix"><?php
				foreach ( $instagram['data'] as $item ) {

                        switch ( $size ) {
                            case 'thumbnail':
                                $image_src = $item['images']['thumbnail']['url'];
                                break;
                            case 'medium':
                                $image_src = $item['images']['low_resolution']['url'];
                                break;
                            case 'large':
                                $image_src = $item['images']['standard_resolution']['url'];
                                break;
                            default:
                                $image_src = $item['images']['low_resolution']['url'];
                                break;
                        }

                        if ( $link != '' ) {
                            $username = $item['user']['username'];
                        }

                        echo '<li class="' . esc_attr( $liclass ) . '">
                            <a href="' . esc_url( $item['link'] ) . '" target="' . esc_attr( $target ) . '"  class="' . esc_attr( $aclass ) . '">
                                <img src="' . esc_url( $image_src ) . '"  alt="' . esc_attr( $item['caption']['text'] ) . '" title="' . esc_attr( $item['caption']['text'] ) . '"  class="' . esc_attr( $imgclass ) . '"/>';

						if ($info) {
						echo '<div class="insta-info">
							<span class="insta-likes">' . $item['likes']['count']. '</span>
							<span class="insta-comments">' . $item['comments']['count']. '</span>
						</div>';
						}
					echo '</a></li>';
				}
				?></ul><?php

				if($slider) {

           			$items = '[[0, '.$mobile.'], [479,'.$tablet_portrait.'], [619,'.$tablet_land.'], [1200, '.$notebook .'], [1600, '.$large.']]';
		        	echo '
				        <script type="text/javascript">
				            (function() {
				                var instaOptions = {
				                    items:4,
				                    lazyLoad : false,
				                    autoPlay: ' . (($slider_autoplay == true) ? $slider_speed : "false" ). ',
				                    dots: ' . (($pagination_type == "hide") ? "false" : "true") . ',
				                    nav: ' . (($hide_buttons == true) ? "false" : "true" ). ',
				                    navText:["",""],
				                    rewind: ' . (($slider_autoplay == true) ? "true" : "false" ). ',
				                    itemsCustom: '.$items.'
				                };

				                jQuery(".instagram-slider-'.$box_id.'").owlCarousel(instaOptions);

								var instaOwl = jQuery(".instagram-slider-'.$box_id.'").data("owl.carousel");

				                jQuery( window ).bind( "vc_js", function() {
				                	jQuery(".instagram-slider-'.$box_id.'").trigger(\'refresh.owl.carousel\');
									jQuery(".instagram-slider-'.$box_id.' .owl-pagination").addClass("pagination-type-'.$pagination_type.' hide-for-'.$hide_fo.'");
								} );

				            })();
				        </script>
				    ';
			        if ( $pagination_type != 'hide' && $default_color != '#e6e6e6' && $active_color !='#b3a089' ) {
				        echo '
				            <style>
				                .instagram-slider-'.$box_id.' .owl-pagination .owl-page{
				                    background-color:'.$default_color.';
				                }
				                .instagram-slider-'.$box_id.'.owl-carousel .owl-pagination .owl-page:hover{
				                    background-color:'.$active_color.';
				                }
				                .instagram-slider-'.$box_id.' .owl-pagination .owl-page.active{
				                    background-color:'.$active_color.';
				                }
				            </style>
				        ';
				    }
				}
			}
		} else {
            echo '<p class="woocommerce-info">' . esc_html__( 'To use this element select instagram user', 'legenda' ) . '</p>';
        }

        $linkclass = apply_filters( 'wpiw_link_class', 'clear et-follow-instagram' );
		$linkaclass = apply_filters( 'wpiw_linka_class', '' );

		switch ( substr( $username, 0, 1 ) ) {
			case '#':
				$url = '//instagram.com/explore/tags/' . str_replace( '#', '', $username );
				break;

			default:
				$url = '//instagram.com/' . str_replace( '@', '', $username );
				break;
		}

		if ( '' !== $link ) {
			?><p class="<?php echo esc_attr( $linkclass ); ?>"><a href="<?php echo trailingslashit( esc_url( $url ) ); ?>" rel="me" target="<?php echo esc_attr( $target ); ?>" class="<?php echo esc_attr( $linkaclass ); ?>"><?php echo wp_kses_post( $link ); ?></a></p><?php
		}

		do_action( 'wpiw_after_widget', $instance );

		echo $args['after_widget'];
	}

	function form($instance) {
		$instance = wp_parse_args( (array) $instance, array(
            'title' => esc_html__('Instagram', 'legenda-core'),
            'username' => '',
            'link' => esc_html__('Follow Us', 'legenda-core'),
            'number' => 9,
            'size' => 'large',
            'target' => '_self',
            'info' => false,
            'slider' => false,
            'user' =>''
         ) );

        $title = esc_attr($instance['title']);
        $user     = esc_attr( $instance['user'] );
		$username = esc_attr($instance['username']);
		$number = absint($instance['number']);
		$size = esc_attr($instance['size']);
		$columns = @(int) $instance['columns'];
		$target = esc_attr($instance['target']);
		$link = esc_attr($instance['link']);
		$info = esc_attr($instance['info']);
		$slider = esc_attr($instance['slider']);
		$spacing = @esc_attr($instance['spacing']);

		?>
		<p><label for="<?php echo $this->get_field_id('title'); ?>"><?php esc_html_e('Title', 'legenda-core'); ?>: <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></label></p>
                <p><label for="<?php echo $this->get_field_id('user'); ?>"><?php esc_html_e('Choose Instagram account', 'legenda-core'); ?>:</label>
            <select id="<?php echo $this->get_field_id('user'); ?>" name="<?php echo $this->get_field_name('user'); ?>" class="widefat">
                <option value="" <?php selected( '', $user ); ?>> </option>
                <?php
                    $api_data = get_option( 'etheme_instagram_api_data' );
                    $api_data = json_decode($api_data, true);

                    if ( is_array($api_data) && count( $api_data ) ) {
                        foreach ( $api_data as $key => $value ) {
                            $value = json_decode( $value, true );
                            ?>
                            <option value="<?php echo $key ?>" <?php selected( $key, $user ); ?>><?php echo $value['data']['username']; ?></option>
                        <?php 
                        }
                    }
                ?>
            </select>
        </p>
		<p><label for="<?php echo $this->get_field_id('username'); ?>"><?php esc_html_e('Username or hashtag', 'legenda-core'); ?>: <input class="widefat" id="<?php echo $this->get_field_id('username'); ?>" name="<?php echo $this->get_field_name('username'); ?>" type="text" value="<?php echo $username; ?>" /></label></p>
		<p><label for="<?php echo $this->get_field_id('number'); ?>"><?php esc_html_e('Number of photos', 'legenda-core'); ?>: <input class="widefat" id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" type="text" value="<?php echo $number; ?>" /></label></p>
		<p><label for="<?php echo $this->get_field_id('size'); ?>"><?php esc_html_e('Photo size', 'legenda-core'); ?>:</label>
			<select id="<?php echo $this->get_field_id('size'); ?>" name="<?php echo $this->get_field_name('size'); ?>" class="widefat">
				<option value="thumbnail" <?php selected('thumbnail', $size) ?>><?php esc_html_e('Thumbnail', 'legenda-core'); ?></option>
				<option value="medium" <?php selected('medium', $size) ?>><?php esc_html_e('Medium', 'legenda-core'); ?></option>
				<option value="large" <?php selected('large', $size) ?>><?php esc_html_e('Large', 'legenda-core'); ?></option>
			</select>
		</p>
		<p><label for="<?php echo $this->get_field_id('target'); ?>"><?php esc_html_e('Open links in', 'legenda-core'); ?>:</label>
			<select id="<?php echo $this->get_field_id('target'); ?>" name="<?php echo $this->get_field_name('target'); ?>" class="widefat">
				<option value="_self" <?php selected('_self', $target) ?>><?php esc_html_e('Current window (_self)', 'legenda-core'); ?></option>
				<option value="_blank" <?php selected('_blank', $target) ?>><?php esc_html_e('New window (_blank)', 'legenda-core'); ?></option>
			</select>
		</p>
		<p><label for="<?php echo $this->get_field_id('columns'); ?>"><?php esc_html_e('Columns', 'legenda-core'); ?>:</label>
			<select id="<?php echo $this->get_field_id('columns'); ?>" name="<?php echo $this->get_field_name('columns'); ?>" class="widefat">
				<option value="2" <?php selected(2, $columns) ?>>2</option>
				<option value="3" <?php selected(3, $columns) ?>>3</option>
				<option value="4" <?php selected(4, $columns) ?>>4</option>
				<option value="5" <?php selected(5, $columns) ?>>5</option>
				<option value="6" <?php selected(6, $columns) ?>>6</option>
			</select>
		</p>
		<p><label for="<?php echo $this->get_field_id('link'); ?>"><?php esc_html_e('Link text', 'legenda-core'); ?>: <input class="widefat" id="<?php echo $this->get_field_id('link'); ?>" name="<?php echo $this->get_field_name('link'); ?>" type="text" value="<?php echo $link; ?>" /></label></p>
		<p>
			<input type="checkbox" <?php checked( true, $info, true); ?> id="<?php echo $this->get_field_id('info'); ?>" name="<?php echo $this->get_field_name('info'); ?>">
			<label for="<?php echo $this->get_field_id('info'); ?>"><?php esc_html_e('Additional information', 'legenda-core'); ?></label>
		</p>
		<p>
			<input type="checkbox" <?php checked( true, $slider, true); ?> id="<?php echo $this->get_field_id('slider'); ?>" name="<?php echo $this->get_field_name('slider'); ?>">
			<label for="<?php echo $this->get_field_id('slider'); ?>"><?php esc_html_e('Carousel', 'legenda-core'); ?></label>
		</p>
		<p>
			<input type="checkbox" <?php checked( true, $spacing, true); ?> id="<?php echo $this->get_field_id('spacing'); ?>" name="<?php echo $this->get_field_name('spacing'); ?>">
			<label for="<?php echo $this->get_field_id('spacing'); ?>"><?php esc_html_e('Without spacing', 'legenda-core'); ?></label>
		</p>
		<?php

	}

	function update($new_instance, $old_instance) {
		$instance = $old_instance;
		$instance['title'] = strip_tags($new_instance['title']);
        $instance['user']     =  ( $new_instance['user'] ) ? $new_instance['user'] : '' ;
		$instance['username'] = trim(strip_tags($new_instance['username']));
		$instance['number'] = !absint($new_instance['number']) ? 9 : $new_instance['number'];
		$instance['columns'] = !absint($new_instance['columns']) ? 3 : $new_instance['columns'];
		$instance['size'] = (($new_instance['size'] == 'thumbnail' || $new_instance['size'] == 'medium' || $new_instance['size'] == 'large' || $new_instance['size'] == 'small') ? $new_instance['size'] : 'thumbnail');
		$instance['target'] = (($new_instance['target'] == '_self' || $new_instance['target'] == '_blank') ? $new_instance['target'] : '_self');
		$instance['link'] = strip_tags($new_instance['link']);
		$instance['info'] = ($new_instance['info'] != '') ? true : false;
		$instance['slider'] = ($new_instance['slider'] != '') ? true : false;
		$instance['spacing'] = ($new_instance['spacing'] != '') ? true : false;
		return $instance;
	}

    function et_get_instagram( $number = '' , $tag, $last = '', $token = false ){
        $count    = $number;
        $api_data = get_option( 'etheme_instagram_api_data' );
        $api_data = json_decode($api_data, true);
        $username = '';

        foreach ( $api_data as $key => $value ) {
            $value = json_decode( $value, true );

            if ( $key == $token ) {
                $username = $value['data']['username'];
            }
        }
        if ( $tag ) {
            $instagram = get_transient('etheme-instagram-data-tag-' . $tag);
        } else {
            if ( $username ) {
                $instagram = get_transient( 'etheme-instagram-data-user-' . $username);
            } else {
                return new WP_Error('error', esc_html__( 'Error: To use this element select instagram user', 'legenda-core' ) );
            }
        } 

        $callback = $instagram;
        if ( $instagram === false || isset( $_GET['et_reinit_instagram'] ) ) {
            $api_settings = get_option( 'etheme_instagram_api_settings' );
            $api_settings = json_decode($api_settings, true);
            
            $insta_time = etheme_get_option( 'instagram_time' );

            switch ( $api_settings['time_type'] ) {
                case 'min':
                    $insta_time = $api_settings['time'] * MINUTE_IN_SECONDS;
                    break;

                case 'hour':
                    $insta_time = $api_settings['time'] * HOUR_IN_SECONDS;
                    break;

                case 'day':
                    $insta_time = $api_settings['time'] * DAY_IN_SECONDS;
                    break;
                default:
                    $insta_time = 2*HOUR_IN_SECONDS;
                    break;
            }

            if ( ! $token ) {
                return new WP_Error('error', esc_html__( 'Error: To use this element enter instagram access token', 'legenda-core' ) );
            }

            if ( ! $number ) {
                $number = '&count=33';
            } else {
                $number = '&count=' . $number;
            }

            if ( $last ) {
                $last = '&max_id=' . $last;
            }

            $url = 'https://api.instagram.com/v1/users/self/media/recent/?access_token=' . $token . $last . $number;

            if (!empty( $tag )) {
                global $wp_version;
                $url = 'https://www.instagram.com/explore/tags/' . $tag . '/?__a=1';

                $callback = wp_remote_get( $url, array(
                    'user-agent' => 'Instagram/' . $wp_version . '; ' . home_url()
                ) );

                if ( is_wp_error( $callback ) ){
                    return new WP_Error( 'error', esc_html__( 'Unable to communicate with Instagram.', 'legenda-core' ) );

                }
                if ( 200 != wp_remote_retrieve_response_code( $callback ) ){
                    return new WP_Error( 'error', esc_html__( 'Instagram did not return a 200.', 'legenda-core' ) );

                }

                $callback = wp_remote_retrieve_body( $callback );
                $callback = json_decode($callback, true);

                if ( isset( $callback['graphql']['user']['edge_owner_to_timeline_media']['edges'] ) ) {
                    $images = $callback['graphql']['user']['edge_owner_to_timeline_media']['edges'];
                } elseif ( isset( $callback['graphql']['hashtag']['edge_hashtag_to_media']['edges'] ) ) {
                    $images = $callback['graphql']['hashtag']['edge_hashtag_to_media']['edges'];
                } else {
                    return new WP_Error( 'error', esc_html__( 'Instagram has returned invalid data.', 'wp-instagram-widget' ) );
                }

                $i = 0;

                $instagram = array();
                foreach ( $images as $image ) {
                    
                    if ( $i == $count ) {
                        break;
                    }

                    $i++;
                    $caption = __( 'Instagram Image', 'wp-instagram-widget' );
                    if ( ! empty( $image['node']['edge_media_to_caption']['edges'][0]['node']['text'] ) ) {
                        $caption = wp_kses( $image['node']['edge_media_to_caption']['edges'][0]['node']['text'], array() );
                    }
                    $instagram['data'][] = array(
                        'images' => array(
                            'thumbnail' => array(
                                'url' => preg_replace( '/^https?\:/i', '', $image['node']['thumbnail_resources'][0]['src'] ),
                                'width' => '150',
                                'height' => '150',
                            ), 
                            'low_resolution' => array(
                                'url' => preg_replace( '/^https?\:/i', '', $image['node']['thumbnail_resources'][2]['src'] ),
                                'width' => '320',
                                'height' => '320',
                            ), 
                            'standard_resolution' => array(
                                'url' => preg_replace( '/^https?\:/i', '', $image['node']['thumbnail_resources'][4]['src'] ),
                                'width' => '640',
                                'height' => '640',
                            ), 
                        ),
                        'caption'     => array(
                            'text' => $caption
                        ),
                        $caption,
                        'user' => array(
                            'username' => ''
                        ),
                        'link'        => trailingslashit( '//www.instagram.com/p/' . $image['node']['shortcode'] ),
                        'comments'    => array(
                            'count' => $image['node']['edge_media_to_comment']['count'],
                        ), 
                        'likes'       => array(
                            'count' => $image['node']['edge_liked_by']['count'],
                        ), 
                    );
                }

                $callback = $instagram;

            } else {
                $callback = wp_remote_get( $url );

                if ( ! isset($callback['response']) ) {
                    return new WP_Error('error', esc_html__( 'Error: Can not get response', 'legenda-core' ));
                } elseif( ! isset( $callback['response']['code'] ) ){
                    return new WP_Error('error', esc_html__( 'Error: Can not get response code', 'legenda-core' ));
                } elseif( $callback['response']['code'] != 200 ){

                    $callback = wp_remote_retrieve_body( $callback );
                    $callback = json_decode($callback);

                    return new WP_Error('error', esc_html__( 'Error: ', 'legenda-core' ) . $callback->meta->error_message);
                }

                $callback = wp_remote_retrieve_body( $callback );
                $callback = json_decode($callback, true);

                if ( empty( $callback ) ) {
                    return new WP_Error('error', esc_html__( 'Error: instagram did not return any dada', 'legenda-core' ));
                }
            }

            if ( $tag ) {
                set_transient( 'etheme-instagram-data-tag-' . $tag, $callback, $insta_time );
            } else {
                set_transient( 'etheme-instagram-data-user-' . $username, $callback, $insta_time );
            }
        }
        return $callback;
    }
}