<?php
// **********************************************************************//
// ! Remove Default STYLES
// **********************************************************************//

add_action('after_setup_theme', 'et_template_hooks');
if(!function_exists('et_template_hooks')) {
	function et_template_hooks() {
		remove_action( 'woocommerce_cart_collaterals', 'woocommerce_cross_sell_display' );

		add_action( 'woocommerce_after_add_to_cart_button', 'etheme_wishlist_btn', 50 );
	}
}

// @todo Remove it in 2023, use only add_filter( 'woocommerce_enqueue_styles', 'etheme_return_empty_array');
if(defined('WC_VERSION')){
	if ( version_compare( WC_VERSION, '6.9.0', '>=' ) ) {
		add_filter( 'woocommerce_enqueue_styles', 'etheme_return_empty_array');
	} else {
		add_filter( 'woocommerce_enqueue_styles', '__return_false' );
	}
}

function etheme_return_empty_array(){
	return array();
};

// **********************************************************************//
// ! Change single product main gallery image size
// **********************************************************************//
add_filter( 'woocommerce_gallery_image_size', function( $size ) {
	return 'woocommerce_single';
} );

// **********************************************************************//
// ! Product Video
// **********************************************************************//

add_action( 'woocommerce_process_product_meta', 'et_save_video_meta' );

if(!function_exists('et_save_video_meta')) {
	function et_save_video_meta($post_id) {
		// Gallery Images
		$video_ids =  explode( ',',  $_POST['product_video_gallery']  ) ;
		update_post_meta( $post_id, '_product_video_gallery', implode( ',', $video_ids ) );
		update_post_meta( $post_id, '_product_video_code',  $_POST['et_video_code']  );
	}
}

if(!function_exists('et_get_external_video')) {
	function et_get_external_video($post_id) {
		if(!$post_id) return false;
		$product_video_code = get_post_meta( $post_id, '_product_video_code', true );

		return $product_video_code;
	}
}

if(!function_exists('et_get_attach_video')) {
	function et_get_attach_video($post_id) {
		if(!$post_id) return false;
		$product_video_code = get_post_meta( $post_id, '_product_video_gallery', false );

		return $product_video_code;
	}
}

add_action( 'woocommerce_product_bulk_edit_end', 'et_new_product_edit', 10, 2 );
function et_new_product_edit() {
   ?>

		<div class="inline-edit-group">
			<label class="alignleft">
				<span class="title"><?php esc_html_e( 'Mark product as "New"', 'legenda' ); ?></span>
				<span class="input-text-wrap">
					<select class="product_new" name="product_new">
					<?php
						$options = array(
							'1' 	=> __( '— No change —', 'legenda' ),
							'2' => __( 'No', 'legenda' ),
							'3' => __( 'Yes', 'legenda' ),
						);
						foreach ( $options as $key => $value ) {
							echo '<option value="' . esc_attr( $key ) . '">' . $value . '</option>';
						}
					?>
					</select>
				</span>
			</label>
		</div>

   <?php
}

// **********************************************************************//
// ! Product brand label
// **********************************************************************//

add_action( 'admin_enqueue_scripts', 'et_brand_admin_scripts' );
if(!function_exists('et_brand_admin_scripts')) {
    function et_brand_admin_scripts() {
        $screen = get_current_screen();
        if ( in_array( $screen->id, array('edit-brand') ) )
		  wp_enqueue_media();
    }
}
if(!function_exists('et_product_brand_image')) {
	function et_product_brand_image() {
		global $post, $wpdb, $product;
        $terms = wp_get_post_terms( $post->ID, 'brand' );

        if ( is_wp_error($terms) ) return;

        if ( ! is_array( $terms ) && ! is_object( $terms ) ) {
        	$terms = array();
        }

        if(count($terms)>0) {
        	?>
			<div class="product-brands">
				<h4 class="title"><span><?php esc_html_e('Product brand', 'legenda') ?></span></h4>
	        	<?php
			        foreach($terms as $brand) {
			            $image 			= '';

			            $thumbnail_id 	= absint( get_term_meta ( $brand->term_id, 'thumbnail_id', true ) );
			            if( is_wp_error( $thumbnail_id ) ){
			            	continue;
			            } else {
			            	$thumbnail_id 	= absint( $thumbnail_id );
			            }
			        	if ($thumbnail_id) : ?>
		                	<a href="<?php echo get_term_link($brand); ?>">
	                    		<img src="<?php echo wp_get_attachment_image_url( $thumbnail_id, 'full' ); ?>" title="<?php echo esc_attr($brand->name); ?>" alt="<?php echo esc_attr($brand->name); ?>" class="brand-image" />
		                	</a>
			            <?php else : ?>
			                	<h3><a href="<?php echo get_term_link($brand); ?>"><?php echo esc_html($brand->name); ?></a></h3>
			            <?php endif;
			        }
	        	?>
			</div>
        	<?php
        }
	}
}

add_action( 'brand_add_form_fields', 'et_brand_fileds' );
if(!function_exists('et_brand_fileds')) {
	function et_brand_fileds() {
		global $woocommerce;
		?>
		<div class="form-field">
			<label><?php esc_html_e( 'Thumbnail', 'legenda' ); ?></label>
			<div id="brand_thumbnail" style="float:left;margin-right:10px;"><img src="<?php echo wc_placeholder_img_src(); ?>" width="60px" height="60px" /></div>
			<div style="line-height:60px;">
				<input type="hidden" id="brand_thumbnail_id" name="brand_thumbnail_id" />
				<button type="submit" class="upload_image_button button"><?php esc_html_e( 'Upload/Add image', 'legenda' ); ?></button>
				<button type="submit" class="remove_image_button button"><?php esc_html_e( 'Remove image', 'legenda' ); ?></button>
			</div>
			<script type="text/javascript">

				 // Only show the "remove image" button when needed
				 if ( ! jQuery('#brand_thumbnail_id').val() )
					 jQuery('.remove_image_button').hide();

				// Uploading files
				var file_frame;

				jQuery(document).on( 'click', '.upload_image_button', function( event ){

					event.preventDefault();

					// If the media frame already exists, reopen it.
					if ( file_frame ) {
						file_frame.open();
						return;
					}

					// Create the media frame.
					file_frame = wp.media.frames.downloadable_file = wp.media({
						title: '<?php _e( 'Choose an image', 'legenda' ); ?>',
						button: {
							text: '<?php _e( 'Use image', 'legenda' ); ?>',
						},
						multiple: false
					});

					// When an image is selected, run a callback.
					file_frame.on( 'select', function() {
						attachment = file_frame.state().get('selection').first().toJSON();

						jQuery('#brand_thumbnail_id').val( attachment.id );
						jQuery('#brand_thumbnail img').attr('src', attachment.url );
						jQuery('.remove_image_button').show();
					});

					// Finally, open the modal.
					file_frame.open();
				});

				jQuery(document).on( 'click', '.remove_image_button', function( event ){
					jQuery('#brand_thumbnail img').attr('src', '<?php echo wc_placeholder_img_src(); ?>');
					jQuery('#brand_thumbnail_id').val('');
					jQuery('.remove_image_button').hide();
					return false;
				});

			</script>
			<div class="clear"></div>
		</div>
		<?php
	}
}


add_action( 'brand_edit_form_fields', 'et_edit_brand_fields', 10,2 );
if(!function_exists('et_edit_brand_fields')) {
    function et_edit_brand_fields( $term, $taxonomy ) {
    	global $woocommerce;

    	$image 			= '';
    	$thumbnail_id 	= absint( get_term_meta( $term->term_id, 'thumbnail_id', true ) );
    	if ($thumbnail_id) :
    		$image = wp_get_attachment_thumb_url( $thumbnail_id );
    	else :
    		$image = wc_placeholder_img_src();
    	endif;
    	?>
    	<tr class="form-field">
    		<th scope="row" valign="top"><label><?php esc_html_e( 'Thumbnail', 'legenda' ); ?></label></th>
    		<td>
    			<div id="brand_thumbnail" style="float:left;margin-right:10px;"><img src="<?php echo esc_url($image); ?>" width="60px" height="60px" /></div>
    			<div style="line-height:60px;">
    				<input type="hidden" id="brand_thumbnail_id" name="brand_thumbnail_id" value="<?php echo esc_attr($thumbnail_id); ?>" />
    				<button type="submit" class="upload_image_button button"><?php esc_html_e( 'Upload/Add image', 'legenda' ); ?></button>
    				<button type="submit" class="remove_image_button button"><?php esc_html_e( 'Remove image', 'legenda' ); ?></button>
    			</div>
    			<script type="text/javascript">

    				// Uploading files
    				var file_frame;

    				jQuery(document).on( 'click', '.upload_image_button', function( event ){

    					event.preventDefault();

    					// If the media frame already exists, reopen it.
    					if ( file_frame ) {
    						file_frame.open();
    						return;
    					}

    					// Create the media frame.
    					file_frame = wp.media.frames.downloadable_file = wp.media({
    						title: '<?php _e( 'Choose an image', 'legenda' ); ?>',
    						button: {
    							text: '<?php _e( 'Use image', 'legenda' ); ?>',
    						},
    						multiple: false
    					});

    					// When an image is selected, run a callback.
    					file_frame.on( 'select', function() {
    						attachment = file_frame.state().get('selection').first().toJSON();

    						jQuery('#brand_thumbnail_id').val( attachment.id );
    						jQuery('#brand_thumbnail img').attr('src', attachment.url );
    						jQuery('.remove_image_button').show();
    					});

    					// Finally, open the modal.
    					file_frame.open();
    				});

    				jQuery(document).on( 'click', '.remove_image_button', function( event ){
    					jQuery('#brand_thumbnail img').attr('src', '<?php echo wc_placeholder_img_src(); ?>');
    					jQuery('#brand_thumbnail_id').val('');
    					jQuery('.remove_image_button').hide();
    					return false;
    				});

    			</script>
    			<div class="clear"></div>
    		</td>
    	</tr>
    	<?php
    }
}

if(!function_exists('et_brands_fields_save')) {
    function et_brands_fields_save( $term_id, $tt_id, $taxonomy ) {

    	if ( isset( $_POST['brand_thumbnail_id'] ) )
    		update_woocommerce_term_meta( $term_id, 'thumbnail_id', absint( $_POST['brand_thumbnail_id'] ) );

    	delete_transient( 'wc_term_counts' );
    }
}

add_action( 'created_term', 'et_brands_fields_save', 10,3 );
add_action( 'edit_term', 'et_brands_fields_save', 10,3 );

// **********************************************************************//
// ! AJAX Quick View
// **********************************************************************//



add_action('wp_ajax_et_product_quick_view', 'et_product_quick_view');
add_action('wp_ajax_nopriv_et_product_quick_view', 'et_product_quick_view');
if(!function_exists('et_product_quick_view')) {
	function et_product_quick_view() {
		if(empty($_POST['prodid'])) {
			echo 'Error: Absent product id';
			die();
		}

		$args = array(
			'p' => (int) $_POST['prodid'],
			'post_type' => 'product'
		);

		if( class_exists('SmartProductPlugin') )
			remove_filter('woocommerce_single_product_image_html', array('SmartProductPlugin', 'wooCommerceImage'), 999, 2 );


		$the_query = new WP_Query( $args );
		if ( $the_query->have_posts() ) {
			while ( $the_query->have_posts() ) : $the_query->the_post();
				wc_get_template('product-quick-view.php');
			endwhile;
			wp_reset_query();
			wp_reset_postdata();
		} else {
			echo 'No posts were found!';
		}
		die();
	}
}


// **********************************************************************//
// ! AJAX Remove
// **********************************************************************//

add_action('wp_ajax_et_remove_from_cart', 'et_remove_from_cart');
add_action('wp_ajax_nopriv_et_remove_from_cart', 'et_remove_from_cart');
if(!function_exists('et_remove_from_cart')) {
	function et_remove_from_cart() {
		global $woocommerce;
		$msg = __('Provide a key of your item', 'legenda');
		if ( isset($_POST['key']) && $_POST['key']) {

			$woocommerce->cart->set_quantity( $_POST['key'], 0 );

			$msg = __( 'Product successfully removed.', 'legenda' );

        }
		et_woocommerce_get_refreshed_fragments(array('msg' => $msg));
		die($msg);
	}
}

// **********************************************************************//
// ! Product Labels
// **********************************************************************//

if(!function_exists('etheme_wc_product_labels')) {
	function etheme_wc_product_labels( $product_id = '' ) {
	    echo etheme_wc_get_product_labels($product_id);
	}
}


if(!function_exists('etheme_wc_get_product_labels')) {
	function etheme_wc_get_product_labels( $product_id = '' ) {
		global $post, $wpdb,$product;
	    $count_labels = 0;
	    $output = '';

	    if ( etheme_get_option('sale_icon') ) :
	        if ($product->is_on_sale()) {$count_labels++;
	            $output .= '<span class="label-icon sale-label">'.__( 'Sale!', 'legenda' ).'</span>';
	        }
	    endif;

	    if ( etheme_get_option('new_icon') ) : $count_labels++;
	        if(etheme_product_is_new($product_id)) :
	            $second_label = ($count_labels > 1) ? 'second_label' : '';
	            $output .= '<span class="label-icon new-label '.$second_label.'">'.__( 'New!', 'legenda' ).'</span>';
	        endif;
	    endif;
	    return $output;
	}
}


// **********************************************************************//
// ! Is product New
// **********************************************************************//

if(!function_exists('etheme_product_is_new')) {
	function etheme_product_is_new( $product_id = '' ) {
		global $post, $wpdb;
	    $key = 'product_new';
		if(!$product_id) $product_id = $post->ID;
		if(!$product_id) return false;
	    $_etheme_new_label = get_post_meta($product_id, $key);
	    if(isset($_etheme_new_label[0]) && $_etheme_new_label[0] == 'enable') {
	        return true;
	    }
	    return false;
	}
}

// **********************************************************************//
// ! Grid/List switcher
// **********************************************************************//

add_action('woocommerce_before_shop_loop', 'etheme_grid_list_switcher',115);
if(!function_exists('etheme_grid_list_switcher')) {
	function etheme_grid_list_switcher() {
		?>
		<?php $view_mode = etheme_get_option('view_mode'); ?>
		<?php if($view_mode == 'grid_list'): ?>
			<div class="view-switcher hidden-tablet hidden-phone">
				<label><?php esc_html_e('View as:', 'legenda'); ?></label>
				<div class="switchToGrid"><i class="icon-th-large"></i></div>
				<div class="switchToList"><i class="icon-th-list"></i></div>
			</div>
		<?php elseif($view_mode == 'list_grid'): ?>
			<div class="view-switcher hidden-tablet hidden-phone">
				<label><?php esc_html_e('View as:', 'legenda'); ?></label>
				<div class="switchToList"><i class="icon-th-list"></i></div>
				<div class="switchToGrid"><i class="icon-th-large"></i></div>
			</div>
		<?php endif ;?>


		<?php
	}
}

// **********************************************************************//
// ! Catalog Mode
// **********************************************************************//
$just_catalog = etheme_get_option('just_catalog');

function etheme_remove_loop_button(){
    remove_action( 'woocommerce_after_shop_loop_item', 'woocommerce_template_loop_add_to_cart', 10 );
	remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
	remove_action( 'woocommerce_simple_add_to_cart', 'woocommerce_simple_add_to_cart', 30 );
	remove_action( 'woocommerce_grouped_add_to_cart', 'woocommerce_grouped_add_to_cart', 30 );
	remove_action( 'woocommerce_single_variation', 'woocommerce_single_variation_add_to_cart_button', 20 );
	remove_action( 'woocommerce_external_add_to_cart', 'woocommerce_external_add_to_cart', 30 );
}

if($just_catalog) {
    add_action('init','etheme_remove_loop_button');
}


// **********************************************************************//
// ! Template hooks
// **********************************************************************//

add_action( 'woocommerce_before_main_content', 'et_back_to_page', 40 ); // add pagination above the products
add_action( 'woocommerce_before_shop_loop', 'woocommerce_pagination', 40 ); // add pagination above the products
add_action( 'woocommerce_after_shop_loop', 'woocommerce_result_count', 1 );
add_action( 'woocommerce_after_shop_loop', 'woocommerce_catalog_ordering', 3 );
//remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_login_form', 10 ); // remove login form before checkout
//remove_action( 'woocommerce_before_checkout_form', 'woocommerce_checkout_coupon_form', 10 );// remove coupon form before checkout
remove_action( 'woocommerce_after_main_content', 'woocommerce_output_content_wrapper_end');
remove_action( 'woocommerce_before_main_content', 'woocommerce_output_content_wrapper');
remove_action( 'woocommerce_after_shop_loop_item_title', 'woocommerce_template_loop_rating', 5 );


remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash', 10 );
remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_images', 20 );

remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_title', 5 );
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10 );
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_add_to_cart', 30 );
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_meta', 40 );
remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_sharing', 50 );
remove_action( 'woocommerce_cart_is_empty', 'wc_empty_cart_message', 10 );

remove_action( 'woocommerce_widget_shopping_cart_total', 'woocommerce_widget_shopping_cart_subtotal', 10 );
add_action( 'woocommerce_widget_shopping_cart_total', 'et_woocommerce_widget_shopping_cart_subtotal', 10 );

if ( !function_exists('et_woocommerce_widget_shopping_cart_subtotal') ) {
	function et_woocommerce_widget_shopping_cart_subtotal () {
		echo '<p class="small-h pull-left">' . esc_html__('Total: ', 'legenda') .'</p><span class="big-coast pull-right">' . WC()->cart->get_cart_subtotal() . '</span>'; // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
	}
}


// **********************************************************************//
// ! Set number of products per page
// **********************************************************************//
add_filter( 'loop_shop_per_page', 'et_products_per_page', 20 );
if ( !function_exists('et_products_per_page') ) {
	function et_products_per_page () { 
		$products_per_page = etheme_get_option('products_per_page');
		return $products_per_page; 
	}
}

// **********************************************************************//
// ! Category thumbnail
// **********************************************************************//
if(!function_exists('etheme_category_header')){
	function etheme_category_header() {
		if(function_exists('et_get_term_meta')){
			global $wp_query;
			$cat = $wp_query->get_queried_object();
			if(!property_exists($cat, "term_id") && !is_search()){
			    echo do_shortcode(etheme_get_option('product_bage_banner'));
			}else{
			    $image = etheme_get_option('product_bage_banner');
				$queried_object = get_queried_object();

				if (isset($queried_object->term_id)){
					
					$term_id = $queried_object->term_id;
					$content = et_get_term_meta($term_id, 'cat_meta');

					if(isset($content[0]['cat_header']) && !empty($content[0]['cat_header'])){
						echo '<div class="category-description">';
						echo do_shortcode($content[0]['cat_header']);
						echo '</div>';
					}

					if(isset($content[0]) && !empty([0])){
						echo '<div class="category-description">';
						echo do_shortcode($content[0]);
						echo '</div>';
					}
				}
			}
		}
	}
}

// **********************************************************************//
// ! Review form
// **********************************************************************//
//add_action('after_page_wrapper', 'etheme_review_form');
if(!function_exists('etheme_review_form')) {
	function etheme_review_form( $product_id = '' ) {
		global $woocommerce, $product,$post;
		$title_reply = '';

		if ( have_comments() ) :
			$title_reply = __( 'Add a review', 'legenda' );

		else :

			$title_reply = __( 'Be the first to review', 'legenda' ).' &ldquo;'.$post->post_title.'&rdquo;';
		endif;

		$commenter = wp_get_current_commenter();

		echo '<div id="review_form">';

		echo '<h4>'.__('Add your review', 'legenda').'</h4>';

		$comment_form = array(
			'title_reply' => '',
			'comment_notes_before' => '',
			'comment_notes_after' => '',
			'fields' => array(
				'author' => '<p class="comment-form-author">' . '<label for="author">' . __( 'Name', 'legenda' ) . '</label> ' . '<span class="required">*</span>' .
				            '<input id="author" name="author" type="text" value="' . esc_attr( $commenter['comment_author'] ) . '" size="30" aria-required="true" /></p>',
				'email'  => '<p class="comment-form-email"><label for="email">' . __( 'Email', 'legenda' ) . '</label> ' . '<span class="required">*</span>' .
				            '<input id="email" name="email" type="text" value="' . esc_attr(  $commenter['comment_author_email'] ) . '" size="30" aria-required="true" /></p>',
			),
			'label_submit' => __( 'Submit Review', 'legenda' ),
			'logged_in_as' => '',
			'comment_field' => ''
		);

		if ( get_option('woocommerce_enable_review_rating') == 'yes' ) {

			$comment_form['comment_field'] = '<p class="comment-form-rating"><label for="rating">' . __( 'Rating', 'legenda' ) .'</label><select name="rating" id="rating">
				<option value="">'.__( 'Rate&hellip;', 'legenda' ).'</option>
				<option value="5">'.__( 'Perfect', 'legenda' ).'</option>
				<option value="4">'.__( 'Good', 'legenda' ).'</option>
				<option value="3">'.__( 'Average', 'legenda' ).'</option>
				<option value="2">'.__( 'Not that bad', 'legenda' ).'</option>
				<option value="1">'.__( 'Very Poor', 'legenda' ).'</option>
			</select></p>';

		}

		$comment_form['comment_field'] .= '<p class="comment-form-comment"><label for="comment">' . __( 'Your Review', 'legenda' ) . '</label><textarea id="comment" name="comment" cols="25" rows="8" aria-required="true"></textarea></p>' . $woocommerce->nonce_field('comment_rating', true, false);


			comment_form( apply_filters( 'woocommerce_product_review_comment_form_args', $comment_form ) );



		echo '</div>';
	}
}

// **********************************************************************//
// ! User area in account page sidebar
// **********************************************************************//
add_action('etheme_before_account_sidebar', 'etheme_user_info',10);
if(!function_exists('etheme_user_info')) {
	function etheme_user_info() {
		global $current_user;
		wp_get_current_user();
		if(is_user_logged_in()) {
			?>
				<li class="user-sidearea">
					<?php echo get_avatar( $current_user->ID, 50 ); ?>
					<?php echo '<strong>' . $current_user->user_login . "</strong>\n"; ?>
					<br>
					<a href="<?php echo wp_logout_url(home_url()); ?>"><?php esc_html_e('Logout', 'legenda') ?></a>
				</li>
			<?php
		}
	}
}


// **********************************************************************//
// ! Login form popup
// **********************************************************************//
if ( !is_user_logged_in()) {
	add_action('after_page_wrapper', 'etheme_login_form_modal');
}

if(!function_exists('etheme_login_form_modal')) {
	function etheme_login_form_modal() {
		global $woocommerce;
		?>
			<div id="loginModal" class="modal hide fade" tabindex="-1" role="dialog" aria-hidden="true">
				<div>
					<div class="modal-header">
						<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
						<h3 class="title"><span><?php esc_html_e('Login', 'legenda'); ?></span></h3>
					</div>
					<div class="modal-body">
						<?php do_action('etheme_before_login'); ?>
						<form method="post" class="login">
							<p class="form-row form-row-<?php if (get_option('woocommerce_enable_myaccount_registration')=='yes') : ?>wide<?php else: ?>first<?php endif; ?>">
								<label for="username"><?php esc_html_e( 'Username or email', 'legenda' ); ?> <span class="required">*</span></label>
								<input type="text" class="input-text" name="username" />
							</p>
							<p class="form-row form-row-<?php if (get_option('woocommerce_enable_myaccount_registration')=='yes') : ?>wide<?php else: ?>last<?php endif; ?>">
								<label for="password"><?php esc_html_e( 'Password', 'legenda' ); ?> <span class="required">*</span></label>
								<input class="input-text" type="password" name="password" />
							</p>
							<div class="clear"></div>

							<p class="form-row">
								<?php wp_nonce_field( 'woocommerce-login' ); ?>
								<input type="submit" class="button filled active" name="login" value="<?php esc_html_e( 'Login', 'legenda' ); ?>" />
								<a class="lost_password" href="<?php echo esc_url( wc_lostpassword_url() ); ?>"><?php esc_html_e( 'Lost Password?', 'legenda' ); ?></a>
								<a href="<?php echo get_permalink( get_option('woocommerce_myaccount_page_id') ); ?>" class="right"><?php esc_html_e('Create Account', 'legenda') ?></a>
							</p>
						</form>
					</div>
				</div>
			</div>
		<?php
	}
}

// **********************************************************************//
// ! Shopping cart modal
// **********************************************************************//

add_action('after_page_wrapper', 'etheme_cart_modal');
if(!function_exists('etheme_cart_modal')) {
	function etheme_cart_modal( $product_id = '' ) {
		global $woocommerce, $product,$post;

		echo '<div id="cartModal" class="modal hide fade" tabindex="-1" role="dialog" aria-hidden="true"><div id="shopping-cart-modal">';

		?>
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
				<h3 class="title"><span><?php esc_html_e('Cart', 'legenda'); ?></span></h3>
			</div>
		<?php

		echo '<div class="modal-body">';
		?>
			<div class="shopping-cart-modal a-right" >
			    <div class="cart-popup-container">
				    <div class="cart-popup">
				    	<div class="widget_shopping_cart_content">
						<?php
						    etheme_cart_items(150);
						?>
						</div>
				    </div>
			    </div>
			</div>

	    <?php


		echo '</div>';


		echo '</div></div>';
	}
}


// **********************************************************************//
// ! Top Cart Widget
// **********************************************************************//

if ( ! function_exists( 'et_cart_quantity' ) ) {

	function et_cart_quantity( $fragments ) {
		ob_start();
		et_cart_summ();
		$fragments['a.cart-summ'] = ob_get_clean();
		return $fragments;
	}

	add_filter( 'woocommerce_add_to_cart_fragments', 'et_cart_quantity' );

};


if(!function_exists('etheme_top_cart')) {
	function etheme_top_cart($content = true) {
        global $woocommerce;
		?>
		<div class="shopping-cart-widget a-right">
			<?php et_cart_summ(); ?>
			<?php if ($content) : ?>
				<div class="widget_shopping_cart_content">
					<?php woocommerce_mini_cart(); ?>
				</div>
			<?php endif; ?>
		</div>

    <?php
	}
}

if(!function_exists('et_cart_summ')) {
	function et_cart_summ() {
        global $woocommerce;
        ?>
			<a href="<?php echo wc_get_cart_url(); ?>" class="cart-summ" data-items-count="<?php echo WC()->cart->get_cart_contents_count(); ?>">
				<div class="cart-bag">
					<?php esc_html_e('Cart', 'legenda'); ?>
					<?php echo wp_kses_data( sprintf( '<span class="badge-number">%1$u %2$s</span>', WC()->cart->get_cart_contents_count(), _nx( 'item for', 'items for', WC()->cart->get_cart_contents_count(), 'top cart items count text', 'legenda' ) ) );?>
					<span class="price-summ cart-totals"><?php wc_cart_totals_subtotal_html(); ?></span>
				</div>
			</a>
        <?php
	}
}


if(!function_exists('etheme_cart_items')) {
	function etheme_cart_items ($limit = 3) {
        global $woocommerce;
        if ( ! WC()->cart->is_empty() ) {
          ?>
			<ul class='order-list products-small-popup'>
          <?php
            $counter = 0;
            do_action( 'woocommerce_before_mini_cart_contents' );
            foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
                $counter++;
                if($counter > $limit) continue;
                $_product     = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
                $product_id   = apply_filters( 'woocommerce_cart_item_product_id', $cart_item['product_id'], $cart_item, $cart_item_key );


                if ( ! apply_filters('woocommerce_widget_cart_item_visible', true, $cart_item, $cart_item_key ) )
                    continue;

                if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_widget_cart_item_visible', true, $cart_item, $cart_item_key ) ) {
                	$product_name      = apply_filters( 'woocommerce_cart_item_name', $_product->get_name(), $cart_item, $cart_item_key );
                    $product_permalink = apply_filters( 'woocommerce_cart_item_permalink', $_product->is_visible() ? $_product->get_permalink( $cart_item ) : '', $cart_item, $cart_item_key );
                ?>
					<li class="woocommerce-mini-cart-item <?php echo esc_attr( apply_filters( 'woocommerce_mini_cart_item_class', 'mini_cart_item', $cart_item, $cart_item_key ) ); ?> product-item-popup">
						<?php
                            echo apply_filters( 'woocommerce_cart_item_remove_link',
                            	sprintf('<a href="%s" aria-label="%s" data-product_id="%s" data-product_sku="%s" data-cart_item_key="%s" class="remove remove_from_cart_button close-order-li delete-btn" title="%s"><i class="icon-remove"></i></a>',
                            		esc_url( wc_get_cart_remove_url( $cart_item_key ) ),
                            		esc_html__( 'Remove this item', 'legenda' ) ,
                            		esc_attr( $product_id ),
                            		esc_attr( $_product->get_sku() ),
                            		esc_attr( $cart_item_key ),
                            		esc_html__( 'Remove this item', 'legenda' ) ), $cart_item_key );

                        ?>
						<div class="media">
							<a class="pull-left product-image" href="<?php echo esc_url( $product_permalink ); ?>">
								<?php echo apply_filters( 'woocommerce_cart_item_thumbnail', $_product->get_image(), $cart_item, $cart_item_key ); ?>
							</a>
							<div class="media-body">
								<h4 class="media-heading"><a href="<?php echo esc_url( $product_permalink ); ?>"><?php echo apply_filters('woocommerce_widget_cart_product_title', $_product->get_name(), $_product ) ?></a></h4>
								<div class="descr-box">
									<?php echo wc_get_formatted_cart_item_data( $cart_item );?>
									<span class="coast"><?php esc_html_e( 'Qty: ', 'legenda' ); echo esc_html($cart_item['quantity']); ?><span class='medium-coast pull-right'><?php echo apply_filters( 'woocommerce_cart_item_price', WC()->cart->get_product_price( $_product ), $cart_item, $cart_item_key ); ?></span></span>
								</div>
							</div>
						</div>
					</li>
                <?php
                }
            }

            do_action( 'woocommerce_mini_cart_contents' );
        ?>
		</ul>

        <?php
        } else {
            echo '<p class="woocommerce-mini-cart__empty-message empty a-center">' . esc_html__( 'No products in the cart.', 'legenda' ) . '</p>';
        }


        if ( ! WC()->cart->is_empty() ) { ?>
          	<div class="totals">
				<?php
					/**
					 * Woocommerce_widget_shopping_cart_total hook.
					 *
					 * @hooked woocommerce_widget_shopping_cart_subtotal - 10
					 */
					do_action( 'woocommerce_widget_shopping_cart_total' );
				?>
          	</div>

			<div class="clearfix"></div>
			<?php do_action( 'woocommerce_widget_shopping_cart_before_buttons' ); ?>
			<div class='bottom-btn'>
				<a href="<?php echo wc_get_cart_url(); ?>" class='btn text-center button left'><?php echo __('View Cart', 'legenda'); ?></a>
				<a href="<?php echo wc_get_checkout_url(); ?>" class='btn text-center button active right'><?php echo __('Checkout', 'legenda'); ?></a>
			</div>

            <?php
            do_action( 'woocommerce_widget_shopping_cart_after_buttons' );

        }
	}
}


if(!function_exists('et_step_checkout')) {
	function et_step_checkout(){
		$messages = array(
			'password'    => __( 'CREATE AN ACCOUNT PASSWORD IS A REQUIRED FIELD.', 'legenda' ),
			'first_name'  => __( 'BILLING FIRST NAME IS A REQUIRED FIELD.', 'legenda' ),
			'last_name'	  => __( 'BILLING LAST NAME IS A REQUIRED FIELD.', 'legenda' ),
			'email_field' => __( 'BILLING EMAIL ADDRESS IS A REQUIRED FIELD.', 'legenda' ),
			'phone' 	  => __( 'BILLING PHONE REQUIRED FIELD.', 'legenda' ),
			'country' 	  => __( 'BILLING STATE IS A REQUIRED FIELD.', 'legenda' ),
			'address_1'   => __( 'BILLING ADDRESSIS A REQUIRED FIELD.', 'legenda' ),
			'city' 		  => __( 'BILLING TOWN / CITY IS A REQUIRED FIELD.', 'legenda' ),
			'postcode'    => __( 'BILLING POSTCODE / ZIP A REQUIRED FIELD.', 'legenda' ),
		);
		$messages = json_encode( $messages );
		exit( $messages );
	}
	add_action('wp_ajax_et_step_checkout', 'et_step_checkout');
	add_action('wp_ajax_nopriv_et_step_checkout', 'et_step_checkout');
}


add_filter('woocommerce_pagination_args', function ($args) {
    $args['prev_text'] = '';
	$args['next_text'] = '';
    return $args;
});