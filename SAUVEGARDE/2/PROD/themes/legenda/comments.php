<?php 
	// Prevent the direct loading

	defined('ABSPATH') || exit(esc_html__('You cannot access this file', 'legenda'));

	// Check if post is pwd protected

	if(post_password_required()){
		?>
			<p><?php esc_html_e('This post is password protected. Enter the password to view the comments.', 'legenda'); ?></p>
		<?php
		return;
	}
	
	if(have_comments()) :?>
	
		<h4 class="comments-title"><?php comments_number(esc_html__('No Comments', 'legenda'), esc_html__('One Comment', 'legenda'), esc_html__('% Comments', 'legenda')); ?></h4>

		<ol class="commentslist">
			<?php wp_list_comments('callback=etheme_comments'); ?>
		</ol>

		<?php if (get_comment_pages_count() > 1 && get_option('page_comments')): ?>
			
			<div class="comments-nav">
				<div class="left"><?php previous_comments_link(esc_html__('&larr; Older Comments', 'legenda')); ?></div>
				<div class="right"><?php next_comments_link(esc_html__('Newer Comments &rarr;', 'legenda')); ?></div>
				<div class="clear"></div>
			</div>

		<?php endif ?>

	<?php elseif(!comments_open() && !is_page() && post_type_supports(get_post_type(), 'comments')) : ?>
		
		<p><?php esc_html_e('Comments are closed', 'legenda') ?></p>

	<?php 
	endif;

	// Display Comment Form
	comment_form();
?>