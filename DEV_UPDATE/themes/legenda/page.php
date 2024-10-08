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
				<?php if(have_posts()): while(have_posts()) : the_post(); ?>
					
					<?php the_content(); ?>

					<div class="post-navigation">
						<?php wp_link_pages(); ?>
					</div>
					
					<?php if ($post->ID != 0 && current_user_can('edit_post', $post->ID)): ?>
						<?php edit_post_link( __('Edit this', 'legenda'), '<p class="edit-link">', '</p>' ); ?>
					<?php endif ?>

				<?php endwhile; else: ?>

					<h3><?php esc_html_e('No pages were found!', 'legenda') ?></h3>

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