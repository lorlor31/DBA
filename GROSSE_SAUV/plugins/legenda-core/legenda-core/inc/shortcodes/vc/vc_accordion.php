<?php 
    function vc_theme_vc_accordion($atts, $content = null) {
      $output = $title = $interval = $el_class = $collapsible = $active_tab = '';
      //
      extract(shortcode_atts(array(
          'title' => '',
          'interval' => 0,
          'el_class' => '',
          'collapsible' => 'no',
          'active_tab' => '1'
      ), $atts));

      $el_class = ' '.$el_class;
      
      if($active_tab == 'false') {
      	$el_class .= ' closed-tabs ';
      }
      $css_class = apply_filters(VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, 'wpb_content_element '.$el_class.' not-column-inherit');


      $output .= wpb_widget_title(array('title' => $title, 'extraclass' => 'wpb_accordion_heading'));

      $output .= "\n\t".'<div class="'.$css_class.' tabs accordion">'; 
      $output .= "\n\t\t\t".wpb_js_remove_wpautop($content);
      $output .= "\n\t".'</div> ';
      return $output;
    }
?>