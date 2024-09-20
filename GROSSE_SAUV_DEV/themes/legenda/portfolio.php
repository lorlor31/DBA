<?php
/**
 * Template Name: Portfolio
 *
 */
 ?>
 
<?php 
	extract(etheme_get_page_sidebar());
?>

<?php 
	get_header();
?>

<?php if ($page_heading != 'disable' && ($page_slider == 'no_slider' || $page_slider == '')): ?>
	<?php et_page_heading(); ?>
<?php endif ?>

<?php if($page_slider != 'no_slider' && $page_slider != ''): ?>
	
	<?php echo do_shortcode('[rev_slider_vc alias="'.$page_slider.'"]'); ?>

<?php endif; ?>

<div class="container">
	<div class="page-content sidebar-position-without">
		<div class="row">
			<div class="content span12">
				<?php if( have_posts() ): the_post();  ?>
					<?php echo do_shortcode(get_the_content()); ?>
				<?php endif; ?>

				<?php if ( etheme_get_option( 'enable_portfolio' ) ): ?>
					<?php get_etheme_portfolio(); ?>
				<?php else: ?>
					<p class="alert-info"><?php esc_html_e( 'To use "Portfolio Template" enable "Portfolio" in the MODULE section of theme options', 'legenda' ); ?></p>
				<?php endif; ?>
			</div>
		</div>

	</div>
</div>
	
<?php
	get_footer();
?>