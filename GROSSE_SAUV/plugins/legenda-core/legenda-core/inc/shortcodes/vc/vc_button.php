<?php 
    $setting_vc_button = array (
      "params" => array(
          array(
            "type" => "textfield",
            "heading" => __("Text on the button", "legenda-core"),
            "holder" => "button",
            "class" => "wpb_button",
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
            "heading" => __("Target", "legenda-core"),
            "param_name" => "target",
            "value" => $target_arr,
            "dependency" => Array('element' => "href", 'not_empty' => true)
          ),
          array(
            "type" => "dropdown",
            "heading" => __("Type", "legenda-core"),
            "param_name" => "btn_type",
            "value" => array('bordered', 'filled'),
            "description" => __("Button type.", "legenda-core")
          ),
          /*
          array(
            "type" => "dropdown",
            "heading" => __("Icon", "legenda-core"),
            "param_name" => "icon",
            "value" => $icons_arr,
            "description" => __("Button icon.", "legenda-core")
          ),*/
          array(
            "type" => "dropdown",
            "heading" => __("Size", "legenda-core"),
            "param_name" => "size",
            "value" => array('small','medium','big'),
            "description" => __("Button size.", "legenda-core")
          ),
          array(
            "type" => "textfield",
            "heading" => __("Extra class name", "legenda-core"),
            "param_name" => "el_class",
            "description" => __("If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", "legenda-core")
          )
        )
    );
    vc_map_update('vc_button', $setting_vc_button);

    function vc_theme_vc_button($atts, $content = null) {
      $output = $btn_type = $size = $icon = $target = $href = $el_class = $title = $position = '';
      extract(shortcode_atts(array(
          'btn_type' => '',
          'size' => '',
          'target' => '_self',
          'href' => '',
          'el_class' => '',
          'title' => __('Text on the button', "legenda-core"),
          'position' => ''
      ), $atts));

      if ( $target == 'same' || $target == '_self' ) { $target = ''; }
      $target = ( $target != '' ) ? ' target="'.$target.'"' : '';

      $btn_type = ( $btn_type != '' ) ? ' '.$btn_type : '';
      $size = ( $size != '') ? ' '.$size : ' ';
      $position = ( $position != '' ) ? ' '.$position.'-button-position' : '';
      $el_class = ' '.$el_class;

      $css_class = apply_filters(VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, ' '.$btn_type.$size.$el_class.$position);

      if ( $href != '' ) {
          $output = '<a class="button '.$css_class.'" title="'.$title.'" href="'.$href.'"'.$target.'>' . $title . '</a>';
      } else {
          $output .= '<button class="button '.$css_class.'">'.$title.'</button>';

      }

      return $output;
    }
?>