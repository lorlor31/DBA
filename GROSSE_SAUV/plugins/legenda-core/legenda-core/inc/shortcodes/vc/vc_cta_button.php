<?php 
    $setting_cta_button = array (
      "params" => array(
          array(
            "type" => "textarea_html",
            "heading" => __("Text", "legenda-core"),
            "param_name" => "content",
            "value" => __("Click edit button to change this text.", "legenda-core"),
            "description" => __("Enter your content.", "legenda-core")
          ),
          array(
            "type" => "dropdown",
            "heading" => __("Block Style", "legenda-core"),
            "param_name" => "style",
            "value" => array(
              "" => "",
              __("Default", "legenda-core") => "default", 
              __("Filled", "legenda-core") => "filled", 
              __("Without Border", "legenda-core") => "without-border", 
              __("Dark", "legenda-core") => "dark"
            )
          ),
          array(
            "type" => "textfield",
            "heading" => __("Text on the button", "legenda-core"),
            "param_name" => "title",
            "value" => __("Text on the button", "legenda-core"),
            "description" => __("Text on the button.", "legenda-core")
          ),
          array(
            "type" => "textfield",
            "heading" => __("URL (Link)", "legenda-core"),
            "param_name" => "href",
            "description" => __("Button link.", "legenda-core")
          ),
          array(
            "type" => "dropdown",
            "heading" => __("Button position", "legenda-core"),
            "param_name" => "position",
            "value" => array(__("Align right", "legenda-core") => "right", __("Align left", "legenda-core") => "left"),
            "description" => __("Select button alignment.", "legenda-core")
          )
        )
    );
    vc_map_update('vc_cta_button', $setting_cta_button);

    function vc_theme_vc_cta_button($atts, $content = null) {
      $output = $call_title = $href = $title = $call_text = $el_class = '';
      extract(shortcode_atts(array(
          'href' => '',
          'style' => '',
          'title' => '',
          'position' => 'right'
      ), $atts));

      return do_shortcode('[callto btn_position="'.$position.'" btn="'.$title.'" style="'.$style.'" link="'.$href.'"]'.$content.'[/callto]');
    }
?>