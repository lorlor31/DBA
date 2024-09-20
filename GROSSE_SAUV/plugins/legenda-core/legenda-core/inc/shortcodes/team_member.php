<?php  
	if ( ! function_exists( 'etheme_team_member_shortcode' ) ) :
		function etheme_team_member_shortcode($atts, $content = null) {

		    $a = shortcode_atts(array(
		        'class' => '',
		        'type' => 1,
		        'name' => '',
		        'email' => '',
		        'twitter' => '',
		        'facebook' => '',
		        'skype' => '',
		        'linkedin' => '',
		        'instagram' => '',
		        'position' => '',
		        'content' => '',
		        'img' => '',
		        'img_size' => '270x170'
		    ), $atts);

		    $image = '';

		    $img_size = explode('x', $a['img_size']);

		    $width = $img_size[0];
		    $height = $img_size[1];

		    if($a['img'] != '') {
		        $image = new_etheme_get_image( $a['img'], array( $width, $height ) );
		    }

		    if ($a['content'] != '') {
		        $content = $a['content'];
		    }


		    $html = '';
		    $span = 12;
		    $html .= '<div class="team-member member-type-'.$a['type'].' '.$a['class'].'">';

		        if($a['type'] == 2) {
		            $html .= '<div class="row-fluid">';
		        }
			    if($image != ''){

		            if($a['type'] == 2) {
		                $html .= '<div class="span6">';
		                $span = 6;
		            }
		            $html .= '<div class="member-image">';
		                $html .= $image;
		                if ($a['linkedin'] != '' || $a['twitter'] != '' || $a['facebook'] != '' || $a['skype'] != '' || $a['instagram'] != '') {
		                    $html .= '<div class="member-mask">';
		                        $html .= '<div class="mask-text">';
		                            $html .= '<fieldset>';
		                            $html .= '<legend>'.__('Social Profiles', 'legenda-core').'</legend>';
		                            $html .= '';
		                                if ($a['linkedin'] != '') {
		                                    $html .= '<a href="'.$a['linkedin'].'"><i class="icon-linkedin"></i></a>';
		                                }
		                                if ($a['twitter'] != '') {
		                                    $html .= '<a href="'.$a['twitter'].'"><i class="icon-twitter"></i></a>';
		                                }
		                                if ($a['facebook'] != '') {
		                                    $html .= '<a href="'.$a['facebook'].'"><i class="icon-facebook"></i></a>';
		                                }
		                                if ($a['skype'] != '') {
		                                    $html .= '<a href="skype:'.$a['skype'].'?chat"><i class="icon-skype"></i></a>';
		                                }
		                                if ($a['instagram'] != '') {
		                                    $html .= '<a href="'.$a['instagram'].'"><i class="icon-instagram"></i></a>';
		                                }
		                            $html .= '</fieldset>';
		                        $html .= '</div>';
		                    $html .= '</div>';
		                }
		            $html .= '</div>';
		            $html .= '<div class="clear"></div>';
		            if($a['type'] == 2) {
		                $html .= '</div>';
		            }
			    }


		        if($a['type'] == 2) {
		            $html .= '<div class="span'.$span.'">';
		        }
		        $html .= '<div class="member-details">';
				    if($a['name'] != ''){
					    $html .= '<h5 class="member-position">'.$a['position'].'</h5>';
				    }
		            if($a['position'] != ''){
		                $html .= '<h4>'.$a['name'].'</h4>';
		            }
		            if($a['email'] != ''){
		                $html .= '<p class="member-email"><span>'.__('Email:', 'legenda-core').'</span> <a href="mailto:'.$a['email'].'">'.$a['email'].'</a></p>';
		            }
				    $html .= do_shortcode($content);
		    	$html .= '</div>';

		        if($a['type'] == 2) {
		                $html .= '</div>';
		            $html .= '</div>';
		        }
		    $html .= '</div>';


		    return $html;
		}
	endif;

	if ( !function_exists('etheme_register_team_member') ) {

		function etheme_register_team_member () {
			if(!function_exists('vc_map')) {
				return;
			}

		    $team_member_params = array(
		      'name' => 'Team member',
		      'base' => 'team_member',
		      'icon' => 'icon-wpb-etheme',
		      'category' => 'Eight Theme',
		      'params' => array(
		        array(
		          'type' => 'textfield',
		          "heading" => __("Member name", 'legenda-core'),
		          "param_name" => "name"
		        ),
		        array(
		          'type' => 'textfield',
		          "heading" => __("Member email", 'legenda-core'),
		          "param_name" => "email"
		        ),
		        array(
		          'type' => 'textfield',
		          "heading" => __("Position", 'legenda-core'),
		          "param_name" => "position"
		        ),
		        array(
		          'type' => 'attach_image',
		          "heading" => __("Avatar", 'legenda-core'),
		          "param_name" => "img"
		        ),
		        array(
		          "type" => "textfield",
		          "heading" => __("Image size", "legenda-core"),
		          "param_name" => "img_size",
		          "description" => __("Enter image size. Example in pixels: 200x100 (Width x Height).", "legenda-core")
		        ),
		        array(
		          "type" => "textarea_html",
		          "holder" => "div",
		          "heading" => __("Member information", "legenda-core"),
		          "param_name" => "content",
		          "value" => __("Member description", "legenda-core")
		        ),
		        array(
		          "type" => "dropdown",
		          "heading" => __("Display Type", "legenda-core"),
		          "param_name" => "type",
		          "value" => array( 
		              "", 
		              __("Vertical", 'legenda-core') => 1,
		              __("Horizontal", 'legenda-core') => 2
		            )
		        ),
		        array(
		          'type' => 'textfield',
		          "heading" => __("Twitter link", 'legenda-core'),
		          "param_name" => "twitter"
		        ),
		        array(
		          'type' => 'textfield',
		          "heading" => __("Facebook link", 'legenda-core'),
		          "param_name" => "facebook"
		        ),
		        array(
		          'type' => 'textfield',
		          "heading" => __("Linkedin", 'legenda-core'),
		          "param_name" => "linkedin"
		        ),
		        array(
		          'type' => 'textfield',
		          "heading" => __("Skype name", 'legenda-core'),
		          "param_name" => "skype"
		        ),
		        array(
		          'type' => 'textfield',
		          "heading" => __("Instagram", 'legenda-core'),
		          "param_name" => "instagram"
		        ),
		        array(
		          "type" => "textfield",
		          "heading" => __("Extra Class", 'legenda-core'),
		          "param_name" => "class",
		          "description" => __('If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 'legenda-core')
		        )
		      )
		    );  
	    	vc_map($team_member_params);
	    }
	}
?>