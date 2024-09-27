<?php
// Exit if accessed directly
use WpAssetCleanUp\Main;

if (! defined('WPACU_PRO_CLASSES_PATH')) {
    exit;
}

add_action('init', function() {
    $triggerIf = \WpAssetCleanUp\Menu::userCanAccessAssetCleanUp() && (is_admin() || \WpAssetCleanUp\AssetsManager::instance()->frontendShow());

    // Load the classes and its actions only when the user is an admin
    // and the admin is within the /wp-admin/ area or when is visiting the main website and CSS/JS manager is loaded at the bottom of the page
	if ($triggerIf) {
		new WpAssetCleanUpPro\OutputPro();

        $updateAdminPro = new \WpAssetCleanUpPro\Admin\UpdatePro();
        $updateAdminPro->init();
    }
});

// Add a condition to avoid loading the class if it's not needed (e.g. the homepage on a root installation)

// Trigger this outside the site's main URL (home page) as it is meant to trigger on the following pages:
// Author page (individual, not for all authors) | 404 `Not Found` Page (any URL) | Date Page | Archive custom post type
if (isset($_SERVER['REQUEST_URI']) && $_SERVER['REQUEST_URI'] !== '/') {
    $compareOne = parse_url(get_site_url(), PHP_URL_PATH);
    $compareOne = $compareOne ? rtrim($compareOne, '/') : $compareOne;
    $compareTwo = $_SERVER['REQUEST_URI'] ? rtrim($_SERVER['REQUEST_URI'], '/') : $_SERVER['REQUEST_URI'];

    if ( $compareOne !== $compareTwo ) {
        $exceptionsPro = new \WpAssetCleanUpPro\LoadExceptionsPro();
        $exceptionsPro->init();
    }
}

$wpacuMainPro = new \WpAssetCleanUpPro\MainPro();
$wpacuMainPro->init();

// [CSS/JS Manager]
if (is_admin()) {
    // Manage in the Dashboard (default)
    $wpacuMainAdminPro = new \WpAssetCleanUpPro\Admin\MainAdminPro();
    $wpacuMainAdminPro->init();
} else {
    // Manage in the front-end (bottom of the page)
    add_action('wp', function () {
        $isFrontEndEditView  = Main::instance()->isFrontendEditView;
        $isDashboardEditView = ( ! $isFrontEndEditView && Main::instance()->isGetAssetsCall );

        if ($isDashboardEditView || $isFrontEndEditView) {
            $wpacuMainAdminPro = new \WpAssetCleanUpPro\Admin\MainAdminPro();
            $wpacuMainAdminPro->init();
        }
    });
}
// [/CSS/JS Manager]

if (is_admin()) {
    new \WpAssetCleanUpPro\Admin\PluginsManagerPro();

    $wpacuLicensePro = new \WpAssetCleanUpPro\Admin\LicensePro();
    $wpacuLicensePro->init();

    $wpacuPluginPro = new \WpAssetCleanUpPro\Admin\PluginPro();
    $wpacuPluginPro->init();
}

if ( ! is_admin() && ! wpacuIsDefinedConstant('WPACU_ALLOW_ONLY_UNLOAD_RULES') ) {
	$optimizeCssPro = new \WpAssetCleanUpPro\OptimiseAssets\OptimizeCssPro();
	$optimizeCssPro->init();

    if ( (isset($_GET['wpacu_preload_css_async']) && $_GET['wpacu_preload_css_async']) || ! wpacuIsDefinedConstant('WPACU_NO_ASSETS_PRELOADED') ) {
        $wpacuPreloadsPro = new \WpAssetCleanUpPro\PreloadsPro();
        $wpacuPreloadsPro->init();
    }
}

// Update the premium plugin within the Dashboard similar to other plugins from WordPress.org
include_once WPACU_PRO_DIR . '/wpacu-pro-updater.php';
