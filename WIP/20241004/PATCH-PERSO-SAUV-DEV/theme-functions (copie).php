<?php
// **********************************************************************//
// ! Set Content Width
// **********************************************************************//
if (!isset( $content_width )) $content_width = 1170;

// **********************************************************************//
// ! Include CSS and JS
// **********************************************************************//
if(!function_exists('etheme_enqueue_styles')) {
    function etheme_enqueue_styles() {
       global $etheme_responsive, $etheme_theme_data;
        $etheme_theme_data = wp_get_theme( 'legenda' );
        $is_woocommerce = class_exists('WooCommerce');

        if ( !is_admin() ) {

            if ( etheme_get_option('defaultfonts') != 'off' ) {
                wp_enqueue_style("et-fonts",get_template_directory_uri().'/css/et-fonts.css', array(), $etheme_theme_data->Version );
            }

            wp_enqueue_style("et-font-awesome",get_stylesheet_directory_uri().'/css/font-awesome.css', array( 'fonts' ) );
            wp_enqueue_style("style",get_stylesheet_directory_uri().'/style.css', array(), $etheme_theme_data->Version);

            if ( is_rtl() ) {
                wp_enqueue_style("rtl-style",get_stylesheet_directory_uri().'/rtl.css', array('style'), $etheme_theme_data->Version);
            }
            
            wp_enqueue_style('js_composer_front');


            if($etheme_responsive){
                wp_enqueue_style("responsive",get_template_directory_uri().'/css/responsive.css', array(), $etheme_theme_data->Version);
            }

	        $etheme_color_version = etheme_get_option('main_color_scheme');
	        $page_color_scheme = etheme_get_custom_field('page_color_scheme');


	        if($etheme_color_version=='dark' || $page_color_scheme=='dark') {
		        wp_enqueue_style("dark",get_template_directory_uri().'/css/dark.css', array(), $etheme_theme_data->Version);
	        }

            if ($etheme_responsive): 
                $min_width = etheme_get_option('responsive_from') ? etheme_get_option('responsive_from') : '1440';
                wp_enqueue_style('large-resolution',  get_template_directory_uri().'/css/large-resolution.css', array(), false, '(min-width: '.$min_width.'px)'); ?>
            <?php endif;

            $script_depends = array('jquery');

            if( $is_woocommerce ) {
                $script_depends[] = 'wc-add-to-cart-variation';
            }

            wp_enqueue_script('jquery');

            wp_enqueue_script('head', get_template_directory_uri().'/js/head.js'); // modernizr, owl carousel, Swiper, FullWidth helper
            if(etheme_get_option('product_img_hover') == 'tooltip'){
                wp_enqueue_script('tooltip', get_template_directory_uri().'/js/tooltip.min.js');
            }

	        //wp_enqueue_script('flexslider', get_template_directory_uri().'/js/libs/jquery.flexslider.js',array(),false,true);
	        //wp_enqueue_script('emodal', get_template_directory_uri().'/js/libs/emodal.js',array(),false,true);
	        //wp_enqueue_script('magnific-popup', get_template_directory_uri().'/js/libs/jquery.magnific-popup.js',array(),false,true);
	        //wp_enqueue_script('hoverIntent', get_template_directory_uri().'/js/libs/jquery.hoverIntent.js',array(),false,true);
	        //wp_enqueue_script('masonrysadsad', get_template_directory_uri().'/js/libs/jquery.masonry.min.js',array(),false,true);
	        //wp_enqueue_script('easing', get_template_directory_uri().'/js/libs/jquery.easing.js',array(),false,true);
	        //wp_enqueue_script('cookie', get_template_directory_uri().'/js/libs/cookie.js',array(),false,true);
	        //wp_enqueue_script('zoom', get_template_directory_uri().'/js/libs/zoom.js',array(),false,true);
	        //wp_enqueue_script('nicescroll', get_template_directory_uri().'/js/libs/jquery.nicescroll.min.js',array(),false,true);
	        //wp_enqueue_script('bootstrap', get_template_directory_uri().'/js/libs/bootstrap.min.js',array(),false,true);
	        //wp_enqueue_script('zoom', get_template_directory_uri().'/js/libs/zoom.js',array(),false,true);
            wp_enqueue_script('all_plugins', get_template_directory_uri().'/js/plugins.min.js',$script_depends,false,true);
            wp_enqueue_script('waypoints');
            wp_enqueue_script('vc_waypoints');

	        if(class_exists('Redux') && class_exists('WooCommerce') && is_product() ){
		        // avaliablbe only
		        // https://okb.us/wp-content/plugins/swinxyzoom/libs/swinxytouch/documentation/
		        // 01/02/2013 with jquery-1.8.3
		        wp_enqueue_script('et-zoom', get_template_directory_uri().'/js/libs/zoom.js',array(),'1.0.0',true);
	        }

            wp_enqueue_script('etheme', get_template_directory_uri().'/js/etheme.min.js',$script_depends,false,true);
            wp_localize_script( 'etheme', 'myAjax', 
                array( 'ajaxurl' => admin_url( 'admin-ajax.php' ), 
                    'noresults' => __('No results were found!', 'legenda')
                )
            );

            wp_localize_script( 'all_plugins', 'ethemeLocal', array('tClose' => __('Close (Esc)', 'legenda')));

        }
    }
}

add_action( 'wp_enqueue_scripts', 'etheme_enqueue_styles', 130);



// **********************************************************************//
// ! Screet chat fix
// **********************************************************************//

define('SC_CHAT_LICENSE_KEY', '69e13e4c-3dfd-4a70-83c8-3753507f5ae8');
if(!function_exists('etheme_chat_init')) {
    function etheme_chat_init () {
        if ( ! get_option( 'sc_chat_validate_license' ) ) {
	        if (class_exists('ScreetsChat')) {
		        if (!get_option('sc_chat_validate_license', false)) {
			        update_option('sc_chat_validate_license', 1);
		        }
	        }
        }
    }
}

add_action( 'after_setup_theme', 'etheme_chat_init');

// **********************************************************************//
// ! Function for disabling Responsive layout
// **********************************************************************//
if(!function_exists('etheme_set_responsive')) {
    function etheme_set_responsive() {
        global $etheme_responsive;
        $etheme_responsive = etheme_get_option('responsive');
        if(isset($_COOKIE['responsive'])) {
            $etheme_responsive = false;
        }
        if(isset($_GET['responsive']) && $_GET['responsive'] == 'off') {
            if (!isset($_COOKIE['responsive'])) {
                setcookie('responsive', 1, time()+1209600, COOKIEPATH, COOKIE_DOMAIN, false);
            }
            wp_redirect(get_home_url()); exit();
        }elseif(isset($_GET['responsive']) && $_GET['responsive'] == 'on') {
            if (isset($_COOKIE['responsive'])) {
                setcookie('responsive', 1, time()-1209600, COOKIEPATH, COOKIE_DOMAIN, false);
            }
            wp_redirect(get_home_url()); exit();
        }
    }
}

add_action( 'init', 'etheme_set_responsive');

if(!function_exists('et_http')) {
    function et_http() {
        return (is_ssl())?"https://":"http://";
    }
}

if(!function_exists('et_print_filters_for')) {
    function et_print_filters_for( $hook = '' ) {
        global $wp_filter;
        if( empty( $hook ) || !isset( $wp_filter[$hook] ) )
            return;

        print '<pre>';
        print_r( $wp_filter[$hook] );
        print '</pre>';
    }
}


// **********************************************************************//
// ! BBPress add user role
// **********************************************************************//
if(!function_exists('etheme_bb_user_role')) {
    function etheme_bb_user_role() {
        if(!function_exists('bbp_is_deactivation')) return;

        // Bail if deactivating bbPress

        if ( bbp_is_deactivation() )
            return;

        // Catch all, to prevent premature user initialization
        if ( ! did_action( 'set_current_user' ) )
            return;

        // Bail if not logged in or already a member of this site
        if ( ! is_user_logged_in() )
            return;

        // Get the current user ID
        $user_id = get_current_user_id();

        // Bail if user already has a forums role
        if ( bbp_get_user_role( $user_id ) )
            return;

        // Bail if user is marked as spam or is deleted
        if ( bbp_is_user_inactive( $user_id ) )
            return;

        /** Ready *****************************************************************/

        // Load up bbPress once
        $bbp         = bbpress();

        // Get whether or not to add a role to the user account
        $add_to_site = bbp_allow_global_access();

        // Get the current user's WordPress role. Set to empty string if none found.
        $user_role   = bbp_get_user_blog_role( $user_id );

        // Get the role map
        $role_map    = bbp_get_user_role_map();

        /** Forum Role ************************************************************/

        // Use a mapped role
        if ( isset( $role_map[$user_role] ) ) {
            $new_role = $role_map[$user_role];

        // Use the default role
        } else {
            $new_role = bbp_get_default_role();
        }

        /** Add or Map ************************************************************/

        // Add the user to the site
        if ( true === $add_to_site ) {

            // Make sure bbPress roles are added
            bbp_add_forums_roles();

            $bbp->current_user->add_role( $new_role );

        // Don't add the user, but still give them the correct caps dynamically
        } else {
            $bbp->current_user->caps[$new_role] = true;
            $bbp->current_user->get_role_caps();
        }

        $new_role = bbp_get_default_role();

        bbp_set_user_role( $user_id, $new_role );
    }
}

add_action( 'init', 'etheme_bb_user_role');



// **********************************************************************//
// ! Exclude some css from minifier
// **********************************************************************//


add_filter('bwp_minify_style_ignore', 'et_exclude_css');

if(!function_exists('et_exclude_css')) {
    function et_exclude_css($excluded) {
        $excluded = array('font-awesome');
        return $excluded;
    }
}


// **********************************************************************//
// ! Add classes to body
// **********************************************************************//
add_filter('body_class','et_add_body_classes');
if(!function_exists('et_add_body_classes')) {
    function et_add_body_classes($classes) {
        if(etheme_get_option('top_panel')) $classes[] = 'topPanel-enabled ';
        if(etheme_get_option('right_panel')) $classes[] = 'rightPanel-enabled ';
        if(etheme_get_option('fixed_nav')) $classes[] = 'fixNav-enabled ';
        if(etheme_get_option('fade_animation')) $classes[] = 'fadeIn-enabled ';
        if(etheme_get_option('cats_accordion')) $classes[] = 'accordion-enabled ';
        if(get_header_type() == 8) $classes[] = 'header-vertical-enable ';
        if(!class_exists('Woocommerce') || etheme_get_option('just_catalog') || !etheme_get_option('cart_widget')) $classes[] = 'top-cart-disabled ';

        if ( etheme_get_option('google_captcha_site') && etheme_get_option('google_captcha_secret') ) {
           $classes[] = 'g_captcha';
        }

        $classes[] = 'banner-mask-'.etheme_get_option('banner_mask');
        $classes[] = etheme_get_option('main_layout');
        return $classes;
    }
}


if(!function_exists('et_html_tag_schema')) {
    function et_html_tag_schema() {
        $schema     = 'http://schema.org/';
        $type       = 'WebPage';

        // Is single post
        // if ( is_singular( 'post' ) ) {
            //$type   = 'Article';
        // }

        // Is author page
        // elseif ( is_author() ) {
            //$type   = 'ProfilePage';
        // }

        // Is search results page
        // elseif ( is_search() ) {
            //$type   = 'SearchResultsPage';
        // }

        echo 'itemscope="itemscope" itemtype="' . esc_attr( $schema ) . esc_attr( $type ) . '"';
    }
}

add_filter( 'document_title_separator', 'etheme_page_title_sep' );
if ( !function_exists('etheme_page_title_sep') ) {
    function etheme_page_title_sep ($sep) {
        $sep = '|';
        return $sep;
    }
}

// **********************************************************************//
// ! Theme 3d plugins
// **********************************************************************//
add_action( 'init', 'etheme_3d_plugins' );
if(!function_exists('etheme_3d_plugins')) {
    function etheme_3d_plugins() {
        if(function_exists( 'set_revslider_as_theme' )){
            set_revslider_as_theme();
        }
    }
}

if(!function_exists('etheme_vcSetAsTheme')) {
    add_action( 'vc_before_init', 'etheme_vcSetAsTheme' );
    function etheme_vcSetAsTheme() {
        if(function_exists( 'vc_set_as_theme' )){
            vc_set_as_theme();
        }
    }
}


if(!defined('YITH_REFER_ID')) {
    define('YITH_REFER_ID', '1028760');
}



// **********************************************************************//
// ! Add theme support
// **********************************************************************//

if(function_exists('add_theme_support')) {
    add_theme_support( 'post-thumbnails', array('post', 'page', 'product') );
    add_theme_support( 'automatic-feed-links' );
    add_theme_support( 'woocommerce' );
    add_theme_support( 'woocommerce', array(
 
        // Product grid theme settings
        'product_grid'          => array(
            'min_columns'     => 1,
            'max_columns'     => 6,
        ),
    ));
    add_theme_support( 'post-formats', array( 'gallery' ) );
}
// **********************************************************************//
// ! Add admin styles and scripts
// **********************************************************************//

add_action('admin_enqueue_scripts', 'etheme_load_admin_styles');
function etheme_load_admin_styles() {
    wp_enqueue_style('farbtastic');
    wp_enqueue_style('etheme_admin_css', ETHEME_CODE_CSS_URL.'/admin.css');

	global $pagenow;

	if (
		'themes.php' === $pagenow
		&& isset($_GET['page'])
		&& $_GET['page'] == 'LegendaThemeOptions'
	) {
		wp_enqueue_style('etheme_redux_css', ETHEME_CODE_CSS_URL.'/redux-options.css', array('redux-admin-css') );

	}

    wp_enqueue_style("et-font-awesome",get_template_directory_uri().'/css/font-awesome.min.css');
	
}
add_action('admin_init','etheme_add_admin_script', 1130);

function etheme_add_admin_script(){
    add_thickbox();
    wp_enqueue_script('theme-preview');
    wp_enqueue_script('common');
    wp_enqueue_script('wp-lists');
    wp_enqueue_script('postbox');
    wp_enqueue_script('farbtastic');
    wp_enqueue_script('etheme_admin_js', ETHEME_CODE_JS_URL.'/admin.js', array(),false,true);
	
	add_filter( 'redux/enqueue/' . apply_filters( 'legenda_redux_demo/opt_name', 'legenda_redux_demo' ) . '/args/admin_theme/css_url', function() { return ETHEME_CODE_CSS_URL.'/redux-options.css'; } );
}

// **********************************************************************//
// ! Menus
// **********************************************************************//
if(!function_exists('etheme_register_menus')) {
    function etheme_register_menus() {
        register_nav_menus(array(
            'main-menu' => __('Main menu', 'legenda'),
            'mobile-menu' => __('Mobile menu', 'legenda'),
            'account-menu' => __('Account menu', 'legenda')
        ));
    }
}

add_action('init', 'etheme_register_menus');

/*
* Get sidebars list for options
* ******************************************************************* */

if(!function_exists('etheme_get_sidebars')) {
    function etheme_get_sidebars() {
        global $wp_registered_sidebars;
        $sidebars[] = '--Choose--';
        foreach( $wp_registered_sidebars as $id=>$sidebar ) {
            $sidebars[ $id ] = $sidebar[ 'name' ];
        }
        return $sidebars;
    }
}

// **********************************************************************//
// ! Get logo
// **********************************************************************//
if (!function_exists('etheme_logo')) {
    function etheme_logo( $logo = 'logo', $echo = true ) {
        global $panel_filters;

        $logoimg = ( $logo == 'fixed' && etheme_get_option( 'logo-fixed' ) != '' ) ? etheme_get_option( 'logo-fixed' ) : etheme_get_option( 'logo' ) ;
        $logoimg = etheme_get_logo_data($logo);
        $logoimg_url = $logoimg['url'];
        $logoimg_alt = $logoimg['alt'];

        $logoimg_url = apply_filters('etheme_logo_src',$logoimg_url);
        if($panel_filters) {
            $logoimg_url = apply_filters('logo_panel_filters',$logoimg_url);
        }

        $custom_logo = etheme_get_custom_field('custom_logo', et_get_page_id());

        if($custom_logo != '') {
           $logoimg_url = $custom_logo;
        }

        if ( !$echo ) return '<a href="' . home_url() . '"><img src="' . esc_url($logoimg_url) . '" alt="' . esc_attr($logoimg_alt) . '" /></a>';

        echo '<a href="' . home_url() . '"><img src="' . esc_url($logoimg_url) . '" alt="' . esc_attr($logoimg_alt) . '" /></a>';

        ?>
    
    <?php }
}

if (!function_exists('etheme_get_logo_data')) {
    function etheme_get_logo_data( $logo = 'logo' ) {
        $logo_data = array(
            'url' => PARENT_URL.'/images/logo.png',
            'alt' => get_bloginfo('name')
        );

        $fixed_logo = etheme_get_option( 'logo-fixed' );
        $header_logo = etheme_get_option( 'logo' );

        if ( $logo == 'fixed' ) {
            if (!empty($fixed_logo['url'])) {
                $logo_data['url'] = $fixed_logo['url'];
            }
            elseif ( !empty($header_logo['url']) ) {
                $logo_data['url'] = $header_logo['url'];
            }
            if (!empty($fixed_logo['alt'])) {
                $logo_data['alt'] = $fixed_logo['alt'];
            }
            elseif (!empty($header_logo['alt'])) {
                $logo_data['alt'] = $header_logo['alt'];
            }
        }
        else {
            if (!empty($header_logo['url'])) {
                $logo_data['url'] = $header_logo['url'];
            }
            if (!empty($header_logo['alt'])) {
                $logo_data['alt'] = $header_logo['alt'];
            }
        }

        return $logo_data;

    }
}

if(!function_exists('et_get_menus_options')) {
    function et_get_menus_options() {
        $menus = array(""=>"Default");
        $nav_terms = get_terms( 'nav_menu', array( 'hide_empty' => true ) );
        foreach ( $nav_terms as $obj ) {
            $menus[$obj->slug] = $obj->name;
        }
        return $menus;
    }
}

if(!function_exists('et_get_main_menu')) {
    function et_get_main_menu( $menu_id = 'main-menu', $class = '' ) {

        $custom_menu_slug = 'custom_nav';
        $cache_slug = 'et_get_' . $menu_id;
        $custom_menu = etheme_get_custom_field( $custom_menu_slug );
        if(!empty($custom_menu) && $custom_menu != '') {
            $output = false;
	        $output = ($class != '') ? wp_cache_get( $class, $custom_menu, $cache_slug ) : '';
            if ( !$output ) {
                ob_start();

                wp_nav_menu(array(
                    'menu' => $custom_menu,
                    'before' => '',
                    'after' => '',
                    'link_before' => '',
                    'link_after' => '',
                    'depth' => 4,
                    'fallback_cb' => false,
                    'walker' => new Et_Navigation
                ));

                $output = ob_get_contents();
                ob_end_clean();

	            if ($class != '') wp_cache_add( $class, $custom_menu, $output, $cache_slug );
            }

            echo $output; // all data escaped
            return;
        }

        if ( has_nav_menu( $menu_id ) ) {
            $output = false;
	        $output = ($class != '') ? wp_cache_get( $class, $menu_id, $cache_slug ) : '';
            if ( !$output ) {
                ob_start();

                wp_nav_menu(array(
                    'theme_location' => $menu_id,
                    'before' => '',
                    'after' => '',
                    'link_before' => '',
                    'link_after' => '',
                    'depth' => 4,
                    'fallback_cb' => false,
                    'walker' => new Et_Navigation
                ));

                $output = ob_get_contents();
                ob_end_clean();

	            if ($class != '') wp_cache_add( $class, $menu_id, $output, $cache_slug );
            }

            echo $output; // all data escaped
        } else {
            ?>
                <br>
                <p class="a-center"><?php esc_html_e('Set your main menu in', 'legenda');?> <strong>><?php esc_html_e( 'Appearance &gt; Menus', 'legenda'); ?></strong>></p>
            <?php
        }
    }
}

add_action("wp_ajax_et_close_promo", "et_close_promo");
add_action("wp_ajax_nopriv_et_close_promo", "et_close_promo");
if(!function_exists('et_close_promo')) {
    function et_close_promo() {
        $versionsUrl = 'http://8theme.com/import/';
        $ver = 'promo';
        $folder = $versionsUrl.''.$ver;

        $txtFile = $folder.'/legenda.txt';
        $file_headers = @get_headers($txtFile);

        $etag = $file_headers[4];
        update_option('et_close_promo_etag', $etag);
        die();
    }
}


// **********************************************************************//
// ! Get gallery from content
// **********************************************************************//
if(!function_exists('et_gallery_from_content')) {
    function et_gallery_from_content($content) {

        $result = array(
            'ids' => array(),
            'filtered_content' => ''
        );

        preg_match('/\[gallery.*ids=.(.*).\]/', $content, $ids);
        if(!empty($ids)) {
            $result['ids'] = explode(",", $ids[1]);
            $content =  str_replace($ids[0], "", $content);
            $result['filtered_content'] = apply_filters( 'the_content', $content);
        }

        return $result;

    }
}

// **********************************************************************//
// ! Get post classes
// **********************************************************************//
if(!function_exists('et_post_class')) {
    function et_post_class( $cols = false ) {
        $classes = array();

        if($cols) {
            $classes[] = 'post-grid';
            $classes[] = 'isotope-item';
            $classes[] = 'col-md-' . $cols;
        } else {
            $classes[] = 'blog-post';
        }

        $classes[] = 'layout-'.etheme_get_option('blog_layout');

        return $classes;
    }
}


// **********************************************************************//
// ! Init owl carousel gallery
// **********************************************************************//
if(!function_exists('et_owl_init')) {
    function et_owl_init( $el, $atts = array() ) {
        extract( shortcode_atts( array(
            'singleItem' => 'true',
            'itemsCustom' => '[1600, 1]',
            'has_nav' => false,
            'nav_for' => false
        ), $atts ));
        ?>
            jQuery('<?php echo esc_attr($el); ?>').owlCarousel({
                items:1,
                nav: true,
                navText: ["",""],
                lazyLoad: false,
                rewindNav: false,
                addClassActive: true,
                singleItem : <?php echo esc_attr($singleItem); ?>,
                autoHeight : true,
                itemsCustom: <?php echo esc_attr($itemsCustom); ?>,
                <?php if ($has_nav): ?>
                    afterMove: function(args) {
                        var owlMain = jQuery("<?php echo esc_attr($el); ?>").data('owlCarousel');
                        var owlThumbs = jQuery("<?php echo esc_attr($has_nav); ?>").data('owlCarousel');

                        jQuery('.active-thumbnail').removeClass('active-thumbnail')
                        jQuery("<?php echo esc_attr($has_nav); ?>").find('.owl-item').eq(owlMain.currentItem).addClass('active-thumbnail');
                        if(typeof owlThumbs != 'undefined') {
                            owlThumbs.goTo(owlMain.currentItem-1);
                        }
                    }
                <?php endif ?>
            });
        <?php

        if ( $nav_for ) {
            ?>

                jQuery('<?php echo esc_attr($el); ?> .owl-item').click(function(e) {
                    var owlMain = jQuery("<?php echo esc_attr($nav_for); ?>").data('owlCarousel');
                    var owlThumbs = jQuery("<?php echo esc_attr($el); ?>").data('owlCarousel');
                    owlMain.goTo(jQuery(e.currentTarget).index());
                });

            <?php
        }

    }
}


// **********************************************************************//
// ! Meta data block (byline)
// **********************************************************************//
if(!function_exists('et_byline')) {
    function et_byline( $atts = array() ) {
        extract( shortcode_atts( array(
            'author' => 0
        ), $atts ) );
        ?>
            <div class="post-info">
                <span class="posted-on">
                    <?php esc_html_e('Posted on', 'legenda') ?>
                    <span class="published"><?php the_time(get_option('date_format')); ?></span>
                    <?php esc_html_e('at', 'legenda') ?>
                    <?php the_time(get_option('time_format')); ?>
                </span>
                <span class="posted-by"> <?php esc_html_e('by', 'legenda');?><span class="vcard"> <span class="fn"><?php the_author_posts_link(); ?></span></span></span> /
                <span class="posted-in"><?php the_category(',&nbsp;') ?></span>
                <?php // Display Comments

                    if(comments_open() && !post_password_required()) {
                        echo ' / ';
                        comments_popup_link('0', '1 Comment', '% Comments', 'post-comments-count');
                    }

                 ?>
            </div>
        <?php
    }
}


// **********************************************************************//
// ! Get read more button text
// **********************************************************************//
if(!function_exists('et_get_read_more')) {
    function et_get_read_more() {
        return '<span class="button right read-more">'.__('Read More', 'legenda').'</span>';
    }
}


// **********************************************************************//
// ! For demo site
// **********************************************************************//
if(!function_exists('logo_panel_filters')) {
    add_filter('logo_panel_filters', 'logo_panel_filters');
    function logo_panel_filters($value) {
        global $post;
        $logo = '';
        switch($post->post_name) {
            case 'restaurant':
                $logo = '_restaurant';
            break;
            case 'toys':
                $logo = '_toys';
            break;
            case 'underwear':
                $logo = '_underwear';
            break;
            case 'sport':
                $logo = '_sport';
            break;
            case 'candy':
                $logo = '_candy';
            break;
            case 'watches':
                $logo = '_watches';
            break;
            case 'cars':
                $logo = '_cars';
            break;
            case 'games':
                $logo = '_games';
            break;
            case 'home-onepage':
            case 'home-parallax':
            case 'home-sidebar':
            case 'home-transparent':
            case 'home-coming-soon':
                $logo = '_white';
            break;
        }
        if($logo != '')

            $value = get_et_panel_url().'images/params/logo'.$logo.'.png';

        return $value;
    }
}


if(!function_exists('etheme_top_links')) {
    function etheme_top_links() {
        ?>
            <ul class="links">
                <?php if ( is_user_logged_in() ) : ?>
                    <?php if(class_exists('Woocommerce')): ?> <li class="my-account-link"><a href="<?php echo get_permalink( get_option('woocommerce_myaccount_page_id') ); ?>"><?php esc_html_e( 'Your Account', 'legenda' ); ?></a>
                    <div class="submenu-dropdown">
                        <?php  if ( has_nav_menu( 'account-menu' ) ) : ?>
                            <?php wp_nav_menu(array(
                                'theme_location' => 'account-menu',
                                'before' => '',
                                'after' => '',
                                'link_before' => '',
                                'link_after' => '',
                                'depth' => 4,
                                'fallback_cb' => false
                            )); ?>
                        <?php else: ?>
                            <h4 class="a-center install-menu-info">Set your account menu in <em>Apperance &gt; Menus</em></h4>
                        <?php endif; ?>
                    </div>
                </li><?php endif; ?>
                        <li class="logout-link"><a href="<?php echo wp_logout_url(home_url()); ?>"><?php esc_html_e( 'Logout', 'legenda' ); ?></a></li>
                <?php else : ?>
                    <?php
                        $reg_id = etheme_tpl2id('et-registration.php');
                        $reg_url = get_permalink($reg_id);
                    ?>
                    <?php if(class_exists('Woocommerce')): ?><li class="login-link"><a href="<?php echo get_permalink( get_option('woocommerce_myaccount_page_id') ); ?>"><?php esc_html_e( 'Sign In', 'legenda' ); ?></a></li><?php endif; ?>
                    <?php if(!empty($reg_id)): ?><li class="register-link"><a href="<?php echo esc_url($reg_url); ?>"><?php esc_html_e( 'Register', 'legenda' ); ?></a></li><?php endif; ?>
                <?php endif; ?>
            </ul>
        <?php
    }
}

// **********************************************************************//
// ! Add Facebook Open Graph Meta Data
// **********************************************************************//

//Adding the Open Graph in the Language Attributes
if(!function_exists('et_add_opengraph_doctype')) {
    function et_add_opengraph_doctype( $output ) {
        return $output . ' xmlns:og="http://opengraphprotocol.org/schema/" xmlns:fb="http://www.facebook.com/2008/fbml"';
    }
}
add_filter('language_attributes', 'et_add_opengraph_doctype');

//Lets add Open Graph Meta Info

if(!function_exists('et_insert_fb_in_head')) {
    function et_insert_fb_in_head() {
        global $post;
        if ( !etheme_get_option('default_og_tags') && !is_singular()) //if it is not a post or a page
            return;

            $description = et_excerpt( $post->post_content, $post->post_excerpt );

            if (is_null($description)){
	            $description = '';
            }

            $description = strip_tags($description);
            $description = str_replace("\"", "'", $description);

            echo '<meta property="og:title" content="' . get_the_title() . '"/>';
            echo '<meta property="og:type" content="article"/>';
            echo '<meta property="og:description" content="' . $description . '"/>';
            echo '<meta property="og:url" content="' . get_permalink() . '"/>';
            echo '<meta property="og:site_name" content="'. get_bloginfo('name') .'"/>';

            if(!has_post_thumbnail( $post->ID )) {
                $default_image = ETHEME_BASE_URI.'images/facebook-default.jpg';
                echo '<meta property="og:image" content="' . $default_image . '"/>';
            }
            else{
                $thumbnail_src = wp_get_attachment_image_src( get_post_thumbnail_id( $post->ID ), 'large' );
                if ( $thumbnail_src && isset($thumbnail_src[0])){
	                echo '<meta property="og:image" content="' . esc_attr( $thumbnail_src[0] ) . '"/>';
                }
            }
            echo "";
    }
}
add_action( 'wp_head', 'et_insert_fb_in_head', 5 );

if(!function_exists('et_excerpt')) {
    function et_excerpt($text, $excerpt){

        if ($excerpt) return $excerpt;

        $text = strip_shortcodes( $text );

	    $text_no_comments = et_remove_html_comments($text);

	    if (
		    ! $text
		    || empty($text)
		    || is_null($text)
		    || ! $text_no_comments
		    || empty($text_no_comments)
		    || is_null($text_no_comments)
	    ){
		    return '';
	    }

        $text = apply_filters('the_content', $text);
        $text = str_replace(']]>', ']]&gt;', $text);
        $text = strip_tags($text);
        $excerpt_length = apply_filters('excerpt_length', 55);
        $excerpt_more = apply_filters('excerpt_more', ' ' . '[...]');
        $words = preg_split("/[\n
         ]+/", $text, $excerpt_length + 1, PREG_SPLIT_NO_EMPTY);
        if ( count($words) > $excerpt_length ) {
                array_pop($words);
                $text = implode(' ', $words);
                $text = $text . $excerpt_more;
        } else {
                $text = implode(' ', $words);
        }

        return apply_filters('wp_trim_excerpt', $text, $excerpt);
        }
}

// **********************************************************************//
// ! Add hatom data for google webmaster tools
// **********************************************************************//

if ( ! function_exists( 'et_add_hatom_data' ) ) {
    function et_add_hatom_data($content) {
        $t = get_the_modified_time('F jS, Y');
        if ( is_home() || is_singular() || is_archive() ) {
            $content .= '<div class="hatom-extra" style="display:none;visibility:hidden;"> was last modified: <span class="updated"> '.$t.'</span> </div>';
        }
        return $content;
    }
    if ( etheme_get_option('enable_hatom_meta') ) {
        add_filter('the_content', 'et_add_hatom_data');
        add_filter('get_the_content', 'et_add_hatom_data');
        add_filter('the_excerpt', 'et_add_hatom_data');
        add_filter('get_the_excerpt', 'et_add_hatom_data');
    }
}

if(!function_exists('et_registration_email')) {
    function et_registration_email($username = '') {
        global $woocommerce;
        ob_start(); ?>
            <div style="background-color: #f5f5f5;width: 100%;-webkit-text-size-adjust: none;margin: 0;padding: 70px 0 70px 0;">
                <div style="-webkit-box-shadow: 0 0 0 3px rgba(0,0,0,0.025) ;box-shadow: 0 0 0 3px rgba(0,0,0,0.025);-webkit-border-radius: 6px;border-radius: 6px ;background-color: #fdfdfd;border: 1px solid #dcdcdc; padding:20px; margin:0 auto; width:500px; max-width:100%; color: #737373; font-family:Arial; font-size:14px; line-height:150%; text-align:left;">
                    <?php etheme_logo('logo', true); ?>
                    <p><?php printf(__('Thanks for creating an account on %s. Your username is %s.', 'legenda'), get_bloginfo( 'name' ), $username);?></p>
                    <?php if (class_exists('Woocommerce')): ?>

                        <p><?php printf(__('You can access your account area to view your orders and change your password here: <a href="%s">%s</a>.', 'legenda'), get_permalink( get_option('woocommerce_myaccount_page_id') ), get_permalink( get_option('woocommerce_myaccount_page_id') ));?></p>

                    <?php endif; ?>

                </div>
            </div>
        <?php
        $output = ob_get_contents();
        ob_end_clean();
        return $output;
    }
}

function et_sender_email( $original_email_address ) {
    return etheme_get_option('contacts_email');
}
 
// Function to change sender name
function et_sender_name( $original_email_from ) {
    return etheme_get_option('contacts_tag_line');
}
 
// Hooking up our functions to WordPress filters 
add_filter( 'wp_mail_from', 'et_sender_email' );
add_filter( 'wp_mail_from_name', 'et_sender_name' );

// **********************************************************************//
// ! Etheme search
// **********************************************************************//
if(!function_exists('etheme_search')) {
    function etheme_search($atts) {
        $search_products = etheme_get_option('search_products');
        $search_posts = etheme_get_option('search_posts');
        $search_projects = etheme_get_option('search_projects');
        $search_pages = etheme_get_option('search_pages');
        extract( shortcode_atts( array(
            'products' => (!empty($search_products)),
            'posts' => (!empty($search_posts)),
            'portfolio' => (!empty($search_projects)),
            'pages' => (!empty($search_pages)),
            'images' => 1,
            'count' => 3,
            'class' => ''
        ), $atts ) );

        if ( etheme_get_option( 'enable_portfolio' ) == 'off' ) {
            $portfolio = false;
        }

        $search_input = $output = $page_field = '';
        $post_type = "post";
        $search_key = 's';

        if($products == 1) {
            $post_type = "product";
        }

        $action = home_url( '/' );

        $search_page = etheme_tpl2id('search-results.php');

        if( ! empty( $search_page ) ) {
            //$action = get_the_permalink( $search_page );
            $page_field .= '<input type="hidden" name="page_id" value="'.$search_page.'"/>';
            $search_key = 'search';
            $post_type = "";
        }


        if(get_search_query() != '') {
            $search_input = get_search_query();
        }

        $output .= '<div class="et-mega-search '.$class.'" data-products="'.$products.'" data-count="'.$count.'" data-posts="'.$posts.'" data-portfolio="'.$portfolio.'" data-pages="'.$pages.'" data-images="'.$images.'">';
            $output .= '<form method="get" action="'. esc_url( $action ).'">';
                $output .= $page_field;
                $output .= '<input type="text" value="'.$search_input.'" name="'.esc_attr($search_key).'" autocomplete="off" placeholder="'.__('Search', 'legenda').'"/>';
                if( ! empty($post_type) ) $output .= '<input type="hidden" name="post_type" value="'.$post_type.'"/>';
                $output .= '<input type="submit" value="'.__( 'Go', 'legenda' ).'" class="button active filled"  /> ';
            $output .= '</form>';
            $output .= '<span class="et-close-results"></span>';
            $output .= '<div class="et-search-result">';
            $output .= '</div>';
        $output .= '</div>';

        return $output;

    }
}

// **********************************************************************//
// ! Header Type
// **********************************************************************//
function get_header_type() {
    $custom_header = etheme_get_custom_field('custom_header', et_get_page_id());
    if ( $custom_header && $custom_header != 'inherit' ) {
        return $custom_header;
    }
    return etheme_get_option('header_type');
}

add_filter('custom_header_filter', 'get_header_type',10);


// **********************************************************************//
// ! Footer Type
// **********************************************************************//
function get_footer_type() {
    return etheme_get_option('footer_type');
}

add_filter('custom_footer_filter', 'get_footer_type',10);


// **********************************************************************//
// ! Function to display comments
// **********************************************************************//


if(!function_exists('etheme_comments')) {
    function etheme_comments($comment, $args, $depth) {
        $GLOBALS['comment'] = $comment;
        if(get_comment_type() == 'pingback' || get_comment_type() == 'trackback') :
            ?>

            <li id="comment-<?php comment_ID(); ?>" class="pingback">
                <div class="comment-block row-fluid">
                    <div class="span12">
                        <div class="author-link"><?php esc_html_e('Pingback:', 'legenda') ?></div>
                        <div class="comment-reply"> <?php edit_comment_link(); ?></div>
                        <?php comment_author_link(); ?>

                    </div>
                </div>
            <?php
        elseif(get_comment_type() == 'comment') :?>

            <li id="comment-<?php comment_ID(); ?>" <?php comment_class(); ?>>
                <div class="comment-block row-fluid">

                    <div class="row-fluid comment-heading">
                        <div class="comment-author-avatar">
                            <?php
                                $avatar_size = 170;
                                echo get_avatar($comment, $avatar_size);
                             ?>
                        </div>
                        <div class="author-link"><?php comment_author_link(); ?></div><br>
                        <div class="comment-date"><?php comment_date(); ?> - <?php comment_time(); ?></div>
                        <div class="comment-reply"> <?php comment_reply_link(array_merge($args, array('depth' => $depth, 'max_depth' => $args['max_depth']))); ?></div>
                    </div>

                    <div class="row-fluid">

                        <?php if ($comment->comment_approved == '0'): ?>
                            <p class="awaiting-moderation"><?php __('Your comment is awaiting moderation.', 'legenda') ?></p>
                        <?php endif ?>

                        <?php comment_text(); ?>

                    </div>
                </div>

        <?php endif;
    }
}

// **********************************************************************//
// ! Custom Comment Form
// **********************************************************************//

if(!function_exists('etheme_custom_comment_form')) {
    function etheme_custom_comment_form($defaults) {
        $defaults['comment_notes_before'] = '
            <p class="comment-notes">
                <span id="email-notes">
                ' . esc_html__( 'Your email address will not be published. Required fields are marked', 'legenda' ) . '
                </span>
            </p>
        ';
        $defaults['comment_notes_after'] = '';
        $dafaults['id_form'] = 'comments_form';

        $defaults['comment_field'] = '<label for="comment">'.__('Comment', 'legenda').'</label><div class="comment-form-comment row-fluid"><textarea class="span8 required-field"  id="comment" name="comment" cols="45" rows="12" aria-required="true"></textarea></div>';

        return $defaults;
    }
}

add_filter('comment_form_defaults', 'etheme_custom_comment_form');

if(!function_exists('etheme_custom_comment_form_fields')) {
    function etheme_custom_comment_form_fields() {
        $commenter = wp_get_current_commenter();
        $req = get_option('require_name_email');
        $reqT = '<span class="required">*</span>';
        $consent  = empty( $commenter['comment_author_email'] ) ? '' : ' checked="checked"';
        $aria_req = ($req ? " aria-required='true'" : ' ');

        $fields = array(
            'author' => '<p class="comment-form-author">'.
                            '<label for="author">'.__('Name', 'legenda').' '.($req ? $reqT : '').'</label>'.
                            '<div class="row-fluid">'.
                            '<input id="author" name="author" type="text" class="span5 ' . ($req ? ' required-field' : '') . '" value="' . esc_attr($commenter['comment_author']) . '" size="30" ' . $aria_req . '>'.
                            '</div>'.
                        '</p>',
            'email' => '<p class="comment-form-email">'.
                            '<label for="email">'.__('Email', 'legenda').' '.($req ? $reqT : '').'</label>'.
                            '<div class="row-fluid">'.
                            '<input id="email" name="email" type="text" class="span5' . ($req ? ' required-field' : '') . '" value="' . esc_attr($commenter['comment_author_email']) . '" size="30" ' . $aria_req . '>'.
                            '</div>'.
                        '</p>',
            'url' => '<p class="comment-form-url">'.
                            '<label for="url">'.__('Website', 'legenda').'</label>'.
                            '<div class="row-fluid">'.
                            '<input id="url" name="url" type="text" class="span5" value="' . esc_attr($commenter['comment_author_url']) . '" size="30">'.
                            '</div>'.
                        '</p>',
            'cookies' => '
                <p class="comment-form-cookies-consent">
                    <label for="wp-comment-cookies-consent">
                        <input id="wp-comment-cookies-consent" name="wp-comment-cookies-consent" type="checkbox" value="yes"' . $consent . ' />' . '
                        <span>' . esc_html__( 'Save my name, email, and website in this browser for the next time I comment.', 'legenda' ) . '</span>
                    </label>
                </p>'
        );

        return $fields;
    }
}

add_filter('comment_form_default_fields', 'etheme_custom_comment_form_fields');
// **********************************************************************//
// ! Register Sidebars
// **********************************************************************//

if(function_exists('register_sidebar')) {
    register_sidebar(array(
        'name' => __('Main Sidebar', 'legenda'),
        'id' => 'main-sidebar',
        'description' => __('The main sidebar area', 'legenda'),
        'before_widget' => '<div id="%1$s" class="sidebar-widget %2$s">',
        'after_widget' => '</div><!-- //sidebar-widget -->',
        'before_title' => '<h4 class="widget-title">',
        'after_title' => '</h4>',
    ));
    register_sidebar(array(
        'name' => __('Shop Sidebar', 'legenda'),
        'id' => 'shop-sidebar',
        'description' => __('Shop page widget area', 'legenda'),
        'before_widget' => '<div id="%1$s" class="sidebar-widget %2$s">',
        'after_widget' => '</div><!-- //sidebar-widget -->',
        'before_title' => '<h4 class="widget-title">',
        'after_title' => '</h4>',
    ));
    register_sidebar(array(
        'name' => __('Single product page Sidebar', 'legenda'),
        'id' => 'single-sidebar',
        'description' => __('Single product page widget area', 'legenda'),
        'before_widget' => '<div id="%1$s" class="sidebar-widget %2$s">',
        'after_widget' => '</div><!-- //sidebar-widget -->',
        'before_title' => '<h4 class="widget-title">',
        'after_title' => '</h4>',
    ));
    register_sidebar(array(
        'name' => __('Shopping cart sidebar', 'legenda'),
        'id' => 'cart-sidebar',
        'description' => __('Area after cart totals block', 'legenda'),
        'before_widget' => '<div id="%1$s" class="sidebar-widget %2$s">',
        'after_widget' => '</div><!-- //sidebar-widget -->',
        'before_title' => '<h4 class="widget-title">',
        'after_title' => '</h4>',
    ));
    register_sidebar(array(
        'name' => __('Right side panel area', 'legenda'),
        'id' => 'right-panel-sidebar',
        'description' => __('Right side panel widget area', 'legenda'),
        'before_widget' => '<div id="%1$s" class="sidebar-widget %2$s">',
        'after_widget' => '</div><!-- //sidebar-widget -->',
        'before_title' => '<h4 class="widget-title">',
        'after_title' => '</h4>',
    ));
    register_sidebar(array(
        'name' => __('Hidden top panel area', 'legenda'),
        'id' => 'top-panel-sidebar',
        'description' => __('Hidden top panel widget area', 'legenda'),
        'before_widget' => '<div id="%1$s" class="sidebar-widget %2$s">',
        'after_widget' => '</div><!-- //sidebar-widget -->',
        'before_title' => '<h4 class="widget-title">',
        'after_title' => '</h4>',
    ));
    register_sidebar(array(
        'name' => __('Place in header top bar', 'legenda'),
        'id' => 'languages-sidebar',
        'description' => __('Can be used for placing languages switcher of some contacts information.', 'legenda'),
        'before_widget' => '<div id="%1$s" class="%2$s">',
        'after_widget' => '</div><!-- //sidebar-widget -->',
        'before_title' => '<h4 class="widget-title">',
        'after_title' => '</h4>',
    ));

    register_sidebar(array(
        'name' => __('Prefooter Row', 'legenda'),
        'id' => 'prefooter',
        'before_widget' => '<div id="%1$s" class="prefooter-sidebar-widget %2$s">',
        'after_widget' => '</div><!-- //prefooter-sidebar-widget -->',
        'before_title' => '<h4 class="widget-title">',
        'after_title' => '</h4>',
    ));


    register_sidebar(array(
        'name' => __('Footer 1', 'legenda'),
        'id' => 'footer1',
        'before_widget' => '<div id="%1$s" class="footer-sidebar-widget %2$s">',
        'after_widget' => '</div><!-- //footer-sidebar-widget -->',
        'before_title' => '<h4 class="widget-title">',
        'after_title' => '</h4>',
    ));

    register_sidebar(array(
        'name' => __('Footer 2', 'legenda'),
        'id' => 'footer2',
        'before_widget' => '<div id="%1$s" class="footer-sidebar-widget %2$s">',
        'after_widget' => '</div><!-- //sidebar-widget -->',
        'before_title' => '<h4 class="widget-title">',
        'after_title' => '</h4>',
    ));


    register_sidebar(array(
        'name' => __('Footer Copyright', 'legenda'),
        'id' => 'footer9',
        'before_widget' => '<div id="%1$s" class="footer-sidebar-widget %2$s">',
        'after_widget' => '</div><!-- //footer-sidebar-widget -->',
        'before_title' => '<h4 class="widget-title">',
        'after_title' => '</h4>',
    ));

    register_sidebar(array(
        'name' => __('Footer Links', 'legenda'),
        'id' => 'footer10',
        'before_widget' => '<div id="%1$s" class="footer-sidebar-widget %2$s">',
        'after_widget' => '</div><!-- //footer-sidebar-widget -->',
        'before_title' => '<h4 class="widget-title">',
        'after_title' => '</h4>',
    ));
}

// **********************************************************************//
// ! Set exerpt
// **********************************************************************//
function etheme_excerpt_length( $length ) {
    return 35;
}

add_filter( 'excerpt_length', 'etheme_excerpt_length', 999 );

function etheme_excerpt_more( $more ) {
    return '...';
}

add_filter('excerpt_more', 'etheme_excerpt_more');

// **********************************************************************//
// ! Contact page functions
// **********************************************************************//
if(!function_exists('isValidMail')){
    function isValidMail($email){
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }
}

function et_check_captcha($greresponse = false){
    if ( $greresponse ) {
        $secret = etheme_get_option('google_captcha_secret');

        if ( function_exists( 'wp_remote_get' ) ) {
            $response = wp_remote_get( "https://www.google.com/recaptcha/api/siteverify?secret={$secret}&response={$greresponse}" );
            $response = wp_remote_retrieve_body( $response );
            $verify   = json_decode( $response );
            if ( $verify->success == true ) {
                return true;
            }
        }

        if ( $captcha_success->success == true ) {
            return true;
        }
    }
    return false;
}

function et_display_captcha(){

    $gcaptcha = etheme_get_option('google_captcha_site');

    if ( ! $gcaptcha ) {
        return;
    }
    ?>
        <script src='https://www.google.com/recaptcha/api.js'></script>
        <div class="g-recaptcha" data-sitekey="<?php echo esc_html($gcaptcha); ?>"></div>
    <?php
}

// **********************************************************************//
// ! Send message from contact form
// **********************************************************************//

add_action( 'wp_ajax_et_send_msg_action', 'et_send_msg_action' );
add_action( 'wp_ajax_nopriv_et_send_msg_action', 'et_send_msg_action' );
if(!function_exists('et_send_msg_action')) {
    function et_send_msg_action() {
        $error_name  = false;
        $error_email = false;
        $error_msg   = false;

        if(isset($_GET['contact-submit'])) {
            header("Content-type: application/json");
            $name = '';
            $email = '';
            $website = '';
            $message = '';
            $reciever_email = '';
            $return = array();

            if ( etheme_get_option('google_captcha_site') && etheme_get_option('google_captcha_secret') ) {
                if ( et_check_captcha($_GET['greresponse']) != true ) {
                    $return['status'] = 'error';
                    $return['msg'] = __('The security code you entered did not match. Please try again.', 'legenda');
                    echo json_encode($return);
                    die();
                }
            }

            if(trim($_GET['contact-name']) === '') {
                $error_name = true;
            } else{
                $name = trim($_GET['contact-name']);
            }

            if(trim($_GET['contact-email']) === '' || !isValidMail($_GET['contact-email'])) {
                $error_email = true;
            } else{
                $email = trim($_GET['contact-email']);
            }

            if(trim($_GET['contact-msg']) === '') {
                $error_msg = true;
            } else{
                $message = trim($_GET['contact-msg']);
            }

            $website = stripslashes(trim($_GET['contact-website']));

            // Check if we have errors

            if(!$error_name && !$error_email && !$error_msg) {
                // Get the received email
                $reciever_email = etheme_get_option('contacts_email');

                $subject = 'You have been contacted by ' . $name;

                $body = "You have been contacted by $name. Their message is: " . PHP_EOL . PHP_EOL;
                $body .= $message . PHP_EOL . PHP_EOL;
                $body .= "You can contact $name via email at $email";
                if ($website != '') {
                    $body .= " and visit their website at $website" . PHP_EOL . PHP_EOL;
                }
                $body .= PHP_EOL . PHP_EOL;

                $headers = "From $email ". PHP_EOL;
                $headers .= "Reply-To: $email". PHP_EOL;
                $headers .= "MIME-Version: 1.0". PHP_EOL;
                $headers .= "Content-type: text/plain; charset=utf-8". PHP_EOL;
                $headers .= "Content-Transfer-Encoding: quoted-printable". PHP_EOL;

                if(function_exists('et_mail') && et_mail($reciever_email, $subject, $body, $headers)) {
                    $return['status'] = 'success';
                    $return['msg'] = __('All is well, your email has been sent.', 'legenda');
                } else{
                    $return['status'] = 'error';
                    $return['msg'] = __('Error while sending a message!', 'legenda');
                }

            }else{
                // Return errors
                $return['status'] = 'error';
                $return['msg'] = __('Please, fill in the required fields!', 'legenda');
            }

            echo json_encode($return);
            die();
        }
    }
}


/***************************************************************/
/* Etheme Global Search */
/***************************************************************/

add_action("wp_ajax_et_get_search_result", "et_get_search_result_action");
add_action("wp_ajax_nopriv_et_get_search_result", "et_get_search_result_action");
if(!function_exists('et_get_search_result_action')) {
    function et_get_search_result_action() {

        $args = array();

        $search_count = etheme_get_option('search_result_count');

        $args['s']          = sanitize_text_field($_REQUEST['s']);
        $args['count']      = ( ! empty( $search_count ) ) ? intval( $search_count ) : intval( $_REQUEST['count'] );
        $args['images']     = intval($_REQUEST['images']);
        $args['products']   = intval($_REQUEST['products']);
        $args['posts']      = intval($_REQUEST['posts']);
        $args['portfolio']  = intval($_REQUEST['portfolio']);
        $args['pages']      = intval($_REQUEST['pages']);

        $result = et_get_search_result( $args );

        echo json_encode($result);

        die();
    }
}

if(!function_exists('et_get_search_result')) {
    function et_get_search_result( $args ) {
        $word = $args['s'];
        if( empty( $word ) ) return array();

        $response = array(
            'results' => false,
            'html' => ''
        );

        if(isset($args['count'])) {
            $count = $args['count'];
        } else {
            $count = 3;
        }

        $format = (!empty($args['format'])) ? $args['format'] : '';

        if($args['products'] && class_exists('WooCommerce')) {
            $products_args = array(
                'args' => array(
                    'post_type' => 'product',
                    'post_status' => 'publish',
                    'posts_per_page' => $count,
                    's' => $word,
                ),
                'image' => $args['images'],
                'link' => true,
                'title' => __('View Products', 'legenda'),
                'class' => 'et-result-products',
                'format' => $format,
            );
            $args['tax_query'][] = array(
                'taxonomy' => 'product_visibility',
                'field'    => 'name',
                'terms'    => 'hidden',
                'operator' => 'NOT IN',
            );
            $products = et_search_get_posts($products_args);
            if($products) {
                $response['results'] = true;
                if( $format == 'array' ) {
                    $response['posts']['products'] = $products;
                } else {
                    $response['html'] .= $products;
                }
            }
        }

        if($args['posts']) {
            $posts_args = array(
                'args' => array(
                    'post_type' => 'post',
                    'post_status' => 'publish',
                    'posts_per_page' => $count,
                    's' => $word
                ),
                'title' => __('From the blog', 'legenda'),
                'image' => false,
                'link' => true,
                'class' => 'et-result-post',
                'format' => $format,
            );
            $posts = et_search_get_posts($posts_args);
            if($posts) {
                $response['results'] = true;
                if( $format == 'array' ) {
                    $response['posts']['posts'] = $posts;
                } else {
                    $response['html'] .= $posts;
                }
            }
        }

        if($args['portfolio']) {
            $portfolio_args = array(
                'args' => array(
                    'post_type' => 'etheme_portfolio',
                    'post_status' => 'publish',
                    'posts_per_page' => $count,
                    's' => $word
                ),
                'image' => false,
                'link' => false,
                'title' => __('Portfolio', 'legenda'),
                'class' => 'et-result-portfolio',
                'format' => $format,
            );
            $portfolio = et_search_get_posts($portfolio_args);
            if($portfolio) {
                $response['results'] = true;
                if( $format == 'array' ) {
                    $response['posts']['portfolio'] = $portfolio;
                } else {
                    $response['html'] .= $portfolio;
                }
            }
        }


        if($args['pages']) {
            $pages_args = array(
                'args' => array(
                    'post_type' => 'page',
                    'post_status' => 'publish',
                    'posts_per_page' => $count,
                    's' => $word
                ),
                'image' => false,
                'link' => false,
                'title' => __('Pages', 'legenda'),
                'class' => 'et-result-pages',
                'format' => $format,
            );
            $pages = et_search_get_posts($pages_args);
            if($pages) {
                $response['results'] = true;
                if( $format == 'array' ) {
                    $response['posts']['pages'] = $pages;
                } else {
                    $response['html'] .= $pages;
                }
            }
        }
        
        if ( !$response['results'] ) {
            $response['html'] = __('Nothing found', 'legenda');
        }

        return $response;
    }
}


if(!function_exists('et_search_get_posts')) {
    function et_search_get_posts($args) {
        extract($args);

        $query = apply_filters('et_search_get_posts', new WP_Query( $args ));
        $search_product = etheme_get_option( 'search_products' ) && etheme_get_option( 'search_out_products' );

        if($format == 'array') {
            return $query->get_posts();
        }

        // The Loop
        if ( $query->have_posts() ) {

            ob_start();
            if($title != '') {
                ?>

                    <h5 class="title"><span><?php if($link): ?><a href="<?php echo esc_url( home_url() ).'/?s='.$args['s'].'&post_type='.$args['post_type']; ?>" title="<?php esc_attr_e('Show all', 'legenda'); ?>"><?php endif; ?>
                        <?php echo esc_html($title); ?>
                    <?php if($link): ?>&rarr;</a><?php endif; ?></span></h5>

                <?php
            }
            ?>
                <ul class="<?php echo esc_attr($class); ?>">
                    <?php
                        while ( $query->have_posts() ) {
                            $query->the_post();

                            $id = get_the_ID();

                             if ( $search_product && get_post_type() == 'product' ) {

                                $product = get_product( $id );
                                if ( ! $product->is_in_stock() ) continue;

                             }


                            ?>
                                <li>
                                    
                                    <?php if( $image && has_post_thumbnail( $id ) ) echo wp_get_attachment_image( get_post_thumbnail_id( $id ), array( 30, 30 ) ); ?>
                                    <?php // if( $image && has_post_thumbnail( get_the_ID() ) ) echo new_etheme_get_image( get_post_thumbnail_id( get_the_ID() ), array( 30, 30 ) ); ?>

                                    <a href="<?php the_permalink(); ?>">
                                        <?php echo get_the_title(); ?>
                                    </a>

                                </li>
                            <?php
                        }
                    ?>
                </ul>
            <?php
            $output = ob_get_contents();
            ob_end_clean();
            return $output;
        } else {
            return false;
        }
        /* Restore original Post Data */
        wp_reset_postdata();
        return;
    }
}

//add_filter('et_search_get_posts','ae_search_get_result');
function ae_search_get_result($query) {
    if ($query->query['post_type'] != 'product')
        return $query;

    $posts_with_tags = search_by_product_tag($query->posts,$query->query['s']);
    foreach ($posts_with_tags as $post) {
        $query->posts[] = $post;

    }
    $query->query_vars['no_found_rows'] = 3;
    $query->post_count = 3;
    //var_dump($query);
    return $query;
}



// **********************************************************************//
// ! Posted info
// **********************************************************************//
if(!function_exists('etheme_posted_info')) {
    function etheme_posted_info ($title = ''){
        $posted_by = '<div class="post-info">';
        $posted_by .= '<span class="posted-on">';
        $posted_by .= __('Posted on', 'legenda').' ';
        $posted_by .= get_the_time(get_option('date_format')).' ';
        $posted_by .= get_the_time(get_option('time_format')).' ';
        $posted_by .= '</span>';
        $posted_by .= '<span class="posted-by"> '.__('by', 'legenda').' '.get_the_author_link().'</span>';
        $posted_by .= '</div>';
        return $title.$posted_by;
    }
}

if(!function_exists('etheme_pagination')) {
    function etheme_pagination($wp_query, $paged, $pages = '', $range = 2) {
         $output = '';
         $showitems = ($range * 2)+1;

         if(empty($paged)) $paged = 1;

         if($pages == '')
         {
             $pages = $wp_query->max_num_pages;
             if(!$pages)
             {
                 $pages = 1;
             }
         }

         if(1 != $pages)
         {
             $output .= "<nav class='portfolio-pagination'>";
                 $output .= '<ul class="page-numbers">';
                     if($paged > 2 && $paged > $range+1 && $showitems < $pages) $output .= "<li><a href='".get_pagenum_link(1)."' class='prev page-numbers'>prev</a></li>";

                     for ($i=1; $i <= $pages; $i++)
                     {
                         if (1 != $pages &&( !($i >= $paged+$range+1 || $i <= $paged-$range-1) || $pages <= $showitems ))
                         {
                             $output .= ($paged == $i)? "<li><span class='page-numbers current'>".$i."</span></li>":"<li><a href='".get_pagenum_link($i)."' class='inactive' >".$i."</a></li>";
                         }
                     }

                     if ($paged < $pages && $showitems < $pages) $output .= "<li><a href='".get_pagenum_link($paged + 1)."' class='next page-numbers'>next</a></li>";
                 $output .= '</ul>';
             $output .= "</nav>\n";
         }

         return $output;
    }
}

// **********************************************************************//
// ! Create products grid by args
// **********************************************************************//
if(!function_exists('etheme_products')) {
    function etheme_products($args,$title = false, $columns = 4){
        global $wpdb, $woocommerce_loop;
        $products = new WP_Query( $args );
        $class = ''; 

        $output = ob_start(); 

        ?><div class="woocommerce"><?php

        wc_setup_loop( array(
            'columns'      => $columns,
            'name'         => 'product',
            'is_shortcode' => true,
            'total'        => $args['posts_per_page']
        ) );

        if ( $products->have_posts() ) :  
            if ( wc_get_loop_prop( 'total' ) ) { 
                if ($title != '') {
                    echo '<h2 class="title"><span>'.$title.'</span></h2>';
                }   
         ?>
            <?php woocommerce_product_loop_start(); ?>

                <?php while ( $products->have_posts() ) : $products->the_post(); ?>

                   <?php echo wc_get_template_part( 'content', 'product' ); ?>

                <?php endwhile; // end of the loop. ?>
                
            <?php woocommerce_product_loop_end(); ?>
            <?php } ?>
        <?php endif;

        wp_reset_postdata();
        wc_reset_loop(); 

        ?></div><?php

        $output = ob_get_clean();

        return $output;
            
    }
}
// **********************************************************************//
// ! Create products slider by args
// **********************************************************************//
if(!function_exists('etheme_create_slider')) {
    function etheme_create_slider($args, $slider_args = array()) {//, $title = false, $shop_link = true, $slider_type = false, $items = '[[0, 1], [479,2], [619,2], [768,4],  [1200, 4], [1600, 4]]', $style = 'default'
	    global $wpdb, $woocommerce_loop;
	    extract( shortcode_atts( array(
		    'title'       => false,
		    'shop_link'   => false,
		    'slider_type' => false,
		    'items'       => '[[0, 1], [479,2], [619,2], [768,4],  [1200, 4], [1600, 4]]',
		    'style'       => 'default',
		    'full_width'  => false,
		    'block_id'    => false
	    ), $slider_args ) );

	    $box_id      = rand( 1000, 10000 );
	    $multislides = new WP_Query( $args );
	    $shop_url    = get_permalink( wc_get_page_id( 'shop' ) );
	    $class       = '';
	    if ( ! $slider_type ) {
		    $woocommerce_loop['lazy-load'] = true;
		    $woocommerce_loop['style']     = $style;
	    }

	    if ( $multislides->post_count > 1 ) {
		    $class .= ' posts-count-gt1';
	    }
	    if ( $multislides->post_count < 4 ) {
		    $class .= ' posts-count-lt4';
	    }
	    
	    if ( $full_width ) {
		    $class .= ' slider-full-width';
        }
	    if ( $multislides->have_posts() ) :
		    echo '<div class="slider-container ' . $class . '">';
		    if ( $title ) {
			    echo '<h2 class="title"><span>' . $title . '</span></h2>';
		    }
		    if ( $shop_link && $title ) {
			    echo '<a href="' . $shop_url . '" class="show-all-posts hidden-tablet hidden-phone">' . __( 'View more products', 'legenda' ) . '</a>';
		    }
		    echo '<div class="items-slider products-slider ' . $slider_type . '-container slider-' . $box_id . '">';
		    echo '<div class="slider owl-carousel clearfix  ' . $slider_type . '-wrapper">';
		    $_i = 0;
		    if ( $block_id && $block_id != '' && et_get_block( $block_id ) != '' ) {
			    echo '<div class=" ' . $slider_type . '-slide">';
			    echo et_get_block( $block_id );
			    echo '</div><!-- slide-item -->';
		    }
		    if ( class_exists( 'Woocommerce' ) ) {
                while ( $multislides->have_posts() ) : $multislides->the_post();
                    $_i ++;
                        global $product;
                        if ( ! $product->is_visible() ) {
                            continue;
                        }
                        echo '<div class="slide-item product-slide ' . $slider_type . '-slide">';
                        wc_get_template_part( 'content', 'product' );
                        echo '</div><!-- slide-item -->';
    
                endwhile;
		    }
		    echo '</div><!-- slider -->';
		    echo '</div><!-- products-slider -->';
		    echo '</div><!-- slider-container -->';
	    endif;
	    wp_reset_query();
	    unset( $woocommerce_loop['lazy-load'] );
	    unset( $woocommerce_loop['style'] );
	    
	    $items_desktop = 4;
	    
	    //if ( $items != '[[0, 1], [479,2], [619,2], [768,4],  [1200, 4], [1600, 4]]' ) {
		//    $items_desktop = $items['desktop'];
		//    $items = '[[0, ' . $items['phones'] . '], [479,' . $items['phones'] . '], [619,' . $items['tablet'] . '], [768,' . $items['tablet'] . '],  [1200, ' . $items['notebook'] . '], [1600, ' . $items['desktop'] . ']]';
	    //}

	    $phones = isset($items['phones']) ? $items['phones'] : '1';
	    $tablet = isset($items['tablet']) ? $items['tablet'] : '2';
	    $notebook = isset($items['notebook']) ? $items['notebook'] : '3';
	    $desktop = isset($items['desktop']) ? $items['desktop'] : $items_desktop;


	    $items = '{0:{items:' . $phones . '}, 479:{items:' . $phones . '}, 619:{items:' . $tablet . '}, 768:{items:' . $tablet . '},  1200:{items:' . $notebook . '}, 1600:{items: ' . $desktop . '}}';


	    if ($full_width || $multislides->have_posts()) : ?>
        
            <script type="text/javascript">
	    
	        <?php
            
            if ( $full_width ) { ?>
                if(jQuery(window).width() > 767) {
                    jQuery(".slider-<?php echo esc_js($box_id); ?>").etFullWidth();
                }
            <?php
            
            }
            
	        if( $multislides->have_posts()) { ?>
                    var slider = jQuery(".slider-<?php echo esc_js($box_id); ?> .slider");
                    slider.owlCarousel({
                        //items: <?php //echo esc_js($items_desktop); ?>,
                        dots: false,
                        lazyLoad : true,
                        nav: true,
                        navText:["",""],
                        rewind: false,
                        <?php if ( $full_width ) : ?>
                        center: true,
                        <?php endif; ?>
                        responsive: <?php echo esc_js($items); ?>
                    });
            <?php } ?>
            
        </script>
        
        <?php endif;
    }
}


if(!function_exists('etheme_create_flex_slider')) {
    function etheme_create_flex_slider($args,$title = false, $shop_link = true, $sidebar_slider = false){
        global $wpdb;
        $box_id = rand(1000,10000);
        $multislides = new WP_Query( $args );
        $sliderHeight = etheme_get_option('default_slider_height');
        $shop_url = get_permalink(wc_get_page_id('shop'));
        $class = '';
        if($sidebar_slider) {
            $class .= ' sidebar-slider-flex';
            $sliderHeight = 410;
        }

        if($multislides->post_count > 1) {
            $class .= ' posts-count-gt1';
        }
        if($multislides->post_count < 5) {
            $class .= ' posts-count-lt5';
        }
       if ( $multislides->have_posts() ) :
              echo '<div class="slider-container '.$class.'">';
                if ($title) {
                    echo '<h5 class="title"><span>'.$title.'</span></h5>';
                }
                  if($shop_link)
                    echo '<a href="'.$shop_url.'" class="show-all-posts hidden-tablet hidden-phone">'.__('View more products', 'legenda').'</a>';
                    echo '<div class="slider-viewport">';
                        echo '<div class="slider-'.$box_id.'">';
                            echo '<div class="slider">';
                            $_i=0;
                            echo '<div class="slide-item product-slide">';

                                while ($multislides->have_posts()) : $multislides->the_post();
                                    $_i++;

                                    if(class_exists('Woocommerce')) {
                                        global $product;
                                        if (!$product->is_visible()) continue;
                                            wc_get_template_part( 'content', 'product' );
                                        if($sidebar_slider){
                                            if($_i%2 == 0 && $_i != $multislides->post_count) {
                                                echo '</div><!-- slide-item -->';
                                                echo '<div class="slide-item product-slide">';
                                            }
                                        } else {
                                            echo '</div><!-- slide-item -->';
                                            echo '<div class="slide-item product-slide">';
                                        }
                                    }

                                endwhile;

                            echo '</div><!-- slide-item -->';
                            echo '</div><!-- slider -->';
                        echo '</div><!-- products-slider -->';
                    echo '</div><!-- slider-viewport -->';
              echo '</div><!-- slider-container -->';
       endif;
        wp_reset_query();

        echo '
            <script type="text/javascript">
                 // store the slider in a local variable
                  var $window = jQuery(window),
                      flexslider = { vars:{} };

                  // tiny helper function to add breakpoints
                  function getGridSize2() {
                    if ( window.innerWidth < 480 ) {
                        return 2;
                    }
                    if ( window.innerWidth < 600 ) {
                        return 3;
                    }
                    if ( window.innerWidth > 600 && window.innerWidth < 768 ) {
                        return 4;
                    }
                    if ( window.innerWidth > 768 && window.innerWidth < 980 ) {
                        return 1;
                    }
                    if ( window.innerWidth > 980 ) {
                        return 2;
                    }
                  }

                jQuery(document).ready(function($) {
                    jQuery(".slider-'.$box_id.'").flexslider({
                        selector: ".slider .slide-item",
                        animation: "slide",
                        slideshow: false,
                        animationLoop: false,
                        controlNav: true,
                        directionNav:true,
                        itemWidth:105,
                        minItems: getGridSize2(), // use function to pull in initial value
                        maxItems: getGridSize2(), // use function to pull in initial value
                        itemMargin:0

                    });

                    var slideItemCount = $(".single-product-sidebar .sidebar-slider-flex .slider .slide-item");
                    if ( slideItemCount.length == 1 ) {
                        slideItemCount.css({
                            position:"relative",
                            left: "25%",
                            transform: "translateX(-50%)"
                        });
                    }
                });

                // check grid size on resize event
                  $window.resize(function(){
                    var gridSize = getGridSize2();

                    flexslider.vars.minItems = gridSize;
                    flexslider.vars.maxItems = gridSize;
                  });

            </script>
        ';

    }
}


// **********************************************************************//
// ! Create posts slider by args
// **********************************************************************//
if(!function_exists('etheme_create_posts_slider')) {
    function etheme_create_posts_slider( $args,$title = false, $more_link = true, $date = false, $excerpt = false, $width = 300, $height = 200 ){
        $box_id = rand(1000,10000);
        $multislides = new WP_Query( $args );
        $lightbox = etheme_get_option('blog_lightbox');
        $sliderHeight = etheme_get_option('default_blog_slider_height');
        $posts_url = get_permalink(get_option('page_for_posts'));
        $class = '';
        if($multislides->post_count > 1) {
            $class = ' posts-count-gt1';
        }
        if($multislides->post_count < 4) {
            $class .= ' posts-count-lt4';
        }

        if ( $multislides->have_posts() ) :
              echo '<div class="slider-container '.$class.'">';
                  if ($title) {
                        echo '<h2 class="title"><span>'.$title.'</span></h2>';
                  }
                  if($more_link)
                    echo '<a href="'.$posts_url.'" class="show-all-posts hidden-tablet hidden-phone">'.__('View more posts', 'legenda').'</a>';
                  echo '<div class="items-slider posts-slider slider-'.$box_id.'">';
                        echo '<div class="owl-carousel slider">';
                        $_i=0;
                        while ($multislides->have_posts()) : $multislides->the_post();
                            $_i++;
                                echo '<div class="slide-item post-slide">';
                                    if(has_post_thumbnail()){
                                        echo '<div class="post-images">';
                                            echo '<a href="'.get_permalink().'">'. new_etheme_get_image( get_post_thumbnail_id( get_the_ID() ), array( $width, $height ) ) .'</a>';

                                            echo '<div class="blog-mask">';
                                                echo '<div class="mask-content">';
                                                    if($lightbox):
                                                        echo '<a href="' . get_the_post_thumbnail_url() . '" rel="lightbox"><i class="icon-resize-full"></i></a>';
                                                    endif;
                                                    echo '<a href="'.get_permalink().'"><i class="icon-link"></i></a>';
                                                echo '</div>';
                                            echo '</div>';
                                        echo '</div>';
                                    }

                                    if($date){
                                        echo '<div class="post-information">';
                                        the_category(',&nbsp;');
                                        echo ' '.get_the_date('M') . ' <span>' . get_the_date('d') . '</span>' . ', ' . get_the_date('Y');
                                        echo '</div>';
                                    }
                                    echo '<h5><a href="'.get_permalink().'">' . get_the_title() . '</a></h5>';
                                    if($excerpt) the_excerpt();
                                echo '</div><!-- slide-item -->';

                        endwhile;
                        echo '</div><!-- slider -->';
                  echo '</div><!-- items-slider -->';
              echo '</div><div class="clear"></div><!-- slider-container -->';

            echo '
                <script type="text/javascript">
                    jQuery(".slider-'.$box_id.' .slider").owlCarousel({
                        items:4,
                        lazyLoad : true,
                        dots: false,
                        nav: true,
                        navText: ["",""],
                        rewindNav: false,
                        itemsCustom: [[0, 1], [479,2], [619,2], [768,4],  [1200, 4], [1600, 4]]
                    });

                </script>
            ';

        endif;
        wp_reset_query();

    }
}

// **********************************************************************//
// ! Custom sidebars
// **********************************************************************//

/**
*
*   Function for adding sidebar (AJAX action)
*/

function etheme_add_sidebar(){
    if (!wp_verify_nonce($_GET['_wpnonce_etheme_widgets'],'etheme-add-sidebar-widgets') ) die( 'Security check' );
    if($_GET['etheme_sidebar_name'] == '') die('Empty Name');
    $option_name = 'etheme_custom_sidebars';
    if(!get_option($option_name) || get_option($option_name) == '') delete_option($option_name);

	$new_sidebar = '8theme sidebar: ' . $_GET['etheme_sidebar_name'];

    if(get_option($option_name)) {
        $et_custom_sidebars = etheme_get_stored_sidebar();
        $et_custom_sidebars[] = trim($new_sidebar);
        $result = update_option($option_name, $et_custom_sidebars);
    }else{
        $et_custom_sidebars[] = $new_sidebar;
        $result2 = add_option($option_name, $et_custom_sidebars);
    }


    if($result) die('Updated');
    elseif($result2) die('added');
    else die('error');
}


/**
*
*   Function for registering previously stored sidebars
*/
function etheme_register_stored_sidebar(){
    $et_custom_sidebars = etheme_get_stored_sidebar();
    if(is_array($et_custom_sidebars)) {
        foreach($et_custom_sidebars as $name){
            register_sidebar( array(
                'name' => ''.$name.'',
                'id' => str_replace( ' ', '-', $name ),
                'class' => 'etheme_custom_sidebar',
                'before_widget' => '<div id="%1$s" class="widget-container %2$s">',
                'after_widget' => '</div>',
                'before_title' => '<h3 class="widget-title">',
                'after_title' => '</h3>',
            ) );
        }
    }

}

/**
*
*   Function gets stored sidebar array
*/
function etheme_get_stored_sidebar(){
    $option_name = 'etheme_custom_sidebars';
    return get_option($option_name);
}

add_action( 'widgets_init', 'etheme_register_stored_sidebar' );


// **********************************************************************//
// ! Get sidebar
// **********************************************************************//

if(!function_exists('etheme_get_sidebar')) {
    function etheme_get_sidebar ($name = false) {
        do_action( 'get_sidebar', $name );
        if($name) {
            include(TEMPLATEPATH . '/sidebar-'.$name.'.php');
        }else{
            include(TEMPLATEPATH . '/sidebar.php');
        }
    }
}


// **********************************************************************//
// ! Check file exists by url
// **********************************************************************//
if ( ! function_exists( 'etheme_file_exists' ) ) :
    function etheme_file_exists( $url ) {
        $upload_dir = wp_upload_dir();
        $upload_dir = $upload_dir['basedir'];
        $url = explode( '/uploads', $url );

        $file = $upload_dir;

        if ( isset( $url[1] ) ) {
           $file .= $url[1];
        }

        return file_exists( $file );
    }
endif;

/*
* Get revolution sliders list for options
* ******************************************************************* */

if(!function_exists('etheme_get_revsliders')) {
    function etheme_get_revsliders() {
        global $wpdb;
        if(class_exists('RevSliderAdmin')) {
            
            $rs = $wpdb->get_results( 
                "
                SELECT id, title, alias
                FROM ".$wpdb->prefix."revslider_sliders
                ORDER BY id ASC LIMIT 100
                "
            );
            $revsliders = array(
                'no_slider' => 'No Slider'
            );
            if ($rs) {
                $_ri = 1;
                foreach ( $rs as $slider ) {
                    $revsliders[$slider->alias] = $slider->title;
                    $_ri++;
                }
            }
            
            return $revsliders;
        } else {
            return array('' => 'You need to install Revolution Slider plugin');
        }
    }
}

if ( !function_exists('etheme_before_after_breadcrumbs') ) {
    function etheme_before_after_breadcrumbs($content = '', $before = '<span class="current">', $after = '</span>') {
        return $before . $content . $after;
    }
}

// **********************************************************************//
// ! Site breadcrumbs
// **********************************************************************//
if(!function_exists('etheme_breadcrumbs')) {
    function etheme_breadcrumbs() {

      $showOnHome = 0; // 1 - show breadcrumbs on the homepage, 0 - don't show
      $delimiter = '<span class="delimeter">/</span>'; // delimiter between crumbs
      $home = __('Home', 'legenda'); // text for the 'Home' link
      $showCurrent = 1; // 1 - show current post/page title in breadcrumbs, 0 - don't show

      global $post;
      $homeLink = home_url();
      if (is_front_page()) {

        if ($showOnHome == 1) echo '<div id="crumbs"><a href="' . $homeLink . '">' . $home . '</a></div>';

          } else if (class_exists('bbPress') && is_bbpress()) {
        $bbp_args = array(
            'before' => '<div class="breadcrumbs" id="breadcrumb">',
            'after' => '</div>'
        );
        bbp_breadcrumb($bbp_args);
      } else {

        echo '<div class="breadcrumbs">';
        echo '<div id="breadcrumb">';
        echo '<a href="' . $homeLink . '">' . $home . '</a> ' . $delimiter . ' ';

        if ( is_category() ) {
          $thisCat = get_category(get_query_var('cat'), false);
          if ($thisCat->parent != 0) echo get_category_parents($thisCat->parent, TRUE, ' ' . $delimiter . ' ');
          echo etheme_before_after_breadcrumbs('Archive by category "' . single_cat_title('', false) . '"');

        } elseif ( is_search() ) {
          echo etheme_before_after_breadcrumbs('Search results for "' . get_search_query() . '"');

        } elseif ( is_day() ) {
          echo '<a href="' . get_year_link(get_the_time('Y')) . '">' . get_the_time('Y') . '</a> ' . $delimiter . ' ';
          echo '<a href="' . get_month_link(get_the_time('Y'),get_the_time('m')) . '">' . get_the_time('F') . '</a> ' . $delimiter . ' ';
          echo etheme_before_after_breadcrumbs(get_the_time('d'));

        } elseif ( is_month() ) {
          echo '<a href="' . get_year_link(get_the_time('Y')) . '">' . get_the_time('Y') . '</a> ' . $delimiter . ' ';
          echo etheme_before_after_breadcrumbs(get_the_time('F'));

        } elseif ( is_year() ) {
          echo etheme_before_after_breadcrumbs(get_the_time('Y'));

        } elseif ( is_single() && !is_attachment() ) {
          if ( get_post_type() == 'etheme_portfolio' ) {
            $portfolioId = etheme_tpl2id('portfolio.php');
            $portfolioLink = get_permalink($portfolioId);
            $post_type = get_post_type_object(get_post_type());
            $slug = $post_type->rewrite;
            echo '<a href="' . $portfolioLink . '/">' . $post_type->labels->name . '</a>';
            echo ($showCurrent == 1) ? ' ' . $delimiter . ' ' . etheme_before_after_breadcrumbs(get_the_title()) : '';
          } elseif ( get_post_type() != 'post' ) {
            $post_type = get_post_type_object(get_post_type());
            $slug = $post_type->rewrite;
            echo '<a href="' . $homeLink . '/' . $slug['slug'] . '/">' . $post_type->labels->singular_name . '</a>';
            echo ($showCurrent == 1) ? ' ' . $delimiter . ' ' . etheme_before_after_breadcrumbs(get_the_title()) : '';
          } else {
            $cat = get_the_category();
            if(isset($cat[0])) {
                $cat = $cat[0];
                echo get_category_parents($cat, TRUE, ' ' . $delimiter . ' ');
            }
            echo ($showCurrent == 1) ? etheme_before_after_breadcrumbs( get_the_title()) : '';
          }

        } elseif ( !is_single() && !is_page() && get_post_type() != 'post' && !is_404() ) {
          $post_type = get_post_type_object(get_post_type());
          echo etheme_before_after_breadcrumbs($post_type->labels->singular_name);

        } elseif ( is_attachment() ) {
          $parent = get_post($post->post_parent);
          //$cat = get_the_category($parent->ID); $cat = $cat[0];
          //echo get_category_parents($cat, TRUE, ' ' . $delimiter . ' ');
          //echo '<a href="' . get_permalink($parent) . '">' . $parent->post_title . '</a>';
          echo ($showCurrent == 1) ? ' '  . etheme_before_after_breadcrumbs(get_the_title()) : '';

        } elseif ( is_page() && !$post->post_parent ) {
          echo ($showCurrent == 1) ? etheme_before_after_breadcrumbs(get_the_title()) : '';

        } elseif ( is_page() && $post->post_parent ) {
          $parent_id  = $post->post_parent;
          $breadcrumbs = array();
          while ($parent_id) {
            $page = get_page($parent_id);
            $breadcrumbs[] = '<a href="' . get_permalink($page->ID) . '">' . get_the_title($page->ID) . '</a>';
            $parent_id  = $page->post_parent;
          }
          $breadcrumbs = array_reverse($breadcrumbs);
          for ($i = 0; $i < count($breadcrumbs); $i++) {
            echo '' . $breadcrumbs[$i];
            if ($i != count($breadcrumbs)-1) echo ' ' . $delimiter . ' ';
          }
          echo ($showCurrent == 1) ? ' ' . $delimiter . ' ' . etheme_before_after_breadcrumbs( get_the_title() ) : '';

        } elseif ( is_tag() ) {
          echo etheme_before_after_breadcrumbs( 'Posts tagged "' . single_tag_title('', false) . '"' );

        } elseif ( is_author() ) {
           global $author;
          $userdata = get_userdata($author);
          echo etheme_before_after_breadcrumbs('Articles posted by ' . $userdata->display_name);

        } elseif ( is_404() ) {
          echo etheme_before_after_breadcrumbs('Error 404');
        }else{

            echo __('Blog', 'legenda');
        }

        if ( get_query_var('paged') ) {
            $one_page = is_category() || is_day() || is_month() || is_year() || is_search() || is_tag() || is_author();
          if ( $one_page ) echo ' (';
          echo ' ('.__('Page', 'legenda') . ' ' . get_query_var('paged').')';
          if ( $one_page ) echo ')';
        }

        echo '</div>';
        et_back_to_page();
        echo '</div>';

      }
    }
}

if(!function_exists('et_back_to_page')) {
    function et_back_to_page() {
        echo '<a class="back-to" href="javascript: history.go(-1)"><span>â€¹</span>'.__('Return to Previous Page','legenda').'</a>';
    }
}


if(!function_exists('et_show_more_posts')) {
    function et_show_more_posts() {
        return 'class="button big"';
    }
}


// **********************************************************************//
// ! Footer Demo Widgets
// **********************************************************************//

if(!function_exists('etheme_footer_demo')) {
    function etheme_footer_demo($position){
        switch ($position) {
            case 'footer2':

                ?>

                    <div class="row-fluid">
                        <div class="span3">
                            <h4 class="widget-title">About Company</h4>
                            <div>
                                <p>We are a company of highly skilled developers and designers, specialized in working with Magento/Wordpress system management</p>

                                <h6>Contact information</h6>
                                30 South Park Avenue
                                San Francisco, CA 94108
                                Phone: +78 123 456 789
                            </div>

                        </div>
                        <div class="span3">
                            <?php
                                $args = array(
                                    'widget_id' => 'etheme_widget_recent_comments',
                                    'before_widget' => '<div class="footer-sidebar-widget etheme_widget_recent_comments">',
                                    'after_widget' => '</div><!-- //sidebar-widget -->',
                                    'before_title' => '<h4 class="widget-title">',
                                    'after_title' => '</h4>'
                                );

                                $instance = array(
                                    'number' => 2,
                                    'title' => __('Recent Comments', 'legenda')
                                );


                                if ( class_exists('Etheme_Recent_Comments_Widget') ) { $widget = new Etheme_Recent_Comments_Widget();
                                $widget->widget($args, $instance); }
                            ?>
                        </div>
                        <div class="span3">
                            <?php
                                echo do_shortcode('[vc_wp_posts show_date="1" title="Recent Posts" number="3"]');
                            ?>
                        </div>
                        <div class="span3">
                            <?php
                                $args = array(
                                    'widget_id' => 'etheme_widget_flickr',
                                    'before_widget' => '<div class="footer-sidebar-widget etheme_widget_flickr">',
                                    'after_widget' => '</div><!-- //sidebar-widget -->',
                                    'before_title' => '<h4 class="widget-title">',
                                    'after_title' => '</h4>'
                                );

                                $instance = array(
                                    'screen_name' => '52617155@N08',
                                    'number' => 6,
                                    'show_button' => 1,
                                    'title' => __('Flickr Photos', 'legenda')
                                );


                                if ( class_exists('Etheme_Flickr_Widget') ) { $widget = new Etheme_Flickr_Widget();
                                $widget->widget($args, $instance); }
                            ?>
                        </div>
                    </div>

                <?php

            break;
            case 'footer9':
                ?>
                    <p><a href="<?php home_url(); ?>"><img src="<?php echo PARENT_URL.'/images/'; ?>logo-small.png" class="logo-small"></a></p>
                <?php
                break;
            case 'footer10':
                ?>
                    <p style="line-height: 35px;"><?php esc_html_e('Wordpress DEMO Store. All Rights Reserved.', 'legenda') ?><p>
                <?php
                break;
        }
    }
}


// **********************************************************************//
// ! Wishlist
// **********************************************************************//

//add_action('woocommerce_after_add_to_cart_button', 'etheme_wishlist_btn', 20);
//add_action('woocommerce_after_shop_loop_item', 'etheme_wishlist_btn', 20);

if(!function_exists('etheme_wishlist_btn')) {
    function etheme_wishlist_btn() {
        if(class_exists('YITH_WCWL')) {
            $class = (get_option( 'yith_wcwl_frontend_css' ) == 'yes') ? 'with-styles' : '';
            $class = 'with-styles';
            echo '<div class="wishlist-btn-container '.$class.'">';
                echo do_shortcode('[yith_wcwl_add_to_wishlist]');
            echo '</div>';
        }
    }
}

// **********************************************************************//
// ! Get page sidebar position
// **********************************************************************//

if(!function_exists('etheme_get_page_sidebar')) {
    function etheme_get_page_sidebar() {
        $result = array(
            'position' => '',
            'responsive' => '',
            'sidebarname' => '',
            'page_heading' => 'enable',
            'page_slider' => 'no_slider',
            'sidebar_span' => 'span4',
            'content_span' => 'span8'
        );


        $result['responsive'] = etheme_get_option('blog_sidebar_responsive');
        $result['position'] = etheme_get_option('blog_sidebar');
        $result['page_heading'] = etheme_get_custom_field('page_heading');
        $result['page_slider'] = etheme_get_custom_field('page_slider');
        $result['sidebar_width'] = etheme_get_option('blog_sidebar_width');

        $page_sidebar_state = etheme_get_custom_field('sidebar_state');
        $sidebar_width = etheme_get_custom_field('sidebar_width');
        $widgetarea = etheme_get_custom_field('widget_area');


        if($result['sidebar_width'] != '') {
            $content_width = 12 - $result['sidebar_width'];
            $result['sidebar_span'] = 'span'.$result['sidebar_width'];
            $result['content_span'] = 'span'.$content_width;
        }

        if($sidebar_width != '') {
            $content_width = 12 - $sidebar_width;
            $result['sidebar_span'] = 'span'.$sidebar_width;
            $result['content_span'] = 'span'.$content_width;
        }

        if($widgetarea != '') {
            $result['sidebarname'] = 'custom';
        }
        if($page_sidebar_state != '') {
            $result['position'] = $page_sidebar_state;
        }
        if($result['position'] == 'no_sidebar') {
            $result['position'] = 'without';
            $result['content_span'] = 'span12';
        }

        return $result;

    }
}

if(!function_exists('et_get_page_id')) {
    function et_get_page_id() {
        global $post;

        $id = 0;

        if(isset($post->ID) && is_singular('page')) {
            $id = $post->ID;
        } else if( is_home() ) {
            $id = get_option( 'page_for_posts' );
        } else if( get_post_type() == 'etheme_portfolio' || is_singular( 'etheme_portfolio' ) ) {
            $id = etheme_tpl2id( 'portfolio.php' );
        }

        if(class_exists('WooCommerce') && (is_shop() || is_product_category() || is_product_tag() || is_singular( "product" ))) {
            $id = get_option('woocommerce_shop_page_id');
        }

        return $id;
    }
}

// **********************************************************************//
// ! Get blog sidebar position
// **********************************************************************//

if(!function_exists('etheme_get_blog_sidebar')) {
    function etheme_get_blog_sidebar() {

        $page_for_posts = get_option( 'page_for_posts' );

        $result = array(
            'position' => '',
            'responsive' => '',
            'sidebarname' => '',
            'sidebar_width' => 3,
            'sidebar_span' => 'span4',
            'content_span' => 'span8',
            'blog_layout' => 'default',
        );

        $result['responsive'] = etheme_get_option('blog_sidebar_responsive');
        $result['position'] = etheme_get_option('blog_sidebar');
        $result['blog_layout'] = etheme_get_option('blog_layout');
        $result['sidebar_width'] = etheme_get_option('blog_sidebar_width');
        $result['page_slider'] = etheme_get_custom_field('page_slider', $page_for_posts);


        $result['page_heading'] = etheme_get_custom_field('page_heading', $page_for_posts);
        $page_sidebar_state = etheme_get_custom_field('sidebar_state', $page_for_posts);
        $sidebar_width = etheme_get_custom_field('sidebar_width', $page_for_posts);

        $content_width = 12 - $result['sidebar_width'];
        $result['sidebar_span'] = 'span'.$result['sidebar_width'];
        $result['content_span'] = 'span'.$content_width;


        if($sidebar_width != '') {
            $content_width = 12 - $sidebar_width;
            $result['sidebar_span'] = 'span'.$sidebar_width;
            $result['content_span'] = 'span'.$content_width;
        }

        if($page_sidebar_state != '') {
            $result['position'] = $page_sidebar_state;
        }

        if($result['position'] == 'no_sidebar' || $result['blog_layout'] == 'grid') {
            $result['position'] = 'without';
            $result['content_span'] = 'span12';
        }

        return $result;

    }
}

// **********************************************************************//
// ! Get shop sidebar position
// **********************************************************************//

if(!function_exists('etheme_get_shop_sidebar')) {
    function etheme_get_shop_sidebar() {

        $result = array(
            'position' => 'left',
            'responsive' => '',
            'product_per_row' => 3,
            'sidebar_span' => 'span3',
            'content_span' => 'span9'
        );

        $shop_page = wc_get_page_id( 'shop' );

        $result['responsive'] = etheme_get_option('blog_sidebar_responsive');
        $result['position'] = etheme_get_option('grid_sidebar');
        $result['product_per_row'] = wc_get_loop_prop( 'columns' );
        $result['page_slider'] = etheme_get_custom_field('page_slider', $shop_page);
        $result['page_heading'] = etheme_get_custom_field('page_heading', $shop_page);
        $page_sidebar_state = etheme_get_custom_field('sidebar_state', $shop_page);

        //$result['product_per_row'] = apply_filters('shop_column_count', $result['product_per_row']);
        //$result['product_page_sidebar'] = apply_filters('shop_sidebar', $result['product_page_sidebar']);

        if($result['position'] == 'without') {
            $result['content_span'] = 'span12';
        }

        if($page_sidebar_state != '') {
            $result['position'] = $page_sidebar_state;
        }
        if($result['position'] == 'no_sidebar') {
            $result['position'] = 'without';
            $result['content_span'] = 'span12';
        }

        // if($result['product_per_row'] == 6){
        //     $result['position'] = 'without';
        //     $result['content_span'] = 'span12';
        // }


        return $result;
    }
}

// **********************************************************************//
// ! Get single product page sidebar position
// **********************************************************************//

if(!function_exists('etheme_get_single_product_sidebar')) {
    function etheme_get_single_product_sidebar() {
        global $product;

        $result = array(
            'position' => 'left',
            'responsive' => '',
            'images_span' => '5',
            'meta_span' => '4'
        );

        $result['single_product_sidebar'] = ( is_active_sidebar('single-sidebar') || (etheme_get_option('upsell_location') == 'sidebar' && sizeof( $product->get_upsell_ids() )  > 0) ) ? true : false;
        $result['responsive'] = etheme_get_option('blog_sidebar_responsive');
        $result['position'] = etheme_get_option('single_sidebar');

        $result['single_product_sidebar'] = apply_filters('single_product_sidebar', $result['single_product_sidebar']);

        if(!$result['single_product_sidebar'] || $result['position'] == 'no_sidebar') {
            $result['position'] = 'without';
            $result['images_span'] = '6';
            $result['meta_span'] = '6';
            $result['single_product_sidebar'] = false;
        }

        return $result;
    }
}


add_filter( 'wp_nav_menu_objects', 'add_menu_parent_class' );
function add_menu_parent_class( $items ) {

    $parents = array();
    foreach ( $items as $item ) {
        if ( $item->menu_item_parent && $item->menu_item_parent > 0 ) {
            $parents[] = $item->menu_item_parent;
        }
    }

    foreach ( $items as $item ) {
        if ( in_array( $item->ID, $parents ) ) {
            $item->classes[] = 'menu-parent-item';
        }
    }

    return $items;
}


// **********************************************************************//
// ! Enable shortcodes in text widgets
// **********************************************************************//
add_filter('widget_text', 'do_shortcode');

// **********************************************************************//
// ! Custom meta fields to categories
// **********************************************************************//
if(function_exists('et_get_term_meta')){

function etheme_taxonomy_edit_meta_field($term, $taxonomy) {
    $id = $term->term_id;
    $term_meta = et_get_term_meta($id,'cat_meta');



    if(!$term_meta){$term_meta = et_add_term_meta($id, 'cat_meta', '');}
     ?>
    <tr class="form-field">
    <th scope="row" valign="top"><label for="term_meta[cat_header]"><?php esc_html_e( 'Category Header', 'legenda' ); ?></label></th>
        <td>
	        <?php
	            if (isset($term_meta[0]['cat_header'])){
		            $content = $term_meta[0]['cat_header'];
                } elseif (isset($term_meta[0])){
		            $content = $term_meta[0];
                } else {
		            $content = '';
	            }

                $editor_id = 'term_meta';
                $settings = array('media_buttons' => true, 'textarea_name' => 'term_meta[cat_header]');
                wp_editor($content, $editor_id, $settings);
	        ?>
        </td>
    </tr>
<?php
}

add_action( 'product_cat_edit_form_fields', 'etheme_taxonomy_edit_meta_field', 20, 2 );

// **********************************************************************//
// ! Save meta fields
// **********************************************************************//
function save_taxonomy_custom_meta( $term_id ) {

    if ( isset($_POST['term_meta']) && isset( $_POST['term_meta']['cat_header'] ) ) {
        $term_meta = et_get_term_meta($term_id,'cat_meta');
        if ( isset ( $_POST['term_meta']['cat_header'] ) ) {
            $term_meta = $_POST['term_meta']['cat_header'];
        }
        // Save the option array.
        et_update_term_meta($term_id, 'cat_meta', $term_meta);
    }
}
add_action( 'edited_product_cat', 'save_taxonomy_custom_meta', 10, 2 );
}

if(!function_exists('et_get_home_option')) {
  function et_get_home_option() {
    return apply_filters('et_get_home_option', array(
      'home_1' => array(
          'title'   => 'Home Page 1',
          'home_id' => '129',
      ),
      'home_2' => array(
          'title'   => 'Home Page 2',
          'home_id' => '1164',
      ),
      'home_3' => array(
          'title'   => 'Home Page 3',
          'home_id' => '1179',
      ),
      'home_4' => array(
          'title'   => 'Home Page 4',
          'home_id' => '1188',
      ),
      'home_5' => array(
          'title'   => 'Home Page 5',
          'home_id' => '1205',
      ),
      'home_6' => array(
          'title'   => 'Home Page 6',
          'home_id' => '1215',
      ),
      'home_7' => array(
          'title'   => 'Home Page 7',
          'home_id' => '1227',
      ),
      'home_8' => array(
          'title'   => 'Home Page 8',
          'home_id' => '1247',
      ),
      'home_9' => array(
          'title'   => 'Home Page 9',
          'home_id' => '1275',
      ),
      'home_10' => array(
          'title'   => 'Home Page 10',
          'home_id' => '1282',
      ),
    ));
  }
}

if(!function_exists('et_get_versions_option')) {
  function et_get_versions_option() {
    return apply_filters('et_get_versions_option', array(
      'ecommerce' => array(
          'home_id' => '129',
          'title'   => 'e-commerce',
      ),
      'corporate' => array(
          'title'   => 'corporate',
          'home_id' => '774',
      ),
      'dark' => array(
          'title'   => 'dark',
          'home_id' => '1139',
      ),
      'candy' => array(
          'title'   => 'candy',
          'home_id' => '944',
      ),
      'car' => array(
          'title'   => 'car',
          'home_id' => '789',
      ),
      'game' => array(
          'title'   => 'game',
          'home_id' => '837',
      ),
      'restaurant' => array(
          'title'   => 'restaurant',
          'home_id' => '1054',
      ),
      'sport' => array(
          'title'   => 'sport',
          'home_id' => '1020',
      ),
      'toys' => array(
          'title'   => 'toys',
          'home_id' => '1037',
      ),
      'underwear' => array(
          'title'   => 'underwear',
          'home_id' => '999',
      ),
      'watches' => array(
          'title'   => 'watches',
          'home_id' => '894',
      ),
      'left_sidebar' => array(
          'title'   => 'left sidebar',
          'home_id' => '9563',
      ),
      'onepage' => array(
          'title'   => 'onepage',
          'home_id' => '9518',
      ),
      'parallax' => array(
          'title'   => 'parallax',
          'home_id' => '9546',
      ),
      'transparent' => array(
          'title'   => 'transparent',
          'home_id' => '9538',
      ),
      'coming_soon' => array(
          'title'   => 'coming soon',
          'home_id' => '9570',
      ),
    ));
  }
}


// **********************************************************************//
// ! Add google analytics code
// **********************************************************************//
add_action('init', 'et_google_analytics');
if(!function_exists('et_google_analytics')) {
function et_google_analytics() {
    $googleCode = etheme_get_option('google_code');

    if(empty($googleCode)) return;

    if(strpos($googleCode,'UA-') === 0) {

        $googleCode = "

<script type='text/javascript'>

var _gaq = _gaq || [];
_gaq.push(['_setAccount', '".$googleCode."']);
_gaq.push(['_trackPageview']);

(function() {
var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
})();

</script>

";
    }

    add_action('wp_head', 'et_print_google_code');
}

function et_print_google_code() {
    $googleCode = etheme_get_option('google_code');

    if(!empty($googleCode)) {
        echo etheme_get_option('google_code');
    }
}

}

// **********************************************************************//
// ! Related posts
// **********************************************************************//

if(!function_exists('et_get_related_posts')) {
    function et_get_related_posts($postId = false, $limit = 5){
        global $post;
        if(!$postId) {
            $postId = $post->ID;
        }
        $categories = get_the_category($postId);
        if ($categories) {
            $category_ids = array();
            foreach($categories as $individual_category) $category_ids[] = $individual_category->term_id;

            $args = array(
                'category__in' => $category_ids,
                'post__not_in' => array($postId),
                'showposts'=>$limit, // Number of related posts that will be shown.
                'caller_get_posts'=>1
            );
            etheme_create_posts_slider($args);
        }
    }
}


// **********************************************************************//
// ! Get activated theme
// **********************************************************************//

function etheme_activated_theme() {
    $activated_data = get_option( 'etheme_activated_data' );
    $theme = ( isset( $activated_data['theme'] ) && ! empty( $activated_data['theme'] ) ) ? $activated_data['theme'] : false ;
    return $theme;
}

// **********************************************************************//
// ! Is theme activatd
// **********************************************************************//

function etheme_is_activated() {
    if ( etheme_activated_theme() != 'legenda' ) return false;
    return get_option( 'xtheme_is_activated', false );
}

// **********************************************************************//
// ! Custom Static Blocks Post Type
// **********************************************************************//

if(!function_exists('et_get_static_blocks')) {
    function et_get_static_blocks () {
        $return_array = array();
        $args = array( 'post_type' => 'staticblocks', 'posts_per_page' => 50);
        //if ( class_exists( 'bbPress') ) remove_action( 'set_current_user', 'bbp_setup_current_user' );
        $myposts = get_posts( $args );
        $i=0;
        foreach ( $myposts as $post ) {
            $i++;
            $return_array[$i]['label'] = get_the_title($post->ID);
            $return_array[$i]['value'] = $post->ID;
        }
        wp_reset_postdata();
        //if ( class_exists( 'bbPress') ) add_action( 'set_current_user', 'bbp_setup_current_user', 10 );
        return $return_array;
    }
}


if(!function_exists('et_show_block')) {
    function et_show_block ($id = false) {
        echo et_get_block($id);
    }
}

add_filter('et_the_content', 'wpautop', 10);
add_filter('et_the_content', 'do_shortcode', 11);

if(!function_exists('et_get_block')) {
    function et_get_block($id = false) {
        if(!$id) return;
        global $post;
        $args = array( 'include' => $id,'post_type' => 'staticblocks', 'posts_per_page' => 50);
        $output = '';
        $myposts = get_posts( $args );
        foreach ( $myposts as $post ) {
            setup_postdata($post);
            //$output = wpautop(do_shortcode(get_the_content($post->ID)));
            $output = apply_filters('et_the_content', get_the_content());
            $shortcodes_custom_css = get_post_meta( $post->ID, '_wpb_shortcodes_custom_css', true );
            if ( ! empty( $shortcodes_custom_css ) ) {
                $output .= '<style type="text/css" data-type="vc_shortcodes-custom-css">';
                $output .= $shortcodes_custom_css;
                $output .= '</style>';
            }
        }
        wp_reset_postdata();
        return $output;
   }
}

if ( !function_exists('et_custom_styles_responsive') ) {
    function et_custom_styles_responsive () {
        $css = '';
        $custom_css = etheme_get_option('custom_css');
        $custom_css_desktop = etheme_get_option('custom_css_desktop');
        $custom_css_tablet = etheme_get_option('custom_css_tablet');
        $custom_css_wide_mobile = etheme_get_option('custom_css_wide_mobile');
        $custom_css_mobile = etheme_get_option('custom_css_mobile');
        if($custom_css != '') {
            $css .= $custom_css;
        }
        if($custom_css_desktop != '') {
            $css .= '@media (min-width: 993px) { ' . $custom_css_desktop . ' }';
        }
        if($custom_css_tablet != '') {
            $css .= '@media (min-width: 768px) and (max-width: 992px) {' . $custom_css_tablet . ' }';
        }
        if($custom_css_wide_mobile != '') {
            $css .= '@media (min-width: 481px) and (max-width: 767px) { ' . $custom_css_wide_mobile . ' }';
        }
        if($custom_css_mobile != '') {
            $css .= '@media (max-width: 480px) { ' . $custom_css_mobile . ' }';
        }
        return $css;
    }
}


// **********************************************************************//
// ! Promo Popup
// **********************************************************************//
add_action('after_page_wrapper', 'et_promo_popup');
if(!function_exists('et_promo_popup')) {
    function et_promo_popup() {
        if(!etheme_get_option('promo_popup')) return;
        if ( etheme_get_option( 'promo_popup' ) == 'home' && ! is_front_page() ) return;
        $bg = etheme_get_option('pp_bg');
        $padding = etheme_get_option('pp_padding');
        ?>
            <a class="etheme-popup " href="#etheme-popup">Open modal</a>

            <div id="etheme-popup" class="white-popup-block mfp-hide">
                <?php echo do_shortcode(etheme_get_option('pp_content')); ?>
                <a class="popup-modal-dismiss" href="#"><i class="icon-remove"></i></a>
                <p class="checkbox-label">
                    <input type="checkbox" value="do-not-show" name="showagain" id="showagain" class="showagain" />
                    <label for="showagain"><?php esc_html_e('Do not show this popup again', 'legenda'); ?></label>
                </p>
            </div>
            <style type="text/css">
                #etheme-popup {
                    width: <?php echo (etheme_get_option('pp_width') != '') ? etheme_get_option('pp_width') : 770 ; ?>px;
                    height: <?php echo (etheme_get_option('pp_height') != '') ? etheme_get_option('pp_height') : 350 ; ?>px;
                    <?php if(!empty($bg['background-color'])): ?>  background-color: <?php echo esc_attr($bg['background-color']); ?>;<?php endif; ?>
                    <?php if(!empty($bg['background-image'])): ?>  background-image: url(<?php echo esc_attr($bg['background-image']); ?>) ; <?php endif; ?>
                    <?php if(!empty($bg['background-attachment'])): ?>  background-attachment: <?php echo esc_attr($bg['background-attachment']); ?>;<?php endif; ?>
                    <?php if(!empty($bg['background-repeat'])): ?>  background-repeat: <?php echo esc_attr($bg['background-repeat']); ?>;<?php  endif; ?>
                    <?php if(!empty($bg['background-color'])): ?>  background-color: <?php echo esc_attr($bg['background-color']); ?>;<?php  endif; ?>
                    <?php if(!empty($bg['background-position'])): ?>  background-position: <?php echo esc_attr($bg['background-position']); ?>;<?php endif; ?>
                }
            </style>
        <?php
    }
}


// **********************************************************************//
// ! Helper functions
// **********************************************************************//

if(!function_exists('actions_to_remove')) {
    function actions_to_remove($tag, $array) {
        foreach($array as $action) {
            remove_action($tag, $action[0], $action[1]);
        }
    }
}
if(!function_exists('et_get_actions')) {
    function et_get_actions($tag = '') {
        global $wp_filter;
        return $wp_filter[$tag];
    }
}

if(!function_exists('jsString')) {
    function jsString($str='') {
        return trim(preg_replace("/('|\"|\r?\n)/", '', $str));
    }
}
if(!function_exists('hex2rgb')) {
    function hex2rgb($hex) {
       $hex = str_replace("#", "", $hex);

       if(strlen($hex) == 3) {
          $r = hexdec(substr($hex,0,1).substr($hex,0,1));
          $g = hexdec(substr($hex,1,1).substr($hex,1,1));
          $b = hexdec(substr($hex,2,1).substr($hex,2,1));
       } else {
          $r = hexdec(substr($hex,0,2));
          $g = hexdec(substr($hex,2,2));
          $b = hexdec(substr($hex,4,2));
       }
       $rgb = array($r, $g, $b);
       //return implode(",", $rgb); // returns the rgb values separated by commas
       return $rgb; // returns an array with the rgb values
    }
}

if(!function_exists('trunc')) {
    function trunc($phrase, $max_words) {
       $phrase_array = explode(' ',$phrase);
       if(count($phrase_array) > $max_words && $max_words > 0)
          $phrase = implode(' ',array_slice($phrase_array, 0, $max_words)).' ...';
       return $phrase;
    }
}

if(!function_exists('et_get_icons')) {
    function et_get_icons() {
        $iconsArray = array("adjust","anchor","archive","arrows","arrows-h","arrows-v","asterisk","ban","bar-chart-o","barcode","bars","beer","bell","bell-o","bolt","book","bookmark","bookmark-o","briefcase","bug","building-o","bullhorn","bullseye","calendar","calendar-o","camera","camera-retro","caret-square-o-down","caret-square-o-left","caret-square-o-right","caret-square-o-up","certificate","check","check-circle","check-circle-o","check-square","check-square-o","circle","circle-o","clock-o","cloud","cloud-download","cloud-upload","code","code-fork","coffee","cog","cogs","comment","comment-o","comments","comments-o","compass","credit-card","crop","crosshairs","cutlery","dashboard","desktop","dot-circle-o","download","edit","ellipsis-h","ellipsis-v","envelope","envelope-o","eraser","exchange","exclamation","exclamation-circle","exclamation-triangle","external-link","external-link-square","eye","eye-slash","female","fighter-jet","film","filter","fire","fire-extinguisher","flag","flag-checkered","flag-o","flash","flask","folder","folder-o","folder-open","folder-open-o","frown-o","gamepad","gavel","gear","gears","gift","glass","globe","group","hdd-o","headphones","heart","heart-o","home","inbox","info","info-circle","key","keyboard-o","laptop","leaf","legal","lemon-o","level-down","level-up","lightbulb-o","location-arrow","lock","magic","magnet","mail-forward","mail-reply","mail-reply-all","male","map-marker","meh-o","microphone","microphone-slash","minus","minus-circle","minus-square","minus-square-o","mobile","mobile-phone","money","moon-o","music","pencil","pencil-square","pencil-square-o","phone","phone-square","picture-o","plane","plus","plus-circle","plus-square","plus-square-o","power-off","print","puzzle-piece","qrcode","question","question-circle","quote-left","quote-right","random","refresh","reply","reply-all","retweet","road","rocket","rss","rss-square","search","search-minus","search-plus","share","share-square","share-square-o","shield","shopping-cart","sign-in","sign-out","signal","sitemap","smile-o","sort","sort-alpha-asc","sort-alpha-desc","sort-amount-asc","sort-amount-desc","sort-asc","sort-desc","sort-down","sort-numeric-asc","sort-numeric-desc","sort-up","spinner","square","square-o","star","star-half","star-half-empty","star-half-full","star-half-o","star-o","subscript","suitcase","sun-o","superscript","tablet","tachometer","tag","tags","tasks","terminal","thumb-tack","thumbs-down","thumbs-o-down","thumbs-o-up","thumbs-up","ticket","times","times-circle","times-circle-o","tint","toggle-down","toggle-left","toggle-right","toggle-up","trash-o","trophy","truck","umbrella","unlock","unlock-alt","unsorted","upload","user","users","video-camera","volume-down","volume-off","volume-up","warning","wheelchair","wrench", "check-square","check-square-o","circle","circle-o","dot-circle-o","minus-square","minus-square-o","plus-square","plus-square-o","square","square-o","bitcoin","btc","cny","dollar","eur","euro","gbp","inr","jpy","krw","money","rmb","rouble","rub","ruble","rupee","try","turkish-lira","usd","won","yen","align-center","align-justify","align-left","align-right","bold","chain","chain-broken","clipboard","columns","copy","cut","dedent","eraser","file","file-o","file-text","file-text-o","files-o","floppy-o","font","indent","italic","link","list","list-alt","list-ol","list-ul","outdent","paperclip","paste","repeat","rotate-left","rotate-right","save","scissors","strikethrough","table","text-height","text-width","th","th-large","th-list","underline","undo","unlink","angle-double-down","angle-double-left","angle-double-right","angle-double-up","angle-down","angle-left","angle-right","angle-up","arrow-circle-down","arrow-circle-left","arrow-circle-o-down","arrow-circle-o-left","arrow-circle-o-right","arrow-circle-o-up","arrow-circle-right","arrow-circle-up","arrow-down","arrow-left","arrow-right","arrow-up","arrows","arrows-alt","arrows-h","arrows-v","caret-down","caret-left","caret-right","caret-square-o-down","caret-square-o-left","caret-square-o-right","caret-square-o-up","caret-up","chevron-circle-down","chevron-circle-left","chevron-circle-right","chevron-circle-up","chevron-down","chevron-left","chevron-right","chevron-up","hand-o-down","hand-o-left","hand-o-right","hand-o-up","long-arrow-down","long-arrow-left","long-arrow-right","long-arrow-up","toggle-down","toggle-left","toggle-right","toggle-up", "angle-double-down","angle-double-left","angle-double-right","angle-double-up","angle-down","angle-left","angle-right","angle-up","arrow-circle-down","arrow-circle-left","arrow-circle-o-down","arrow-circle-o-left","arrow-circle-o-right","arrow-circle-o-up","arrow-circle-right","arrow-circle-up","arrow-down","arrow-left","arrow-right","arrow-up","arrows","arrows-alt","arrows-h","arrows-v","caret-down","caret-left","caret-right","caret-square-o-down","caret-square-o-left","caret-square-o-right","caret-square-o-up","caret-up","chevron-circle-down","chevron-circle-left","chevron-circle-right","chevron-circle-up","chevron-down","chevron-left","chevron-right","chevron-up","hand-o-down","hand-o-left","hand-o-right","hand-o-up","long-arrow-down","long-arrow-left","long-arrow-right","long-arrow-up","toggle-down","toggle-left","toggle-right","toggle-up","adn","android","apple","bitbucket","bitbucket-square","bitcoin","btc","css3","dribbble","dropbox","facebook","facebook-square","flickr","foursquare","github","github-alt","github-square","gittip","google-plus","google-plus-square","html5","instagram","linkedin","linkedin-square","linux","maxcdn","pagelines","pinterest","pinterest-square","renren","skype","stack-exchange","stack-overflow","trello","tumblr","tumblr-square","twitter","twitter-square","vimeo-square","vk","weibo","windows","xing","xing-square","youtube","youtube-play","youtube-square","ambulance","h-square","hospital-o","medkit","plus-square","stethoscope","user-md","wheelchair");
        return array_unique($iconsArray);

    }
}



if(!function_exists('vc_icon_form_field')) {
    function vc_icon_form_field($settings, $value) {
        $settings_line = '';
        $selected = '';
        $array = et_get_icons();
        if($value != '') {
            $array = array_diff($array, array($value));
            array_unshift($array,$value);
        }

        $settings_line .= '<div class="et-icon-selector">';
        $settings_line .= '<input type="hidden" value="'.$value.'" name="'.$settings['param_name'].'" class="et-hidden-icon wpb_vc_param_value wpb-icon-select '.$settings['param_name'].' '.$settings['type'] . '">';
            foreach ($array as $icon) {
                if ($value == $icon) {
                    $selected = 'selected';
                }
                $settings_line .= '<span class="et-select-icon '.$selected.'" data-icon-name='.$icon.'><i class="fa fa-'.$icon.'"></i></span>';
                $selected = '';
            }

        $settings_line .= '<script>';
        $settings_line .= 'jQuery(".et-select-icon").click(function(){';
            $settings_line .= 'var iconName = jQuery(this).data("icon-name");';
            $settings_line .= 'console.log(iconName);';
            $settings_line .= 'if(!jQuery(this).hasClass("selected")) {';
                $settings_line .= 'jQuery(".et-select-icon").removeClass("selected");';
                $settings_line .= 'jQuery(this).addClass("selected");';
                $settings_line .= 'jQuery(this).parent().find(".et-hidden-icon").val(iconName);';
            $settings_line .= '}';

        $settings_line .= '});';
        $settings_line .= '</script>';

        $settings_line .= '</div>';
        return $settings_line;
    }
}

if (! function_exists('et_is_woo_exists')) :
/**
 *
 * Chack if WooCommerce enable.
 *
 */
    function et_is_woo_exists() {
        return class_exists( 'WooCommerce' );
    }
endif;


if ( ! function_exists( 'et_page_heading' ) ) {
/**
 *
 * Output theme heading.
 *
 */
   function et_page_heading(){
    $bk_style = etheme_get_option('breadcrumb_bg');

$style = '';
$bk_color = '';

if (!empty($bk_style['background-color'])) {
    $style .= 'background-color:' . $bk_style['background-color'] . '; ';
    $bk_color .= ' style="' . $style . '"';
}

if (!empty($bk_style['background-repeat'])) {
    $style .= 'background-repeat:' . $bk_style['background-repeat'] . '; ';
}

if (!empty($bk_style['background-attachment'])) {
    $style .= 'background-attachment:' . $bk_style['background-attachment'] . '; ';
}

if (!empty($bk_style['background-position'])) {
    $style .= 'background-position:' . $bk_style['background-position'] . '; ';
}

if(!empty($bk_style['background-image'])) {
    $style .= 'background-image: url(' . $bk_style['background-image'] . '); ';
}

?>
<div class="page-heading bc-type-<?php etheme_option('breadcrumb_type'); ?> " <?php echo 'style="'.$style.'"'; ?>>
        <?php echo '<div class="container"' . $bk_color . '>'; ?>

            <div class="row-fluid">
                <div class="span12 a-center">

                    <?php if (et_is_woo_exists()&&(is_woocommerce()||is_cart()||is_checkout()||is_product_category()||is_product_tag())): ?>

                        <?php if ( apply_filters( 'woocommerce_show_page_title', true ) ) : ?>

                            <?php if ( is_shop() || is_product_category() || is_product_tag() || is_tax() ): ?>
                                <?php echo '<h1 class="title"><span' . $bk_color . '>';
                                    woocommerce_page_title();
                                echo '</span></h1>'; ?>
                            <?php else: ?>
                                <?php echo '<h1 class="title product_title"><span' . $bk_color .'>';
                                    the_title(); ?>
                                <?php echo '</span></h1>'; ?>
                            <?php endif; ?>

                        <?php endif; ?>
                            <?php
                                /**
                                 * woocommerce_before_main_content hook
                                 *
                                 * @hooked woocommerce_output_content_wrapper - 10 (outputs opening divs for the content)
                                 * @hooked woocommerce_breadcrumb - 20
                                 */
                                do_action('woocommerce_before_main_content');
                            ?>

                    <?php else: ?>

                        <h1 class="title">
                            <?php echo '<span' . $bk_color . '>'; ?>
                                <?php
                                    if( is_home() && get_option( 'page_for_posts' ) ) {
                                        $postspage_id = get_option( 'page_for_posts' );
                                        echo get_the_title($postspage_id);
                                    } elseif ( is_search() ) {
                                        echo get_search_query();
                                    } elseif ( is_category() ) {
                                        $cat = get_category(get_query_var( 'cat' ), false);
                                        echo esc_html($cat->name);
                                    } elseif ( is_tag() ) {
                                        echo single_tag_title();
                                    } elseif ( is_author() ) {
                                        echo get_the_author();
                                    } elseif ( is_year() ) {
                                        echo get_the_time( 'Y' );
                                    } elseif ( is_month() ) {
                                        echo get_the_time( 'Y - F' );
                                    } elseif ( is_day() ) {
                                        echo get_the_time( 'Y - F - d' );
                                    } elseif ( is_404() ){
                                        esc_html_e( 'Not found', 'legenda' );
                                    } else {
                                        the_title();
                                    }
                                 ?>
                            </span>
                        </h1>
                            <?php etheme_breadcrumbs(); ?>
                    <?php endif ?>

                </div>
            </div>
        </div>
    </div>
<?php
   }
}


if ( ! function_exists( 'et_excerpt_length' )):
/**
 *
 * Change excerpt length.
 *
 */
function et_excerpt_length() {
 return etheme_get_option('excerpt_length');
}
add_filter( 'excerpt_length', 'et_excerpt_length', 999 );

endif;

if (!function_exists('et_add_mce_button')) :
/**
 *
 * Add shortcodes to MCE.
 *
 */

function et_add_mce_button() {
    if ( !current_user_can( 'edit_posts' ) && !current_user_can( 'edit_pages' ) ) {
        return;
    }
    if ( 'true' == get_user_option( 'rich_editing' ) ) {
        add_filter( 'mce_external_plugins', 'et_add_tinymce_plugin' );
        add_filter( 'mce_buttons', 'et_register_mce_button' );
    }
}
add_action('admin_head', 'et_add_mce_button');
endif;


if (!function_exists('et_add_tinymce_plugin')) :
/**
 *
 * Declare script for new button.
 *
 */

function et_add_tinymce_plugin( $plugin_array ) {
    $plugin_array['et_mce_button'] = get_template_directory_uri() .'/framework/thirdparty/mce/mce.js';
    return $plugin_array;
}
endif;


if (!function_exists('et_register_mce_button')) :
/**
 *
 * Register new button in the editor.
 *
 */

function et_register_mce_button( $buttons ) {
    array_push( $buttons, 'et_mce_button' );
    return $buttons;
}
endif;

// **********************************************************************//
// ! Search form popup
// **********************************************************************//

add_action('after_page_wrapper', 'etheme_search_form_modal');
if(!function_exists('etheme_search_form_modal')) {
    function etheme_search_form_modal() {
        ?>
            <div id="searchModal" class="mfp-hide modal-type-1 zoom-anim-dialog" role="search">
                <div class="modal-dialog text-center">
                    <h3 class="large-h"><?php esc_html_e('Search', 'legenda'); ?></h3>
                    <small class="mini-text"><?php esc_html_e('Use the search box to find the product you are looking for.', 'legenda'); ?></small>

                    <?php get_template_part('woosearchform'); ?>

                </div>
            </div>
        <?php
    }
}




// **********************************************************************//
// ! New etheme_get_image function
// **********************************************************************//

if( ! function_exists( 'new_etheme_get_image' ) ) :
	function new_etheme_get_image( $attach_id, $size ) {
		$image = false;
		if ( function_exists( 'wpb_getImageBySize' ) ) {
			$image = wpb_getImageBySize( array(
				'attach_id' => $attach_id,
				'thumb_size' => $size,
			) );
			if (isset($image['thumbnail'])) {
				$image = $image['thumbnail'];
			}
		}

		if (!$image || ! str_contains($image, 'src="http')) {
			$image = wp_get_attachment_image( $attach_id, $size );
		}

		return $image;
	}
endif;


// **********************************************************************//
// ! Get image id from src
// **********************************************************************//

if( ! function_exists( 'et_attach_id_from_src' ) ) :
    function et_attach_id_from_src( $image_src ) {
        global $wpdb;

        $id = $wpdb->get_var(
            $wpdb->prepare( "SELECT ID FROM {$wpdb->posts} WHERE guid = '%s'", $image_src )
        );

        return $id;
    }
endif;


add_action('wp_ajax_etheme_deactivate_theme', 'etheme_deactivate_theme');
if( ! function_exists( 'etheme_deactivate_theme' ) ) :
    function etheme_deactivate_theme() {

        $data = array(
            'api_key' => 0,
            'theme' => 0,
            'purchase' => 0,
        );

        update_option( 'etheme_activated_data', maybe_unserialize( $data ) );

        // update_option( 'etheme_api_key', 0 );
        // update_option( 'etheme_purchase_code', 0 );

    }

endif;


add_action( 'wp_ajax_etheme_save_bulk_edit', 'etheme_save_bulk_edit' );

if( ! function_exists( 'etheme_save_bulk_edit' ) ) :
    function etheme_save_bulk_edit() {

       $post_ids = ( isset( $_POST[ 'post_ids' ] ) && !empty( $_POST[ 'post_ids' ] ) ) ? $_POST[ 'post_ids' ] : array();
       $product_new = ( isset( $_POST[ 'product_new' ] ) && !empty( $_POST[ 'product_new' ] ) ) ? $_POST[ 'product_new' ] : NULL;

       if ( !empty( $post_ids ) && is_array( $post_ids ) && !empty( $product_new ) ) {
          foreach( $post_ids as $post_id ) {
             update_post_meta( (int) $post_id , 'product_new', (string) $product_new );
          }
       }

    }

endif;

// **********************************************************************// 
// ! Wp title
// **********************************************************************// 
if(!function_exists('etheme_wp_title')) {
    function etheme_wp_title($title, $sep ) {
        global $paged, $page;

        if ( is_feed() ) {
            return $title;
        }

        // Add the site name.
        $title .= get_bloginfo( 'name', 'display' );

        // Add the site description for the home/front page.
        $site_description = get_bloginfo( 'description', 'display' );
        if ( $site_description && ( is_home() || is_front_page() ) ) {
            $title = "$title $sep $site_description";
        }

        // Add a page number if necessary.
        if ( ( $paged >= 2 || $page >= 2 ) && ! is_404() ) {
            $title = "$title $sep " . sprintf( esc_html__( 'Page %s', 'legenda' ), max( $paged, $page ) );
        }

        return $title;
    }
    add_filter( 'wp_title', 'etheme_wp_title', 10, 2 );
}


// **********************************************************************// 
// ! Instagram feed
// **********************************************************************// 
// Instagram feed : remove user
add_action('wp_ajax_et_instagram_user_remove', 'et_instagram_user_remove');
if( ! function_exists( 'et_instagram_user_remove' ) ) {
    function et_instagram_user_remove() {
        if ( isset( $_POST['token'] ) && $_POST['token'] ) {
            $api_data = get_option( 'etheme_instagram_api_data' );
            $api_data = json_decode($api_data, true);

            if ( isset($api_data[$_POST['token']]) ) {
                unset($api_data[$_POST['token']]);
                update_option('etheme_instagram_api_data',json_encode($api_data));
                echo "success";
                die();
            }
            echo "this token is not exist";
            die();
        }
        echo "empty token";
        die();
    }
}

// Instagram feed : save settings
add_action('wp_ajax_et_instagram_save_settings', 'et_instagram_save_settings');
if( ! function_exists( 'et_instagram_save_settings' ) ) {
    function et_instagram_save_settings() {
        if ( isset( $_POST['time'] ) && isset( $_POST['time_type'] ) ) {
            $api_settings = get_option( 'etheme_instagram_api_settings' );
            $api_settings = json_decode($api_settings, true);

            $api_settings['time']      = ( $_POST['time'] && $_POST['time'] != 0 && $_POST['time'] !== '0' ) ? $_POST['time'] : 2;
            $api_settings['time_type'] = $_POST['time_type'];

            update_option('etheme_instagram_api_settings',json_encode($api_settings));
            echo "success";
            die();
        }
        echo "some data is not provided";
        die();
    }
}

// Instagram feed : add user
add_action('wp_ajax_et_instagram_user_add', 'et_instagram_user_add');
if( ! function_exists( 'et_instagram_user_add' ) ) {
    function et_instagram_user_add() {
        if ( isset( $_POST['token'] ) && $_POST['token'] ) {
            $user_url = 'https://api.instagram.com/v1/users/self/?access_token=' . $_POST['token'];
            $api_data = get_option( 'etheme_instagram_api_data' );
            $api_data = json_decode($api_data, true);

            if ( ! is_array( $api_data ) ) {
                $api_data = array();
            }

            $user_data = wp_remote_get($user_url);

            if ( ! $user_data ) {
                echo "Unable to communicate with Instagram";
                die();
            }

            if ( is_wp_error( $user_data ) ) {
                echo esc_html__( 'Unable to communicate with Instagram.', 'legenda' );
                die();
            }

            if ( 200 !== wp_remote_retrieve_response_code( $user_data ) ) {
                echo esc_html__( 'Instagram did not return a 200.', 'legenda' );
                die();
            }

            $user_data = wp_remote_retrieve_body( $user_data );

            if ( ! isset( $api_data[$_POST['token']] ) ) {
                $api_data[$_POST['token']] = $user_data;
            } else {
                echo "this token already exist";
                die();
            }

            update_option('etheme_instagram_api_data',json_encode($api_data));

            echo "success";
            die();
        }
        echo "empty token";
        die();
    }
}

if (get_option('old_widgets_panel_type')){
	add_filter( 'gutenberg_use_widgets_block_editor', '__return_false' );
	add_filter( 'use_widgets_block_editor', '__return_false' );
}

function etheme_woo_version_check($required = '7.0.1') {
	return defined('WC_VERSION') && version_compare( WC_VERSION, $required, '>=' );
}

if (! function_exists('et_remove_html_comments')){
	function et_remove_html_comments($content = '') {
		return preg_replace('/<!--(.|\s)*?-->/', '', $content);
	}
}