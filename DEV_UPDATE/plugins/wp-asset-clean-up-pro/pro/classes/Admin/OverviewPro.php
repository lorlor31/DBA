<?php
namespace WpAssetCleanUpPro\Admin;

use WpAssetCleanUp\Admin\MiscAdmin;
use WpAssetCleanUp\Admin\Overview;
use WpAssetCleanUpPro\LoadExceptionsPro;
use WpAssetCleanUpPro\MainPro;

/**
 *
 */
class OverviewPro
{
    /**
     * @param $data
     *
     * @return mixed
     */
    public static function getPageOverviewData($data)
    {
        $data['plugins_with_rules'] = array();

        // Any plugins unloaded site-wide (with exceptions) or based on other conditions?
        // Get all the saved rules for both active, inactive and deleted plugins
        $getAllPluginsRules = PluginsManagerPro::getPluginRulesFiltered(false, true);

        if ( ! empty($getAllPluginsRules) ) {
            // Are there plugins with rules?
            // Fetch all plugins and get the needed information (title, path, icon)
            // only from the ones with rules
            $currentPluginsWithRules = array();

            // Get all current plugins (active and inactive) and their basic information
            $allCurrentPlugins = get_plugins();

            foreach ($allCurrentPlugins as $currentPluginPath => $currentPluginData) {
                // Skip Asset CleanUp as it's obviously needed for the functionality
                if (strpos($currentPluginPath, 'wp-asset-clean-up') !== false) {
                    continue;
                }

                foreach (array_keys($getAllPluginsRules) as $locationKey) {
                    if ( ! isset( $getAllPluginsRules[ $locationKey ][ $currentPluginPath ] ) ) {
                        continue; // the rule is irrelevant because the targeted plugin is deleted (not even inactive)
                    }

                    $currentPluginsWithRules[$locationKey][] = array(
                        'title' => $currentPluginData['Name'],
                        'path'  => $currentPluginPath,
                        'rules' => $getAllPluginsRules[ $locationKey][ $currentPluginPath ]
                    );
                }
            }

            if ( ! empty($currentPluginsWithRules) ) {
                foreach ( array_keys( $currentPluginsWithRules ) as $locationKey ) {
                    usort( $currentPluginsWithRules[ $locationKey ], static function( $a, $b ) {
                        return strcmp( $a['title'], $b['title'] );
                    } );
                }
            }

            $pluginsDir = dirname( WPACU_PLUGIN_DIR ) . '/';

            // Get active plugins and their basic information
            $activePlugins = wp_get_active_and_valid_plugins();

            foreach ($activePlugins as $activePluginKey => $activePluginValue) {
                $activePlugins[$activePluginKey] = str_replace($pluginsDir, '', $activePluginValue);
            }

            // Multisite?
            $data['plugins_active_network'] = array();

            if (is_multisite()) {
                $networkActivePlugins = wp_get_active_network_plugins();

                if ( ! empty( $networkActivePlugins ) ) {
                    foreach ( $networkActivePlugins as $networkActivePlugin ) {
                        $networkActivePluginSanitized     = str_replace( $pluginsDir, '', $networkActivePlugin );
                        $activePlugins[]                  = $networkActivePluginSanitized;
                        $data['plugins_active_network'][] = $networkActivePluginSanitized;
                    }
                }
            }

            $activePlugins = array_unique($activePlugins);

            $data['plugins_active']     = $activePlugins;
            $data['plugins_with_rules'] = $currentPluginsWithRules; // all rules for all plugins
            $data['plugins_icons']      = MiscAdmin::getAllActivePluginsIcons();
        }

        return $data;
    }

    /**
     * @param $allHandles
     * @param $filterFor
     * @param $extraValues
     *
     * @return array
     */
    public static function filterHandlesWithAtLeastOneRule($filterFor, $allHandles, $extraValues = array())
    {
        if ($filterFor === 'load_exceptions') {
            // Load exception for all pages of [post] type having specific taxonomies set
            $wpacuPostTypeLoadExceptionsViaTax = MainPro::getTaxonomyValuesAssocToPostTypeLoadExceptions();

            if ( ! empty($wpacuPostTypeLoadExceptionsViaTax)) {
                foreach ($wpacuPostTypeLoadExceptionsViaTax as $postType => $assetsData) {
                    if ( ! (isset($assetsData['styles']) || isset($assetsData['scripts']))) {
                        continue;
                    }

                    foreach ($assetsData as $assetType => $assetsValues) {
                        foreach ($assetsValues as $assetHandle => $assetData) {
                            if (isset($assetData['enable']) && $assetData['enable'] && ! empty($assetData['values'])) {
                                $allHandles[ $assetType ][ $assetHandle ]['load_exception_post_type_via_tax'][ $postType ] = $assetData['values'];
                                }
                        }
                    }
                }
            }

            // Load exception for all pages belonging to a specific taxonomy (e.g. /category/[any_value_here])
            $wpacuLoadExceptionsViaTaxType = MainPro::getLoadExceptionsViaTaxType();

            if ( ! empty($wpacuLoadExceptionsViaTaxType) ) {
                foreach ( $wpacuLoadExceptionsViaTaxType as $taxonomyName => $assetsData ) {
                    if ( ! empty($assetsData) ) {
                        foreach ( $assetsData as $assetType => $assetHandles ) {
                            foreach ( $assetHandles as $assetHandle ) {
                                $allHandles[$assetType][$assetHandle]['load_exception_via_tax_type'][] = $taxonomyName;
                            }
                        }
                    }
                }
            }

            // Load exception for all archive pages belonging to any author (e.g. /author/[any_value_here])
            $wpacuLoadExceptionsViaAuthorType = MainPro::getLoadExceptionsViaAuthorType();

            if ( ! empty($wpacuLoadExceptionsViaAuthorType) ) {
                foreach ( $wpacuLoadExceptionsViaAuthorType as $assetType => $assetHandles ) {
                    foreach ( $assetHandles as $assetHandle ) {
                        $allHandles[$assetType][$assetHandle]['load_exception_via_author_type'] = 1;
                    }
                }
            }

            /*
             * Load exceptions for 404, Search, Date
             */
            $loadExceptionsClass  = new LoadExceptionsPro();
            $loadExceptionsExtras = $loadExceptionsClass->getAllExtrasLoadExceptions();

            if ( ! empty($loadExceptionsExtras)) {
                foreach ($loadExceptionsExtras as $refKeyExtra => $values) {
                    foreach ($values as $assetType => $assetHandles) {
                        foreach ($assetHandles as $assetHandle) {
                            $allHandles[ $assetType ][ $assetHandle ]['load_exception_on_this_page'][ $refKeyExtra ] = 1;
                            }
                    }
                }
            }
        }

        if ($filterFor === 'unload_bulk') {
            $unloadBulkType   = $extraValues['unload_bulk_type'];
            $unloadBulkValues = $extraValues['unload_bulk_values'];
            $assetType        = $extraValues['asset_type'];

            if ($unloadBulkType === 'post_type_via_tax') {
                foreach ($unloadBulkValues as $postType => $assetHandles) {
                    foreach ($assetHandles as $assetHandle => $assetData) {
                        if (isset($assetData['enable']) && $assetData['enable'] && ! empty($assetData['values'])) {
                            $allHandles[ $assetType ][ $assetHandle ]['unload_bulk'][$unloadBulkType][$postType] = $assetData['values'];
                            }
                    }
                }

            }

            if (in_array($unloadBulkType, array('date', '404', 'search')) || (strpos($unloadBulkType, 'custom_post_type_archive_') !== false)) {
                foreach ($unloadBulkValues as $assetHandle) {
                    $allHandles[ $assetType ][ $assetHandle ]['unload_bulk'][$unloadBulkType] = 1;
                    }
            }

            if ($unloadBulkType === 'taxonomy') {
                foreach ($unloadBulkValues as $taxonomyType => $assetHandles) {
                    foreach ($assetHandles as $assetHandle) {
                        $allHandles[ $assetType ][ $assetHandle ]['unload_bulk'][$unloadBulkType][] = $taxonomyType;
                        }
                }
            }

            if ($unloadBulkType === 'author' && ! empty($unloadBulkValues['all'])) {
                foreach ($unloadBulkValues['all'] as $assetHandle) {
                    $allHandles[ $assetType ][ $assetHandle ]['unload_bulk'][$unloadBulkType] = 1;
                    }
            }
        }

        return $allHandles;
    }

    /**
     * @param $filterFor
     * @param $handleData
     * @param $handleChangesOutput
     * @param $anyRule
     * @param $hasRedundantRules
     *
     * @return array
     */
    public static function filterRenderHandleChangesOutput($filterFor, $handleData, $handleChangesOutput, $anyRule, $hasRedundantRules = false)
    {
        if ($filterFor === 'unload_bulk') {
            if (isset($handleData['unload_bulk']['post_type_via_tax'])) {
                foreach ($handleData['unload_bulk']['post_type_via_tax'] as $postType => $termIds) {
                    if (empty($termIds)) {
                        continue;
                    }

                    $taxTermsToList = $taxLabelsToNames = array();
                    $anyDelTaxList  = array();

                    foreach ($termIds as $termId) {
                        if ( ! term_exists((int)$termId)) {
                            $anyDelTaxList[] = $termId;
                            continue;
                        }

                        $term                                 = get_term($termId);
                        $taxonomy                             = get_taxonomy($term->taxonomy);
                        $taxLabelsToNames[ $taxonomy->label ] = $term->taxonomy;
                        $taxTermsToList[ $taxonomy->label ][] = $term->name . ' (' . $term->count . ')';
                    }

                    $handleChangesOutput['bulk'] .= ' <span style="color: #cc0000;">Unloaded on all pages of <strong>' . $postType . '</strong> post type' . Overview::anyNoPostTypeEntriesMsg($postType) . ' with these taxonomies:</span> ';

                    if ( ! empty($taxTermsToList)) {
                        foreach (array_keys($taxTermsToList) as $taxonomyLabel) {
                            usort($taxTermsToList[ $taxonomyLabel ], static function($a, $b) {
                                return strcasecmp($a, $b);
                            });
                        }
                    }

                    foreach ($taxTermsToList as $categoryTitle => $termsAssoc) {
                        $handleChangesOutput['bulk'] .= '<strong>' . $categoryTitle . '</strong> (' . $taxLabelsToNames[ $categoryTitle ] . '): ' . implode(', ', $termsAssoc) . ' | ';
                    }
                    $handleChangesOutput['bulk'] = rtrim($handleChangesOutput['bulk'], ' | ');

                    if ( ! empty($anyDelTaxList)) {
                        $handleChangesOutput['bulk'] = ' <span style="color: #cc0000;" title="The following taxonomy IDs were also found (the taxonomies might have been deleted from the database): ' . implode(', ', $anyDelTaxList) . '" class="wpacu-tooltip dashicons dashicons-warning"></span>';
                    }

                    $handleChangesOutput['bulk'] .= '<br />';

                    $anyRule = true;
                }
            }

            if ( ! empty($handleData['unload_bulk']['taxonomy'])) {
                $handleChangesOutput['bulk'] .= ' <span style="color: #cc0000;">Unloaded for all pages belonging to the following taxonomies: <strong>';

                $taxonomyList = '';

                foreach ($handleData['unload_bulk']['taxonomy'] as $taxonomy) {
                    $appendAfter = '';

                    if ( ! taxonomy_exists($taxonomy)) {
                        $appendAfter = ' <span style="color: #cc0000;" title="The following taxonomy might not exist anymore: ' . $taxonomy . '" class="wpacu-tooltip dashicons dashicons-warning"></span>';
                    }

                    $taxonomyList .= $taxonomy . $appendAfter . ', ';
                }

                $taxonomyList = trim($taxonomyList, ', ');

                $handleChangesOutput['bulk'] .= $taxonomyList;

                $handleChangesOutput['bulk'] .= '</strong></span>, ';

                $anyRule = true;
            }

            $unloadBulkKeys    = array_keys($handleData['unload_bulk']);
            $unloadBulkKeysStr = implode('', $unloadBulkKeys);

            if (isset($handleData['unload_bulk']['date'])
                || isset($handleData['unload_bulk']['404'])
                || isset($handleData['unload_bulk']['search'])
                || (strpos($unloadBulkKeysStr, 'custom_post_type_archive_') !== false)
            ) {
                foreach ($handleData['unload_bulk'] as $bulkType => $bulkValue) {
                    if ($bulkType === 'date' && $bulkValue === 1) {
                        $handleChangesOutput['bulk'] .= ' <span style="color: #cc0000;">Unloaded on all archive `Date` pages (any date)</span>, ';
                        $anyRule                     = true;
                    }
                    if ($bulkType === 'search' && $bulkValue === 1) {
                        $handleChangesOutput['bulk'] .= ' <span style="color: #cc0000;">Unloaded on `Search` page (any keyword)</span>, ';
                        $anyRule                     = true;
                    }
                    if ($bulkType === 404 && $bulkValue === 1) {
                        $handleChangesOutput['bulk'] .= ' <span style="color: #cc0000;">Unloaded on `404 Not Found` page (any URL)</span>, ';
                        $anyRule                     = true;
                    }
                    if (strpos($bulkType, 'custom_post_type_archive_') !== false) {
                        $customPostType              = str_replace('custom_post_type_archive_', '', $bulkType);
                        $handleChangesOutput['bulk'] .= ' <span style="color: #cc0000;">Unloaded on the archive (list of posts) page of <strong>' . $customPostType . '</strong> custom post type' . Overview::anyNoPostTypeEntriesMsg($customPostType) . '</span>, ';
                        $anyRule               = true;
                    }
                }
            }

            if (isset($handleData['unload_bulk']['author']) && $handleData['unload_bulk']['author']) {
                $handleChangesOutput['bulk'] .= ' <span style="color: #cc0000;">Unloaded on all <strong>author</strong> pages</span>, ';
                $anyRule                     = true;
            }

            return array('handle_changes_output' => $handleChangesOutput, 'any_rule' => $anyRule);
        }

        if ($filterFor === 'unload_on_this_page') {
            // Unload on this page: taxonomy such as 'category', 'product_cat' (specific one, not all categories)
            if (isset($handleData['unload_on_this_page']['term'])) {
                $handleChangesOutput['on_this_tax'] = '<span style="color: #cc0000;">Unloaded in the following pages:</span> ';

                $taxList = '';

                sort($handleData['unload_on_this_page']['term']);

                foreach ($handleData['unload_on_this_page']['term'] as $termId) {
                    $taxData = term_exists((int)$termId) ? get_term($termId) : false;

                    if ( ! $taxData || (isset($taxData->errors['invalid_taxonomy']) && ! empty($taxData->errors['invalid_taxonomy'])) ) {
                        $taxList .= '<span style="color: darkred; font-style: italic;">Error: Taxonomy with ID '.$termId.' does not exist anymore (rule does not apply)</span>';
                    } else {
                        $taxonomy = $taxData->taxonomy;

                        global $wp_rewrite;
                        $termPermalink = $wp_rewrite->get_extra_permastruct( $taxonomy );

                        if ($termPermalink) {
                            $termLink    = get_term_link( $taxData, $taxonomy );
                            $termRelLink = str_replace( site_url(), '', $termLink );
                            $taxList     .= '<a target="_blank" href="' . $termLink . '">' . $termRelLink . '</a>, ';
                        } else {
                            $termLink    = @get_term_link( $taxData, $taxonomy );
                            $termRelLink = str_replace( site_url(), '', $termLink );
                            $taxList     .= '<a target="_blank" href="' . $termLink . '">' . $termRelLink . '</a> <span style="color: #cc0000;" title="The taxonomy might not exist anymore as its permalink could not be retrieved" class="wpacu-tooltip dashicons dashicons-warning"></span>, ';
                        }
                    }
                }

                $handleChangesOutput['on_this_tax'] .= rtrim($taxList, ', ');

                if (isset($handleChangesOutput['site_wide'])) {
                    $handleChangesOutput['on_this_tax'] .= ' * <em>unnecessary, as it\'s already unloaded site-wide</em>';
                    $hasRedundantRules                  = true;
                }

                $anyRule = true;
            }

            if ( ! empty($handleData['unload_on_this_page']['user']) ) {
                $handleChangesOutput['on_this_tax'] = '<span style="color: #cc0000;">Unloaded in the following author pages: ';

                $taxList = '';

                sort($handleData['unload_on_this_page']['user']);

                foreach ($handleData['unload_on_this_page']['user'] as $userId) {
                    $user = get_user_by('id', $userId);

                    if (isset($user->ID)) {
                        $authorLink    = get_author_posts_url( $userId );
                        $authorRelLink = str_replace( site_url(), '', $authorLink );

                        $taxList .= '<a target="_blank" href="' . $authorLink . '">' . $authorRelLink . '</a>, ';
                    } else {
                        $taxList .= '<s style="color: #cc0000;">N/A (The user with the following was deleted: <strong>'.$userId.'</strong>)</s>, ';
                    }
                }

                $handleChangesOutput['on_this_tax'] .= rtrim($taxList, ', ');

                if (isset($handleChangesOutput['site_wide'])) {
                    $handleChangesOutput['on_this_tax'] .= ' * <em>unnecessary, as it\'s already unloaded site-wide</em>';
                    $hasRedundantRules                  = true;
                }

                $handleChangesOutput['on_this_tax'] .= '</span>';

                $anyRule = true;
            }

            return array(
                'handle_changes_output' => $handleChangesOutput,
                'any_rule'              => $anyRule,
                'has_redundant_rules'   => $hasRedundantRules
            );
        }

        if ($filterFor === 'unload_regex') {
            // Unload via RegEx
            if (isset($handleData['unload_regex']) && $handleData['unload_regex']) {
                $handleChangesOutput['unloaded_via_regex'] = '<span style="color: #cc0000;">Unloaded if</span> the request URI (from the URL) matches this RegEx(es): <code style="color: #cc0000;">'.nl2br($handleData['unload_regex']).'</code>';

                if (isset($handleChangesOutput['site_wide'])) {
                    $handleChangesOutput['unloaded_via_regex'] .= ' * <em>unnecessary, as it\'s already unloaded site-wide</em>';
                    $hasRedundantRules                         = true;
                }

                $anyRule = true;
            }

            return array(
                'handle_changes_output' => $handleChangesOutput,
                'any_rule'              => $anyRule,
                'has_redundant_rules'   => $hasRedundantRules
            );
        }

        if ($filterFor === 'load_exceptions') {
            // Load exception on all pages of [post] type when specific taxonomies are set
            if (isset($handleData['load_exception_post_type_via_tax'])) {
                $handleChangesOutput['load_exception_post_type_via_tax'] = '';

                foreach ($handleData['load_exception_post_type_via_tax'] as $postType => $termIds) {
                    $taxTermsToList = $taxLabelsToNames = array();

                    $handleChangesOutput['load_exception_post_type_via_tax'] .=
                        '<span style="color: green;">Loaded (as an exception)</span> in all pages of <strong>'
                        . $postType .
                        '</strong> post type' . Overview::anyNoPostTypeEntriesMsg($postType).' that have these taxonomies set: ';

                    foreach ($termIds as $termId) {
                        if ( ! term_exists((int)$termId) ) {
                            $appendAfter = ' <span style="color: #cc0000;" title="The taxonomy might not be available anymore as it was not detected from the specified ID: '.$termId.'" class="wpacu-tooltip dashicons dashicons-warning"></span>';
                            $handleChangesOutput['load_exception_post_type_via_tax'] .= '<strong><s>' . $termId . '</s></strong>'.$appendAfter.' | ';
                            continue;
                        }

                        $term = get_term( $termId );
                        $taxonomy = get_taxonomy($term->taxonomy);
                        $taxLabelsToNames[$taxonomy->label] = $term->taxonomy;
                        $taxTermsToList[$taxonomy->label][] = $term->name. ' ('.$term->count.')';
                    }

                    if ( ! empty($taxTermsToList) ) {
                        foreach ( array_keys( $taxTermsToList ) as $taxonomyLabel ) {
                            usort( $taxTermsToList[ $taxonomyLabel ], static function( $a, $b ) {
                                return strcasecmp( $a, $b );
                            } );
                        }

                        foreach ( $taxTermsToList as $categoryTitle => $termsAssoc ) {
                            $handleChangesOutput['load_exception_post_type_via_tax'] .= '<strong>' . $categoryTitle . '</strong> (' . $taxLabelsToNames[ $categoryTitle ] . '): ' . implode( ', ', $termsAssoc ) . ' | ';
                        }

                        $handleChangesOutput['load_exception_post_type_via_tax'] = rtrim( $handleChangesOutput['load_exception_post_type_via_tax'], ' | ' );
                        $handleChangesOutput['load_exception_post_type_via_tax'] .= '<br />';

                        $anyRule = true;
                    }
                }
            }

            // Load exceptions? Per taxonomy page (e.g. /category/clothes/)
            if ( ! empty($handleData['load_exception_on_this_page']['term']) ) {
                $handleChangesOutput['load_exception_on_this_taxonomy'] = '<span style="color: green;">Loaded (as an exception) in the following <strong>taxonomy</strong> pages: ';

                $postsList = '';

                sort($handleData['load_exception_on_this_page']['term']);

                foreach ($handleData['load_exception_on_this_page']['term'] as $termId) {
                    $termData = get_term_by('term_taxonomy_id', $termId);

                    if ($termData) {
                        $postsList .= '<a title="" target="_blank" href="' . esc_url( admin_url( 'term.php?taxonomy=' . $termData->taxonomy . '&tag_ID=' . $termId ) ) . '">' . $termId . '</a> (' . $termData->name . ' / taxonomy: <strong>' . $termData->taxonomy . '</strong>), ';
                    } else {
                        $postsList .= '<span style="color: darkred; font-style: italic;">Error: Taxonomy with ID '.$termId.' does not exist anymore (rule does not apply)</span>';
                    }
                }

                $handleChangesOutput['load_exception_on_this_taxonomy'] .= rtrim($postsList, ', ');
                $handleChangesOutput['load_exception_on_this_taxonomy'] .= '</span>';

                $anyRule = true;
            }

            if ( ! empty($handleData['load_exception_via_tax_type']) ) {
                $handleChangesOutput['load_exception_via_tax_type'] = '<span style="color: green;">Loaded (as an exception) on all pages belonging to the following <strong>taxonomy</strong> pages: ';

                $taxList = '';

                sort($handleData['load_exception_via_tax_type']);

                foreach ($handleData['load_exception_via_tax_type'] as $taxName) {
                    $taxData = get_taxonomy($taxName);

                    if ($taxData) {
                        $taxList .= '<a title="" target="_blank" href="' . esc_url( admin_url( 'edit-tags.php?taxonomy=' . $taxName ) ) . '">' . $taxData->label . '</a> (<strong>'.$taxName.'</strong>), ';
                    } else {
                        $taxList .= '<span style="color: darkred; font-style: italic;">Error: Taxonomy '.$taxName.' does not seem to exist anymore (rule does not apply)</span>';
                    }
                }

                $handleChangesOutput['load_exception_via_tax_type'] .= rtrim($taxList, ', ');
                $handleChangesOutput['load_exception_via_tax_type'] .= '</span>';
            }

            if (isset($handleData['load_exception_via_author_type'])) {
                $handleChangesOutput['load_exception_via_author_type'] = '<span style="color: green;">Loaded (as an exception) on all author archive pages</span>';
            }

            // Load exceptions? Per user archive page (e.g. /author/john/)
            if (isset($handleData['load_exception_on_this_page']['user'])) {
                $handleChangesOutput['load_exception_on_this_user'] = '<span style="color: green;">Loaded (as an exception) in the following user archive pages: ';

                $usersList = '';

                sort($handleData['load_exception_on_this_page']['user']);

                foreach ($handleData['load_exception_on_this_page']['user'] as $userId) {
                    $userData = get_user_by('id', $userId);

                    if ($userData) {
                        $usersList .= '<a title="" target="_blank" href="' . esc_url ( admin_url( 'user-edit.php?user_id=' . $userData->ID ) ) . '"><strong>' . $userData->data->user_nicename . '</strong>' . ' (User ID: ' . $userData->ID . ')</a>, ';
                    } else {
                        $usersList .= '<span style="color: darkred; font-style: italic;">Error: User with ID '.$userId.' does not exist anymore (rule does not apply)</span>';
                    }
                }

                $handleChangesOutput['load_exception_on_this_user'] .= rtrim($usersList, ', ') . '</span>';
                $anyRule = true;
            }

            // Load exceptions? Search page
            if (isset($handleData['load_exception_on_this_page']['search'])) {
                $handleChangesOutput['load_exception_on_search_any_term'] = '<span style="color: green;">Loaded (as an exception) in a `Search` page (any term)</span>';
                $anyRule = true;
            }

            // Load exceptions? 404 page
            if (isset($handleData['load_exception_on_this_page']['404'])) {
                $handleChangesOutput['load_exception_on_404_page'] = '<span style="color: green;">Loaded (as an exception) in a `404 (Not Found)` page</span>';
                $anyRule = true;
            }

            // Load exceptions? Date archive page
            if (isset($handleData['load_exception_on_this_page']['date'])) {
                $handleChangesOutput['load_exception_on_date_archive_page'] = '<span style="color: green;">Loaded (as an exception) in a `Date` archive page</span>';
                $anyRule = true;
            }

            // Load exceptions? Custom post type archive page
            $loadExceptionsPageStr = isset($handleData['load_exception_on_this_page']) && is_array($handleData['load_exception_on_this_page']) ? implode('', array_keys($handleData['load_exception_on_this_page'])) : '';
            if (strpos($loadExceptionsPageStr, 'custom_post_type_archive_') !== false) {
                foreach (array_keys($handleData['load_exception_on_this_page']) as $loadExceptionForDataType) {
                    if (strpos($loadExceptionForDataType, 'custom_post_type_archive_') !== false) {
                        $customPostType = str_replace('custom_post_type_archive_', '', $loadExceptionForDataType);
                        $handleChangesOutput['load_exception_on_'.$loadExceptionForDataType] =
                            '<span style="color: green;">Loaded (as an exception) in an archive page (custom post type: <em>'.$customPostType.'</em>)'.
                            Overview::anyNoPostTypeEntriesMsg($customPostType).'</span>';
                    }
                }

                $anyRule = true;
            }

            if (isset($handleData['load_regex']) && $handleData['load_regex']) {
                if ($anyRule) {
                    $textToShow = ' <strong>or</strong> <span style="color: green;">if the request URI (from the URL) matches this RegEx:</span>';
                } else {
                    $textToShow = '<span style="color: green;">Loaded (as an exception) if the request URI (from the URL) matches this RegEx(es):</span>';
                }

                $handleChangesOutput['load_exception_regex'] = $textToShow.' <code style="color: green;">'.nl2br($handleData['load_regex']).'</code>';
                $anyRule = true;
            }

            return array(
                'handle_changes_output' => $handleChangesOutput,
                'any_rule'              => $anyRule,
                'has_redundant_rules'   => $hasRedundantRules
            );
        }

        return array();
    }
}
