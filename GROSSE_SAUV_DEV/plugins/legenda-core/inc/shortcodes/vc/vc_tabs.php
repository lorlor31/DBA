<?php 
    $tab_id_1 = time().'-1-'.rand(0, 100);
    $tab_id_2 = time().'-2-'.rand(0, 100);
    vc_map( array(
      "name"  => __("Tabs", "legenda-core"),
      "base" => "vc_tabs",
      "show_settings_on_create" => true,
      "is_container" => true,
      "icon" => "icon-wpb-ui-tab-content",
      "category" => __('Content', 'legenda-core'),
      "params" => array(
        array(
          "type" => "textfield",
          "heading" => __("Widget title", "legenda-core"),
          "param_name" => "title",
          "description" => __("What text use as a widget title. Leave blank if no title is needed.", "legenda-core")
        ),
        array(
          "type" => "dropdown",
          "heading" => __("Tabs type", "legenda-core"),
          "param_name" => "type",
          "value" => array(__("Default", "legenda-core") => '', 
              __("Accordion", "legenda-core") => 'accordion', 
              __("Left bar", "legenda-core") => 'left-bar', 
              __("Right bar", "legenda-core") => 'right-bar')
        ),
        array(
          "type" => "textfield",
          "heading" => __("Extra class name", "legenda-core"),
          "param_name" => "el_class",
          "description" => __("If you wish to style particular content element differently, then use this field to add a class name and then refer to it in your css file.", "legenda-core")
        )
      ),
      "custom_markup" => '
      <div class="wpb_tabs_holder wpb_holder vc_container_for_children">
      <ul class="tabs_controls">
      </ul>
      %content%
      </div>'
      ,
      'default_content' => '
      [vc_tab title="'.__('Tab 1','legenda-core').'" tab_id="'.$tab_id_1.'"][/vc_tab]
      [vc_tab title="'.__('Tab 2','legenda-core').'" tab_id="'.$tab_id_2.'"][/vc_tab]
      ',
      "js_view" => ($vc_is_wp_version_3_6_more ? 'VcTabsView' : 'VcTabsView35')
    ) );


    function vc_theme_vc_tabs($atts, $content = null) {
      $output = $title = $interval = $el_class = $collapsible = $active_tab = '';
      //
      extract(shortcode_atts(array(
          'title' => '',
          'interval' => 0,
          'el_class' => '',
          'type' => '',
          'collapsible' => 'no',
          'active_tab' => '1'
      ), $atts));

      $el_class = ' '.$el_class;
      $css_class = apply_filters(VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, 'wpb_content_element '.$el_class.' '.$type.' not-column-inherit');


      $output .= wpb_widget_title(array('title' => $title, 'extraclass' => 'wpb_accordion_heading'));

      $output .= "\n\t".'<div class="'.$css_class.' tabs">'; 
      $output .= "\n\t\t\t".wpb_js_remove_wpautop($content);
      $output .= "\n\t".'</div> ';
      return $output;
    }
?>