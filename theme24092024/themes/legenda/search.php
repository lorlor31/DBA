<?php 
	get_header();
?>
<?php 
	extract(etheme_get_blog_sidebar());
	$postspage_id = get_option('page_for_posts');
?>

	<?php et_page_heading(); ?>

<div class="container">
	<div class="page-content sidebar-position-<?php echo esc_attr($position); ?> responsive-sidebar-<?php echo esc_attr($responsive); ?>">
		<div class="row">
			<?php if($position == 'left'): ?>
				<div class="<?php echo esc_attr($sidebar_span); ?> sidebar sidebar-left">
					<?php etheme_get_sidebar($sidebarname); ?>
				</div>
			<?php endif; ?>

			<div class="content <?php echo esc_attr($content_span); ?>">
					<?php if ($blog_layout == 'grid'): ?>
						<div class="blog-masonry row">
					<?php endif ?>

						<?php if(have_posts()): while(have_posts()) : the_post(); ?>

								<?php get_template_part('content', $blog_layout); ?>

						<?php endwhile; ?>

					<?php if ($blog_layout == 'grid'): ?>
						</div>
					<?php endif ?>

				<?php else: ?>

					<h1><?php esc_html_e('No posts were found!', 'legenda') ?></h1>

				<?php endif; ?>

				<div class="articles-nav">
					<div class="left"><?php next_posts_link(__('&larr; Older Posts', 'legenda')); ?></div>
					<div class="right"><?php previous_posts_link(__('Newer Posts &rarr;', 'legenda')); ?></div>
					<div class="clear"></div>
				</div>

			</div>

			<?php if($position == 'right'): ?>
				<div class="<?php echo esc_attr($sidebar_span); ?> sidebar sidebar-right">
					<?php etheme_get_sidebar($sidebarname); ?>
				</div>
			<?php endif; ?>
		</div>


	</div>
</div>

	
<?php
	get_footer();
?>