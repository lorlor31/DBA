<?php
/**
 * Template Name: Search results
 *
 */
 ?>

<?php 
	get_header();
?>

<?php 
	extract(etheme_get_page_sidebar());
?>

<?php if ($page_heading != 'disable' && ($page_slider == 'no_slider' || $page_slider == '')): ?>
	<?php et_page_heading(); ?>
<?php endif ?>

<?php if($page_slider != 'no_slider' && $page_slider != ''): ?>
	
	<?php echo do_shortcode('[rev_slider_vc alias="'.$page_slider.'"]'); ?>

<?php endif; ?>

<div class="container">
	<div class="page-content sidebar-position-<?php echo esc_attr($position); ?> responsive-sidebar-<?php echo esc_attr($responsive); ?>">
		<div class="row-fluid">
			<?php if($position == 'left'): ?>
				<div class="<?php echo esc_attr($sidebar_span); ?> sidebar sidebar-left">
					<?php etheme_get_sidebar($sidebarname); ?>
				</div>
			<?php endif; ?>
			
			<div class="content <?php echo esc_attr($content_span); ?>">
				<?php if(have_posts()): ?>
					<?php while(have_posts()) : the_post(); ?>
						
						<?php the_content(); ?>

						<?php 

							$args = array();

							$search_products = etheme_get_option('search_products');
							$search_posts = etheme_get_option('search_posts');
							$search_projects = etheme_get_option('search_projects');
							$search_pages = etheme_get_option('search_pages');
							$search_count = etheme_get_option('search_result_count');

							$args['s']          = sanitize_text_field($_REQUEST['search']);
							$args['count']      = (! empty( $search_count) );
							$args['images']     = 1;
							$args['products']   = (! empty( $search_products) );
							$args['posts']      = (! empty( $search_posts) );
							$args['portfolio']  = (! empty( $search_projects) );
							$args['pages']      = (! empty( $search_pages) );
							$args['format']     = 'array';

							$result = et_get_search_result( $args );

							if (!empty($result['posts'])) {
								$posts = $result['posts'];
							} else {
								$posts = '';
							}

							if( $args['products'] ):
							?>
								
							<?php
							if( ! empty( $posts['products'] ) ) {
								?>
								<div class="product-loop products-grid product-count-3">
								<h3 class="title"><span><?php esc_html_e('Products results', 'legenda'); ?></span></h3>
								<a href="<?php echo home_url().'/?s='.$args['s']; ?>&post_type=product" title="<?php esc_html_e('Show all', 'legenda'); ?>" class="view-all-results"><?php _e('Show all', 'legenda'); ?></a>
									<div>
										<?php
										foreach ($posts['products'] as $key => $post) {
											setup_postdata( $post );
											wc_get_template_part( 'content', 'product' );
										}
										?>
									</div>
									<div class="clear"></div>
								</div>
								<?php
								wp_reset_postdata();
							} else {
								?>
									<p class="warning"><?php esc_html_e('No products found maching your request.', 'legenda') ?></p>
								<?php
							}
							endif;

							if( $args['posts'] ):
							?>
							<?php
							if( ! empty( $posts['posts'] ) ) {
								?>
								<div class="blog-results">
									<h3 class="title"><span><?php esc_html_e('Blog results', 'legenda'); ?></span></h3>
									<a href="<?php echo home_url().'/?s='.$args['s']; ?>&post_type=post" title="<?php esc_html_e('Show all', 'legenda'); ?>" class="view-all-results"><?php esc_html_e('Show all', 'legenda'); ?></a>
									<div>
										<?php
										foreach ($posts['posts'] as $key => $post) {
											setup_postdata( $post );
											get_template_part( 'content', 'grid' );
										}
										?>
									</div>
									<div class="clear"></div>
								</div>
								<?php
								wp_reset_postdata();
							} else {
								?>
									<p class="warning"><?php _e('No posts found maching your request.', 'legenda') ?></p>
								<?php
							}
							endif;

							if( ! empty( $posts['portfolio'] ) && $args['portfolio'] ) {
								?>
								<h3 class="title"><span><?php esc_html_e('Portfolio results', 'legenda'); ?></span></h3>
								<div class="portfolio-results row-fluid">
									<?php
									foreach ($posts['portfolio'] as $key => $post) {
										setup_postdata( $post );
										get_template_part( 'content', 'portfolio' );
									}
									?>
									<div class="clear"></div>
								</div>
								<?php
								wp_reset_postdata();
							}

							if( ! empty( $posts['pages'] ) && $args['pages'] ) {
								?>
								<h3 class="title"><span><?php esc_html_e('Pages', 'legenda'); ?></span></h3>
								<div class="pages-results row-fluid">
									<?php
									foreach ($posts['pages'] as $key => $post) {
										setup_postdata( $post );
										?>
											<h4><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h4>
										<?php
									}
									?>
									<div class="clear"></div>
								</div>
								<?php
								wp_reset_postdata();
							}
						?>
						<?php break; ?>
					<?php endwhile;?>
				<?php endif; ?>

			</div>

			<?php if($position == 'right'): ?>
				<div class="<?php echo esc_attr($sidebar_span); ?> sidebar sidebar-right">
					<?php etheme_get_sidebar($sidebarname); ?>
				</div>
			<?php endif; ?>
		</div><!-- end row-fluid -->

	</div>
</div><!-- end container -->
	
<?php
	get_footer();
?>