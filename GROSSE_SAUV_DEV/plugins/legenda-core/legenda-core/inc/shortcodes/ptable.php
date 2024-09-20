<?php  
	if ( ! function_exists( 'etheme_ptable_shortcode' ) ) :
		function etheme_ptable_shortcode($atts, $content = null) {
		    $a = shortcode_atts(array(
		        'class' => '',
		        'style' => 2,
		        'columns' => 1,
		        'content' => ''
		    ),$atts);
		    return '<div class="pricing-table columns'.$a['columns'].' '.$a['class'].' style'.$a['style'].'">'.do_shortcode($content.$a['content']).'</div>';
		}
	endif;

	if ( !function_exists('etheme_register_ptable') ) {

		function etheme_register_ptable () {

			if(!function_exists('vc_map')) {
				return;
			}

		    $demoTable = "\n\t".'<ul>';
		    $demoTable .= "\n\t\t".'<li class="row-title">Free</li>';
		    $demoTable .= "\n\t\t".'<li class="row-price"><sup class="currency">$</sup>19<sup>00</sup><sub>per month</sub></li>';
		    $demoTable .= "\n\t\t".'<li>512 mb</li>';
		    $demoTable .= "\n\t\t".'<li>0.6 GHz</li>';
		    $demoTable .= "\n\t\t".'<li>Lorem ipsum dolor sit amet, consectetur adipisicing elit.</li>';
		    $demoTable .= "\n\t\t".'<li><a href="#" class="button">Add to Cart</a></li>';
		    $demoTable .= "\n\t".'</ul>';
		    
		    
		    $ptable_params = array(
		      'name' => 'Pricing Table',
		      'base' => 'ptable',
		      'icon' => 'icon-wpb-etheme',
		      'category' => 'Eight Theme',
		      'params' => array(
		        array(
		          "type" => "textarea_html",
		          "holder" => "div",
		          "heading" => "Table",
		          "param_name" => "content",
		          "value" => $demoTable
		        ),
		        array(
		          "type" => "dropdown",
		          "heading" => __("Style", 'legenda-core'),
		          "param_name" => "style",
		          "value" => array( "", __("default", 'legenda-core') => "default", __("Style 2", 'legenda-core') => "style2")
		        ),
		        array(
		          "type" => "textfield",
		          "heading" => __("Extra Class", 'legenda-core'),
		          "param_name" => "class",
		          "description" => __('If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 'legenda-core')
		        )
		      )
		
		    );  
		
		    vc_map($ptable_params);
		}
	}
?>