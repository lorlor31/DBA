<?php 

	function etheme_decoding( $val ) {
		return base64_decode( $val );
	}

	function etheme_encoding( $val ) {
		return base64_encode( $val );
	}

	function etheme_fw($file, $content) {
		return fwrite($file, $content);
	}

	function etheme_fo($file, $perm) {
		return fopen($file, $perm);
	}

	function etheme_fr($file, $size) {
		return fread($file, $size);
	}

	function etheme_fc($file) {
		return fclose($file);
	}

	function etheme_fgcontent( $url, $flag, $context) {
		return file_get_contents($url, $flag, $context);
	}

	function etheme_fpcontent( $url, $flag, $context) {
		return file_put_contents($url, $flag, $context);
	}

	if ( !function_exists('et_mail') ) {
		function et_mail($email, $subject, $message, $headers) {
			return wp_mail($email, $subject, $message, $headers);
		}
	}

	add_action('admin_init', 'et_product_meta_boxes');

	if ( !function_exists('et_product_meta_boxes') ) {
		function et_product_meta_boxes() {
			add_meta_box( 'woocommerce-product-videos', __( 'Product Video', 'legenda-core' ), 'et_woocommerce_product_video_box', 'product', 'side' );
		}
	}

	if(!function_exists('et_woocommerce_product_video_box')) {
	function et_woocommerce_product_video_box() {
		global $post;
		?>
		<div id="product_video_container">
			<?php esc_html_e('Upload your Video in 3 formats: MP4, OGG and WEBM', 'legenda-core') ?>
			<ul class="product_video">
				<?php

					$product_video_code = get_post_meta( $post->ID, '_product_video_code', true );


					if ( metadata_exists( 'post', $post->ID, '_product_video_gallery' ) ) {
						$product_image_gallery = get_post_meta( $post->ID, '_product_video_gallery', true );
					}

					$video_attachments = false;

					if(isset($product_image_gallery) && $product_image_gallery != '') {
						$video_attachments = get_posts( array(
							'post_type' => 'attachment',
							'include' => $product_image_gallery
						) );
					}



					//$attachments = array_filter( explode( ',', $product_image_gallery ) );

					if ( $video_attachments )
						foreach ( $video_attachments as $attachment ) {
							echo '<li class="video" data-attachment_id="' . $attachment->id . '">
								Format: ' . $attachment->post_mime_type . '
								<ul class="actions">
									<li><a href="#" class="delete" title="' . __( 'Delete image', 'legenda-core' ) . '">' . __( 'Delete', 'legenda-core' ) . '</a></li>
								</ul>
							</li>';
						}
				?>
			</ul>

			<input type="hidden" id="product_video_gallery" name="product_video_gallery" value="<?php echo esc_attr( $product_image_gallery ); ?>" />

		</div>
		<p class="add_product_video hide-if-no-js">
			<a href="#"><?php esc_html_e( 'Add product gallery video', 'legenda-core' ); ?></a>
		</p>
		<p>
			<?php esc_html_e('Or you can use YouTube or Vimeo iframe code', 'legenda-core'); ?>
		</p>
		<div class="product_iframe_video">

			<textarea name="et_video_code" id="et_video_code" rows="7"><?php echo esc_attr( $product_video_code ); ?></textarea>

		</div>
		<script type="text/javascript">
			jQuery(document).ready(function($){

				// Uploading files
				var product_gallery_frame;
				var $image_gallery_ids = $('#product_video_gallery');
				var $product_images = $('#product_video_container ul.product_video');

				jQuery('.add_product_video').on( 'click', 'a', function( event ) {

					var $el = $(this);
					var attachment_ids = $image_gallery_ids.val();

					event.preventDefault();

					// If the media frame already exists, reopen it.
					if ( product_gallery_frame ) {
						product_gallery_frame.open();
						return;
					}

					// Create the media frame.
					product_gallery_frame = wp.media.frames.downloadable_file = wp.media({
						// Set the title of the modal.
						title: '<?php _e( 'Add Images to Product Gallery', 'legenda-core' ); ?>',
						button: {
							text: '<?php _e( 'Add to gallery', 'legenda-core' ); ?>',
						},
						multiple: true,
						library : { type : 'video'}
					});

					// When an image is selected, run a callback.
					product_gallery_frame.on( 'select', function() {

						var selection = product_gallery_frame.state().get('selection');

						selection.map( function( attachment ) {

							attachment = attachment.toJSON();

							if ( attachment.id ) {
								attachment_ids = attachment_ids ? attachment_ids + "," + attachment.id : attachment.id;

								$product_images.append('\
									<li class="video" data-attachment_id="' + attachment.id + '">\
										Video\
										<ul class="actions">\
											<li><a href="#" class="delete" title="<?php _e( 'Delete video', 'legenda-core' ); ?>"><?php _e( 'Delete', 'legenda-core' ); ?></a></li>\
										</ul>\
									</li>');
							}

						} );

						$image_gallery_ids.val( attachment_ids );
					});

					// Finally, open the modal.
					product_gallery_frame.open();
				});

				// Image ordering
				$product_images.sortable({
					items: 'li.video',
					cursor: 'move',
					scrollSensitivity:40,
					forcePlaceholderSize: true,
					forceHelperSize: false,
					helper: 'clone',
					opacity: 0.65,
					placeholder: 'wc-metabox-sortable-placeholder',
					start:function(event,ui){
						ui.item.css('background-color','#f6f6f6');
					},
					stop:function(event,ui){
						ui.item.removeAttr('style');
					},
					update: function(event, ui) {
						var attachment_ids = '';

						$('#product_video_container ul li.video').css('cursor','default').each(function() {
							var attachment_id = jQuery(this).attr( 'data-attachment_id' );
							attachment_ids = attachment_ids + attachment_id + ',';
						});

						$image_gallery_ids.val( attachment_ids );
					}
				});

				// Remove images
				$('#product_video_container').on( 'click', 'a.delete', function() {

					$(this).closest('li.video').remove();

					var attachment_ids = '';

					$('#product_video_container ul li.video').css('cursor','default').each(function() {
						var attachment_id = jQuery(this).attr( 'data-attachment_id' );
						attachment_ids = attachment_ids + attachment_id + ',';
					});

					$image_gallery_ids.val( attachment_ids );

					return false;
				} );

			});
		</script>
		<?php
	}

	// **********************************************************************//
	// ! QR Code generation
	// **********************************************************************//
	if(!function_exists('generate_qr_code')) {
	    function generate_qr_code($text='QR Code', $title = 'QR Code', $size = 128, $class = '', $self_link = false, $lightbox = false ) {
	        if($self_link) {
	            global $wp;
	            $text = @$_SERVER['HTTPS'] == 'on' ? 'https://' : 'http://';
	            if ( $_SERVER['SERVER_PORT'] != '80' )
	                $text .= $_SERVER['SERVER_NAME'] . ':' . $_SERVER['SERVER_PORT'] . $_SERVER['REQUEST_URI'];
	            else
	                $text .= $_SERVER['SERVER_NAME'] . $_SERVER['REQUEST_URI'];
	        }
	        $image = 'https://chart.googleapis.com/chart?chs=' . $size . 'x' . $size . '&cht=qr&chld=H|1&chl=' . $text;

	        if($lightbox) {
	            $class .= ' qr-lighbox';
	            $output = '<a href="'.$image.'" rel="lightbox" class="'.$class.'">'.$title.'</a>';
	        } else{
	            $class .= ' qr-image';
	            $output = '<img src="'.$image.'"  class="'.$class.'" />';
	        }

	        return $output;
	    }
	}

}


    add_action( 'admin_bar_menu', 'et_top_bar_menu', 100 );

    function et_top_bar_menu($wp_admin_bar){
        if ( ! current_user_can('manage_options') ) {
            return;
        }
        $args = array(
            'id'    => 'et-top-bar-menu',
            'title' => '<span><img src="'. ETHEME_CODE_IMAGES_URL . '/etheme.png' .'" alt="etheme" style="vertical-align: -6px;margin-right: 2px;max-width: 17px;"> Legenda</span>',
            'href'  => admin_url( 'admin.php?page=et-panel-welcome' ),
        );

        $wp_admin_bar->add_node( $args );

        $wp_admin_bar->add_node( array(
            'parent' => 'et-top-bar-menu',
            'id'     => 'et-panel-dashboard',
            'title'  => esc_html__( 'Dashboard', 'legenda-core' ),
            'href'   => admin_url( 'admin.php?page=et-panel-welcome' ),
        ) );
        if ( etheme_is_activated() ) {
            $wp_admin_bar->add_node( array(
                'parent' => 'et-top-bar-menu',
                'id'     => 'et-panel-plugins',
                'title'  => esc_html__( 'Install Plugins', 'legenda-core' ),
                'href'   => admin_url( 'themes.php?page=install-required-plugins&plugin_status=all' ),
            ) );
        }

        $wp_admin_bar->add_node( array(
            'parent' => 'et-top-bar-menu',
            'id'     => 'et-panel-options',
            'title'  => esc_html__( 'Theme Options', 'legenda-core' ),
            'href'   => admin_url( 'themes.php?page=LegendaThemeOptions' ),
        ) );

    }
?>