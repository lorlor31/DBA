<?php 
    function vc_theme_vc_accordion_tab($atts, $content = null) {
      global $tab_count;
      $output = $title = '';

      extract(shortcode_atts(array(
        'title' => __("Section", "legenda-core")
      ), $atts));

      $tab_count++;

          $output .= "\n\t\t\t\t" . '<a href="#tab_'.$tab_count.'" id="tab_'.$tab_count.'" class="tab-title">'.$title.'</a>';
          $output .= "\n\t\t\t\t" . '<div id="content_tab_'.$tab_count.'" class="tab-content">';
              $output .= ($content=='' || $content==' ') ? __("Empty section. Edit page to add content here.", "legenda-core") : "\n\t\t\t\t" . wpb_js_remove_wpautop($content);
              $output .= "\n\t\t\t\t" . '</div>';
      return $output;
    }	
?>