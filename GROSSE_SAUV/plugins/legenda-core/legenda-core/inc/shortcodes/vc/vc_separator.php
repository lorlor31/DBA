<?php 
    $setting_vc_separator = array (
    'show_settings_on_create' => true,
  	'params' => array(
          array(
            "type" => "dropdown",
            "heading" => __("Type", "legenda-core"),
            "param_name" => "type",
            "value" => array( 
                "", 
                __("Default", 'legenda-core') => "",
                __("Double", 'legenda-core') => "double",
                __("Dashed", 'legenda-core') => "dashed",
                __("Dotted", 'legenda-core') => "dotted",
                __("Double Dotted", 'legenda-core') => "double dotted",
                __("Double Dashed", 'legenda-core') => "double dashed",
                __("Horizontal break", 'legenda-core') => "horizontal-break",
                __("Space", 'legenda-core') => "space"
              )
          ),
          array(
            "type" => "textfield",
            "heading" => __("Height", "legenda-core"),
            "param_name" => "height",
            "dependency" => Array('element' => "type", 'value' => array('space'))
          ),
          array(
            "type" => "textfield",
            "heading" => __("Extra class", "legenda-core"),
            "param_name" => "class"
          )
        ) 
    );
    vc_map_update('vc_separator', $setting_vc_separator);

    function vc_theme_vc_separator($atts, $content = null) {
      $output = $color = $el_class = $css_animation = '';
      extract(shortcode_atts(array(
          'type' => '',
          'class' => '',
          'height' => ''
      ), $atts));

      $output .= do_shortcode('[hr class="'.$type.' '.$class.'" height="'.$height.'"]');
      return $output;
    }
?>