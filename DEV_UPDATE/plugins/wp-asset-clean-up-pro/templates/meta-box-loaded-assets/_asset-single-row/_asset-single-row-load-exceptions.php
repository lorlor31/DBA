<?php
/*
 * The file is included from /templates/meta-box-loaded-assets/_asset-single-row.php
*/

if ( ! isset($data, $assetType, $assetTypeS) ) {
    exit(); // no direct access
}

$isGroupUnloaded = $data['row']['is_group_unloaded'];

// [wpacu_pro]
$isMarkedForPostTypeViaTaxUnload = isset($data['handle_unload_post_type_via_tax'][$assetType][$data['row']['obj']->handle ]['enable'], $data['handle_unload_post_type_via_tax'][$assetType][$data['row']['obj']->handle]['values'])
    && $data['handle_unload_post_type_via_tax'][$assetType][$data['row']['obj']->handle ]['enable'] && ! empty($data['handle_unload_post_type_via_tax'][$assetType][$data['row']['obj']->handle]['values']);
$isMarkedForTaxUnload = (isset($data['handle_unload_via_tax'][$assetType]) && ! empty($data['handle_unload_via_tax'][$assetType])) ? in_array($data['row']['obj']->handle, $data['handle_unload_via_tax'][$assetType]) : false;
$isMarkedForRegExUnload = isset($data['handle_unload_regex'][$assetType][ $data['row']['obj']->handle ]['enable']) ? $data['handle_unload_regex'][$assetType][ $data['row']['obj']->handle ]['enable'] : false;
// [/wpacu_pro]

$anyUnloadRuleSet = ($isGroupUnloaded ||
    // [wpacu_pro]
    $isMarkedForRegExUnload ||
    $isMarkedForPostTypeViaTaxUnload ||
    $isMarkedForTaxUnload ||
    // [/wpacu_pro]
    $data['row']['checked']);

if ($anyUnloadRuleSet || $data['row']['is_load_exception_per_page']) {
    $data['row']['at_least_one_rule_set'] = true;
}

$loadExceptionOptionsAreaCss = '';

if ($data['row']['global_unloaded']) {
    // Move it to the right side or extend it to avoid so much empty space and a higher DIV
	$loadExceptionOptionsAreaCss = 'display: contents;';
}
?>
<div class="wpacu_exception_options_area_load_exception <?php if (! $anyUnloadRuleSet) { echo 'wpacu_hide'; } ?>" style="<?php echo $loadExceptionOptionsAreaCss; ?>">
    <div data-<?php echo $assetTypeS; ?>-handle="<?php echo htmlentities(esc_attr($data['row']['obj']->handle), ENT_QUOTES); ?>"
         class="wpacu_exception_options_area_wrap">
        <fieldset>
            <legend>Make an exception from any unload rule &amp; <strong>always load it</strong>:</legend>
		    <ul class="wpacu_area_two wpacu_asset_options wpacu_exception_options_area">
                <li>
                    <label for="wpacu_load_it_option_<?php echo $assetTypeS; ?>_<?php echo htmlentities(esc_attr($data['row']['obj']->handle), ENT_QUOTES); ?>">
                        <input data-handle="<?php echo htmlentities(esc_attr($data['row']['obj']->handle), ENT_QUOTES); ?>"
                               data-handle-for="<?php echo $assetTypeS; ?>"
                                  id="wpacu_load_it_option_<?php echo $assetTypeS; ?>_<?php echo htmlentities(esc_attr($data['row']['obj']->handle), ENT_QUOTES); ?>"
                                  class="wpacu_load_it_option_on_this_page wpacu_<?php echo $assetTypeS; ?> wpacu_load_exception"
                                  type="checkbox"
                            <?php if ($data['row']['is_load_exception_per_page']) { ?> checked="checked" <?php } ?>
                                  name="wpacu_<?php echo $assetType; ?>_load_it[]"
                                  value="<?php echo htmlentities(esc_attr($data['row']['obj']->handle), ENT_QUOTES); ?>" />
                        <span><?php echo $data['page_load_text']; ?></span></label>
                </li>
                <?php
                if ($data['bulk_unloaded_type'] === 'post_type') {
                    // Only show it on edit post/page/custom post type
                    switch ($data['post_type']) {
                        case 'product':
                            $loadBulkText = __('On all WooCommerce "Product" pages', 'wp-asset-clean-up');
                            break;
                        case 'download':
                            $loadBulkText = __('On all Easy Digital Downloads "Download" pages', 'wp-asset-clean-up');
                            break;
                        default:
                            $loadBulkText = sprintf(__('On all pages of "<strong>%s</strong>" post type', 'wp-asset-clean-up'), $data['post_type']);
                    }
                    ?>
                    <li for="wpacu_load_it_option_post_type_<?php echo $assetTypeS; ?>_<?php echo htmlentities(esc_attr($data['row']['obj']->handle), ENT_QUOTES); ?>">
                        <label><input data-handle="<?php echo htmlentities(esc_attr($data['row']['obj']->handle), ENT_QUOTES); ?>"
                                      data-handle-for="<?php echo $assetTypeS; ?>"
                                      id="wpacu_load_it_option_post_type_<?php echo $assetTypeS; ?>_<?php echo htmlentities(esc_attr($data['row']['obj']->handle), ENT_QUOTES); ?>"
                                      class="wpacu_load_it_option_post_type wpacu_<?php echo $assetTypeS; ?> wpacu_load_exception"
                                      type="checkbox"
                                <?php if ($data['row']['is_load_exception_post_type']) { ?> checked="checked" <?php } ?>
                                      name="<?php echo WPACU_FORM_ASSETS_POST_KEY; ?>[<?php echo $assetType; ?>][<?php echo htmlentities(esc_attr($data['row']['obj']->handle), ENT_QUOTES); ?>][load_it_post_type]"
                                      value="1"/>
                            <span><?php echo wp_kses($loadBulkText, array('strong' => array())); ?></span></label>
                    </li>
                    <?php
                    if (isset($data['post_type']) && $data['post_type'] !== 'attachment' && ! empty($data['post_type_has_tax_assoc'])) {
                        include __DIR__ . '/_asset-single-row-load-exceptions-post-type-taxonomy.php';
                    }
                }

                // [wpacu_pro]
                if (in_array($data['bulk_unloaded_type'], array('taxonomy', 'author'))) {
                    do_action('wpacu_pro_bulk_load_output', $data, $data['row']['obj'], $assetTypeS);
                }

                $handleLoadRegex = (isset($data['handle_load_regex'][$assetType][$data['row']['obj']->handle]) && $data['handle_load_regex'][$assetType][$data['row']['obj']->handle])
                    ? $data['handle_load_regex'][$assetType][$data['row']['obj']->handle]
                    : array();

                $handleLoadRegex['enable'] = isset($handleLoadRegex['enable']) && $handleLoadRegex['enable'];
                $handleLoadRegex['value']  = (isset($handleLoadRegex['value']) && $handleLoadRegex['value']) ? $handleLoadRegex['value'] : '';

                $isLoadRegExEnabledWithValue = $handleLoadRegex['enable'] && $handleLoadRegex['value'];

                if ($isLoadRegExEnabledWithValue) { $data['row']['at_least_one_rule_set'] = true; }
                // [/wpacu_pro]
                ?>
                <li>
                    <label for="wpacu_load_it_regex_option_<?php echo $assetTypeS; ?>_<?php echo htmlentities(esc_attr($data['row']['obj']->handle), ENT_QUOTES); ?>">
                        <input data-handle="<?php echo htmlentities(esc_attr($data['row']['obj']->handle), ENT_QUOTES); ?>"
                               data-handle-for="<?php echo $assetTypeS; ?>"
                               id="wpacu_load_it_regex_option_<?php echo $assetTypeS; ?>_<?php echo htmlentities(esc_attr($data['row']['obj']->handle), ENT_QUOTES); ?>"
                               class="wpacu_load_it_option_regex wpacu_<?php echo $assetTypeS; ?> wpacu_load_exception"
                               type="checkbox"
                               name="wpacu_handle_load_regex[<?php echo $assetType; ?>][<?php echo htmlentities(esc_attr($data['row']['obj']->handle), ENT_QUOTES); ?>][enable]"
                            <?php if ($isLoadRegExEnabledWithValue) { ?> checked="checked" <?php } ?>
                               value="1" />&nbsp;<span>If the URL (its URI) is matched by a RegEx(es):</span></label>
                    <a style="text-decoration: none; color: inherit; vertical-align: middle;" target="_blank" href="https://assetcleanup.com/docs/?p=21#wpacu-method-2"><span class="dashicons dashicons-editor-help"></span></a>
                    <!-- [wpacu_pro] -->
                    <div class="wpacu_load_regex_input_wrap <?php if (! $isLoadRegExEnabledWithValue) { echo 'wpacu_hide'; } ?>">
                        <div class="wpacu_regex_rule_area">
                            <textarea <?php if (! $isLoadRegExEnabledWithValue) { echo 'disabled="disabled"'; } ?>
                                class="wpacu_regex_rule_textarea"
                                data-handle="<?php echo htmlentities(esc_attr($data['row']['obj']->handle), ENT_QUOTES); ?>"
                                data-handle-for="<?php echo $assetTypeS; ?>"
                                data-wpacu-adapt-height="1"
                                name="wpacu_handle_load_regex[<?php echo $assetType; ?>][<?php echo htmlentities(esc_attr($data['row']['obj']->handle), ENT_QUOTES); ?>][value]"><?php echo esc_attr($handleLoadRegex['value']); ?></textarea>
                            <p style="margin-top: 0 !important;"><small><span style="font-weight: 500;">Note:</span> Multiple RegEx rules can be added as long as they are one per line.</small></p>
                        </div>
                    </div>
                    <!-- [/wpacu_pro] -->
                </li>
                <?php
                $isLoadItLoggedIn = in_array($data['row']['obj']->handle, $data['handle_load_logged_in'][$assetType]);
                if ($isLoadItLoggedIn) { $data['row']['at_least_one_rule_set'] = true; }
                ?>
                <li id="wpacu_load_it_user_logged_in_option_<?php echo $assetTypeS; ?>_<?php echo htmlentities(esc_attr($data['row']['obj']->handle), ENT_QUOTES); ?>">
                    <label><input data-handle="<?php echo htmlentities(esc_attr($data['row']['obj']->handle), ENT_QUOTES); ?>"
                                  data-handle-for="<?php echo $assetTypeS; ?>"
                                  id="wpacu_load_it_user_logged_in_option_<?php echo $assetTypeS; ?>_<?php echo htmlentities(esc_attr($data['row']['obj']->handle), ENT_QUOTES); ?>"
                                  class="wpacu_load_it_option_user_logged_in wpacu_<?php echo $assetTypeS; ?> wpacu_load_exception"
                                  type="checkbox"
                            <?php if ($isLoadItLoggedIn) { ?> checked="checked" <?php } ?>
                                  name="wpacu_load_it_logged_in[<?php echo $assetType; ?>][<?php echo htmlentities(esc_attr($data['row']['obj']->handle), ENT_QUOTES); ?>]"
                                  value="1"/>
                        <span><?php esc_html_e('If the user is logged-in', 'wp-asset-clean-up'); ?></span></label>
                </li>
		    </ul>
            <div class="wpacu_clearfix"></div>
        </fieldset>
	</div>
</div>
