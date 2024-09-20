<?php  
	if ( ! function_exists( 'etheme_featured_post_shortcode' ) ) :
		function etheme_featured_post_shortcode($atts) {

		    $a = shortcode_atts(array(
		        'title' => '',
		        'id' => '',
		        'class' => '',
		        'more_posts' => 1
		    ),$atts);
		    $limit = 1;
		    $width = 300;
		    $height = 300;
		    $lightbox = etheme_get_option('blog_lightbox');
		    $blog_slider = etheme_get_option('blog_slider');
		    $posts_url = get_permalink(get_option('page_for_posts'));
		    $args = array(
		        'p'                     => $a['id'],
		        'post_type'             => 'post',
		        'ignore_sticky_posts'   => 1,
		        'no_found_rows'         => 1,
		        'posts_per_page'        => $limit
		    );

		    $the_query = new WP_Query( $args );
		    ob_start();
		    ?>

		    <?php if ( $the_query->have_posts() ) : ?>

		        <?php while ( $the_query->have_posts() ) : $the_query->the_post(); ?>

		            <div class="featured-posts <?php echo $a['class']; ?>">
		                <?php if ($a['title'] != ''): ?>
		                    <h3 class="title a-left"><span><?php echo $a['title']; ?></span></h3>
		                    <?php if ($a['more_posts']): ?>
		                            <?php echo '<a href="'.$posts_url.'" class="show-all-posts hidden-tablet hidden-phone">'.__('View more posts', 'legenda-core').'</a>'; ?>
		                    <?php endif ?>
		                <?php endif ?>
		                <div class="featured-post row-fluid">
		                    <div class="span6">
		                        <?php if ( has_post_thumbnail() ): ?>
		                            <div class="post-images nav-type-small">
		                                <ul class="slides">
		                                     <li><a href="<?php the_permalink(); ?>"><img src="<?php echo get_the_post_thumbnail_url(); ?>"></a></li>
		                                </ul>
		                                <div class="blog-mask">
		                                    <div class="mask-content">
		                                        <?php if($lightbox): ?><a href="<?php the_post_thumbnail_url(); ?>" rel="lightbox"><i class="icon-resize-full"></i></a><?php endif; ?>
		                                        <a href="<?php the_permalink(); ?>"><i class="icon-link"></i></a>
		                                    </div>
		                                </div>
		                            </div>
		                        <?php endif ?>
		                    </div>
		                    <div class="span6">
		                        <h4 class="post-title"><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
		                        <div class="post-info">
		                            <span class="posted-on">
		                                <?php _e('Posted on', 'legenda-core') ?>
		                                <?php the_time(get_option('date_format')); ?>
		                                <?php _e('at', 'legenda-core') ?>
		                                <?php the_time(get_option('time_format')); ?>
		                            </span>
		                            <span class="posted-by"> <?php _e('by', 'legenda-core');?> <?php the_author_posts_link(); ?></span>
		                        </div>
		                        <div class="post-description">
		                            <?php the_excerpt(); ?>
		                            <a href="<?php the_permalink(); ?>" class="button read-more"><?php _e('Read More', 'legenda-core') ?></a>
		                        </div>
		                    </div>
		                </div>
		            </div>

		        <?php endwhile; ?>

		        <?php wp_reset_postdata(); ?>

		    <?php else:  ?>

		        <p><?php _e( 'Sorry, no posts matched your criteria.', 'legenda-core' ); ?></p>

		    <?php endif; ?>

		    <?php
		    $output = ob_get_contents();
		    ob_end_clean();

		    return $output;

		}
	endif;

	if ( !function_exists('etheme_register_single_post') ) {

		function etheme_register_single_post () {

			if(!function_exists('vc_map')) {
				return;
			}

		    $single_post_params = array(
		      'name' => 'Single blog post',
		      'base' => 'single_post',
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
		          "heading" => __("Post ID", 'legenda-core'),
		          "param_name" => "id"
		        ),
		        array(
		          "type" => "dropdown",
		          "heading" => __("Show more posts link", 'legenda-core'),
		          "param_name" => "more_posts",
		          "value" => array( "", __("Show", 'legenda-core') => 1, __("Hide", 'legenda-core') => 0)
		        ),
		        array(
		          "type" => "textfield",
		          "heading" => __("Extra Class", 'legenda-core'),
		          "param_name" => "class",
		          "description" => __('If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.', 'legenda-core')
		        )
		      )
		
		    );  
		
		    vc_map($single_post_params);

	   	}

	}

?>