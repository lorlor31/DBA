<?php
namespace WpAssetCleanUpPro\Admin;

use WpAssetCleanUp\Admin\MainAdmin;
use WpAssetCleanUp\Admin\SettingsAdmin;
use WpAssetCleanUp\Main;
use WpAssetCleanUp\Menu;
use WpAssetCleanUp\Misc;
use WpAssetCleanUpPro\MainPro;

/**
 * Class MainAdminPro
 *
 * This class has functions that are only for the admin's concern
 *
 * @package WpAssetCleanUp
 */
class MainAdminPro
{
    /**
     * @return void
     */
    public function init()
    {
        add_filter('wpacu_filter_list_on_dashboard_ajax_call', array($this, 'filterListOnDashboardAjaxCall'));
        add_filter('wpacu_data_var_template',                  array($this, 'filterDataVarTemplate'));
        add_action('current_screen',                           array($this, 'currentScreen'));

        // "async", "defer" attribute changes to show up in the management list
        add_filter('wpacu_pro_get_scripts_attributes_for_each_asset', array($this, 'getScriptsAttributesToPrintInList'));

        add_action( 'wp_ajax_' . WPACU_PLUGIN_ID . '_update_plugin_setting', array( $this, 'ajaxUpdatePluginSetting' ) );

        // Load via an AJAX call the list of all the taxonomies set for a post type
        // They will show only if at least one value is set (e.g. a tag, category) for a post
        // This is to save resources and have a smaller drop-down
        // The admin needs to set the tag/category/any taxonomy first, then use the drop-down
        add_action('wp_ajax_' . WPACU_PLUGIN_ID . '_load_all_set_terms_for_post_type', array($this, 'ajaxLoadAllSetTermsForPostType'), 10, 2);
    }

    /**
     * @return void
     *
     * @noinspection IssetConstructsCanBeMergedInspection
     */
    public function ajaxUpdatePluginSetting()
    {
        if ( ! isset($_POST['wpacu_setting_key']) || ! isset($_POST['wpacu_setting_value']) || ! isset($_POST['action']) ) {
            echo 'Error: The essential elements are missing. Location: '.__METHOD__;
            exit();
        }

        if ( ! isset($_POST['wpacu_nonce']) ) {
            echo 'Error: The security nonce was not sent for verification. Location: '.__METHOD__;
            exit();
        }

        if ( ! wp_verify_nonce($_POST['wpacu_nonce'], 'wpacu_update_plugin_setting_nonce') ) {
            echo 'Error: The security check has failed. Location: '.__METHOD__;
            exit();
        }

        if ( ! Menu::userCanAccessAssetCleanUp() ) {
            echo 'Error: User does not have permission to perform this action. Location: '.__METHOD__;
            exit();
        }

        $settingKey   = sanitize_text_field($_POST['wpacu_setting_key']);
        $settingValue = sanitize_text_field($_POST['wpacu_setting_value']);

        $settingsAdminClass = new SettingsAdmin();
        $settingsAdminClass->updateOption($settingKey, $settingValue);

        echo 'DONE';
        exit();
    }

    /**
     * This is the base64 encoded list printed when /?wpassetcleanup_load=1 is used
     *
     * @param $list
     *
     * @return mixed
     */
    public static function filterListOnDashboardAjaxCall($list)
    {
        // Any unloaded plugins from "Plugins Manager" (to be printed in the CSS/JS manager plugins area)
        $list['unloaded_plugins'] = isset($GLOBALS['wpacu_filtered_plugins']) ? (array)$GLOBALS['wpacu_filtered_plugins'] : array();
        return $list;
    }

    /**
     * This is triggered only for the Dashboard view
     * Processed within the AJAX call ($data should have the "is_dashboard_view" key)
     *
     * @param $varName
     * @param $data
     *
     * @return array|array[]
     */
    public static function filterThisVarDashboardView($varName, $data)
    {
        if ($varName === 'unloadsRegEx') {
            // For the management of the assets in the Dashboard
            MainPro::$unloads['regex'] = MainPro::getRegExRules('unloads');

            // Any RegEx unload matches?
            if ( ! empty( MainPro::$unloads['regex'] ) ) {
                foreach ( MainPro::$unloads['regex'] as $assetType => $wpacuUlValues ) {
                    if ($assetType === '_set') {
                        continue; // irrelevant here
                    }

                    if ( ! empty( $wpacuUlValues ) ) {
                        foreach ( $wpacuUlValues as $wpacuHandle => $wpacuUlValue ) {
                            if ( isset( $wpacuUlValue['enable'], $wpacuUlValue['value'] ) && $wpacuUlValue['enable'] &&
                                 MainPro::isRegExMatch( $wpacuUlValue['value'], $data['fetch_url']) ) {
                                MainPro::$unloads['regex']['current_url_matches'][$assetType][] = $wpacuHandle;
                            }
                        }
                    }
                }
            }

            return MainPro::$unloads['regex'];
        }

        if ($varName === 'loadExceptionsRegEx') {
            MainPro::$loadExceptions['regex'] = MainPro::getRegExRules('load_exceptions');

            // Any load exceptions matches?
            if ( ! empty(MainPro::$loadExceptions['regex']) ) {
                foreach (MainPro::$loadExceptions['regex'] as $assetType => $wpacuLeValues) {
                    if ($assetType === '_set') {
                        continue; // irrelevant here
                    }

                    if (! empty($wpacuLeValues)) {
                        foreach ($wpacuLeValues as $wpacuHandle => $wpacuLeData) {
                            // Needs to be marked as enabled with a value
                            if ( isset( $wpacuLeData['enable'], $wpacuLeData['value'] ) && $wpacuLeData['enable']
                                 && MainPro::isRegExMatch( $wpacuLeData['value'], $data['fetch_url'] ) ) {
                                MainPro::$loadExceptions['regex']['current_url_matches'][$assetType][] = $wpacuHandle;
                            }
                        }
                    }
                }
            }

            return MainPro::$loadExceptions['regex'];
        }

        if ($varName === 'unloadsPostTypeViaTax' && isset($data['post_type'], $data['post_id']) && $data['post_type'] && $data['post_id']) {
            MainPro::$unloads['post_type_via_tax'] = MainPro::getTaxonomyValuesAssocToPostType($data['post_type']);

            if ( ! empty(MainPro::$unloads['post_type_via_tax']) ) {
                $currentPostTerms = MainPro::getTaxonomyTermIdsAssocToPost($data['post_id']);

                if ( ! empty($currentPostTerms) ) {
                    foreach (MainPro::$unloads['post_type_via_tax'] as $assetType => $wpacuUValues) {
                        if ($assetType === '_set') {
                            continue; // irrelevant here
                        }

                        foreach ($wpacuUValues as $assetHandle => $assetData) {
                            if (isset($assetData['enable']) && $assetData['enable'] && ! empty($assetData['values'])) {
                                // Go through the terms set and check if the current post ID is having the taxonomy value associated with it
                                foreach ($assetData['values'] as $termId) {
                                    if (in_array($termId, $currentPostTerms)) {
                                        MainPro::$unloads['post_type_via_tax']['current_post_matches'][$assetType][] = $assetHandle;
                                        break;
                                    }
                                }
                            }
                        }
                    }
                }
            }

            return MainPro::$unloads['post_type_via_tax'];
        }

        if ($varName === 'loadExceptionsPostTypeViaTax' && isset($data['post_id'], $data['post_type']) && $data['post_id'] && $data['post_type']) {
            MainPro::$loadExceptions['post_type_via_tax'] = MainPro::getTaxonomyValuesAssocToPostTypeLoadExceptions($data['post_type']);

            if ( ! empty(MainPro::$loadExceptions['post_type_via_tax']) ) {
                $currentPostTerms = MainPro::getTaxonomyTermIdsAssocToPost($data['post_id']);

                if ( ! empty($currentPostTerms)) {
                    foreach (MainPro::$loadExceptions['post_type_via_tax'] as $assetType => $wpacuLeValues) {
                        if ($assetType === '_set') {
                            continue; // irrelevant here
                        }

                        foreach ($wpacuLeValues as $assetHandle => $assetData) {
                            if (isset($assetData['enable']) && $assetData['enable'] && ! empty($assetData['values'])) {
                                // Go through the terms set and check if the current post ID is having the taxonomy value associated with it
                                foreach ($assetData['values'] as $termId) {
                                    if (in_array($termId, $currentPostTerms)) {
                                        MainPro::$loadExceptions['post_type_via_tax']['current_post_matches'][$assetType][] = $assetHandle;
                                        break;
                                    }
                                }
                            }
                        }
                    }
                }
            }

            return MainPro::$loadExceptions['post_type_via_tax'];
        }

        if ($varName === 'unloadsViaTaxType' && isset($data['tax_name']) && $data['tax_name']) {
            MainPro::$unloads['tax'] = Main::instance()->getBulkUnload( 'taxonomy', $data['tax_name'] );

            return MainPro::$unloads['tax'];
        }

        if ($varName === 'loadExceptionsViaTaxType' && isset($data['tax_name']) && $data['tax_name']) {
            MainPro::$loadExceptions['tax'] = MainPro::getLoadExceptionsViaTaxType($data['tax_name']);

            return MainPro::$loadExceptions['tax'];
        }

        return array();
    }

    /**
     * @param $data
     *
     * @return mixed
     */
    public function filterDataVarTemplate($data)
    {
        if (isset($data['is_dashboard_view']) && $data['is_dashboard_view']) {
            MainPro::$unloads['regex']        = self::filterThisVarDashboardView('unloadsRegEx', $data); // Any RegEx unload matches?
            MainPro::$loadExceptions['regex'] = self::filterThisVarDashboardView('loadExceptionsRegEx', $data); // Any RegEx load exceptions matches?

            MainPro::$unloads['post_type_via_tax']        = self::filterThisVarDashboardView('unloadsPostTypeViaTax', $data); // Any post type via tax unload matches?
            MainPro::$loadExceptions['post_type_via_tax'] = self::filterThisVarDashboardView('loadExceptionsPostTypeViaTax', $data); // Any post type via tax load exception matches for current post?

            MainPro::$unloads['tax']        = self::filterThisVarDashboardView('unloadsViaTaxType', $data); // Any unloading on all pages of a specific taxonomy type?
            MainPro::$loadExceptions['tax'] = self::filterThisVarDashboardView('loadExceptionsViaTaxType', $data); // Any load exceptions on all pages of a specific taxonomy type?
        }

        /*
         * [START] Any matches for the current page?
         */
        $data['unloads_regex_matches'] = array(
            'styles'  => (isset(MainPro::$unloads['regex']['current_url_matches']['styles'])  ? MainPro::$unloads['regex']['current_url_matches']['styles']  : array()),
            'scripts' => (isset(MainPro::$unloads['regex']['current_url_matches']['scripts']) ? MainPro::$unloads['regex']['current_url_matches']['scripts'] : array())
        );

        $data['load_exceptions_regex_matches'] = array(
            'styles'  => (isset(MainPro::$loadExceptions['regex']['current_url_matches']['styles'])  ? MainPro::$loadExceptions['regex']['current_url_matches']['styles']  : array()),
            'scripts' => (isset(MainPro::$loadExceptions['regex']['current_url_matches']['scripts']) ? MainPro::$loadExceptions['regex']['current_url_matches']['scripts'] : array())
        );

        $data['unloads_post_type_via_tax_matches'] = array(
            'styles'  => (isset(MainPro::$unloads['post_type_via_tax']['current_post_matches']['styles'])  ? MainPro::$unloads['post_type_via_tax']['current_post_matches']['styles']  : array()),
            'scripts' => (isset(MainPro::$unloads['post_type_via_tax']['current_post_matches']['scripts']) ? MainPro::$unloads['post_type_via_tax']['current_post_matches']['scripts'] : array())
        );

        $data['load_exceptions_post_type_via_tax_matches'] = array(
            'styles'  => (isset(MainPro::$loadExceptions['post_type_via_tax']['current_post_matches']['styles'])  ? MainPro::$loadExceptions['post_type_via_tax']['current_post_matches']['styles']  : array()),
            'scripts' => (isset(MainPro::$loadExceptions['post_type_via_tax']['current_post_matches']['scripts']) ? MainPro::$loadExceptions['post_type_via_tax']['current_post_matches']['scripts'] : array())
        );


        $data['unloads_via_tax_type_matches'] = array(
            'styles'  => (isset(MainPro::$unloads['tax']['styles'])  ? MainPro::$unloads['tax']['styles']  : array()),
            'scripts' => (isset(MainPro::$unloads['tax']['scripts']) ? MainPro::$unloads['tax']['scripts'] : array())
        );

        $data['load_exceptions_via_tax_type_matches'] = array(
            'styles'  => (isset(MainPro::$loadExceptions['tax']['styles'])  ? MainPro::$loadExceptions['tax']['styles']  : array()),
            'scripts' => (isset(MainPro::$loadExceptions['tax']['scripts']) ? MainPro::$loadExceptions['tax']['scripts'] : array())
        );

        if ( ! is_admin() && is_author() ) {
            $data['unloads_via_author_type_matches'] = array(
                'styles'  => (isset(MainPro::$unloads['author']['styles']) ? MainPro::$unloads['author']['styles'] : array()),
                'scripts' => (isset(MainPro::$unloads['author']['scripts']) ? MainPro::$unloads['author']['scripts'] : array())
            );

            $data['load_exceptions_via_author_type_matches'] = array(
                'styles'  => (isset(MainPro::$loadExceptions['author']['styles']) ? MainPro::$loadExceptions['author']['styles'] : array()),
                'scripts' => (isset(MainPro::$loadExceptions['author']['scripts']) ? MainPro::$loadExceptions['author']['scripts'] : array())
            );
        }
        /*
         * [END] Any matches for the current page?
         */

        $data['handle_unload_regex'] = MainPro::$unloads['regex'];
        $data['handle_load_regex']   = MainPro::$loadExceptions['regex'];

        // Only when the CSS/JS manager loads for pages, posts and custom post types
        if (isset($data['post_type']) && $data['post_type']) {
            $data['handle_unload_post_type_via_tax'] = MainPro::$unloads['post_type_via_tax'];
            $data['handle_load_post_type_via_tax']   = MainPro::$loadExceptions['post_type_via_tax'];
        }

        if (isset($data['tax_name']) && $data['tax_name']) {
            $data['handle_unload_via_tax'] = MainPro::$unloads['tax'];
            $data['handle_via_tax']        = MainPro::$loadExceptions['tax'];
        }

        if (wpacuIsDefinedConstant('WPACU_NO_MEDIA_QUERIES_LOAD_RULES_SET_FOR_ASSETS')) {
            // Already detected as empty, save extra calls
            $data['media_queries_load'] = MainPro::$mediaQueryLoad;
        } else {
            $data['media_queries_load'] = MainPro::getMediaQueriesLoad();
        }

        // "On this page", "Everywhere", "Not on this page (exception)" list
        $data = apply_filters('wpacu_pro_get_scripts_attributes_for_each_asset', $data);

        // Pull the other bulk unloading such as 'taxonomy' and 'author' pages
        $data = apply_filters('wpacu_pro_get_bulk_unloads', $data);

        if ( ! empty($data['all']['unloaded_plugins']) ) {
            $GLOBALS['wpacu_filtered_plugins'] = (array)$data['all']['unloaded_plugins'];
        }

        return $data;
    }

    /**
     *
     */
    public function currentScreen()
    {
        // Do not show it if 'Hide "Asset CleanUp Pro: CSS & JavaScript Manager" meta box' is checked in 'Settings' -> 'Plugin Usage Preferences'
        // Or if the user has no right to view this (e.g. an editor that does not have admin rights, thus no business with any of the plugin's settings)
        if ( ! Main::instance()->settings['show_assets_meta_box'] || ! Menu::userCanAccessAssetCleanUp() ) {
            return;
        }

        $current_screen = \get_current_screen();

        if ($current_screen->base === 'term' && isset($current_screen->taxonomy) && $current_screen->taxonomy !== '') {
            add_action('admin_head', static function() {
                // Make the CSS/JS List larger
                ?>
                <style data-wpacu-admin-inline-css="1" <?php echo Misc::getStyleTypeAttribute(); ?>>
                    #edittag {
                        max-width: 96%;
                    }
                    tr.form-field[class*="term-"] > th {
                        width: 200px;
                    }
                    tr.form-field[class*="term-"] > td > * {
                        max-width: 550px;
                    }
                </style>
                <?php
            }, PHP_INT_MAX);

            add_action ($current_screen->taxonomy . '_edit_form_fields', static function ($tag) {
                if (! Main::instance()->settings['dashboard_show']) {
                    ?>
                    <tr class="form-field">
                        <th scope="row" valign="top"><label for="wpassetcleanup_list"><?php echo WPACU_PLUGIN_TITLE; ?>: CSS &amp; JavaScript Manager</label></th>
                        <td><?php echo sprintf(__('"Manage in the Dashboard?" is not enabled in the plugin\'s "%sSettings%s", thus, the list is not available.', 'wp-asset-clean-up'), '<a href="'.esc_url(admin_url('admin.php?page=wpassetcleanup_settings')).'">', '</a>'); ?></td>
                    </tr>
                    <?php
                    return;
                }
                $domGetType = Main::instance()->settings['dom_get_type'];
                $fetchAssetsOnClick = Main::instance()->settings['assets_list_show_status'] === 'fetch_on_click';
                ?>
                <tr class="form-field">
                    <th scope="row" valign="top"><label for="wpassetcleanup_list"><?php echo WPACU_PLUGIN_TITLE; ?>: CSS &amp; JavaScript Manager</label></th>
                    <td data-wpacu-taxonomy="<?php echo esc_attr($tag->taxonomy); ?>">
                        <?php
                        $targetUrl = get_term_link($tag, $tag->taxonomy);

                        if (assetCleanUpHasNoLoadMatches($targetUrl)) {
                            $parseUrl = parse_url($targetUrl);
                            $rootUrl = $parseUrl['scheme'].'://'.$parseUrl['host'];
                            $targetUri = str_replace( $rootUrl, '', $targetUrl );
                            ?>
                            <p class="wpacu_verified">
                                <strong>Target URL:</strong> <a target="_blank" href="<?php echo esc_url($targetUrl); ?>"><span><?php echo esc_url($targetUrl); ?></span></a>
                            </p>
                            <?php
                            $msg = sprintf(__('This taxonomy\'s URI <em>%s</em> is matched by one of the RegEx rules you have in <strong>"Settings"</strong> -&gt; <strong>"Plugin Usage Preferences"</strong> -&gt; <strong>"Do not load the plugin on certain pages"</strong>, thus %s is not loaded on that page and no CSS/JS are to be managed. If you wish to view the CSS/JS manager, please remove the matching RegEx rule and reload this page.', 'wp-asset-clean-up-pro'), $targetUri, WPACU_PLUGIN_TITLE);
                            ?>
                            <p class="wpacu-warning" style="margin: 15px 0 0; padding: 10px; font-size: inherit; width: 99%;">
                                <span style="color: red;" class="dashicons dashicons-info"></span> <?php echo wp_kses($msg, array('em' => array(), 'strong' => array())); ?>
                            </p>
                            <?php
                        } else {
                            ?>
                            <input type="hidden"
                                   id="wpacu_ajax_fetch_assets_list_dashboard_view"
                                   name="wpacu_ajax_fetch_assets_list_dashboard_view"
                                   value="1" />
                            <?php
                            if ($fetchAssetsOnClick) {
                                ?>
                                <a style="margin: 10px 0; height: 34px; padding: 2px 16px 1px;" href="#" class="button button-secondary" id="wpacu_ajax_fetch_on_click_btn"><span style="font-size: 22px; vertical-align: middle;" class="dashicons dashicons-download"></span>&nbsp;Fetch CSS &amp; JavaScript Management List</a>
                                <?php
                            }
                            ?>
                            <div id="wpacu_fetching_assets_list_wrap" <?php if ($fetchAssetsOnClick) { echo 'style="display: none;"'; } ?>>
                                <div id="wpacu_meta_box_content">
                                    <?php
                                    if ($domGetType === 'direct') {
                                        $wpacuDefaultFetchListStepDefaultStatus   = '<img src="'.esc_url(admin_url('images/spinner.gif')).'" align="top" width="20" height="20" alt="" />&nbsp; Please wait...';
                                        $wpacuDefaultFetchListStepCompletedStatus = '<span style="color: green;" class="dashicons dashicons-yes-alt"></span> Completed';
                                        ?>
                                        <div id="wpacu-list-step-default-status" style="display: none;"><?php echo wp_kses($wpacuDefaultFetchListStepDefaultStatus, array('img' => array('src' => array(), 'align' => array(), 'width' => array(), 'height' => array(), 'alt' => array()))); ?></div>
                                        <div id="wpacu-list-step-completed-status" style="display: none;"><?php echo wp_kses($wpacuDefaultFetchListStepCompletedStatus, array('span' => array('style' => array(), 'class' => array()))); ?></div>
                                        <div>
                                            <ul class="wpacu_meta_box_content_fetch_steps">
                                                <li id="wpacu-fetch-list-step-1-wrap"><strong>Step 1</strong>: <?php echo sprintf(__('Fetch the assets from <strong>%s</strong>', 'wp-asset-clean-up'), $targetUrl); ?>... <span id="wpacu-fetch-list-step-1-status"><?php echo wp_kses($wpacuDefaultFetchListStepDefaultStatus, array('img' => array('src' => array(), 'align' => array(), 'width' => array(), 'height' => array(), 'alt' => array()))); ?></span></li>
                                                <li id="wpacu-fetch-list-step-2-wrap"><strong>Step 2</strong>: Build the list of the fetched assets and print it... <span id="wpacu-fetch-list-step-2-status"></span></li>
                                            </ul>
                                        </div>
                                    <?php } else { ?>
                                        <div style="margin: 18px 0;">
                                            <img src="<?php echo esc_url(admin_url('images/spinner.gif')); ?>" align="top" width="20" height="20" alt="" />&nbsp;
                                            <?php echo sprintf(__('Fetching the loaded scripts and styles for <strong>%s</strong>... Please wait...', 'wp-asset-clean-up'), $targetUrl); ?>
                                        </div>
                                    <?php } ?>

                                    <hr>
                                    <div style="margin-top: 20px;">
                                        <strong>Is the fetching taking too long? Please do the following:</strong>
                                        <ul style="margin-top: 8px; margin-left: 20px; padding: 0; list-style: disc;">
                                            <li>Check your internet connection and the actual page that is being fetched to see if it loads completely.</li>
                                            <li>If the targeted page loads fine and your internet connection is working fine, please try managing the assets in the front-end view by going to <em>"Settings" -&gt; "Plugin Usage Preferences" -&gt; "Manage in the Front-end"</em></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        <?php
                        }
                        ?>
                    </td>
                </tr>
                <?php
            });
        }
    }

    /**
     * This fetches the list of applied attributes (defer, async) that will be used
     * on the scripts management list
     *
     * @param $data
     *
     * @return mixed
     */
    public function getScriptsAttributesToPrintInList($data)
    {
        $mainProClass = new MainPro();

        // Dashboard view? Fetch the attributes as it's on AJAX mode view (via Dashboard)
        if (! Main::instance()->isFrontendEditView) {
            $mainProClass->scriptsAttrsToApplyOnCurrentPage = $mainProClass->getScriptAttributesToApplyOnCurrentPage($data);
        }

        // If on front-end view getScriptAttributesToApplyOnCurrentPage() was already called
        // and $this->scriptsAttrsThisPage populated within method getScriptAttributesToApplyOnCurrentPage()

        // Any globally loaded attributes?
        if (wpacuIsDefinedConstant('WPACU_NO_SITE_WIDE_SCRIPT_ATTRS_SET')) {
            $scriptGlobalAttributes = array('async' => array(), 'defer' => array());
        } else {
            $scriptGlobalAttributes = $mainProClass->getScriptGlobalAttributes();
        }

        $data['scripts_attributes'] = array(
            'everywhere'       => $scriptGlobalAttributes,
            'on_this_page'     => $mainProClass->onThisPageScriptsAttributes,
            'not_on_this_page' => $mainProClass->onThisPageScriptsAttrsNoLoad
        );

        return $data;
    }

    /**
     *
     */
    public function ajaxLoadAllSetTermsForPostType()
    {
        // Check nonce
        if ( ! isset( $_POST['wpacu_nonce'] ) || ! wp_verify_nonce( $_POST['wpacu_nonce'], 'wpacu_ajax_get_post_type_terms_nonce' ) ) {
            echo 'Error: The security nonce is not valid.';
            exit();
        }

        // Check privileges
        if (! Menu::userCanAccessAssetCleanUp()) {
            echo 'Error: Not enough privileges to perform this action.';
            exit();
        }

        // Current Post Type (depending on the admin's location)
        $postType  = isset($_POST['wpacu_post_type'])  ? sanitize_text_field($_POST['wpacu_post_type']) : '';
        $handle    = isset($_POST['wpacu_handle'])     ? esc_html($_POST['wpacu_handle'])               : '';
        $assetType = isset($_POST['wpacu_asset_type']) ? esc_html($_POST['wpacu_asset_type'])           : '';
        $for       = isset($_POST['wpacu_for'])        ? esc_html($_POST['wpacu_for'])                  : '';

        if ( ! $postType ) {
            echo 'Error: The post type is missing.';
            exit();
        }

        echo self::loadDDOptionsForAllSetTermsForPostType($postType, $assetType, $handle, array(), $for);
        exit();
    }

    /**
     * @param $postType
     * @param $assetType
     * @param $handle
     * @param array $alreadySetTerms
     * @param string $for
     *
     * @return string
     */
    public static function loadDDOptionsForAllSetTermsForPostType($postType, $assetType, $handle, $alreadySetTerms = array(), $for = 'unload')
    {
        if (is_string($alreadySetTerms)) {
            $alreadySetTerms = array();
        }

        $allSetTermsPostType = MainAdmin::getAllSetTaxonomies($postType);

        if (empty($alreadySetTerms)) {
            $alreadySetTerms = ( $for === 'unload' )
                ? MainPro::getTaxonomyValuesAssocToPostType( $postType, $assetType, $handle )
                : MainPro::getTaxonomyValuesAssocToPostTypeLoadExceptions( $postType, $assetType, $handle );
        }

        $output = '';

        foreach (array_keys($allSetTermsPostType) as $taxLabel) {
            $output .= '<optgroup label="'.esc_attr($taxLabel.' ('.$allSetTermsPostType[$taxLabel][0]['taxonomy'].')').'">'."\n";

            $taxDropDown = wp_dropdown_categories(array(
                'taxonomy'     => $allSetTermsPostType[$taxLabel][0]['taxonomy'],
                'echo'         => 0,
                'hierarchical' => 1,
                'show_count'   => 1,
                'order_by'     => 'name'
            ));

            $taxDropDown = preg_replace('@<select[^>]*?>@si', '', $taxDropDown);
            $taxDropDown = str_ireplace('</select>', '', $taxDropDown);

            if ( ! empty($alreadySetTerms) ) {
                foreach ($alreadySetTerms as $termId) {
                    $taxDropDown = str_replace('value="'.$termId.'"', 'selected="selected" value="'.(int)$termId.'"', $taxDropDown);
                }
            }

            $output .= $taxDropDown;

            $output .= '</optgroup>'."\n";
        }

        return $output;
    }
}
