<?php
/*
 * The file is included from /templates/meta-box-loaded-assets/_asset-single-row.php
*/

if (! isset($data, $assetType, $assetTypeS)) {
    exit(); // no direct access
}

if ($assetType === 'scripts') {
    // [wpacu_pro]
    $isAsyncOnThisPage    = in_array($data['row']['obj']->handle, $data['scripts_attributes']['on_this_page']['async']);
    $isAsyncNotOnThisPage = in_array($data['row']['obj']->handle, $data['scripts_attributes']['not_on_this_page']['async']);

    $isDeferOnThisPage    = in_array($data['row']['obj']->handle, $data['scripts_attributes']['on_this_page']['defer']);
    $isDeferNotOnThisPage = in_array($data['row']['obj']->handle, $data['scripts_attributes']['not_on_this_page']['defer']);

    $isAsyncGlobal        = in_array($data['row']['obj']->handle, $data['scripts_attributes']['everywhere']['async']);
    $isDeferGlobal        = in_array($data['row']['obj']->handle, $data['scripts_attributes']['everywhere']['defer']);

    if ($isAsyncOnThisPage || $isDeferOnThisPage || $isAsyncGlobal || $isDeferGlobal) {
        $data['row']['at_least_one_rule_set'] = true;
    }
    // [/wpacu_pro]
    ?>
    <!-- [wpacu_pro] -->
    <div class="wpacu-script-attributes-area wpacu-pro wpacu-only-when-kept-loaded">
        <div <?php if ($isAsyncGlobal || $isDeferGlobal) { echo 'style="display: block; width: 100%;"'; } ?>>Set the following attributes:</div>
        <ul class="wpacu-script-attributes-settings wpacu-first">
            <li><strong><u>async</u></strong> &#10230;</li>
            <li>
                <label for="async_on_this_page_<?php echo htmlentities(esc_attr($data['row']['obj']->handle), ENT_QUOTES); ?>">
                    <input
                        <?php if ( $isAsyncGlobal ) { ?>disabled="disabled"<?php } ?>
                        id="async_on_this_page_<?php echo htmlentities(esc_attr($data['row']['obj']->handle), ENT_QUOTES); ?>"
                        class="wpacu_script_attr_rule_input"
                        type="checkbox"
                        name="wpacu_async[<?php echo htmlentities(esc_attr($data['row']['obj']->handle), ENT_QUOTES); ?>]"
                        <?php if ( $isAsyncOnThisPage ) { echo 'checked="checked"'; } ?>
                        value="on_this_page" />on this page <?php if ( $isAsyncGlobal ) { ?><br/><small>* locked by site-wide rule</small><?php } ?>
                </label>
            </li>
            <li>
                <?php if ($isAsyncGlobal) { ?>
                    <div><strong>Set everywhere</strong> <small>* site-wide</small></div>
                    <div>
                        <label><input data-handle="<?php echo htmlentities(esc_attr($data['row']['obj']->handle), ENT_QUOTES); ?>"
                                      type="radio"
                                      name="wpacu_options_global_attribute_scripts[async][<?php echo htmlentities(esc_attr($data['row']['obj']->handle), ENT_QUOTES); ?>]"
                                      checked="checked"
                                      value="default"/>
                            Keep rule</label>

                        &nbsp;&nbsp;&nbsp;&nbsp;

                        <label><input data-handle="<?php echo htmlentities(esc_attr($data['row']['obj']->handle), ENT_QUOTES); ?>"
                                      type="radio"
                                      name="wpacu_options_global_attribute_scripts[async][<?php echo htmlentities(esc_attr($data['row']['obj']->handle), ENT_QUOTES); ?>]"
                                      value="remove"/>
                            Remove rule</label>
                    </div>
                <?php } else { ?>
                    <label for="async_everywhere_<?php echo htmlentities(esc_attr($data['row']['obj']->handle), ENT_QUOTES); ?>">
                        <input id="async_everywhere_<?php echo htmlentities(esc_attr($data['row']['obj']->handle), ENT_QUOTES); ?>"
                               class="wpacu_script_attr_rule_input wpacu_script_attr_rule_global"
                               type="checkbox"
                               name="wpacu_async[<?php echo htmlentities(esc_attr($data['row']['obj']->handle), ENT_QUOTES); ?>]"
                               value="everywhere"/>everywhere</label>
                <?php } ?>
            </li>
            <li class="wpacu-script-attr-make-exception <?php if (! $isAsyncGlobal) { ?>wpacu_hide<?php } ?>">
                <label for="async_none_<?php echo htmlentities(esc_attr($data['row']['obj']->handle), ENT_QUOTES); ?>">
                    <input id="async_none_<?php echo htmlentities(esc_attr($data['row']['obj']->handle), ENT_QUOTES); ?>"
                           type="checkbox"
                           name="wpacu_async[no_load][]"
                        <?php if ($isAsyncNotOnThisPage) { ?> checked="checked" <?php } ?>
                           value="<?php echo htmlentities(esc_attr($data['row']['obj']->handle), ENT_QUOTES); ?>" />not here (exception)
                </label>
            </li>
        </ul>
        <ul class="wpacu-script-attributes-settings">
            <li><strong><u>defer</u></strong> &#10230;</li>
            <li><label for="defer_on_this_page_<?php echo htmlentities(esc_attr($data['row']['obj']->handle), ENT_QUOTES); ?>">
                    <input <?php if ( $isDeferGlobal ) { ?>disabled="disabled"<?php } ?>
                           id="defer_on_this_page_<?php echo htmlentities(esc_attr($data['row']['obj']->handle), ENT_QUOTES); ?>"
                           class="wpacu_script_attr_rule_input"
                           type="checkbox"
                           name="wpacu_defer[<?php echo htmlentities(esc_attr($data['row']['obj']->handle), ENT_QUOTES); ?>]"
                        <?php if ( $isDeferOnThisPage ) { echo 'checked="checked"'; } ?>
                           value="on_this_page" />on this page <?php if ( $isDeferGlobal ) { ?><br/><small>* locked by site-wide rule</small><?php } ?></label></li>
            <li>
                <?php if ($isDeferGlobal) { ?>
                    <div><strong>Set everywhere</strong> <small>* site-wide</small></div>
                    <div>
                        <label><input data-handle="<?php echo htmlentities(esc_attr($data['row']['obj']->handle), ENT_QUOTES); ?>"
                                      type="radio"
                                      name="wpacu_options_global_attribute_scripts[defer][<?php echo htmlentities(esc_attr($data['row']['obj']->handle), ENT_QUOTES); ?>]"
                                      checked="checked"
                                      value="default"/>
                            Keep rule</label>

                        &nbsp;&nbsp;&nbsp;&nbsp;

                        <label><input data-handle="<?php echo htmlentities(esc_attr($data['row']['obj']->handle), ENT_QUOTES); ?>"
                                      type="radio"
                                      name="wpacu_options_global_attribute_scripts[defer][<?php echo htmlentities(esc_attr($data['row']['obj']->handle), ENT_QUOTES); ?>]"
                                      value="remove"/>
                            Remove rule</label>
                    </div>
                <?php } else { ?>
                    <label for="defer_everywhere_<?php echo htmlentities(esc_attr($data['row']['obj']->handle), ENT_QUOTES); ?>"><input
                            id="defer_everywhere_<?php echo htmlentities(esc_attr($data['row']['obj']->handle), ENT_QUOTES); ?>"
                            class="wpacu_script_attr_rule_input wpacu_script_attr_rule_global"
                            type="checkbox"
                            name="wpacu_defer[<?php echo htmlentities(esc_attr($data['row']['obj']->handle), ENT_QUOTES); ?>]"
                            value="everywhere"/>everywhere</label>
                <?php } ?>
            </li>
            <li class="wpacu-script-attr-make-exception <?php if (! $isDeferGlobal) { ?>wpacu_hide<?php } ?>">
                <label for="defer_none_<?php echo htmlentities(esc_attr($data['row']['obj']->handle), ENT_QUOTES); ?>">
                    <input id="defer_none_<?php echo htmlentities(esc_attr($data['row']['obj']->handle), ENT_QUOTES); ?>"
                           type="checkbox"
                           name="wpacu_defer[no_load][]"
                        <?php if ($isDeferNotOnThisPage) { ?> checked="checked" <?php } ?>
                           value="<?php echo htmlentities(esc_attr($data['row']['obj']->handle), ENT_QUOTES); ?>"/>not here (exception)
                </label>
            </li>
        </ul>
        <div class="wpacu_clearfix"></div>
    </div>
    <div class="wpacu_clearfix"></div>
    <!-- [/wpacu_pro] -->
    <?php
}

$childHandles = isset($data['all_deps']['parent_to_child'][$assetType][$data['row']['obj']->handle]) ? $data['all_deps']['parent_to_child'][$assetType][$data['row']['obj']->handle] : array();

$handleAllStatuses = array();

if ( ! empty($childHandles) ) {
    $handleAllStatuses[] = 'is_parent';
}

if (isset($data['row']['obj']->deps) && ! empty($data['row']['obj']->deps)) {
    $handleAllStatuses[] = 'is_child';
}

if (empty($handleAllStatuses)) {
    $handleAllStatuses[] = 'is_independent';
}

if ($assetType === 'styles') {
    $showMatchMediaFeature = true;
} else {
    $showMatchMediaFeature = false;

    // Is "independent" or has "parents" (is "child") with nothing under it (no "children")
    if ( in_array('is_independent', $handleAllStatuses) || ( in_array('is_child', $handleAllStatuses) && ! in_array('is_parent', $handleAllStatuses) ) ) {
        // "extra" is fine, "after" and "before" are more tricky to accept (at least at this time)
        $wpacuHasExtraInline = ! empty($data['row']['extra_before_js']) || ! empty($data['row']['extra_after_js']);

        if ( ! $wpacuHasExtraInline ) {
            $showMatchMediaFeature = true;
        }
    }
}

if ( ! $showMatchMediaFeature ) {
    return;
}

// The media attribute is different from "all"
$assetHasDistinctiveMediaAttr    = isset($data['row']['obj']->args) && $data['row']['obj']->args && $data['row']['obj']->args !== 'all';
$showMatchMediaAlertForParentCss = $assetType === 'styles' && in_array('is_parent', $handleAllStatuses);
?>
<div class="wpacu-only-when-kept-loaded">
    <div style="margin: 0 0 15px;">
    <?php
    $wpacuDataForSelectId   = 'wpacu_handle_media_query_load_select_'.$assetTypeS.'_'.$data['row']['obj']->handle;
    $wpacuDataForTextAreaId = 'wpacu_handle_media_query_load_textarea_'.$assetTypeS.'_'.$data['row']['obj']->handle;


        // [wpacu_pro]
        $matchMediaLoadArray = (isset($data['media_queries_load'][$assetType][$data['row']['obj']->handle]) && $data['media_queries_load'][$assetType][$data['row']['obj']->handle])
            ? $data['media_queries_load'][$assetType][$data['row']['obj']->handle]
            : array();

        $matchMediaLoadStatus      = isset($matchMediaLoadArray['enable']) ? (int)$matchMediaLoadArray['enable'] : false;
        $matchMediaLoadCustomValue = isset($matchMediaLoadArray['value']) && $matchMediaLoadArray['value'] ? $matchMediaLoadArray['value'] : '';
        $matchMediaLoadCustomValueToPrint = '';

        // Custom one set by the user
        if ( $matchMediaLoadStatus === 1 ) {
            $matchMediaLoadCustomValueToPrint = $matchMediaLoadCustomValue;
        }

        // Existing one: "only if its current media query is matched"
        if ( $matchMediaLoadStatus === 2 ) {
            // The value in the database is "current" (reference for developers to scan the database content if required)

            if ( ! $assetHasDistinctiveMediaAttr ) {
                // The asset had a distinctive "media", but afterward it was set to "all"
                // In this case, make it not enabled, so it will load for any screen
                $matchMediaLoadStatus = false;
            }

            $matchMediaLoadCustomValueToPrint = '';
        }

        if ( $matchMediaLoadCustomValue && in_array($matchMediaLoadStatus, array(1, 2)) ) {
            $data['row']['at_least_one_rule_set'] = true;
        }
        // [/wpacu_pro]
        ?>
        Make the browser download the file&nbsp;

        <select id="<?php echo esc_attr($wpacuDataForSelectId); ?>"
                data-handle="<?php echo htmlentities(esc_attr($data['row']['obj']->handle), ENT_QUOTES); ?>"
                data-wpacu-input="media-query-select"
            <?php
            // Styles
            if ($assetType === 'styles' && $showMatchMediaAlertForParentCss) {
                echo ' data-wpacu-show-parent-alert ';
            }
            ?>
                name="<?php echo WPACU_FORM_ASSETS_POST_KEY; ?>[<?php echo $assetType; ?>][<?php echo htmlentities(esc_attr($data['row']['obj']->handle), ENT_QUOTES); ?>][media_query_load][enable]"
                class="wpacu-screen-size-load wpacu-for-<?php echo $assetTypeS; ?>">
            <option <?php if ( ! $matchMediaLoadStatus ) { echo 'selected="selected"'; } ?> value="">on any screen size (default)</option>

            <?php if ( $assetHasDistinctiveMediaAttr ) { ?>
                <option <?php if ( $matchMediaLoadStatus === 2 ) { echo 'selected="selected"'; } ?> value="2">only if its current media query is matched</option>
            <?php } ?>

            <option <?php if ( $matchMediaLoadStatus === 1 ) { echo ' selected="selected" '; } ?> value="1">only if this media query is matched:</option>
        </select>
        <div data-<?php echo $assetTypeS; ?>-handle="<?php echo htmlentities(esc_attr($data['row']['obj']->handle), ENT_QUOTES); ?>"
             class="wpacu-handle-media-queries-load-field <?php if ($matchMediaLoadStatus === 1) { echo 'wpacu-is-visible'; } ?> wpacu-fade-in">
                <textarea id="<?php echo esc_attr($wpacuDataForTextAreaId); ?>"
                          style="min-height: 40px;"
                          class="wpacu-handle-media-queries-load-field-input"
                          data-wpacu-adapt-height="1"
                          data-wpacu-is-empty-on-page-load="<?php echo ( ! $matchMediaLoadCustomValueToPrint ) ? 'true' : 'false'; ?>"
                  <?php if ( ! $matchMediaLoadCustomValueToPrint ) { echo 'disabled="disabled"'; } ?>
                  name="<?php echo WPACU_FORM_ASSETS_POST_KEY; ?>[<?php echo $assetType; ?>][<?php echo htmlentities(esc_attr($data['row']['obj']->handle), ENT_QUOTES); ?>][media_query_load][value]"><?php echo esc_textarea($matchMediaLoadCustomValueToPrint); ?></textarea> &nbsp;<small style="vertical-align: top;">e.g. <em style="vertical-align: top;">screen and (max-width: 767px)</em></small>
            <div class="wpacu_clearfix"></div>
        </div>
        <div class="wpacu-helper-area">
            <a style="text-decoration: none; color: inherit;" target="_blank" href="https://assetcleanup.com/docs/?p=1023"><span class="dashicons dashicons-editor-help"></span></a>
        </div>
    </div>
</div>
<div class="wpacu_clearfix"></div>
