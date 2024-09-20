<?php 
    /**
     * Etheme Option Tree to Redux migrator.
     *
     * Migrate legenda theme options from Option Tree to Redux
     *
     * @since   4.0.0
     * @version 1.0.0
     */
    class Etheme_Legenda_Options_Migrator {
        private $options_keys = array(
            'switch' => array(
                'to_top', // checkbox => switch
                'fixed_nav', // checkbox => switch
                'favicon_badge', // checkbox => switch
                'mobile_loader', // checkbox => switch

                'top_bar',
                'top_panel', // checkbox => switch

                'languages_area',
                'right_panel',
                'top_links', // checkbox => switch 
                'cart_widget', // checkbox => switch 
                'search_form', // checkbox => switch 
                'wishlist_link', // checkbox => switch 

                'google_map_enable',

                'just_catalog',
                'ajax_filter',
                'cats_accordion',
                'out_of_label',
                'new_icon',
                'sale_icon',
                'product_page_productname', // checkbox => switch
                'product_page_cats', // checkbox => switch
                'product_page_price', // checkbox => switch
                'product_page_addtocart', //checkbox => switch
                'show_related',
                'hide_out_of_stock',
                'ajax_addtocart',
                'show_name_on_single',
                'gallery_lightbox',
                'share_icons',
                'quick_view',
                'quick_product_name',
                'quick_price',
                'quick_rating',
                'quick_sku',
                'quick_descr',
                'quick_add_to_cart',
                'quick_share',
                'search_products',
                'search_out_products',
                'search_posts',
                'search_projects',
                'search_pages',

                'ajax_posts_loading', // checkbox => switch
                'blog_lightbox', // checkbox => switch
                'blog_slider', // checkbox => switch
                'posts_links', // checkbox => switch
                'post_title', // checkbox => switch
                'post_share', // checkbox => switch

                'project_name', // checkbox => switch 
                'project_byline', // checkbox => switch 
                'project_excerpt', // checkbox => switch 
                'recent_projects', // checkbox => switch 
                'portfolio_comments', // checkbox => switch 
                'portfolio_lightbox', // checkbox => switch 
            ),
            'modules' => array(
                'enable_portfolio',
                'enable_brands',
                'enable_testimonials',
                'enable_static_blocks'
            ),
            'media' => array(
                'logo', // upload => media
                'logo-fixed', // upload => media
                'new_icon_url',
                'sale_icon_url',
            ),
            'slider_spinner' => array(
                'new_icon_width', // text => slider
                'new_icon_height', // text => slider
                'sale_icon_width', // text => slider
                'sale_icon_height', // text => slider
                'products_per_page', // text => slider
                'descr_length', // text => slider

                'pp_width',
                'pp_height', 

                'excerpt_length', // text => slider
                'blog_sidebar_width', // text => slider

                'portfolio_image_width', // text => slider
                'portfolio_image_height', // text => slider

                'responsive_from', // text => slider

                'search_result_count', // text => slider
                'portfolio_count', // text => spinner
            ),
            'multitypes' => array(
                'main_layout', // select
                'main_color_scheme', // select
                'header_color_scheme', // select
                'fixed_header_color', // select

                'breadcrumb_type',
                'contact_page_type',

                'checkout_page', 
                'product_img_hover', // select 
                'sidebar_position_mobile', 
                'category_description_position',
                'upsell_location',
                'zoom_effect',
                'tabs_type',
                'tabs_position',

                'view_mode',

                'promo_popup',
                'blog_sidebar_responsive',

                'portfolio_columns', // select 

                'activecol', // colorpicker => color
                'pricecolor', // colorpicker => color
                'fixed_bg', // colorpicker => color
                'mobile_menu_bg', // colorpicker => color
                'mobile_menu_br', // colorpicker => color

                // settings_image_select
                'header_type', // radio-image => image_select
                'footer_type',
                'grid_sidebar', // radio-image => image_select
                'single_sidebar',
                'quick_images',
                'blog_layout', 
                'blog_sidebar', // radio-image => image_select

                // settings_textarea_simple
                'google_code', // textarea_simple => text

                // settings_textarea
                'contacts_privacy', // textarea => editor
                'registration_privacy', // textarea => editor
                'product_bage_banner', // textarea => editor
                'empty_cart_content', // textarea => editor
                'empty_category_content', // textarea => editor
                'custom_tab', // textarea => editor

                'pp_content', // textarea => editor

                // settings_text
                'contacts_email',
                'google_map',
                'google_map_api',
                'custom_tab_title',

                'google_captcha_site',
                'google_captcha_secret',
            ),
            'site_background' => array(
                'background_img',
                'background_cover',
            ),
            'background' => array(
                'breadcrumb_bg',
                'pp_bg',
            ),
            'typography' => array(
                'mainfont',
                'sfont',
                'header_menu_font',
                'mobile_menu_font',
                'mobile_menu_headings_font',
                'h1',
                'h2',
                'h3',
                'h4',
                'h5',
                'h6',
            )
        );

        function __construct(){
            $this->ot_options = get_option( 'option_tree' );
            $this->options = get_option('legenda_redux_demo');
            $this->migration();
        }

        private function migration() {

            foreach ($this->options_keys as $type => $options) {
                switch ($type) {
                    case 'switch':
                        foreach ($options as $value ) {
                            $ot_option = (isset( $this->ot_options[$value] ) && '' != $this->ot_options[$value]) ? $this->ot_options[$value] : '';
                            $ot_option = ( is_array( $ot_option ) || $ot_option > 0 ) ? true : false;

                            $this->options[$value] = $ot_option;
                        }
                    break;
                    case 'media':
                        foreach ($options as $value ) {
                            $ot_option = (isset( $this->ot_options[$value] ) && '' != $this->ot_options[$value]) ? $this->ot_options[$value] : '';

                            $ot_option = str_replace('[template_url]', get_template_directory_uri(), $ot_option);
                            $redux_option = isset($this->options[$value]) ? $this->options[$value] : array();

                            if ( is_array($redux_option) && isset($redux_option['url']) ) {
                                $redux_option['url'] = $ot_option;
                            }
                            else {
                                $redux_option = array(
                                    'url' => $ot_option
                                );
                            }
                            $this->options[$value] = $redux_option;
                        }
                    break;
                    case 'slider_spinner':
                        foreach ($options as $value ) {
                            $ot_option = (isset( $this->ot_options[$value] ) && '' != $this->ot_options[$value]) ? $this->ot_options[$value] : '';
                            $ot_option = (int)$ot_option;

                            $this->options[$value] = $ot_option;
                        }
                    break;
                    case 'modules':
                        foreach ($options as $value ) {
                            $ot_option = (isset( $this->ot_options[$value] ) && '' != $this->ot_options[$value]) ? $this->ot_options[$value] : '';
                            $ot_option = ($ot_option == 'on') ? true : false;

                            $this->options[$value] = $ot_option;
                        }
                    break;
                    case 'multitypes':
                        foreach ($options as $value ) {
                            $ot_option = (isset( $this->ot_options[$value] ) && '' != $this->ot_options[$value]) ? $this->ot_options[$value] : '';

                            $this->options[$value] = $ot_option;
                        }
                    break;
                    case 'site_background':
                        $bg_img = (isset( $this->ot_options['background_img'] ) && '' != $this->ot_options['background_img']) ? $this->ot_options['background_img'] : array();
                        $bg_cover = (isset( $this->ot_options['background_cover'] ) && '' != $this->ot_options['background_cover']) ? $this->ot_options['background_cover'] : '';

                        $redux_option_bg_img = isset($this->options['background_img']) ? $this->options['background_img'] : array();
                        $redux_option_bg_img = !is_array($redux_option_bg_img) ? array() : $redux_option_bg_img;

                        foreach ($redux_option_bg_img as $key => $value) {
                            if ( isset($bg_img[$key]) ) {
                                $redux_option_bg_img[$key] = $bg_img[$key];
                            }
                        }

                        if ( $bg_cover == 'enable' ) {
                            $redux_option_bg_img['background-size'] = 'cover';
                        }
                        else {
                            $redux_option_bg_img['background-size'] = '';
                        }

                        $this->options['background_img'] = $redux_option_bg_img;
                    break;
                    case 'background':
                        foreach ($options as $value) {
                            $ot_option = (isset( $this->ot_options[$value] ) && '' != $this->ot_options[$value]) ? $this->ot_options[$value] : array();
                            $redux_bg_img = isset($this->options[$value]) ? $this->options[$value] : '';
                            $redux_bg_img = !is_array($redux_bg_img) ? 
                            array (
                              'background-color' => '',
                              'background-repeat' => '',
                              'background-size' => '',
                              'background-attachment' => '',
                              'background-position' => '',
                              'background-image' => '',
                            ) : $redux_bg_img;

                            foreach ($ot_option as $key => $sub_value) {

                                $redux_bg_img[$key] = $sub_value;
                            }

                            $this->options[$value] = $redux_bg_img;

                        }
                    break;
                    case 'typography':
                        foreach ($options as $value ) {

                            $ot_option = (isset( $this->ot_options[$value] ) && '' != $this->ot_options[$value]) ? $this->ot_options[$value] : array();
                            $redux_option = isset($this->options[$value]) ? $this->options[$value] : array();
                            $redux_option = !is_array($redux_option) ? array() : $redux_option;

                            foreach ($ot_option as $option_name => $option_value) {
                                switch ($option_name) {
                                    case 'font-color':
                                        $redux_option['color'] = $option_value;
                                        break;
                                    case 'font-family':
                                        $redux_option[$option_name] = str_replace('+', ' ', $option_value);
                                        switch ( $redux_option['font-family'] ) {
                                            case 'times':
                                                $redux_option['font-family'] = "'Times New Roman', Times,serif";
                                            break;
                                            case 'arial':
                                                $redux_option['font-family'] = 'Arial, Helvetica, sans-serif';
                                            break;
                                            case 'georgia':
                                                $redux_option['font-family'] = 'Georgia, serif';
                                            break;
                                            case 'verdana':
                                                $redux_option['font-family'] = 'Verdana, Geneva, sans-serif';
                                                break;
                                            default;
                                        }
                                        break;
                                    case 'font-size':
                                    case 'font-style':
                                    case 'font-weight':
                                    // case 'letter-spacing':
                                    case 'line-height':
                                    case 'text-transform':
                                        $redux_option[$option_name] = $option_value;
                                        break;

                                    default:
                                        break;
                                }
                                if ( $ot_option['google-font'] != '' ) {
                                    $redux_option['font-family'] = str_replace('+', ' ', $ot_option['google-font']);
                                    $redux_option['google'] = true;
                                }
                            }

                            $this->options[$value] = $redux_option;
                        }
                    break;

                    default;
                }
            }

            update_option( 'legenda_redux_demo', $this->options );
            update_option( 'legenda_theme_migrated', true );

            if ( isset( $_GET['legenda_theme_migrate_options'] ) ) {
                wp_safe_redirect( admin_url('?page=LegendaThemeOptions') );
            }

        }
    }

?>