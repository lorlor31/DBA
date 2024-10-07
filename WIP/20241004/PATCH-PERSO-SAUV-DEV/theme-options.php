<?php
    /**
     * ReduxFramework Sample Config File
     * For full documentation, please visit: http://docs.reduxframework.com/
     */

    if ( ! class_exists( 'Redux' ) ) {
        global $legenda_redux_demo;

        $base_options = get_template_directory() . '/framework/base-options.php';
        $base_options = require_once $base_options;
        $base_options = json_decode( $base_options, true );
        $legenda_redux_demo   = $base_options;
        return;
    }


    // This is your option name where all the Redux data is stored.
    $opt_name = "legenda_redux_demo";

    // This line is only for altering the demo. Can be easily removed.
    $opt_name = apply_filters( 'legenda_redux_demo/opt_name', $opt_name );

    /*
     *
     * --> Used within different fields. Simply examples. Search for ACTUAL DECLARATION for field examples
     *
     */

    $sampleHTML = '';
    if ( file_exists( dirname( __FILE__ ) . '/info-html.html' ) ) {
        Redux_Functions::initWpFilesystem();

        global $wp_filesystem;

        $sampleHTML = $wp_filesystem->get_contents( dirname( __FILE__ ) . '/info-html.html' );
    }

    // Background Patterns Reader
    $sample_patterns_path = ReduxFramework::$_dir . '../sample/patterns/';
    $sample_patterns_url  = ReduxFramework::$_url . '../sample/patterns/';
    $sample_patterns      = array();
    
    if ( is_dir( $sample_patterns_path ) ) {

        if ( $sample_patterns_dir = opendir( $sample_patterns_path ) ) {
            $sample_patterns = array();

            while ( ( $sample_patterns_file = readdir( $sample_patterns_dir ) ) !== false ) {

                if ( stristr( $sample_patterns_file, '.png' ) !== false || stristr( $sample_patterns_file, '.jpg' ) !== false ) {
                    $name              = explode( '.', $sample_patterns_file );
                    $name              = str_replace( '.' . end( $name ), '', $sample_patterns_file );
                    $sample_patterns[] = array(
                        'alt' => $name,
                        'img' => $sample_patterns_url . $sample_patterns_file
                    );
                }
            }
        }
    }

    /**
     * ---> SET ARGUMENTS
     * All the possible arguments for Redux.
     * For full documentation on arguments, please refer to: https://github.com/ReduxFramework/ReduxFramework/wiki/Arguments
     * */

    $theme = wp_get_theme(); // For use with some settings. Not necessary.

    if ( is_child_theme() ) {

      $version = wp_get_theme( 'legenda' )->version . ' (child  ' . $theme->get( 'Version' ) . ')';
    
    } else {

      $version =$theme->get( 'Version' );

    }

    $args = array(
        // TYPICAL -> Change these values as you need/desire
        'opt_name'             => $opt_name,
        // This is where your data is stored in the database and also becomes your global variable name.
        'display_name'         => $theme->get( 'Name' ),
        // Name that appears at the top of your panel
        'display_version'      => 'v.' . $version,
        // Version that appears at the top of your panel
        'menu_type'            => 'menu',
        //Specify if the admin menu should appear or not. Options: menu or submenu (Under appearance only)
        'allow_sub_menu'       => false,
        // Show the sections below the admin menu item or not
        'menu_title'           => __( 'Theme Options', 'legenda' ),
        'page_title'           => __( 'Theme Options', 'legenda' ),
        // You will need to generate a Google API key to use this feature.
        // Please visit: https://developers.google.com/fonts/docs/developer_api#Auth
        'google_api_key'       => '',
        // Set it you want google fonts to update weekly. A google_api_key value is required.
        'google_update_weekly' => false,
        // Must be defined to add google fonts to the typography module
        'async_typography'     => false,
        // Use a asynchronous font on the front end or font string
        //'disable_google_fonts_link' => true,                    // Disable this in case you want to create your own google fonts loader
        'admin_bar'            => true,
        // Show the panel pages on the admin bar
        'admin_bar_icon'       => 'dashicons-admin-generic',
        // Choose an icon for the admin bar menu
        'admin_bar_priority'   => 50,
        // Choose an priority for the admin bar menu
        'global_variable'      => '',
        // Set a different name for your global variable other than the opt_name
        'dev_mode'             => false,
        // Show the time the page took to load, etc
        'update_notice'        => true,
        // If dev_mode is enabled, will notify developer of updated versions available in the GitHub Repo
        'customizer'           => true,
        // Enable basic customizer support
        //'open_expanded'     => true,                    // Allow you to start the panel in an expanded way initially.
        //'disable_save_warn' => true,                    // Disable the save warning when a user changes a field

        // OPTIONAL -> Give you extra features
        'page_priority'        => 70,
        // Order where the menu appears in the admin area. If there is any conflict, something will not show. Warning.
        'page_parent'          => 'themes.php',
        // For a full list of options, visit: http://codex.wordpress.org/Function_Reference/add_submenu_page#Parameters
        'page_permissions'     => 'manage_options',
        // Permissions needed to access the options panel.
        'menu_icon'            => ETHEME_CODE_IMAGES_URL . '/etheme.png',
        // Specify a custom URL to an icon
        'last_tab'             => '',
        // Force your panel to always open to a specific tab (by id)
        'page_icon'            => 'icon-themes',
        // Icon displayed in the admin panel next to your menu_title
        'page_slug'            => 'LegendaThemeOptions',
        // Page slug used to denote the panel, will be based off page title then menu title then opt_name if not provided
        'save_defaults'        => true,
        // On load save the defaults to DB before user clicks save or not
        'default_show'         => false,
        // If true, shows the default value next to each field that is not the default value.
        'default_mark'         => '',
        // What to print by the field's title if the value shown is default. Suggested: *
        'show_import_export'   => true,
        // Shows the Import/Export panel when not used as a field.

        // CAREFUL -> These options are for advanced use only
        'transient_time'       => 60 * MINUTE_IN_SECONDS,
        'output'               => true,
        // Global shut-off for dynamic CSS output by the framework. Will also disable google fonts output
        'output_tag'           => true,

        // Allows dynamic CSS to be generated for customizer and google fonts, but stops the dynamic CSS from going to the head
        'footer_credit'     => '8theme',                   // Disable the footer credit of Redux. Please leave if you can help it.

        'templates_path' => trailingslashit(PARENT_DIR) . '/framework/thirdparty/options-framework/et-templates/',

        // FUTURE -> Not in use yet, but reserved or partially implemented. Use at your own risk.
        'database'             => '',
        // possible: options, theme_mods, theme_mods_expanded, transient. Not fully functional, warning!
        'use_cdn'              => true,
        // If you prefer not to use the CDN for Select2, Ace Editor, and others, you may download the Redux Vendor Support plugin yourself and run locally or embed it in your code.

        'show_options_object'  => false,
    );

    Redux::setArgs( $opt_name, $args );

    /*
     * ---> END ARGUMENTS
     */

    /*
     *
     * ---> START SECTIONS
     *
     */

    /*

        As of Redux 3.5+, there is an extensive API. This API can be used in a mix/match mode allowing for


     */

    // ! Get standard redux font list
    $std_fonts = array(
        "Arial, Helvetica, sans-serif"                         => "Arial, Helvetica, sans-serif",
        "'Arial Black', Gadget, sans-serif"                    => "'Arial Black', Gadget, sans-serif",
        "'Bookman Old Style', serif"                           => "'Bookman Old Style', serif",
        "'Comic Sans MS', cursive"                             => "'Comic Sans MS', cursive",
        "Courier, monospace"                                   => "Courier, monospace",
        "Garamond, serif"                                      => "Garamond, serif",
        "Georgia, serif"                                       => "Georgia, serif",
        "Impact, Charcoal, sans-serif"                         => "Impact, Charcoal, sans-serif",
        "'Lucida Console', Monaco, monospace"                  => "'Lucida Console', Monaco, monospace",
        "'Lucida Sans Unicode', 'Lucida Grande', sans-serif"   => "'Lucida Sans Unicode', 'Lucida Grande', sans-serif",
        "'MS Sans Serif', Geneva, sans-serif"                  => "'MS Sans Serif', Geneva, sans-serif",
        "'MS Serif', 'New York', sans-serif"                   => "'MS Serif', 'New York', sans-serif",
        "'Palatino Linotype', 'Book Antiqua', Palatino, serif" => "'Palatino Linotype', 'Book Antiqua', Palatino, serif",
        "Tahoma,Geneva, sans-serif"                            => "Tahoma, Geneva, sans-serif",
        "'Times New Roman', Times,serif"                       => "'Times New Roman', Times, serif",
        "'Trebuchet MS', Helvetica, sans-serif"                => "'Trebuchet MS', Helvetica, sans-serif",
        "Verdana, Geneva, sans-serif"                          => "Verdana, Geneva, sans-serif",
    );

    // demo_data - take a look

    // ! Get custom fonts list
    $fonts = get_option( 'etheme-fonts', false );

    if ( $fonts ) {
        $valid_fonts = array();
        foreach ( $fonts as $value ) {
            $valid_fonts[$value['name']] = $value['name'];
        }
        $fonts_list = array_merge( $std_fonts, $valid_fonts );
    } else {
        $fonts_list = '';
    }
    
    // general section
    Redux::setSection( $opt_name, array(
        'title'  => esc_html__( 'General', 'legenda' ),
        'id'     => 'general',
        'icon'   => 'dashicons dashicons-admin-multisite',
        'fields' => array (
            array (
                'id'       => 'main_layout',
                'type'     => 'select',
                'operator' => 'and',
                'title'    => esc_html__( 'Site Layout', 'legenda' ),
                'options'  => array (
                    'wide'     => esc_html__( 'Wide', 'legenda' ),
                    'boxed'    => esc_html__( 'Boxed', 'legenda' ),
                ),
                'default'  => 'wide'
            ),
            array (
                'id' => 'to_top',
                'type' => 'switch',
                'title' => esc_html__( '"Back To Top" button', 'legenda' ),
                'default' => true,
            ),
            array (
                'id' => 'fixed_nav',
                'type' => 'switch',
                'title' => esc_html__( 'Fixed navigation', 'legenda' ),
                'default' => true,
            ),

            array (
                'id' => 'mobile_loader',
                'type' => 'switch',
                'title' => esc_html__( 'Show loader on mobile', 'legenda' ),
                'default' => true,
            ),
            array(
                'id'       => 'google_code',
                'type'     => 'textarea',
                'rows'     => 5,
                'title'    => esc_html__('Google Analytics Code', 'legenda'),
                'default'  => ''
            ),
            array (
                'id' => 'default_og_tags',
                'type' => 'switch',
                'title' => esc_html__( 'Disable default Open Graphs meta tags', 'legenda' ),
                'default' => false,
            ),
            array(
                'id'       => 'enable_hatom_meta',
                'type'     => 'switch',
                'title'    => esc_html__( 'Hatom meta in post content', 'legenda' ),
                'default'  => false,
            ),

        ),
    ) );

    // modules section 
    Redux::setSection( $opt_name, array(
        'title'  => esc_html__( 'Modules', 'legenda' ),
        'id'     => 'modules',
        'icon'   => 'dashicons dashicons-clipboard',
        'fields' => array (
            array (
                'id' => 'enable_portfolio',
                'type' => 'switch',
                'title' => esc_html__( 'Portfolio', 'legenda' ),
                'default' => true,
            ),
            array (
                'id' => 'enable_brands',
                'type' => 'switch',
                'title' => esc_html__( 'Brands', 'legenda' ),
                'default' => true,
            ),
            array (
                'id' => 'enable_testimonials',
                'type' => 'switch',
                'title' => esc_html__( 'Testimonials', 'legenda' ),
                'default' => true,
            ),
            array (
                'id' => 'enable_static_blocks',
                'type' => 'switch',
                'title' => esc_html__( 'Static Blocks', 'legenda' ),
                'default' => true,
            ),
        ),
    ) );

    Redux::setSection( $opt_name, array(
        'title'  => esc_html__( 'Color Scheme', 'legenda' ),
        'id'     => 'color_scheme',
        'icon'   => 'dashicons dashicons-admin-customizer',
        'fields' => array (
            array (
                'id'       => 'main_color_scheme',
                'type'     => 'select',
                'title'    => esc_html__( 'Main color scheme', 'legenda' ),
                'default'  => 'light',
                'options'  => array (
                    'light'      => esc_html__( 'Light', 'legenda' ),
                    'dark' => esc_html__( 'Dark', 'legenda' ),
                ),
            ),
            array (
                'id' => 'activecol',
                'title' => __('Main Color', 'legenda'),
                'type' => 'color',
                'default' => '#ed1c2e',
                'transparent' => false
            ),
            array (
                'id' => 'pricecolor',
                'title' => __('Price Color', 'legenda'),
                'type' => 'color',
                'default' => '#EE3B3B',
                'transparent' => false
            ),
            array (
                'id'       => 'background_img',
                'type'     => 'background',
                'title'    => esc_html__( 'Site Background', 'legenda' ),
            ),
            array (
                'id'       => 'header_color_scheme',
                'type'     => 'select',
                'title'    => esc_html__( 'Header color scheme (Only for transparent header type)', 'legenda' ),
                'default'  => 'light',
                'options'  => array (
                    'light'      => esc_html__( 'Light', 'legenda' ),
                    'dark' => esc_html__( 'Dark', 'legenda' ),
                ),
            ),
            array (
                'id'       => 'fixed_header_color',
                'type'     => 'select',
                'title'    => esc_html__( 'Fixed header color', 'legenda' ),
                'default'  => 'dark',
                'options'  => array (
                    'light'      => esc_html__( 'Light', 'legenda' ),
                    'dark' => esc_html__( 'Dark', 'legenda' ),
                ),
            ),
            array (
                'id' => 'fixed_bg',
                'title' => esc_html__('Fixed header background color', 'legenda'),
                'type' => 'color',
                'default' => '',
                'transparent' => false
            ),
            array (
                'id'       => 'breadcrumb_bg',
                'type'     => 'background',
                'title'    => esc_html__( 'Breadcrumbs background', 'legenda' ),
            ),
            array (
                'id' => 'mobile_menu_bg',
                'title' => esc_html__('Mobile menu background', 'legenda'),
                'type' => 'color',
                'default' => '#151515',
                'transparent' => false
            ),
            array (
                'id' => 'mobile_menu_br',
                'title' => esc_html__('Mobile menu borders', 'legenda'),
                'type' => 'color',
                'default' => '#222222',
                'transparent' => false
            ),
        ),
    ) );

    // typography section
    Redux::setSection( $opt_name, array(
        'title'  => esc_html__( 'Typography', 'legenda' ),
        'id'     => 'typography',
        'icon'   => 'dashicons dashicons-editor-spellcheck',
        'fields' => array (
            array (
                'id' => 'defaultfonts',
                'type' => 'switch',
                'title' => esc_html__( 'Default fonts', 'legenda' ),
                'default' => true,
            ),
            array (
                'id'             => 'mainfont',
                'type'           => 'typography',
                'fonts'          => $fonts_list,
                'title'          => esc_html__( 'Main Font', 'legenda' ),
                'text-align'     => false,
                'text-transform' => true,
                'letter-spacing' => true,
            ),
            array (
                'id'             => 'sfont',
                'type'           => 'typography',
                'fonts'          => $fonts_list,
                'title'          => esc_html__( 'Body Font', 'legenda' ),
                'text-align'     => false,
                'text-transform' => true,
                'letter-spacing' => true,
            ),
            array (
                'id'             => 'header_menu_font',
                'type'           => 'typography',
                'fonts'          => $fonts_list,
                'title'          => esc_html__( 'Main Menu Font', 'legenda' ),
                'text-align'     => false,
                'text-transform' => true,
                'letter-spacing' => true,
            ),
            array (
                'id'             => 'mobile_menu_font',
                'type'           => 'typography',
                'fonts'          => $fonts_list,
                'title'          => esc_html__( 'Mobile menu font', 'legenda' ),
                'text-align'     => false,
                'text-transform' => true,
                'letter-spacing' => true,
            ),
            array (
                'id'             => 'mobile_menu_headings_font',
                'type'           => 'typography',
                'fonts'          => $fonts_list,
                'title'          => esc_html__( 'Mobile headings font', 'legenda' ),
                'text-align'     => false,
                'text-transform' => true,
                'letter-spacing' => true,
            ),

            array (
                'id'             => 'h1',
                'type'           => 'typography',
                'fonts'          => $fonts_list,
                'title'          => esc_html__( 'H1', 'legenda' ),
                'text-align'     => false,
                'text-transform' => true,
                'letter-spacing' => true,
            ),
            array (
                'id'             => 'h2',
                'type'           => 'typography',
                'fonts'          => $fonts_list,
                'title'          => esc_html__( 'H2', 'legenda' ),
                'text-align'     => false,
                'text-transform' => true,
                'letter-spacing' => true,
            ),
            array (
                'id'             => 'h3',
                'type'           => 'typography',
                'fonts'          => $fonts_list,
                'title'          => esc_html__( 'H3', 'legenda' ),
                'text-align'     => false,
                'text-transform' => true,
                'letter-spacing' => true,
            ),
            array (
                'id'             => 'h4',
                'type'           => 'typography',
                'fonts'          => $fonts_list,
                'title'          => esc_html__( 'H4', 'legenda' ),
                'text-align'     => false,
                'text-transform' => true,
                'letter-spacing' => true,
            ),
            array (
                'id'             => 'h5',
                'type'           => 'typography',
                'fonts'          => $fonts_list,
                'title'          => esc_html__( 'H5', 'legenda' ),
                'text-align'     => false,
                'text-transform' => true,
                'letter-spacing' => true,
            ),
            array (
                'id'             => 'h6',
                'type'           => 'typography',
                'fonts'          => $fonts_list,
                'title'          => esc_html__( 'H6', 'legenda' ),
                'text-align'     => false,
                'text-transform' => true,
                'letter-spacing' => true,
            ),
            // array(
            //     'id'          => 'custom_fonts',
            //     'label'       => 'Custom Fonts',
            //     'default'     => '',
            //     'type'        => 'custom_fonts',
            //     'section'     => 'typography',
            // ),
        ),
    ) );

    // custom fonts section 
    Redux::setSection( $opt_name, array(
        'title'      => esc_html__( 'Upload custom font', 'legenda' ),
        'id'         => 'fonts-uploader',
        'subsection' => true,
        'desc'       => esc_html__( 'Upload the custom font to use throughout the site. For full browser support it\'s recommended to upload all formats. You can upload as many custom fonts as you need. The font you upload here will be available in the font-family drop-downs at the Typography options.', 'legenda' ),
        'icon'       => 'dashicons dashicons-upload',
        'class'      => 'et_fonts-section',
        'fields'     => array (
            array(
                'id'    => 'fonts-uploader',
                'type'  => 'fonts_uploader',
                'title' => false
            ),
        )
    ));

    // header section
    Redux::setSection( $opt_name, array(
        'title'  => esc_html__( 'Header', 'legenda' ),
        'id'     => 'header',
        'icon'   => 'dashicons dashicons-welcome-widgets-menus',
        'fields' => array (
            array (
                'id'       => 'top_bar',
                'type'     => 'switch',
                'title'    => esc_html__( 'Enable top bar', 'legenda' ),
                'default'  => true,
            ),
            array (
                'id'       => 'top_panel',
                'type'     => 'switch',
                'title'    => esc_html__( 'Enable hidden top panel', 'legenda' ),
                'default'  => true,
            ),
            array (
                'id'      => 'header_type',
                'type'    => 'image_select',
                'title'   => esc_html__( 'Header Type', 'legenda' ),
                'options' => array (
                    1 => array (
                        'alt' => esc_html__( 'Default', 'legenda' ),
                        'img'   => ETHEME_CODE_URL . '/images/header_v1.jpg',
                    ),
                    2 => array (
                        'alt' => esc_html__( 'Variant 2', 'legenda' ),
                        'img'   => ETHEME_CODE_URL . '/images/header_v2.jpg',
                    ),
                    3 => array (
                        'alt' => esc_html__( 'Variant 3', 'legenda' ),
                        'img'   => ETHEME_CODE_URL . '/images/header_v3.jpg',
                    ),
                    4 => array (
                        'alt' => esc_html__( 'Variant 4', 'legenda' ),
                        'img'   => ETHEME_CODE_URL . '/images/header_v4.jpg',
                    ),
                    5 => array (
                        'alt' => esc_html__( 'Variant 5', 'legenda' ),
                        'img'   => ETHEME_CODE_URL . '/images/header_v5.jpg',
                    ),
                    6 => array (
                        'alt' => esc_html__( 'Variant 6', 'legenda' ),
                        'img'   => ETHEME_CODE_URL . '/images/header_v6.jpg',
                    ),
                    7 => array (
                        'alt' => esc_html__( 'Default', 'legenda' ),
                        'img'   => ETHEME_CODE_URL . '/images/header_v7.jpg',
                    ),
                    9 => array (
                        'alt' => esc_html__( 'Transparent', 'legenda' ),
                        'img'   => ETHEME_CODE_URL . '/images/header_v9.jpg',
                    ),
                    8 => array (
                        'alt' => esc_html__( 'Vertical', 'legenda' ),
                        'img'   => ETHEME_CODE_URL . '/images/header_v8.jpg',
                    ),
                ),
                'default' => 1
            ),
            array (
                'id'       => 'languages_area',
                'type'     => 'switch',
                'title'    => esc_html__( 'Enable languages area', 'legenda' ),
                'default'  => true,
            ),
            array (
                'id'       => 'right_panel',
                'type'     => 'switch',
                'title'    => esc_html__( 'Use right side panel', 'legenda' ),
                'default'  => false,
            ),
            array (
                'id'    => 'logo',
                'type'  => 'media',
                'title' => esc_html__( 'Logo image', 'legenda' ),
                'subtitle' => esc_html__( 'Upload image: png, jpg or gif file.', 'legenda' ),
            ),
            array (
                'id'    => 'logo-fixed',
                'type'  => 'media',
                'title' => esc_html__( 'Logo image for fixed header', 'legenda' ),
                'subtitle' => esc_html__( 'Upload image: png, jpg or gif file.', 'legenda' ),
            ),
            array (
                'id'       => 'top_links',
                'type'     => 'switch',
                'title'    => esc_html__( 'Enable top links (Register | Sign In)', 'legenda' ),
                'default'  => true,
            ),
            array (
                'id'       => 'cart_widget',
                'type'     => 'switch',
                'title'    => esc_html__( 'Enable cart widget', 'legenda' ),
                'default'  => true,
            ),
            array (
                'id'       => 'search_form',
                'type'     => 'switch',
                'title'    => esc_html__( 'Enable search form in header', 'legenda' ),
                'default'  => true,
            ),
            array (
                'id'       => 'wishlist_link',
                'type'     => 'switch',
                'title'    => esc_html__( 'Show wishlist link', 'legenda' ),
                'default'  => true,
            ),
            array (
                'id'       => 'breadcrumb_type',
                'type'     => 'select',
                'title'    => esc_html__( 'Breadcrumbs Type', 'legenda' ),
                'options'  => array (
                        ''   => esc_html__( 'Default', 'legenda' ),
                        'variant2'  => esc_html__( 'Wide block', 'legenda' ),
                        'without-title' => esc_html__( 'Without title', 'legenda' ),
                    ),
                'default'  => '',
            ),
        ),
    ) );

    // footer section
    Redux::setSection( $opt_name, array(
        'title'  => esc_html__( 'Footer', 'legenda' ),
        'id'     => 'footer',
        'icon'   => 'dashicons dashicons-tagcloud',
        'fields' => array (
            array (
                'id'      => 'footer_type',
                'type'    => 'image_select',
                'title'   => esc_html__( 'Footer Type', 'legenda' ),
                'options' => array (
                    1 => array (
                        'alt' => esc_html__( 'Default', 'legenda' ),
                        'img'   => ETHEME_CODE_URL . '/images/footer_v1.jpg',
                    ),
                    2 => array (
                        'alt' => esc_html__( 'Variant 2', 'legenda' ),
                        'img'   => ETHEME_CODE_URL . '/images/footer_v2.jpg',
                    ),
                    3 => array (
                        'alt' => esc_html__( 'Variant 3', 'legenda' ),
                        'img'   => ETHEME_CODE_URL . '/images/footer_v3.jpg',
                    ),
                ),
                'default' => 1
            ),
            array (
                'id'       => 'footer_demo',
                'type'     => 'switch',
                'title'    => esc_html__( 'Show footer demo blocks', 'legenda' ),
                'subtitle' => esc_html__( 'Will be shown if footer widget areas are empty', 'legenda' ),
                'default'  => false,
            ),
        ),
    ) );

    // shop section
    Redux::setSection( $opt_name, array(
        'title'  => esc_html__( 'Shop', 'legenda' ),
        'id'     => 'shop',
        'icon'   => 'dashicons dashicons-cart',
        'fields' => array (
            array (
                'id'       => 'just_catalog',
                'type'     => 'switch',
                'title'    => esc_html__( 'Just Catalog', 'legenda' ),
                'subtitle' => esc_html__( 'Disable "Add To Cart" button and shopping cart', 'legenda' ),
                'default'  => false,
            ),
            array (
                'id'       => 'checkout_page',
                'type'     => 'select',
                'title'    => esc_html__( 'Checkout page', 'legenda' ),
                'subtitle' => esc_html__( 'Choose the design type of the tabs.', 'legenda' ),
                'default'  => 'stepbystep',
                'options'  => array (
                    'stepbystep' => esc_html__( 'Step By Step', 'legenda' ),
                    'default'     => esc_html__( 'Default', 'legenda' ),
                    'quick'    => esc_html__( 'Quick Checkout', 'legenda' ),
                ),
            ),
            array (
                'id'       => 'ajax_filter',
                'type'     => 'switch',
                'title'    => esc_html__( 'Enable Ajax Filter', 'legenda' ),
                'default'  => true,
            ),
            array (
                'id'       => 'cats_accordion',
                'type'     => 'switch',
                'title'    => esc_html__( 'Enable Navigation Accordion', 'legenda' ),
                'default'  => true,
            ),
            array (
                'id'       => 'out_of_label',
                'type'     => 'switch',
                'title'    => esc_html__( 'Enable "Out Of Stock" label', 'legenda' ),
                'default'  => true,
            ),
            array (
                'id'       => 'new_icon',
                'type'     => 'switch',
                'title'    => esc_html__( 'Enable "NEW" icon', 'legenda' ),
                'default'  => true,
            ),
            array(
                'id'            => 'new_icon_width',
                'type'          => 'slider',
                'title'         => esc_html__('"NEW" Icon width', 'legenda'),
                'default'       => 48,
                'min'           => 0,
                'step'          => 1,
                'max'           => 300,
                'display_value' => 'text',
            ),
            array(
                'id'            => 'new_icon_height',
                'type'          => 'slider',
                'title'         => esc_html__('"NEW" Icon height', 'legenda'),
                'default'       => 48,
                'min'           => 0,
                'step'          => 1,
                'max'           => 300,
                'display_value' => 'text',
            ),
            array (
                'id' => 'new_icon_url',
                'type' => 'media',
                'title' => esc_html__( '"NEW" Icon Image', 'legenda' ),
                'subtitle' => esc_html__( 'Upload image: png, jpg or gif file', 'legenda' ),
            ),

            array (
                'id'       => 'sale_icon',
                'type'     => 'switch',
                'title'    => esc_html__( 'Enable "SALE" icon', 'legenda' ),
                'default'  => true,
            ),
            array(
                'id'            => 'sale_icon_width',
                'type'          => 'slider',
                'title'         => esc_html__('"SALE" Icon width', 'legenda'),
                'default'       => 48,
                'min'           => 0,
                'step'          => 1,
                'max'           => 300,
                'display_value' => 'text',
            ),
            array(
                'id'            => 'sale_icon_height',
                'type'          => 'slider',
                'title'         => esc_html__('"SALE" Icon height', 'legenda'),
                'default'       => 48,
                'min'           => 0,
                'step'          => 1,
                'max'           => 300,
                'display_value' => 'text',
            ),
            array (
                'id' => 'sale_icon_url',
                'type' => 'media',
                'title' => esc_html__( '"SALE" Icon Image', 'legenda' ),
                'subtitle' => esc_html__( 'Upload image: png, jpg or gif file', 'legenda' ),
            ),
            array (
                'id'       => 'product_bage_banner',
                'type'     => 'editor',
                'title'    => esc_html__( 'Product Page Banner', 'legenda' ),
                'subtitle' => esc_html__('Upload image: png, jpg or gif file', 'legenda'),
                'default'     => '<p><img src="'.get_template_directory_uri().'/images/assets/shop-banner.jpg" /></p>',
            ),
            array (
                'id'       => 'empty_cart_content',
                'type'     => 'editor',
                'title'    => esc_html__( 'Text for empty cart', 'legenda' ),
                'default'     => '<h2>Your cart is currently empty</h2>
                    <p>You have not added any items in your shopping cart</p>',
            ),
            array (
                'id'       => 'empty_category_content',
                'type'     => 'editor',
                'title'    => esc_html__( 'Text for empty category', 'legenda' ),
                'default'     => '
                    <h2>No products were found</h2>
                ',
            ),
        )
    ) );

    // product page section
    Redux::setSection( $opt_name, array(
        'title'  => esc_html__( 'Products Page Layout', 'legenda' ),
        'id'     => 'product_grid',
        'icon'   => 'dashicons dashicons-screenoptions',
        'fields' => array (
            array(
                'id'          => 'view_mode',
                'title'       => esc_html__('Products view mode', 'legenda'),
                'type'        => 'select',
                'default'     => 'grid_list',
                'options'     => array(
                    'grid_list' => esc_html__('Grid/List', 'legenda'),
                    'list_grid' => esc_html__('List/Grid', 'legenda'),
                    'grid' => esc_html__('Only Grid', 'legenda'),
                    'list' => esc_html__('Only List', 'legenda')
                )
            ),

            array(
                'id'            => 'products_per_page',
                'type'          => 'slider',
                'title'         => esc_html__('Products per page', 'legenda'),
                'default'       => 12,
                'min'           => 1,
                'step'          => 1,
                'max'           => 250,
                'display_value' => 'text',
            ),
            array (
                'id'       => 'grid_sidebar',
                'type'     => 'image_select',
                'title'    => esc_html__( 'Layout', 'legenda' ),
                'options'  => array (
                    'left' => array (
                        'alt' => esc_html__( 'Left Sidebar', 'legenda' ),
                        'img' => ETHEME_CODE_URL . '/images/layout/left-sidebar.png',
                    ),
                    'right' => array (
                        'alt' => esc_html__( 'Right Sidebar', 'legenda' ),
                        'img' => ETHEME_CODE_URL . '/images/layout/right-sidebar.png',
                    ),
                    'without' => array (
                        'alt' => esc_html__( 'full width', 'legenda' ),
                        'img' => ETHEME_CODE_URL . '/images/layout/full-width.png',
                    ),
                ),
                'default'  => 'left'
            ),
            array (
                'id'       => 'sidebar_position_mobile',
                'type'     => 'select',
                'title'    => esc_html__( 'Sidebar position for mobile', 'legenda' ),
                'options'  => array (
                    'above'    => esc_html__( 'Above content', 'legenda' ),
                    'under' => esc_html__( 'Under content', 'legenda' ),
                ),
                'default'  => 'under',
            ),
            array (
                'id'       => 'category_description_position',
                'type'     => 'select',
                'title'    => esc_html__( 'Category description position', 'legenda' ),
                'options'  => array (
                    'above'    => esc_html__( 'Above products', 'legenda' ),
                    'under' => esc_html__( 'Under products', 'legenda' ),
                ),
                'default'  => 'under',
            ),
            array (
                'id'       => 'product_img_hover',
                'type'     => 'select',
                'title'    => esc_html__( 'Product Image Hover', 'legenda' ),
                'options'  => array (
                    'disable'    => esc_html__( 'Disable', 'legenda' ),
                    'description' => esc_html__( 'Description', 'legenda' ),
                    'swap'    => esc_html__( 'Swap', 'legenda' ),
                    'tooltip'    => esc_html__( 'Tooltip', 'legenda' ),
                    'slider'    => esc_html__( 'Images Slider', 'legenda' ),
                ),
                'default'  => 'slider',
            ),
            array(
                'id'            => 'descr_length',
                'type'          => 'slider',
                'title'         => esc_html__('Number of words for description (hover effect)', 'legenda'),
                'default'       => 30,
                'min'           => 1,
                'step'          => 1,
                'max'           => 300,
                'display_value' => 'text',
            ),
            array (
                'id'       => 'product_page_productname',
                'type'     => 'switch',
                'title'    => esc_html__( 'Show product name', 'legenda' ),
                'default'  => true,
            ),
            array (
                'id'       => 'product_page_cats',
                'type'     => 'switch',
                'title'    => esc_html__( 'Show product categories', 'legenda' ),
                'default'  => true,
            ),
            array (
                'id'       => 'product_page_price',
                'type'     => 'switch',
                'title'    => esc_html__( 'Show Price', 'legenda' ),
                'default'  => true,
            ),
            array (
                'id'       => 'product_page_addtocart',
                'type'     => 'switch',
                'title'    => esc_html__( 'Show "Add to cart" button', 'legenda' ),
                'default'  => true,
            ),
        )
    ) );

    // single product section 
    Redux::setSection( $opt_name, array(
        'title'  => esc_html__( 'Single Product Page', 'legenda' ),
        'id'     => 'single_product',
        'icon'   => 'dashicons dashicons-id',
        'fields' => array (
            array (
                'id' => 'single_sidebar',
                'type' => 'image_select',
                'title' => __( 'Sidebar position', 'legenda' ),
                'options' => array (
                    'no_sidebar' => array (
                        'alt' => __( 'Without Sidebar', 'legenda' ),
                        'img' => ETHEME_CODE_URL . '/images/layout/full-width.png',
                    ),
                    'left' => array (
                        'alt' => __( 'Left Sidebar', 'legenda' ),
                        'img' => ETHEME_CODE_URL . '/images/layout/left-sidebar.png',
                    ),
                    'right' => array (
                        'alt' => __( 'Right Sidebar', 'legenda' ),
                        'img' => ETHEME_CODE_URL . '/images/layout/right-sidebar.png',
                    ),
                ),
                'default' => 'right'
            ),
            array (
                'id'       => 'upsell_location',
                'type'     => 'select',
                'title'    => esc_html__( 'Location of upsell products', 'legenda' ),
                'options'  => array (
                    'sidebar'    => esc_html__( 'Sidebar', 'legenda' ),
                    'after_content' => esc_html__( 'After content', 'legenda' ),
                ),
                'default'  => 'sidebar',
            ),
            array (
                'id'       => 'show_related',
                'type'     => 'switch',
                'title'    => esc_html__( 'Show related products', 'legenda' ),
                'default'  => true,
            ),
            array (
                'id'       => 'hide_out_of_stock',
                'type'     => 'switch',
                'title'    => esc_html__( 'Hide out of stock items from related products', 'legenda' ),
                'default'  => true,
            ),
            array (
                'id'       => 'ajax_addtocart',
                'type'     => 'switch',
                'title'    => esc_html__( 'Ajax "Add To Cart" (for simple products only)', 'legenda' ),
                'default'  => true,
            ),
            array (
                'id'       => 'show_name_on_single',
                'type'     => 'switch',
                'title'    => esc_html__( 'Show Product name', 'legenda' ),
                'default'  => false,
            ),
            array (
                'id'       => 'zoom_effect',
                'type'     => 'select',
                'title'    => esc_html__( 'Zoom effect', 'legenda' ),
                'options'  => array (
                    'disable' => esc_html__( 'Disable', 'legenda' ),
                    'window'    => esc_html__( 'Window', 'legenda' ),
                    'slippy' => esc_html__( 'Slippy', 'legenda' ),
                ),
                'default'  => 'window',
            ),
            array (
                'id'       => 'gallery_lightbox',
                'type'     => 'switch',
                'title'    => esc_html__( 'Enable Lightbox for Product Images', 'legenda' ),
                'default'  => true,
            ),
            array (
                'id'       => 'tabs_type',
                'type'     => 'select',
                'title'    => esc_html__( 'Tabs type', 'legenda' ),
                'options'  => array (
                    'tabs-default'    => esc_html__( 'Default', 'legenda' ),
                    'left-bar' => esc_html__( 'Left Bar', 'legenda' ),
                    'right-bar' => esc_html__( 'Right Bar', 'legenda' ),
                    'accordion' => esc_html__( 'Accordion', 'legenda' ),
                ),
                'default'  => 'tabs-default',
            ),
            array (
                'id'       => 'tabs_position',
                'type'     => 'select',
                'title'    => esc_html__( 'Tabs position', 'legenda' ),
                'options'  => array (
                    'tabs-under'    => esc_html__( 'Under Content', 'legenda' ),
                    'tabs-inside' => esc_html__( 'Inside Content', 'legenda' ),
                    'tabs-disable' => esc_html__( 'Disable', 'legenda' ),
                ),
                'default'  => 'tabs-under',
            ),
            array (
                'id'       => 'share_icons',
                'type'     => 'switch',
                'title'    => esc_html__( 'Show share buttons', 'legenda' ),
                'default'  => true,
            ),
            array (
                'id' => 'custom_tab_title',
                'type' => 'text',
                'title' => __( 'Custom Tab Title', 'legenda' ),
                'default' => 'Returns & Delivery',
            ),
            array (
                'id'       => 'custom_tab',
                'type'     => 'editor',
                'title'    => esc_html__( 'Custom tab content', 'legenda' ),
                'subtitle' => esc_html__('Enter custom content you would like to output to the product custom tab (for all products)', 'legenda'),
                'default'     => '
        [row][column size="one-half"]<h5>Returns and Exchanges</h5><p>There are a few important things to keep in mind when returning a product you purchased.You can return unwanted items by post within 7 working days of receipt of your goods.</p>[checklist style="arrow"]
        <ul>
        <li>You have 14 calendar days to return an item from the date you received it. </li>
        <li>Only items that have been purchased directly from Us.</li>
        <li>Please ensure that the item you are returning is repackaged with all elements.</li>
        </ul>
        [/checklist] [/column][column size="one-half"]
        <h5>Ship your item back to Us</h5>Firstly Print and return this Returns Form to:<br /> <p>30 South Park Avenue, San Francisco, CA 94108, USA<br /> Please remember to ensure that the item you are returning is repackaged with all elements.</p><br /> <span class="underline">For more information, view our full Returns and Exchanges information.</span>[/column][/row]
                    ',
            ),
        )
    ) );

    // quick view section 
    Redux::setSection( $opt_name, array(
        'title'  => esc_html__( 'Quick View', 'legenda' ),
        'id'     => 'quick_view',
        'icon'   => 'dashicons dashicons-visibility',
        'fields' => array (
            array (
                'id'       => 'quick_view',
                'type'     => 'switch',
                'title'    => esc_html__( 'Enable quick view', 'legenda' ),
                'default'  => true,
            ),
            array (
                'id' => 'quick_images',
                'type' => 'select',
                'title' => __( 'Navigation type', 'legenda' ),
                'options' => array (
                    'none' => __( 'None', 'legenda' ),
                    'slider' => __( 'Slider', 'legenda' ),
                    'single' => __( 'Single', 'legenda' ),
                ),
                'default' => 'slider'
            ),
            array (
                'id'       => 'quick_product_name',
                'type'     => 'switch',
                'title'    => esc_html__( 'Product name', 'legenda' ),
                'default'  => true,
            ),
            array (
                'id'       => 'quick_price',
                'type'     => 'switch',
                'title'    => esc_html__( 'Price', 'legenda' ),
                'default'  => true,
            ),
            array (
                'id'       => 'quick_rating',
                'type'     => 'switch',
                'title'    => esc_html__( 'Product star rating', 'legenda' ),
                'default'  => true,
            ),
            array (
                'id'       => 'quick_sku',
                'type'     => 'switch',
                'title'    => esc_html__( 'Product code', 'legenda' ),
                'default'  => true,
            ),
            array (
                'id'       => 'quick_descr',
                'type'     => 'switch',
                'title'    => esc_html__( 'Short description', 'legenda' ),
                'default'  => true,
            ),
            array (
                'id'       => 'quick_add_to_cart',
                'type'     => 'switch',
                'title'    => esc_html__( 'Add to cart', 'legenda' ),
                'default'  => true,
            ),
            array (
                'id'       => 'quick_share',
                'type'     => 'switch',
                'title'    => esc_html__( 'Share icons', 'legenda' ),
                'default'  => true,
            ),
        )
    ) );

    // search section 
    Redux::setSection( $opt_name, array(
        'title'  => esc_html__( 'AJAX Search', 'legenda' ),
        'id'     => 'search',
        'icon'   => 'dashicons dashicons-search',
        'fields' => array (
            array (
                'id'       => 'search_products',
                'type'     => 'switch',
                'title'    => esc_html__( 'Search by products', 'legenda' ),
                'default'  => true,
            ),
            array (
                'id'       => 'search_out_products',
                'type'     => 'switch',
                'title'    => esc_html__( 'Show out of stock products in Ajax search form', 'legenda' ),
                'default'  => true,
            ),
            array (
                'id'       => 'search_posts',
                'type'     => 'switch',
                'title'    => esc_html__( 'Search by posts', 'legenda' ),
                'default'  => true,
            ),
            array (
                'id'       => 'search_projects',
                'type'     => 'switch',
                'title'    => esc_html__( 'Search by projects', 'legenda' ),
                'default'  => true,
            ),
            array (
                'id'       => 'search_pages',
                'type'     => 'switch',
                'title'    => esc_html__( 'Search by pages', 'legenda' ),
                'default'  => true,
            ),
            array (
                'id' => 'search_result_count',
                'type' => 'spinner',
                'title' => __( 'Search result count', 'legenda' ),
                'subtitle' => '<b>'.esc_html__( 'Example:', 'legenda') . '</b> 5',
                'default' => 3,
                'step' => 1,
                'min' => 0,
                'max' => 20
            ),
        )
    ) );

    // promo popup section
    Redux::setSection( $opt_name, array(
        'title'  => esc_html__( 'Promo Popup', 'legenda' ),
        'id'     => 'promo_popup',
        'icon'   => 'dashicons dashicons-editor-expand',
        'fields' => array (
            array (
                'id' => 'promo_popup',
                'type' => 'select',
                'title' => __( 'Enable promo popup', 'legenda' ),
                'options' => array (
                    0 => esc_html__('Disabled', 'legenda'),
                    1 => esc_html__('Enabled', 'legenda'),
                    'home' => esc_html__('Enabled for home only', 'legenda'),
                ),
                'default' => 0,
            ),
            array (
                'id'       => 'pp_content',
                'type'     => 'editor',
                'title'    => esc_html__( 'Popup content', 'legenda' ),
                'default'     => 'You can add any HTML here (admin -> Theme Options -> Promo Popup).<br> We suggest you create a static block and put it here using shortcode',
            ),
            array (
                'id'            => 'pp_width',
                'type'          => 'slider',
                'title'         => esc_html__( 'Popup width', 'legenda' ),
                'default'       => 750,
                'min'           => 0,
                'step'          => 1,
                'max'           => 2000,
                'display_value' => 'text',
            ),
            array (
                'id'            => 'pp_height',
                'type'          => 'slider',
                'title'         => esc_html__( 'Popup height', 'legenda' ),
                'default'       => 350,
                'min'           => 0,
                'step'          => 1,
                'max'           => 1000,
                'display_value' => 'text',
            ),
            array (
                'id'       => 'pp_bg',
                'type'     => 'background',
                'title'    => esc_html__( 'Popup background', 'legenda' ),
            ),
        )
    ) );

    // blog section
    Redux::setSection( $opt_name, array(
        'title'  => esc_html__( 'Blog Layout', 'legenda' ),
        'id'     => 'blog_page',
        'icon'   => 'dashicons dashicons-feedback',
        'fields' => array (
            array (
                'id'       => 'blog_layout',
                'type'     => 'select',
                'title'    => esc_html__( 'Blog Layout', 'legenda' ),
                'options'  => array (
                    'default'    => esc_html__( 'Default', 'legenda' ),
                    'grid' => esc_html__( 'Grid', 'legenda' ),
                    'timeline' => esc_html__( 'Timeline', 'legenda' ),
                    'default_portrait' => esc_html__( 'Small', 'legenda' ),
                ),
                'default'  => 'default',
            ),
            array (
                'id'       => 'ajax_posts_loading',
                'type'     => 'switch',
                'title'    => esc_html__( 'AJAX Infinite Posts Loading', 'legenda' ),
                'default'  => true,
            ),
            array (
                'id'       => 'blog_lightbox',
                'type'     => 'switch',
                'title'    => esc_html__( 'Enable Lightbox For Blog Posts', 'legenda' ),
                'default'  => true,
            ),
            array (
                'id'       => 'blog_slider',
                'type'     => 'switch',
                'title'    => esc_html__( 'Enable Sliders for posts images', 'legenda' ),
                'default'  => true,
            ),
            array (
                'id'       => 'posts_links',
                'type'     => 'switch',
                'title'    => esc_html__( 'Show Previous and Next posts links', 'legenda' ),
                'default'  => true,
            ),
            array (
                'id'       => 'post_title',
                'type'     => 'switch',
                'title'    => esc_html__( 'Show Post title', 'legenda' ),
                'default'  => true,
            ),
            array (
                'id'       => 'post_share',
                'type'     => 'switch',
                'title'    => esc_html__( 'Show Share buttons', 'legenda' ),
                'default'  => true,
            ),
            array(
                'id'            => 'excerpt_length',
                'type'          => 'slider',
                'title'         => esc_html__('Excerpt length (words)', 'legenda'),
                'default'       => 50,
                'min'           => 10,
                'max'           => 200,
                'step'          => 1,
                'display_value' => 'text',
            ),
            array (
                'id' => 'blog_sidebar',
                'type' => 'image_select',
                'title' => __( 'Sidebar position', 'legenda' ),
                'subtitle' => esc_html__( 'Choose the position of the sidebar for the blog page, posts and simple pages. Every page has also an individual option to change the position of the sidebar.', 'legenda' ),
                'options' => array (
                    'no_sidebar' => array (
                        'alt' => __( 'Without Sidebar', 'legenda' ),
                        'img' => ETHEME_CODE_URL . '/images/layout/full-width.png',
                    ),
                    'left' => array (
                        'alt' => __( 'Left Sidebar', 'legenda' ),
                        'img' => ETHEME_CODE_URL . '/images/layout/left-sidebar.png',
                    ),
                    'right' => array (
                        'alt' => __( 'Right Sidebar', 'legenda' ),
                        'img' => ETHEME_CODE_URL . '/images/layout/right-sidebar.png',
                    ),
                ),
                'default' => 'right'
            ),
            array (
                'id'       => 'blog_sidebar_width',
                'type'     => 'select',
                'title'    => esc_html__( 'Sidebar width', 'legenda' ),
                'options'  => array (
                    4    => '1/3',
                    3 => '1/4',
                    2 => '1/6',
                ),
                'default'  => 3,
            ),
            array (
                'id'       => 'blog_sidebar_responsive',
                'type'     => 'select',
                'title'    => esc_html__( 'Sidebar position for responsive layoutt', 'legenda' ),
                'options'  => array (
                    'top'    => esc_html__( 'Top', 'legenda' ),
                    'bottom' => esc_html__( 'Bottom', 'legenda' ),
                ),
                'default'  => 'bottom',
            ),
        )
    ) );

    // portfolio section
    Redux::setSection( $opt_name, array(
        'title'  => esc_html__( 'Portfolio', 'legenda' ),
        'id'     => 'portfolio',
        'icon'   => 'dashicons dashicons-schedule',
        'fields' => array (
            array (
                'id'       => 'portfolio_count',
                'type'     => 'spinner',
                'title'    => esc_html__( 'Items per page', 'legenda' ),
                'subtitle' => esc_html__( 'Use -1 to show all items', 'legenda' ),
                'default'  => '12',
                'min'      => '-1',
                'step'     => '1',
                'max'      => '100',
                'required' => array(
                    array('enable_portfolio', 'equals', true)
                )
            ),
            array (
                'id'       => 'portfolio_columns',
                'type'     => 'select',
                'title'    => esc_html__( 'Columns', 'legenda' ),
                'options'  => array (
                    2    => 2,
                    3    => 3,
                    4    => 4,
                ),
                'default'  => 3,
                'required' => array(
                    array('enable_portfolio', 'equals', true)
                )
            ),
            array (
                'id'       => 'project_name',
                'type'     => 'switch',
                'title'    => esc_html__( 'Show Project names', 'legenda' ),
                'default'  => true,
                'required' => array(
                    array('enable_portfolio', 'equals', true)
                )
            ),
            array (
                'id'       => 'project_byline',
                'type'     => 'switch',
                'title'    => esc_html__( 'Show ByLine', 'legenda' ),
                'default'  => true,
                'required' => array(
                    array('enable_portfolio', 'equals', true)
                )
            ),
            array (
                'id'       => 'project_excerpt',
                'type'     => 'switch',
                'title'    => esc_html__( 'Show Excerpt', 'legenda' ),
                'default'  => true,
                'required' => array(
                    array('enable_portfolio', 'equals', true)
                )
            ),
            array (
                'id'       => 'recent_projects',
                'type'     => 'switch',
                'title'    => esc_html__( 'Show Recent Projects', 'legenda' ),
                'default'  => true,
                'required' => array(
                    array('enable_portfolio', 'equals', true)
                )
            ),
            array (
                'id'       => 'portfolio_comments',
                'type'     => 'switch',
                'title'    => esc_html__( 'Enable Comments For Projects', 'legenda' ),
                'default'  => true,
                'required' => array(
                    array('enable_portfolio', 'equals', true)
                )
            ),
            array (
                'id'       => 'portfolio_lightbox',
                'type'     => 'switch',
                'title'    => esc_html__( 'Enable Lightbox For Projects', 'legenda' ),
                'default'  => true,
                'required' => array(
                    array('enable_portfolio', 'equals', true)
                )
            ),
            array (
                'id'            => 'portfolio_image_width',
                'type'          => 'slider',
                'title'         => esc_html__( 'Project Images width', 'legenda' ),
                'default'       => 720,
                'min'           => 0,
                'step'          => 1,
                'max'           => 1000,
                'display_value' => 'text',
                'required' => array(
                    array('enable_portfolio', 'equals', true)
                )
            ),
            array (
                'id'            => 'portfolio_image_height',
                'type'          => 'slider',
                'title'         => esc_html__( 'Project Images height', 'legenda' ),
                'default'       => 550,
                'min'           => 0,
                'step'          => 1,
                'max'           => 900,
                'display_value' => 'text',
                'required' => array(
                    array('enable_portfolio', 'equals', true)
                )
            ),
        )
    ) );

    // contact section
    Redux::setSection( $opt_name, array(
        'title'  => esc_html__( 'Contact Form', 'legenda' ),
        'id'     => 'contact_form',
        'icon'   => 'dashicons dashicons-email-alt',
        'fields' => array (
            array (
                'id'       => 'google_map_enable',
                'type'     => 'switch',
                'title'    => esc_html__( 'Enable Google Map', 'legenda' ),
                'default'  => true,
            ),
            array (
                'id'       => 'contact_page_type',
                'type'     => 'select',
                'title'    => esc_html__( 'Choose contact page layout', 'legenda' ),
                'default'  => 'default',
                'options'  => array (
                    'default'      => esc_html__( 'Default Layout', 'legenda' ),
                    'custom' => esc_html__( 'Custom layout', 'legenda' ),
                ),
            ),
            array(
                'id'       => 'contacts_email',
                'type'     => 'text',
                'title'    => esc_html__('Your email for contact form', 'legenda'),
                'default'     => 'contact@armoireplus.fr',
            ),
            array(
                'id'       => 'contacts_tag_line',
                'type'     => 'text',
                'title'    => esc_html__('Your main template title', 'legenda'),
                'default'     => get_bloginfo( 'name' ),
            ),
            array (
                'id'       => 'contacts_privacy',
                'type'     => 'editor',
                'title'    => esc_html__( 'Privacy policy for contact form', 'legenda' ),
                'default'     => 'Your personal data will be used to support you Your personal data will be used to support your experience throughout this website, to manage access to your account, and for other purposes described in our <a href="privacy policy page">privacy policy</a>',
            ),
            array(
                'id'       => 'google_map',
                'type'     => 'text',
                'title'    => esc_html__('Longitude and Latitude for google map', 'legenda'),
                'subtitle' => '<b>' . esc_html__('Example:', 'legenda') . '</b>  51.507622,-0.1305',
                'default'     => '51.507622,-0.1305',
            ),
            array(
                'id'       => 'google_map_api',
                'type'     => 'text',
                'title'    => esc_html__('Google Map API', 'legenda'),
                'subtitle' => '<b>'.esc_html__('To find your Google Map API visit ', 'legenda') . '<a href="https://developers.google.com/maps/documentation/javascript/get-api-key" target="_blank">'.esc_html__('documentation', 'legenda') . '</a></b>',
                'default'     => '124537876',
            ),
            array (
                'id'       => 'registration_privacy',
                'type'     => 'editor',
                'title'    => esc_html__( 'Privacy policy for registration forms', 'legenda' ),
                'default'     => esc_html__('Your personal data will be used to support you Your personal data will be used to support your experience throughout this website, to manage access to your account, and for other purposes described in our', 'legenda') . ' <a href="privacy policy page">' . esc_html__('privacy policy', 'legenda') . '</a>',
            ),
        ),
    ) );

    // google captcha section
    Redux::setSection( $opt_name, array(
        'title'  => esc_html__( 'Google Captcha', 'legenda' ),
        'id'     => 'g_captcha',
        'icon'   => 'dashicons dashicons-lock',
        'fields' => array (
            array (
                'id'       => 'google_captcha_site',
                'type'     => 'text',
                'title'    => esc_html__( 'Site key', 'legenda' ),
                'subtitle' => sprintf( esc_html__( '
                    To start using reCAPTCHA v2, you need to %s for your site.', 'legenda' ), '<a target="blank" href="http://www.google.com/recaptcha/admin">' . esc_html__('sign up for an API key pair', 'legenda') . '</a>'),
            ),
            array (
                'id'       => 'google_captcha_secret',
                'type'     => 'text',
                'title'    => esc_html__( 'Secret key', 'legenda' ),
            )
        )
    ) );

    // instagram feed
    // Redux::setSection( $opt_name, array(
    //     'title'  => esc_html__( 'Instagram Feed', 'legenda' ),
    //     'id'     => 'instagram_feed',
    //     'icon'   => 'dashicons dashicons-instagram',
    //     'fields' => array (
    //         array (
    //             'id'       => 'instagram_feed_template',
    //             'type'     => 'instagram_feed',
    //         ),
    //     )
    // ) );

    // responsive section
    Redux::setSection( $opt_name, array(
        'title'  => esc_html__( 'Responsive', 'legenda' ),
        'id'     => 'responsive',
        'icon'   => 'dashicons dashicons-smartphone',
        'fields' => array (
            array(
                'id'       => 'responsive',
                'type'     => 'switch',
                'title'    => esc_html__( 'Enable Responsive Design', 'legenda' ),
                'default'  => true,
            ),
            array(
                'id'        => 'responsive_from',
                'type'      => 'slider',
                'title'     => esc_html__('Large resolution from', 'legenda'),
                'default'   => 1200,
                'min'       => 768,
                'step'      => 1,
                'max'       => 4000,
                'display_value' => 'text',
                'required' => array(
                    array('responsive', 'equals', true)
                )
            ),
        )
    ) );

    Redux::setSection( $opt_name, array(
            'title' => __( 'Custom CSS', 'legenda' ),
            'desc' => esc_html__( 'Once you\'ve isolated a part of theme that you\'d like to change, enter your CSS code to the fields below. Do not add JS or HTML to the fields. Custom CSS, entered here, will override a theme CSS. In some cases, the !important tag may be needed.', 'legenda' ),
            'id' => 'style-custom_css',
            'icon' => 'dashicons dashicons-media-code',
            'subsection' => false,
            'fields' => array (
                array (
                    'id' => 'custom_css',
                    'type' => 'ace_editor',
                    'mode' => 'css',
                    'title' => __( 'Global Custom CSS', 'legenda' ),
                    'compiler' => true
                ),
                array (
                    'id' => 'custom_css_desktop',
                    'type' => 'ace_editor',
                    'mode' => 'css',
                    'title' => __( 'Custom CSS for desktop (992px+)', 'legenda' ),
                    'compiler' => true
                ),
                array (
                    'id' => 'custom_css_tablet',
                    'type' => 'ace_editor',
                    'mode' => 'css',
                    'title' => __( 'Custom CSS for tablet (768px - 991px)', 'legenda' ),
                    'compiler' => true
                ),
                array (
                    'id' => 'custom_css_wide_mobile',
                    'type' => 'ace_editor',
                    'mode' => 'css',
                    'title' => __( 'Custom CSS for mobile landscape (481px - 767px)', 'legenda' ),
                    'compiler' => true
                ),
                array (
                    'id' => 'custom_css_mobile',
                    'type' => 'ace_editor',
                    'mode' => 'css',
                    'title' => __( 'Custom CSS for mobile (0 - 480px)', 'legenda' ),
                    'compiler' => true
                ),
            ),
        ));

    Redux::setSection( $opt_name, array(
        'title'            => esc_html__('Import Demos', 'legenda'),
        'id'               => 'demos',
        'customizer_width' => '400px',
        'icon'             => 'dashicons dashicons-images-alt2',
        'fields'           => array(
            array(
                'id'       => 'demos_package',
                'type'     => 'theme_versions',
                'title'    => esc_html__( 'Theme demo versions', 'legenda' ),
            ),
            
        )
    ) );

    // if ( file_exists( dirname( __FILE__ ) . '/../README.md' ) ) {
    //     $section = array(
    //         'icon'   => 'el el-list-alt',
    //         'title'  => __( 'Documentation', 'legenda' ),
    //         'fields' => array(
    //             array(
    //                 'id'       => '17',
    //                 'type'     => 'raw',
    //                 'markdown' => true,
    //                 'content_path' => dirname( __FILE__ ) . '/../README.md', // FULL PATH, not relative please
    //                 //'content' => 'Raw content here',
    //             ),
    //         ),
    //     );
    //     Redux::setSection( $opt_name, $section );
    // }
    /*
     * <--- END SECTIONS
     */
    

