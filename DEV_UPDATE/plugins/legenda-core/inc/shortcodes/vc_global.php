<?php 
	if (function_exists('vc_remove_element') ) {
		vc_remove_element("vc_text_separator");
		vc_remove_element("vc_tour"); 
		vc_remove_element("vc_carousel"); 
	}

	if(!function_exists('etheme_VS_setup')) {
		if(!function_exists('getCSSAnimation')) {
			function getCSSAnimation($css_animation) {
	            $output = '';
	            if ( $css_animation != '' ) {
	                wp_enqueue_script( 'waypoints' );
	                $output = ' wpb_animate_when_almost_visible wpb_'.$css_animation;
	            }
	            return $output;
			}
		}
		if(!function_exists('buildStyle')) {
		    function buildStyle($bg_image = '', $bg_color = '', $bg_image_repeat = '', $font_color = '', $padding = '', $margin_bottom = '') {
		        $has_image = false;
		        $style = '';
		        if((int)$bg_image > 0 && ($image_url = wp_get_attachment_url( $bg_image, 'large' )) !== false) {
		            $has_image = true;
		            $style .= "background-image: url(".$image_url.");";
		        }
		        if(!empty($bg_color) && function_exists('vc_get_css_color')) {
		            $style .= vc_get_css_color('background-color', $bg_color);
		        }
		        if(!empty($bg_image_repeat) && $has_image) {
		            if($bg_image_repeat === 'cover') {
		                $style .= "background-repeat:no-repeat;background-size: cover;";
		            } elseif($bg_image_repeat === 'contain') {
		                $style .= "background-repeat:no-repeat;background-size: contain;";
		            } elseif($bg_image_repeat === 'no-repeat') {
		                $style .= 'background-repeat: no-repeat;';
		            }
		        }
		        if( !empty($font_color) && function_exists('vc_get_css_color') ) {
		            $style .= vc_get_css_color('color', $font_color); 
		        }
		        if( $padding != '' ) {
		            $style .= 'padding: '.(preg_match('/(px|em|\%|pt|cm)$/', $padding) ? $padding : $padding.'px').';';
		        }
		        if( $margin_bottom != '' ) {
		            $style .= 'margin-bottom: '.(preg_match('/(px|em|\%|pt|cm)$/', $margin_bottom) ? $margin_bottom : $margin_bottom.'px').';';
		        }
		        return empty($style) ? $style : ' style="'.$style.'"';
		    }
		}

		function etheme_VS_setup() {

			global $vc_params_list;
			if (!class_exists('WPBakeryVisualComposerAbstract')) return;
			
			$vc_params_list[] = 'icon';

			$shortcodes = array(
				'config',
				'vc_gallery',
				'vc_separator',
				'vc_toggle',
				'vc_accordion',
				'vc_accordion_tab',
				'vc_tabs',
				'vc_tab',
				'vc_posts_slider',
				'vc_button',
				'vc_cta_button',
				'vc_posts_grid',
			);


			foreach ($shortcodes as $key) {
				if (file_exists(__DIR__ . '/vc/'.$key.'.php')) {
					require_once( 'vc/'.$key.'.php' );
				}
			}
		}
	}

	add_action( 'init', 'etheme_VS_setup', 1000);

?>