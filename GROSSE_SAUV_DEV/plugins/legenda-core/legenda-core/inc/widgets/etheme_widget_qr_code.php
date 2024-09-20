<?php 
	class Etheme_QRCode_Widget extends WP_Widget {

	    function __construct() {
	        $widget_ops = array('classname' => 'etheme_widget_qr_code', 'description' => __( "You can add a QR code image in sidebar to allow your users get quick access from their devices", 'legenda-core') );
	        parent::__construct('etheme-qr-code', '8theme - '.__('QR Code', 'legenda-core'), $widget_ops);
	        $this->alt_option_name = 'etheme_widget_qr_code';
	    }

	    function widget($args, $instance) {
		    if (etheme_admin_widget_preview(esc_html__('8theme - QR Code', 'woopress-core')) !== false) return;
	        extract($args);

	        $title = $instance['title'];
	        $info = $instance['info'];
	        $text = $instance['text'];
	        $size = (int) $instance['size'];
	        $lightbox = (bool) $instance['lightbox'];
	        $currlink = (bool) $instance['currlink'];

		    echo (isset($before_widget)) ? $before_widget : '';
	        if ( $title ) echo $before_title . $title . $after_title;
	        echo generate_qr_code($info, 'Open', $size, '', $currlink, $lightbox );
	        if($text != '') 
	            echo $text;
		    echo (isset($after_widget)) ? $after_widget : '';
	    }

	    function update( $new_instance, $old_instance ) {
	        $instance = $old_instance;
	        $instance['title'] = strip_tags($new_instance['title']);
	        $instance['info'] = strip_tags($new_instance['info']);
	        $instance['text'] = ($new_instance['text']);
	        $instance['size'] = (int) $new_instance['size'];
	        $instance['lightbox'] = (bool) $new_instance['lightbox'];
	        $instance['currlink'] = (bool) $new_instance['currlink'];

	        return $instance;
	    }

	    function form( $instance ) {
	        $block_id = 0;
	        if(!empty($instance['block_id']))
	            $block_id = esc_attr($instance['block_id']);

	        $info = isset($instance['info']) ? $instance['info'] : '';
	        $text = isset($instance['text']) ? $instance['text'] : '';
	        $title = isset($instance['title']) ? $instance['title'] : '';
	        $size = isset($instance['size']) ? (int) $instance['size'] : 256;
	        $lightbox = isset($instance['lightbox']) ? (bool) $instance['lightbox'] : false;
	        $currlink = isset($instance['currlink']) ? (bool) $instance['currlink'] : false;

	?>
	        <?php etheme_widget_input_text(__('Widget title:', 'legenda-core'), $this->get_field_id('title'),$this->get_field_name('title'), $title); ?>

	        <?php etheme_widget_textarea(__('Information to encode:', 'legenda-core'), $this->get_field_id('info'),$this->get_field_name('info'), $info); ?>

	        <?php etheme_widget_input_text(__('Image size:', 'legenda-core'), $this->get_field_id('size'), $this->get_field_name('size'), $size); ?>

	        <?php etheme_widget_input_checkbox(__('Show in lightbox', 'legenda-core'), $this->get_field_id('lightbox'), $this->get_field_name('lightbox'),checked($lightbox, true, false), 1); ?>

	        <?php etheme_widget_input_checkbox(__('Encode link to the current page', 'legenda-core'), $this->get_field_id('currlink'), $this->get_field_name('currlink'),checked($currlink, true, false), 1); ?>

	        <?php etheme_widget_textarea(__('Additional information in widget', 'legenda-core'), $this->get_field_id('text'),$this->get_field_name('text'), $text); ?>

	<?php
	    }
	}
?>