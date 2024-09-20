<?php 
	/**
	 * List all (or limited) product categories
	 *
	 * @access public
	 * @param array $atts
	 * @return string
	 */
	if(!function_exists('etheme_product_categories')) {
	    function etheme_product_categories( $atts ) {
	        global $woocommerce_loop;

		    if ( !class_exists('Woocommerce') ) return false;

	        extract( shortcode_atts( array(
	            'number'       => null,
	            'title'        => '',
	            'orderby'      => 'name',
	            'order'        => 'ASC',
	            'hide_empty'   => 1,
	            'parent'       => '',
	            'display_type' => 'grid',
	            'columns'	   => '4',
	            'items_l'	   => '4',
	            'items_d'	   => '4',
	            'items_t'	   => '3',
	            'items_p'	   => '1',
	            'class'        => '',
	        ), $atts ) );

	        if ( $display_type ==  'grid' ) {
	        	$class .= 'column-' . $columns . ' ';
	        }

	        if ( isset( $atts[ 'ids' ] ) ) {
	            $ids = explode( ',', $atts[ 'ids' ] );
	            $ids = array_map( 'trim', $ids );
	        } else {
	            $ids = array();
	        }

	        $title_output = '';

	        if($title != '') {
	            $title_output = '<h3 class="title"><span>' . $title . '</span></h3>';
	        }

	        $hide_empty = ( $hide_empty == true || $hide_empty == 1 ) ? 1 : 0;

	        // get terms and workaround WP bug with parents/pad counts
	        $args = array(
	            'orderby'    => $orderby,
	            'order'      => $order,
	            'hide_empty' => $hide_empty,
	            'include'    => $ids,
	            'pad_counts' => true,
	            'child_of'   => $parent
	        );

	        $product_categories = get_terms( 'product_cat', $args );

	        if ( $parent !== "" ) {
	            $product_categories = wp_list_filter( $product_categories, array( 'parent' => $parent ) );
	        }

	        if ( $hide_empty ) {
	            foreach ( $product_categories as $key => $category ) {
	                if ( $category->count == 0 ) {
	                    unset( $product_categories[ $key ] );
	                }
	            }
	        }

	        if ( $number ) {
	            $product_categories = array_slice( $product_categories, 0, $number );
	        }

	        $woocommerce_loop['columns'] = $columns;

	        if($display_type == 'slider') {
	            $class .= 'slider-container owl-carousel carousel-area';
	        } else {
	            $class .= 'row';
	        }

	        $box_id = rand(1000,10000);

	        ob_start();

	        // Reset loop/columns globals when starting a new loop
	        $woocommerce_loop['loop'] = $woocommerce_loop['column'] = '';

	        $woocommerce_loop['display_type'] = $display_type;

	        if ( $product_categories ) {


	            if($display_type == 'menu') {
	              $instance = array(
	                'title' => $title,
	                'hierarchical' => 1,
	                'orderby'    => $orderby,
	                'order'      => $order,
	                'hide_empty' => $hide_empty,
	                'include'    => $ids,
	                'pad_counts' => true,
	                'child_of'   => $parent
	              );
	              $args = array();
	              the_widget( 'WC_Widget_Product_Categories', $instance, $args );
	            } else {

	              echo $title_output;

	              echo '<div class="categoriesCarousel '.$class.' slider-'.$box_id.'">';

	              foreach ( $product_categories as $category ) {

	                  wc_get_template( 'content-product_cat.php', array(
	                      'category' => $category,
		                  'category_in_carousel' => true
	                  ) );

	              }

	              echo '</div>';

	            }


	            if($display_type == 'slider') {
	                echo '
	                    <script type="text/javascript">
	                        jQuery(".slider-'.$box_id.'").owlCarousel({
	                            items:4,
	                            lazyLoad : true,
	                            nav: true,
								navText:["",""],
	                            rewindNav: false,
	                            dots: false,
	                            itemsCustom: [[0, 1], [479, ' . $items_p . '], [619, ' . $items_t . '], [768, ' . $items_t . '],  [1200, ' . $items_d . '], [1600, ' . $items_l . ']]
	                        });

	                    </script>
	                ';
	            }

	        }

	        woocommerce_reset_loop();

	        return ob_get_clean();
	    }
	}
    
	if ( !function_exists('etheme_register_etheme_product_categories') ) {

		function etheme_register_etheme_product_categories () {

			if(!function_exists('vc_map')) {
				return;
			}

		    $cats_params = array(
		      'name' => 'Product categories',
		      'base' => 'etheme_product_categories',
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
		          "heading" => __("Number of categories", 'legenda-core'),
		          "param_name" => "number"
		        ),
		        array(
		          "type" => "textfield",
		          "heading" => __("Parent ID", 'legenda-core'),
		          "param_name" => "parent",
	              "description" => __('Get direct children of this term (only terms whose explicit parent is this value). If 0 is passed, only top-level terms are returned. Default is an empty string.', 'legenda-core')
			    ),
		        array(
		          "type" => "dropdown",
		          "heading" => __("Display type", 'legenda-core'),
		          "param_name" => "display_type",
		          "value" => array( 
		              __("Grid", 'legenda-core') => 'grid',
		              __("Slider", 'legenda-core') => 'slider',
		              __("Menu", 'legenda-core') => 'menu'
		            )
		        ),
		        array(
		          "type" => "textfield",
		          "heading" => __("Columns", "js_composer"),
		          "param_name" => "columns",
		          "dependency" => Array('element' => "display_type", 'value' => array('grid')),
		        ),
		        array(
		          "type" => "textfield",
		          "heading" => __("Large Desktops", "js_composer"),
		          "param_name" => "items_l",
		          "dependency" => Array('element' => "display_type", 'value' => array('slider')),
		        ),
		        array(
		          "type" => "textfield",
		          "heading" => __("Desktops", "js_composer"),
		          "param_name" => "items_d",
		          "dependency" => Array('element' => "display_type", 'value' => array('slider')),
		        ),
		        array(
		          "type" => "textfield",
		          "heading" => __("Tablets", "js_composer"),
		          "param_name" => "items_t",
		          "dependency" => Array('element' => "display_type", 'value' => array('slider')),
		        ),
		        array(
		          "type" => "textfield",
		          "heading" => __("Phones", "js_composer"),
		          "param_name" => "items_p",
		          "dependency" => Array('element' => "display_type", 'value' => array('slider')),
		        ),
		        array(
		          "type" => "textfield",
		          "heading" => __("Extra Class", 'legenda-core'),
		          "param_name" => "class"
		        )
		      )
		
		    );  
		
		    vc_map($cats_params);
		}
		}

?>