<?php 
	class Etheme_Recent_Posts_Widget extends WP_Widget {

	    function __construct() {
	        $widget_ops = array('classname' => 'etheme_widget_recent_entries', 'description' => __( "The most recent posts on your blog (Etheme Edit)", 'legenda-core') );
	        parent::__construct('etheme-recent-posts', '8theme - '.__('Recent Posts', 'legenda-core'), $widget_ops);
	        $this->alt_option_name = 'etheme_widget_recent_entries';

	        add_action( 'save_post', array(&$this, 'flush_widget_cache') );
	        add_action( 'deleted_post', array(&$this, 'flush_widget_cache') );
	        add_action( 'switch_theme', array(&$this, 'flush_widget_cache') );
	    }

	    function widget($args, $instance) {
		    if (etheme_admin_widget_preview(esc_html__('8theme - Recent Posts', 'woopress-core')) !== false) return;
	        $cache = wp_cache_get('etheme_widget_recent_entries', 'widget');

	        if ( !is_array($cache) )
	                $cache = array();

	        if ( isset($cache[$args['widget_id']]) ) {
	                echo $cache[$args['widget_id']];
	                return;
	        }

	        ob_start();
	        extract($args);

	        $title = apply_filters('widget_title', empty($instance['title']) ? false : $instance['title']);
	        if ( !$number = (int) $instance['number'] )
	                $number = 10;
	        else if ( $number < 1 )
	                $number = 1;
	        else if ( $number > 15 )
	                $number = 15;

	        $r = new WP_Query(array('showposts' => $number, 'nopaging' => 0, 'post_status' => 'publish', 'ignore_sticky_posts' => 1));
	        if ($r->have_posts()) : ?>
	        <?php echo (isset($before_widget)) ? $before_widget : ''; ?>
	        <?php if ( $title ) echo $before_title . $title . $after_title; ?>
	            <div>
	                <?php  while ($r->have_posts()) : $r->the_post(); ?>
	                    <div class="recent-post-mini">
	                        <?php 
	                            $thumb = wp_get_attachment_image_src( get_post_thumbnail_id(), array(130,130));
	                            $url = isset($thumb[0]) ? $thumb[0] : '';
	                            if($url && $url != ''):
	                        ?>
	                            <a class="postimg" href="<?php the_permalink() ?>" title="<?php echo esc_attr(get_the_title() ? get_the_title() : get_the_ID()); ?>"><?php echo new_etheme_get_image( get_post_thumbnail_id(), array( 70, 70 ) )?></a>
	                            
	                        <?php endif; ?>
	                        <?php
	                            if ( get_the_title() ) $title = get_the_title(); else $title = get_the_ID();

	                            $title = trunc($title, 10);
	                        ?>
	                        <a href="<?php the_permalink() ?>" <?php if(!$url || $url == '') echo 'style="width:100%;"' ?> title="<?php echo esc_attr(get_the_title() ? get_the_title() : get_the_ID()); ?>">
	                            <?php echo $title; ?> 
	                        </a><br />
	                        <?php _e('by', 'legenda-core') ?> <strong><?php the_author(); ?></strong><br>
	                        <?php the_time(get_option('date_format')); ?>
	                        <div class="clear"></div>
	                    </div>
	                <?php endwhile; ?>
	            </div>
	        <?php echo (isset($after_widget)) ? $after_widget : ''; ?>
	    <?php
	                wp_reset_query();  // Restore global post data stomped by the_post().
	        endif;

	        $cache[$args['widget_id']] = ob_get_flush();
	        wp_cache_add('etheme_widget_recent_entries', $cache, 'widget');
	    }

	    function update( $new_instance, $old_instance ) {
	        $instance = $old_instance;
	        $instance['title'] = strip_tags($new_instance['title']);
	        $instance['number'] = (int) $new_instance['number'];
	        $this->flush_widget_cache();

	        $alloptions = wp_cache_get( 'alloptions', 'options' );
	        if ( isset($alloptions['etheme_widget_recent_entries']) )
	                delete_option('etheme_widget_recent_entries');

	        return $instance;
	    }

	    function flush_widget_cache() {
	        wp_cache_delete('etheme_widget_recent_entries', 'widget');
	    }

	    function form( $instance ) {
	        $title = isset($instance['title']) ? esc_attr($instance['title']) : '';
		    $number = isset($instance['number']) ? (int) $instance['number'] : 5;

	?>
	        <p><label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:', 'legenda-core'); ?></label>
	        <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>" type="text" value="<?php echo $title; ?>" /></p>

	        <p><label for="<?php echo $this->get_field_id('number'); ?>"><?php _e('Number of posts to show:', 'legenda-core'); ?></label>
	        <input id="<?php echo $this->get_field_id('number'); ?>" name="<?php echo $this->get_field_name('number'); ?>" type="text" value="<?php echo $number; ?>" size="3" /><br />
	        <small><?php _e('(at most 15)', 'legenda-core'); ?></small></p>
	<?php
	    }
	}
?>