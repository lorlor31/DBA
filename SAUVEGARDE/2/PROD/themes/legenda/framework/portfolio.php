<?php

/**
 *
 * Portfolio
 *
 */

function etheme_get_recent_portfolio($limit, $title = 'Recent Works', $not_in = 0) {
	$args = array(
		'post_type' => 'etheme_portfolio',
		'order' => 'DESC',
		'orderby' => 'date',
		'posts_per_page' => $limit,
		'post__not_in' => array( $not_in )
	);
	
	return etheme_create_portfolio_slider($args, $title);
}

function etheme_create_portfolio_slider($args,$title = false,$width = 540, $height = 340, $crop = true){
	global $wpdb;
	$box_id = rand(1000,10000);
	$multislides = new WP_Query( $args );
	$sliderHeight = etheme_get_option('default_blog_slider_height');
	$class = '';
	if($multislides->post_count > 1) {
		$class = ' posts-count-gt1';
	}
	if($multislides->post_count < 4) {
		$class .= ' posts-count-lt4';
	}
	
	ob_start();
	if ( $multislides->have_posts() ) :
		echo '<div class="slider-container '.$class.'">';
		if ($title) {
			echo '<h3 class="title"><span>'.$title.'</span></h3>';
		}
		echo '<div class="items-slider posts-slider slider-'.$box_id.'">';
		echo '<div class="slider owl-carousel row-fluid">';
		$_i=0;
		while ($multislides->have_posts()) : $multislides->the_post();
			$_i++;
			get_template_part( 'portfolio', 'slide' );
		
		endwhile;
		echo '</div><!-- slider -->';
		echo '</div><!-- products-slider -->';
		echo '</div><!-- slider-container -->';
		
		
		echo '
                <script type="text/javascript">
                    jQuery(".slider-'.$box_id.' .slider").owlCarousel({
                        items:4,
                        lazyLoad : true,
                        nav: true,
                        navText: ["",""],
                        rewindNav: false,
                        dots: false,
                        itemsCustom: [[0, 1], [479,2], [619,2], [768,4],  [1200, 4], [1600, 4]]
                    });

                </script>
            ';
	endif;
	wp_reset_query();
	
	$html = ob_get_contents();
	ob_end_clean();
	
	return $html;
}

function print_item_cats($id) {
	
	//Returns Array of Term Names for "categories"
	$term_list = wp_get_post_terms($id, 'categories', array("fields" => "names"));
	$_i = 0;
	foreach ($term_list as $key => $value) {
		$_i++;
		echo esc_html($value);
		if($_i != count($term_list))
			echo ', ';
	}
}

function get_etheme_portfolio($categories = false, $limit = false, $show_pagination = true, $columns = false, $show_filters = true, $custom_class = '', $all = true, $is_category = false, $cat_name = '', $url = '', $paged = 1 ) {
	
	if ( !etheme_get_option('enable_portfolio') ) {
		echo esc_html__('Please, enable "portfolio" in the module section of theme options.', 'legenda');
		return;
	}
	
	if ( !post_type_exists('etheme_portfolio') ) {
		echo esc_html__('Please, activate Legenda Core plugin and make sure you to use this element.', 'legenda');
		return;
	}
	
	global $et_portfolio_loop;
	$et_portfolio_loop['one_project'] = false;
	
	$paged = ( isset( $_GET['et-paged'] ) && ! empty( $_GET['et-paged'] ) ) ? $_GET['et-paged'] : $paged;
	$url = ( $url != '' ) ? $url : get_permalink();
	$cat = get_query_var('portfolio_category');
	$class = ( $custom_class != '' ) ? $custom_class : 'portfolio-'.rand(100, 9999);
	$pagination_args = array();
	
	if ( !$columns )
		$columns = ( isset( $et_portfolio_loop['columns'] ) && $et_portfolio_loop['columns'] ) ? $et_portfolio_loop['columns'] : etheme_get_option('portfolio_columns');
	else
		$et_portfolio_loop['columns'] = $columns;
	
	$filters_type = etheme_get_option( 'portfolio_filters_type' );
	
	$category_page = ( get_query_var('portfolio_category') ) ? true : $is_category;
	$category_page_name = ( get_query_var('portfolio_category') && $cat_name == '' ) ? get_query_var('portfolio_category') : $cat_name;
	
	$_categories = $categories;
	
	if ( $is_category ) {
		if ( $all ) {
			$cat = $cat_name;
		}
		else {
			$cat = get_term( (int)$categories[0], 'portfolio_category' );
			$cat = $cat->slug;
		}
	}
	
	$tax_query = array();
	
	if(!$limit) {
		$limit = etheme_get_option('portfolio_count');
	}
	
	if ( is_array($categories) && !empty($categories) ) {
		if ( $cat_name != null && $cat_name != '' ) {
			$categories[] = $cat_name;
		}
	}
	elseif ( $cat_name != null && $cat_name != '' ) {
		$categories = array($cat_name);
	}
	
	if ( $is_category ) {
		$tax_query = array(
			array(
				'taxonomy' => 'categories',
				'field' => 'slug',
				'terms' => $cat
			)
		);
	}
	else {
		if( is_array($categories) && !empty($categories)) {
			$tax_query = array(
				array(
					'taxonomy' => 'categories',
					'field' => 'id',
					'terms' => $categories,
					'operator' => 'IN'
				)
			);
		} else if(!empty($cat)) {
			$tax_query = array(
				array(
					'taxonomy' => 'categories',
					'field' => 'slug',
					'terms' => $cat
				)
			);
		}
	}
	
	$args = array(
		'post_type' => 'etheme_portfolio',
		'paged' => $paged,
		'posts_per_page' => $limit,
		'tax_query' => $tax_query,
	);
	
	$loop = new WP_Query($args); ?>
	
	<?php if ( $loop->have_posts() ) : ?>
		<?php if( $show_filters ) :
			if ( get_query_var('portfolio_category') ) {
				$queried_object = get_queried_object();
				$term_id = $queried_object->term_id;
				$_terms = array();
				$_categories = get_term_children( $term_id, 'categories' );
				foreach ($_categories as $key) {
					$_terms[] = get_term( $key, 'categories' );
				}
				$_categories = $_terms;
			}
			else {
				
				
				$_args = array(
					'include' => $_categories,
				);
				
				if ( $filters_type == 'parent' ) {
					$_args['parent'] = false;
				} elseif( $filters_type == 'child' ){
					$_args['childless'] = true;
				}
				
				$_categories = get_terms( 'categories', $_args );
			}
			if ( count($_categories) > 0) : ?>
				<ul class="portfolio-filters">
					<li><a href="#" data-category-id="all" data-filter="*" class="button <?php echo ( !isset($_GET['et-cat']) ) ? 'active' : ''; ?>"><?php echo ( get_query_var('portfolio_category') ) ? $category_page_name : esc_html__('Show All', 'legenda'); ?></a></li>
					<?php
					foreach($_categories as $category) {
						?>
						<li><a href="#" data-category-id="<?php echo esc_attr($category->term_id); ?>" data-filter=".portfolio_category-<?php echo esc_attr($category->slug); ?>" class="button <?php echo ( isset($_GET['et-cat']) && $_GET['et-cat'] == $category->term_id ) ? 'active' : ''; ?>"><?php echo esc_html($category->name); ?></a></li>
						<?php
					}
					
					?>
				</ul>
			<?php endif; ?>
		<?php endif; ?>
	<?php endif; ?>

<div class="portfolio-wrapper clearfix <?php echo esc_attr( $class ); ?>">
	<?php if ( $loop->have_posts() ) : ?>
		
		<div class="portfolio row masonry">
			
			<?php while ( $loop->have_posts() ) :
				
				$loop->the_post();
				
				get_template_part( 'content', 'portfolio' );
			
			
			endwhile; ?>
		
		</div>
		
		<?php if ( $show_pagination && $limit != -1 ){
			$pagination_args = array(
				'pages'  => $loop->max_num_pages,
				'paged'  => $paged,
				'class'  => 'portfolio-pagination',
				'type' => 'custom',
				'url' => $url
			);
			etheme_portfolio_pagination( $pagination_args );
		} ?>
	
	<?php else: ?>
		
		<h3><?php esc_html_e('No projects were found!', 'legenda') ?></h3>
	
	<?php endif; ?>
	<div
		class="et-load-portfolio"
		data-class="<?php echo esc_attr( $class ); ?>"
		data-portfolio-category-page="<?php echo esc_attr( $category_page ); ?>"
		data-portfolio-category-page-name="<?php echo esc_attr( $category_page_name ); ?>"
		data-limit="<?php echo esc_attr( $limit ); ?>"
		data-columns="<?php echo esc_attr( $columns ); ?>">
				<span class="hidden et-element-args" type="text/template" data-element="et_portfolio">
                    <?php echo json_encode( $pagination_args ); ?>
                </span>
	</div>
	</div><?php // end .portfolio-wrapper
}

function etheme_portfolio_pagination($args = array()) {
	extract( shortcode_atts( array(
		'type'   => 'default',
		'url'    => '',
		'pages'  => 1,
		'paged'  => 1,
		'range'  => 2,
		'class'  => '',
		'before' => '',
		'after'  => '',
		'prev_next' => true,
		'prev_text' => '',
		'next_text' => ''
	), $args ) );
	
	if( $pages != 1 ){
		$showitems = ( $range * 2 )+1;
		$out = '';
		
		if ( ! $url ) {
			$url = get_permalink();
		}
		
		if( $prev_next && $paged > 1  ){
			$out .= '<li><a href="' . add_query_arg( 'et-paged', ( $paged - 1 ), $url ) . '" class="prev page-numbers">' . $prev_text . '</a></li>';
		}
		
		
		for ( $i=1; $i <= $pages; $i++ ){
			if ( $pages != 1 &&( ! ( $i >= $paged+$range+1 || $i <= $paged-$range-1 ) || $pages <= $showitems ) ){
				if ( $paged == $i ) {
					$out .= '<li><span class="page-numbers current">' . $i . '</span></li>';
				} else {
					$out .= '<li><a href="' . add_query_arg( 'et-paged', $i, $url ) . '" class="inactive">' . $i . '</a></li>';
				}
			}
		}
		
		if ( $prev_next && $paged < $pages ){
			$out .= '<li><a href="' . add_query_arg( 'et-paged', ( $paged + 1 ), $url ) . '" class="next page-numbers">' . $next_text . '</a></li>';
		}
		
		
		echo '
				<nav class="portfolio-pagination ' . $class . '">
				' . $before . '
				<ul class="page-numbers">' . $out . '</ul>
				' . $after . '
				</nav>
	        ';
	}
}

add_action( 'wp_ajax_et_portfolio_ajax', 'etheme_portfolio_ajax');
add_action( 'wp_ajax_nopriv_et_portfolio_ajax', 'etheme_portfolio_ajax');

if(!function_exists('etheme_portfolio_ajax')) {
	function etheme_portfolio_ajax () {
		$categories = ( $_POST['category'] != 'all') ? array($_POST['category']) : false;
		$url = $_POST['url'];
		if ( $categories ) {
			if ( isset($_GET['et-cat']) ) {
				$url = add_query_arg('et-cat', $_GET['et-cat'], $url);
			}
			else {
				foreach ($categories as $key) {
					$url = add_query_arg('et-cat', $key, $url);
				}
			}
		}
		else {
			// if ( isset($_GET['et-cat']) ) {
			// 	$url = add_query_arg('et-cat', $_GET['et-cat'], $url);
			// }
		}
		$is_cat = $_POST['is_category'] ? true : false;
		$cat_name = $_POST['category_name'];
		$limit = $_POST['limit'];
		$all = ( $_POST['category'] == 'all' ) ? true : false;
		$class = $_POST['class'];
		$columns = $_POST['columns'];
		$result = array();
		ob_start();
		get_etheme_portfolio($categories, $limit, true, $columns, false, $class, $all, $is_cat, $cat_name, $url);
		
		$result['html'] = ob_get_clean();
		
		echo json_encode($result);
		die();
	}
}

add_action( 'wp_ajax_et_portfolio_ajax_pagination', 'etheme_portfolio_ajax_pagination');
add_action( 'wp_ajax_nopriv_et_portfolio_ajax_pagination', 'etheme_portfolio_ajax_pagination');

function etheme_portfolio_ajax_pagination() {
	$url = $_POST['url'];
	$paged = $_POST['paged'];
	$limit = $_POST['limit'];
	$categories = ( $_POST['category'] != null) ? array($_POST['category']) : false;
	if ( $categories ) {
		if ( isset($_GET['et-cat']) ) {
			$url = add_query_arg('et-cat', $_GET['et-cat'], $url);
		}
		else {
			foreach ($categories as $key) {
				$url = add_query_arg('et-cat', $key, $url);
			}
		}
	}
	elseif ( isset($_GET['et-cat']) ) {
		$url = add_query_arg('et-cat', $_GET['et-cat'], $url);
	}
	$is_cat = $_POST['is_category'] ? true : false;
	$cat_name = $_POST['cat'];
	$all = ( (isset($_GET['et-cat']) && !$is_cat) || ($is_cat && $categories) ) ? false : true;
	$class = $_POST['class'];
	$columns = $_POST['columns'];
	ob_start();
	get_etheme_portfolio($categories, $limit, true, $columns, false, $class, $all, $is_cat, $cat_name, $url, $paged);
	$result = ob_get_clean();
	
	echo json_encode($result);
	die();
}

?>