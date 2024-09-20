<?php 

	class Etheme_Brands_Widget extends WP_Widget {

	    function __construct() {
	        $widget_ops = array('classname' => 'etheme_widget_brands', 'description' => __( "Products Filter by brands", 'legenda-core') );
	        parent::__construct('etheme-brands', '8theme - '.__('Brands Filter', 'legenda-core'), $widget_ops);
	        $this->alt_option_name = 'etheme_widget_brands';
	    }

	    function widget($args, $instance) {
		    if (etheme_admin_widget_preview(esc_html__('8theme - Brands Filter', 'woopress-core')) !== false) return;
	        extract($args);

	        $title = $instance['title'];
	        $dropdown = $instance['dropdown'];
		    echo (isset($before_widget)) ? $before_widget : '';
	        if(!$title == '' ){
		        echo $before_title;
		        echo $title;
		        echo $after_title;
	        }

	        if ( function_exists('etheme_get_option') && !etheme_get_option('enable_brands') ) {
	                echo '<p>' . esc_html__( 'To use this widget enable "brands" in the module section of theme options', 'legenda-core' ) . '</p>';
	            echo $after_widget;
	            return;
	        }

	        $current_term = get_queried_object();
			$args = array( 'hide_empty' => false);
			$terms = get_terms('brand', $args);
			$count = count($terms); $i=0;
			if ($count > 0) {
				if($dropdown == 1) {
					?>
						<select id="dropdown_layered_brand">
							<option value="<?php echo get_permalink( wc_get_page_id( 'shop' ) ); ?>"><?php esc_html_e('Any Brand', 'legenda-core'); ?></option>
							<?php
							    foreach ($terms as $term) {
							        $i++;
							        $curr = false;
							        $thumbnail_id 	= absint( get_term_meta( $term->term_id, 'thumbnail_id', true ) );
							        if(isset($current_term->term_id) && $current_term->term_id == $term->term_id) {
								        $curr = true;
							        }
							        ?>
							        	<option <?php if($curr) echo 'selected="selected"'; ?> value="<?php echo get_term_link( $term ); ?>">
							        		<?php echo $term->name; ?>
							        	</option>
									<?php
							    }
							?>
						</select>
					<?php
						wc_enqueue_js("

							jQuery('#dropdown_layered_brand').change(function(){
							
								location.href = jQuery('#dropdown_layered_brand').val();

							});

						");
				} else {
					?>
					<ul>
						<?php
						    foreach ($terms as $term) {
						        $i++;
						        $curr = false;
						        $thumbnail_id 	= absint( get_term_meta( $term->term_id, 'thumbnail_id', true ) );
						        if(isset($current_term->term_id) && $current_term->term_id == $term->term_id) {
							        $curr = true;
						        }
						        ?>
						        	<li <?php if($curr) echo 'class="active-brand"'; ?>>
						        		<a href="<?php echo get_term_link( $term ); ?>" title="<?php echo sprintf(__('View all products from %s', 'legenda-core'), $term->name); ?>"><?php echo $term->name; ?></a>
						        	</li>
								<?php
						    }
						?>
					</ul>
					<?php
				}
	        }
		    echo (isset($after_widget)) ? $after_widget : '';
	    }

	    function update( $new_instance, $old_instance ) {
	        $instance = $old_instance;
	        $instance['title'] = $new_instance['title'];
	        $instance['dropdown'] = $new_instance['dropdown'];

	        return $instance;
	    }

	    function form( $instance ) {
	        $title = isset($instance['title']) ? $instance['title'] : '';
	        $dropdown = isset($instance['dropdown']) ? $instance['dropdown'] : '';

	?>
	        <?php etheme_widget_input_text(__('Title', 'legenda-core'), $this->get_field_id('title'),$this->get_field_name('title'), $title); ?>
	        <?php etheme_widget_input_checkbox(__('Show as a drop down', 'legenda-core'), $this->get_field_id('dropdown'), $this->get_field_name('dropdown'),checked($dropdown, true, false), 1); ?>

	<?php
	    }
	}
?>