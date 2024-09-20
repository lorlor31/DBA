<?php

class WC_SAG_Shortcode_Iframe {

    /** @var WC_SAG_Settings Plugin settings */
    protected $settings;

    /**
     * Constructor
     */
    public function __construct( $settings ) {
        $this->settings = $settings;

        add_shortcode( 'wcsag_iframe', array( $this, 'render_shortcode' ) );
    }

    /**
     * Render shortcode content
     */
    public function render_shortcode( $atts = array(), $content = null ) {
        $atts = shortcode_atts( array(
            'width'  => '100%',
            'height' => 235,
            'format' => 'horizontal'
        ), $atts );

        // Display SAG iframe
        $iframe_url = $this->settings->get( 'sag_domain' ) . '/wp-content/plugins/ag-core/' . ( $atts[ 'format' ] == 'vertical' ? 'widget2.php' : 'widgetFooter.php' ) . '?id=' . $this->settings->get( 'site_id' );

        echo '<iframe width="' . esc_attr( $atts[ 'width' ] ) . '" height="' . esc_attr( $atts[ 'height' ] ) . '" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="' . esc_url( $iframe_url )  . '"></iframe>';
    }
}