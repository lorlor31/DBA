<?php 

	if ( ! function_exists( 'etheme_countdown_shortcode' ) ) :
		function etheme_countdown_shortcode($atts) {
		    $a = shortcode_atts(array(
		        'year' => 2017,
		        'month' => 'January',
		        'day' => 1,
		        'hour' => 00,
		        'minute' => 00,
		        'scheme' => 'white',
		        'class' => '',
		    ),$atts);

		    $date = '';

		    $date .= $a['day'] . ' ';
		    $date .= $a['month'] . ' ';
		    $date .= $a['year'] . ' ';
		    $date .= $a['hour'] . ':';
		    $date .= $a['minute'] . ' ';

			$class = ' ' . $a['scheme'];

			if (!empty($a['class'])) {
				 $class .= ' ' . $a['class'];
			}

		    return '<div class="et-timer'.$class.'" data-final="'.$date.'">
		                <div class="time-block">
		                    <span class="days"><span style="visibility: hidden;">0</span></span>
		                    ' . esc_html__( 'days', 'legenda-core' ) . '
		                </div>
		                <div class="timer-devider">&nbsp;</div>
		                <div class="time-block">
		                    <span class="hours"><span style="visibility: hidden;">0</span></span>
		                    ' . esc_html__( 'hours', 'legenda-core' ) . '
		                </div>
		                <div class="timer-devider">&nbsp;</div>
		                <div class="time-block">
		                    <span class="minutes"><span style="visibility: hidden;">0</span></span>
		                    ' . esc_html__( 'minutes', 'legenda-core' ) . '
		                </div>
		                <div class="timer-devider">&nbsp;</div>
		                <div class="time-block">
		                    <span class="seconds"><span style="visibility: hidden;">0</span></span>
		                    ' . esc_html__( 'seconds', 'legenda-core' ) . '
		                </div>
		                <div class="clear"></div>
		            </div>';
		}
	endif;
    
	if ( !function_exists('etheme_register_countdown') ) {

		function etheme_register_countdown () {

			if(!function_exists('vc_map')) {
				return;
			}

			$countdown_params = array(
		      'name' => 'Countdown',
		      'base' => 'countdown',
		      'icon' => 'icon-wpb-etheme',
		      'category' => 'Eight Theme',
		      'params' => array(
		        array(
		          "type" => "textfield",
		          "heading" => __("Year", 'legenda-core'),
		          "param_name" => "year"
		        ),
		        array(
		          "type" => "dropdown",
		          "heading" => __("Month", 'legenda-core'),
		          "param_name" => "month",
		          "value" => array( 
		          	__("January", 'legenda-core') => 'January', 
		          	__("February", 'legenda-core') => 'February',
		          	__("March", 'legenda-core') => 'March',
		          	__("April", 'legenda-core') => 'April',
		          	__("May", 'legenda-core') => 'May',
		          	__("June", 'legenda-core') => 'June',
		          	__("July", 'legenda-core') => 'July',
		          	__("August", 'legenda-core') => 'August',
		          	__("September", 'legenda-core') => 'September',
		          	__("October", 'legenda-core') => 'October',
		          	__("November", 'legenda-core') => 'November',
		          	__("December", 'legenda-core') => 'December',
		          	)
		        ),
		        array(
		          "type" => "textfield",
		          "heading" => __("Day", 'legenda-core'),
		          "param_name" => "day"
		        ),
		        array(
		          "type" => "textfield",
		          "heading" => __("Hour", 'legenda-core'),
		          "param_name" => "hour"
		        ),
		        array(
		          "type" => "textfield",
		          "heading" => __("Minutes", 'legenda-core'),
		          "param_name" => "minute"
		        ),
		        array(
		          "type" => "dropdown",
		          "heading" => __("Color scheme", 'legenda-core'),
		          "param_name" => "scheme",
		          "value" => array( 
					__("Light", 'legenda-core') => "white",
		          	__("Dark", 'legenda-core') => "dark", 
		          	)
		        ),
		        array(
		          "type" => "textfield",
		          "heading" => __("Extra Class", 'legenda-core'),
		          "param_name" => "class",
		          "description" => __('If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 'legenda-core')
		        )
		      )
		
		    );  
		
		    vc_map($countdown_params);

	   	}
	}

?>