<?php

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
	die;
}

//-----------------------------------------------------
// Chargement des fontes locales de remplacement et des tweaks CSS
//-----------------------------------------------------

add_action('wp_enqueue_scripts', 'awp_enqueue_optimization_css', 1);
function awp_enqueue_optimization_css()
{
    wp_enqueue_style('fonts-open-sans', get_stylesheet_directory_uri() . '/css/font-open-sans.css', array());
    wp_enqueue_style('tweaks', get_stylesheet_directory_uri() . '/css/tweaks.css', array());
}

//-----------------------------------------------------
// Pas de nouveau thème par défaut chaque année
//-----------------------------------------------------

if (!defined('CORE_UPGRADE_SKIP_NEW_BUNDLED')) {
    define('CORE_UPGRADE_SKIP_NEW_BUNDLED', true);
}

//-----------------------------------------------------
// On supprime les filtres SVG ajoutés par WordPress (depuis 5.9)
//-----------------------------------------------------

add_action('init', 'awp_disable_svg_filters');
function awp_disable_svg_filters()
{
    remove_action('wp_body_open', 'wp_global_styles_render_svg_filters');
}

//-----------------------------------------------------
// On supprime les global styles ajoutés par WordPress (depuis 5.9)
//-----------------------------------------------------

add_action('init', 'awp_disable_global_styles');
function awp_disable_global_styles()
{
    remove_action( 'wp_enqueue_scripts', 'wp_enqueue_global_styles' );
}

//-----------------------------------------------------
// On ne charge que le CSS des blocs Gutenberg utilisés
//-----------------------------------------------------
if (strpos($_SERVER['REQUEST_URI'], '/bon-plan/') === false) {
	add_filter('should_load_separate_core_block_assets', '__return_true');
}
//-----------------------------------------------------
// On envoie un maximum de CSS en externe pour le concaténer
//-----------------------------------------------------

add_filter('styles_inline_size_limit', '__return_zero');

//-----------------------------------------------------
// Suppression du link rel=preconnect vers fonts.gstatic.com
//-----------------------------------------------------

add_filter('wp_resource_hints', 'awp_remove_bad_hints', PHP_INT_MAX, 2);
function awp_remove_bad_hints($urls, $relation_type)
{
    if ('dns-prefetch' !== $relation_type) {
        return $urls;
    }
    return array_filter($urls, function($url) {
        return !str_contains($url, 'fonts.gstatic.com') &&
               !str_contains($url, 'stats.wp.com') &&
               !str_contains($url, 'fonts.googleapis.com');
    });
}

//-----------------------------------------------------
// On supprime la ET Font Open Sans en local TTF
//-----------------------------------------------------

add_filter('wp_enqueue_scripts', 'awp_disable_et_fonts', PHP_INT_MAX);
function awp_disable_et_fonts()
{
    wp_deregister_style('et-fonts');
    wp_dequeue_style('et-fonts');
}

//-----------------------------------------------------
// On supprime le classic theme ajouté par WordPress (depuis 6.1)
//-----------------------------------------------------

add_filter('wp_enqueue_scripts', 'awp_disable_classic_theme_styles', PHP_INT_MAX);
function awp_disable_classic_theme_styles()
{
    wp_deregister_style('classic-theme-styles');
    wp_dequeue_style('classic-theme-styles');
}

//-----------------------------------------------------
// On supprime le FontAwesome de Related Products
//-----------------------------------------------------

add_action('wp_enqueue_scripts', 'awp_dequeue_fa_rp_1', 1);
function awp_dequeue_fa_rp_1()
{
    wp_deregister_script('wt-fa-js');
}

add_action('wp_enqueue_scripts', 'awp_dequeue_fa_rp_2', PHP_INT_MAX);
function awp_dequeue_fa_rp_2()
{
    wp_dequeue_script('wt-fa-js');
}

//-----------------------------------------------------
// Suppression de comment-reply.min.js si pas nécessaire
//-----------------------------------------------------

add_action('wp_enqueue_scripts', 'awp_dequeue_comment_reply', PHP_INT_MAX);
function awp_dequeue_comment_reply()
{
    if (is_singular() && (!comments_open() || !get_option('thread_comments') || !get_comments_number(get_the_ID()))) {
        wp_deregister_script('comment-reply');
    }
}

//-----------------------------------------------------
// Vidéos YouTube sans cookies, et donc non bloquées par certaines CMP
//-----------------------------------------------------

add_filter( 'embed_oembed_html', 'awp_optimize_filter_youtube_embed', 10, 2 );
function awp_optimize_filter_youtube_embed( $cached_html, $url = null ) {
if ( strpos( $url, 'youtu' ) ) {
        $cached_html = preg_replace( '/youtube\.com\/(v|embed)\//s', 'youtube-nocookie.com/$1/', $cached_html );
    }
    return $cached_html;
}

//-----------------------------------------------------
// Suppression du loader asynchrone de Redux
//-----------------------------------------------------

add_action( 'redux/loaded', function( $redux ) { $redux->args['disable_google_fonts_link'] = true; }, 1 );
add_action( 'redux/loaded', function( $redux ) { $redux->args['disable_google_fonts_link'] = true; }, 10 );
add_action( 'redux/loaded', function( $redux ) { $redux->args['disable_google_fonts_link'] = true; }, PHP_INT_MAX );

//-----------------------------------------------------
// Pas de lazy-loading sur certaines images https://docs.wp-rocket.me/article/15-disabling-lazy-load-on-specific-images
//-----------------------------------------------------

add_filter('rocket_lazyload_excluded_attributes', 'awp_wp_rocket_nolazyload');
function awp_wp_rocket_nolazyload($attributes)
{
    $attributes[] = 'no-lazy'; // Old way
    $attributes[] = 'Logo-armoire-plus.svg'; // Main logo
    $attributes[] = 'fetchpriority="high"'; // Native WP addition on first images
    return $attributes;
}