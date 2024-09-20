<?php 
	class Etheme_StatickBlock_Widget extends WP_Widget {

	    function __construct() {
	        $widget_ops = array('classname' => 'etheme_widget_satick_block', 'description' => __( "Insert static block, that you created", 'legenda-core') );
	        parent::__construct('etheme-static-block', '8theme - '.__('Statick Block', 'legenda-core'), $widget_ops);
	        $this->alt_option_name = 'etheme_widget_satick_block';
	    }

	    function widget($args, $instance) {
	        extract($args);

	        if ( function_exists('etheme_get_option') && !etheme_get_option('enable_static_blocks') ) {
	            echo '<p>' . esc_html__( 'To use this widget enable "static blocks" in the module section of theme options', 'legenda-core' ) . '</p>';
	            return;
	        }

		    if (isset($instance['block_id'])){
			    $block_id = $instance['block_id'];
			    et_show_block($block_id);
		    }
	    }

	    function update( $new_instance, $old_instance ) {
	        $instance = $old_instance;
	        $instance['block_id'] = $new_instance['block_id'];

	        return $instance;
	    }

	    function form( $instance ) {
	        $block_id = 0;
	        if(!empty($instance['block_id']))
	            $block_id = esc_attr($instance['block_id']);

	?>
	        <p><label for="<?php echo $this->get_field_id('block_id'); ?>"><?php _e('Block name:', 'legenda-core'); ?></label>
	            <?php $sb = et_get_static_blocks(); ?>
	            <select name="<?php echo $this->get_field_name('block_id'); ?>" id="<?php echo $this->get_field_id('block_id'); ?>">
	                <option><?php echo esc_html('--Select--','legenda-core'); ?></option>
	                <?php if (is_array($sb) && count($sb) > 0): ?>
	                    <?php foreach ($sb as $key): ?>
	                        <option value="<?php echo $key['value']; ?>" <?php selected( $block_id, $key['value'] ); ?>><?php echo $key['label'] ?></option>
	                    <?php endforeach ?>
	                <?php endif ?>
	            </select>
	        </p>
	<?php
	    }
	}
?>