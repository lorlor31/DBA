<?php
if ( !etheme_get_option( 'enable_portfolio' ) ): ?>
	<p class="alert-info"><?php esc_html_e( 'To use "Portfolio Template" enable "Portfolio" in the MODULE section of theme options', 'legenda' ); ?></p>
<?php endif; 

$postId = get_the_ID();

$categories = wp_get_post_terms($postId, 'categories');

$catsClass = '';
foreach($categories as $category) {
	$catsClass .= ' portfolio_category-'.$category->slug;
}

$columns = etheme_get_option('portfolio_columns');
$lightbox = etheme_get_option('portfolio_lightbox');


if(isset($_GET['col'])) {
	$columns = $_GET['col'];
}

switch($columns) {
	case 2:
		$span = 'span6';
	break;
	case 3:
		$span = 'span4';
	break;
	case 4:
		$span = 'span3';
	break;
	default:
		$span = 'span4';
	break;
}
	$width = etheme_get_option('portfolio_image_width');
	$height = etheme_get_option('portfolio_image_height');

	if ( ! $width && ! $height ) {
		$size = 'medium';
	} else {
		$size = array( $width, $height );
	}

?>
<div class="portfolio-item columns-count-<?php echo esc_attr($columns); ?> <?php echo esc_attr($span); ?> <?php echo esc_attr($catsClass); ?>">       
		<?php if ( has_post_thumbnail() ): ?>
			<div class="portfolio-image">

					<a href="<?php the_permalink(); ?>"><?php echo new_etheme_get_image( get_post_thumbnail_id(), $size ); ?></a>
					<div class="portfolio-mask">
						<div class="mask-content">
							<?php if($lightbox): ?><a href="<?php the_post_thumbnail_url(); ?>" rel="lightbox"><i class="icon-resize-full"></i></a><?php endif; ?>
							<a href="<?php the_permalink(); ?>"><i class="icon-link"></i></a>
						</div>
					</div>
		    </div>
		<?php endif; ?>
	    <div class="portfolio-descr">
	    		<?php if(etheme_get_option('project_name')): ?>
			    	<h3><a href="<?php the_permalink(); ?>"><?php the_title(); ?></a></h3>
			    <?php endif; ?>
			    
	    		<?php if(etheme_get_option('project_byline')): ?>
					<div class="post-info">
						<span class="posted-on">
							<?php esc_html_e('Posted on', 'legenda') ?>
							<?php the_time(get_option('date_format')); ?> 
							<?php esc_html_e('at', 'legenda') ?> 
							<?php the_time(get_option('time_format')); ?>
						</span> 
						<span class="posted-by"> <?php esc_html_e('by', 'legenda');?> <?php the_author_posts_link(); ?></span> / 
						<span class="posted-in"><?php print_item_cats($postId); ?></span> 
					</div>
			    <?php endif; ?>

    		<?php if(etheme_get_option('project_excerpt')): ?>
				<?php the_excerpt(); ?>
		    <?php endif; ?>

	    </div>    
</div>