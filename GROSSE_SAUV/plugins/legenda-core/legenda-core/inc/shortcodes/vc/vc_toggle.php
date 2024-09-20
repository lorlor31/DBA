<?php 
    function vc_theme_vc_toggle($atts, $content = null) {
      $output = $title = $css_class = $el_class = $open = $css_animation = '';
      extract(shortcode_atts(array(
          'title' => __("Click to toggle", "legenda-core"),
          'el_class' => '',
          'open' => 'false',
          'css_animation' => ''
      ), $atts));


      $open = ( $open == 'true' ) ? 1 : 0;

      $css_class .= getCSSAnimation($css_animation);
      $css_class .= ' '.$el_class;

      $output .= '<div class="toggle-block '.$css_class.'">'.do_shortcode('[toggle title="'.$title.'" class="'.$css_class.'" active="'.$open.'"]'.wpb_js_remove_wpautop($content).'[/toggle]').'</div>';


      return $output;
    }
?>