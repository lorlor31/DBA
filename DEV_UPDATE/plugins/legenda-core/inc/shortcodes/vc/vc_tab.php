<?php 
    vc_map( array(
      "name" => __("Tab", "legenda-core"),
      "base" => "vc_tab",
      "allowed_container_element" => 'vc_row',
      "is_container" => true,
      "content_element" => false,
      "params" => array(
        array(
          "type" => "textfield",
          "heading" => __("Title", "legenda-core"),
          "param_name" => "title",
          "description" => __("Tab title.", "legenda-core")
        ),
        array(
          'type' => 'icon',
          "heading" => __("Icon", 'legenda-core'),
          "param_name" => "icon"
        ),
        array(
          "type" => "tab_id",
          "heading" => __("Tab ID", "legenda-core"),
          "param_name" => "tab_id"
        )
      ),
      'js_view' => ($vc_is_wp_version_3_6_more ? 'VcTabView' : 'VcTabView35')
    ) );
    
    function vc_theme_vc_tab($atts, $content = null) {
      global $tab_count;
      $output = $title = $iconHtml = '';

      extract(shortcode_atts(array(
        'title' => __("Section", "legenda-core"),
        'icon' => ''
      ), $atts));

      $tab_count++;
      if($icon != '') {
        $iconHtml = '<i class="icon-'.$icon.'"></i>';
      }

          $output .= "\n\t\t\t\t" . '<a href="#tab_'.$tab_count.'" id="tab_'.$tab_count.'" class="tab-title">'.$iconHtml.$title.'</a>';
          $output .= "\n\t\t\t\t" . '<div id="content_tab_'.$tab_count.'" class="tab-content">';
              $output .= ($content=='' || $content==' ') ? __("Empty section. Edit page to add content here.", "legenda-core") : "\n\t\t\t\t" . wpb_js_remove_wpautop($content);
              $output .= "\n\t\t\t\t" . '</div>';
      return $output;
    }	
?>