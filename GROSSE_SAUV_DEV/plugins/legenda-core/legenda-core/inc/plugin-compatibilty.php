<?php defined('ABSPATH') || exit( 'No direct script access allowed' );

/**
 * Check plugin compatibility
 *
 * @since   1.2.1
 * @version 1.0.1
*/

    function legenda_plugin_compatible() {

            $plugins = array();

            include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

            if ( is_plugin_active( 'woopress-core/woopress-core.php' ) ) {
                $plugins[] = 'Woopress core';
            } 

            if ( is_plugin_active( 'royal-core/royal-core.php' ) ) {
                $plugins[] = 'Royal core';
            } 

            if ( is_plugin_active( 'classico-core-plugin/post-types.php' ) ) {
                $plugins[] = 'Classico core';
            } 

            if ( is_plugin_active( 'et-core-plugin/et-core-plugin.php' ) ) {
                $plugins[] = 'XStore core';
            } 

            if ( count( $plugins ) ) {
                $html = '<div class="error">';
                    $html .= '<p>'.esc_html__('Attention!', 'legenda-core') .'</p>';
                    $html .= '<p>';
                        $html .= '<strong>';
                            $html .= '<span>'.esc_html__('Legenda Core plugin conflicts with the following plugins:', 'legenda-core') . '</span>';
                            $_i = 0;
                            foreach ( $plugins as $value ) {
                                $_i++;
                                if ( $_i == count( $plugins ) ) {
                                    $html .= '<span>' . $value . '</span>.';
                                } else {
                                    $html .= '<span>' . $value . '</span>, ';
                                }
                            }
                        $html .= '</strong>';
                    $html .= '</p>';
                    $html .= '<p>' . esc_html__('Keep enabled only plugin that comes bundled with activated theme.', 'legenda-core') .'</p>';
                $html .= '</div>';

                add_filter( 'admin_notices', function($msg) use ($html){ echo $html; } );

                return false;
            }

        return true;

    }

    add_action( 'after_setup_theme', 'legenda_theme_old' );

    function legenda_theme_old() {
        $theme = wp_get_theme(get_option('template'));
        $version = $theme['Version'];

        if ( version_compare( '3.10', $version, '>' ) ) {
            return true;
        }
        return false;
    }


    function legenda_theme_activation_text() {
        echo '<p class="alert-info">' . esc_html__('Please, activate Legenda theme to use Legenda Core plugin', 'legenda-core') . '</p><br/>';
    }
?>