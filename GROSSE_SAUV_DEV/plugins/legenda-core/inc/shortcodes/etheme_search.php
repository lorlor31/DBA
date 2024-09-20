<?php 

/***************************************************************/
/* Etheme Global Search */
/***************************************************************/

if ( !function_exists('etheme_register_etheme_search') ) {

	function etheme_register_etheme_search () {
		if(!function_exists('vc_map')) {
			return;
		}

	    $search_params = array(
	      'name' => 'Mega Search Form',
	      'base' => 'etheme_search',
	      'icon' => 'icon-wpb-etheme',
	      'category' => 'Eight Theme',
	      'params' => array(
	        array(
	          "type" => "dropdown",
	          "heading" => __("Search for products", "legenda-core"),
	          "param_name" => "products",
	          "value" => array( 
	              "", 
	              __("Yes", 'legenda-core') => 1,
	              __("No", 'legenda-core') => 0
	            )
	        ),
	        array(
	          "type" => "dropdown",
	          "heading" => __("Display images for products", 'legenda-core'),
	          "param_name" => "images",
	          "value" => array( 
	              "", 
	              __("Yes", 'legenda-core') => 1,
	              __("No", 'legenda-core') => 0
	            )
	        ),
	        array(
	          "type" => "dropdown",
	          "heading" => __("Search for posts", "legenda-core"),
	          "param_name" => "posts",
	          "value" => array( 
	              "", 
	              __("Yes", 'legenda-core') => 1,
	              __("No", 'legenda-core') => 0
	            )
	        ),
	        array(
	          "type" => "dropdown",
	          "heading" => __("Search in portfolio", "legenda-core"),
	          "param_name" => "portfolio",
	          "value" => array( 
	              "", 
	              __("Yes", 'legenda-core') => 1,
	              __("No", 'legenda-core') => 0
	            )
	        ),
	        array(
	          "type" => "dropdown",
	          "heading" => __("Search for pages", "legenda-core"),
	          "param_name" => "pages",
	          "value" => array( 
	              "", 
	              __("Yes", 'legenda-core') => 1,
	              __("No", 'legenda-core') => 0
	            )
	        ),
	        array(
	          "type" => "textfield",
	          "heading" => __("Number of items from each section", 'legenda-core'),
	          "param_name" => "count"
	        ),
	        array(
	          "type" => "textfield",
	          "heading" => __("Extra Class", 'legenda-core'),
	          "param_name" => "class"
	        )
	      )
	
	    );  
	
	    vc_map($search_params);
	}

}

?>