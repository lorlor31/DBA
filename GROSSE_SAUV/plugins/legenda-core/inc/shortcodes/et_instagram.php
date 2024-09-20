<?php  if ( ! defined('ABSPATH')) exit('No direct script access allowed');
// **********************************************************************//
// ! Instagram
// **********************************************************************//

function etheme_instagram_shortcode($atts, $content) {
    $args = shortcode_atts(array(
        'title'  => '',
        'user' => '',
        'username'  => '',
        'number'  => 9,
        'columns'  => 4,
        'size'  => 'thumbnail',
        'target'  => '',
        'slider'  => 0,
        'spacing'  => 0,
        'link'  => '',
        'info' => 0,
        'filter_img' => 0,
        'large' => 4,
        'notebook' => 3,
        'tablet_land' => 2,
        'tablet_portrait' => 2,
        'mobile' => 1,
        'slider_autoplay' => false,
        'slider_speed' => 10000,
        'pagination_type' => 'hide',
        'default_color' => '#e6e6e6',
        'active_color' => '#b3a089',
        'hide_fo' => '',
        'hide_buttons' => false,
    ), $atts);

    ob_start();

    the_widget( 'null_instagram_widget', $args );

    $output = ob_get_contents();
    ob_end_clean();

    return $output;
}


// **********************************************************************//
// ! Register New Element: [8THEME] Instagram
// **********************************************************************//

if ( function_exists('vc_map') ) {

    $api_data = get_option( 'etheme_instagram_api_data' );
    $api_data = json_decode($api_data, true);
    $users    = array( '' => '' );

    if ( is_array($api_data) && count( $api_data ) ) {
        foreach ( $api_data as $key => $value ) {
            $value = json_decode( $value, true );

            $users[$value['data']['username']] = $key;
        }
    }

    $et_instagram = array(
      'name' => '[8THEME] Instagram',
      'base' => 'et_instagram',
      'category' => 'Eight Theme',
      'params' => array_merge(array(
        array(
          "type" => "textfield",
          "heading" => esc_html__("Title", 'legenda-core'),
          "param_name" => "title",
        ),
        array(
            'type' => 'dropdown',
            'heading' => esc_html__( 'Choose Instagram account', 'xstore' ),
            'description' => '<a href="' . admin_url('admin.php?page=et-panel-social'). '" target="_blank">'. esc_html__('Add Instagram account?', 'xstore-core') . '</a>',
            'param_name' => 'user',
            'value' => $users,
        ),
        array(
          "type" => "textfield",
          "heading" => esc_html__("Hashtag", 'legenda-core'),
          "param_name" => "username",
        ),
        array(
          "type" => "textfield",
          "heading" => esc_html__("Numer of photos", 'legenda-core'),
          "param_name" => "number",
        ),
        array(
            'type' => 'dropdown',
            'heading' => esc_html__( 'Photo size', 'legenda-core' ),
            'param_name' => 'size',
            'value' => array(
                __( 'Thumbnail', 'legenda-core' ) => 'thumbnail',
                __( 'Medium', 'legenda-core' ) => 'medium',
                __( 'Large', 'legenda-core' ) => 'large',
            ),
        ),
        array(
            'type' => 'dropdown',
            'heading' => esc_html__( 'Columns', 'legenda-core' ),
            'param_name' => 'columns',
            'value' => array(
                2 => 2,
                3 => 3,
                4 => 4,
                5 => 5,
                6 => 6,
            ),
        ),
        array(
            'type' => 'dropdown',
            'heading' => esc_html__( 'Open links in', 'legenda-core' ),
            'param_name' => 'target',
            'value' => array(
                __( 'Current window (_self)', 'legenda-core' ) => '_self',
                __( 'New window (_blank)', 'legenda-core' ) => '_blank',
            ),
        ),
        array(
          "type" => "textfield",
          "heading" => esc_html__("Link text", 'legenda-core'),
          "param_name" => "link",
        ),
        array(
          "type" => "checkbox",
          "heading" => esc_html__("Additional information", 'legenda-core'),
          "param_name" => "info",
        ),
        array(
            "type" => "dropdown",
            "heading" => esc_html__("Slider", 'legenda-core'),
            "param_name" => "slider",
            "value" => array(
                '' => '',
                'yes' => 'yes',
            )
        ),
        array(
          "type" => "textfield",
          "heading" => __("Number of items on desktop", 'legenda-core'),
          "param_name" => "large",
          "dependency" => array('element' => "slider", 'value' => array('yes')),
        ),
        array(
          "type" => "textfield",
          "heading" => __("Number of items on notebook", 'legenda-core'),
          "param_name" => "notebook",
          "dependency" => array('element' => "slider", 'value' => array('yes')),
        ),
        array(
          "type" => "textfield",
          "heading" => __("Number of items on tablet", 'legenda-core'),
          "param_name" => "tablet_portrait",
          "dependency" => array('element' => "slider", 'value' => array('yes')),
        ),
        array(
          "type" => "textfield",
          "heading" => __("Number of items on phones", 'legenda-core'),
          "param_name" => "mobile",
          "dependency" => array('element' => "slider", 'value' => array('yes')),
        ),
        array(
            'type' => 'checkbox',
            'heading' => esc_html__( 'Without spacing', 'legenda-core' ),
            'param_name' => 'spacing',
            'value' => array(
                __( 'Yes', 'legenda-core' ) => 1,
            ),
        ),
      ), array() )

    );

    vc_map($et_instagram);

}