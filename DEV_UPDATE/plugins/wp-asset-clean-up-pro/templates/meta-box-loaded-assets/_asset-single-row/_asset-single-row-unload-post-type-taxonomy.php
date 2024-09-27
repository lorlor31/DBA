<?php
/*
 * The file is included from /templates/meta-box-loaded-assets/_asset-single-row.php
*/

// [wpacu_pro]
use WpAssetCleanUpPro\Admin\MainAdminPro;
// [/wpacu_pro]

if ( ! isset($data, $assetType, $assetTypeS) ) {
    exit(); // no direct access
}

// Only show it if "Unload site-wide" is NOT enabled
// Otherwise, there's no point to use an unload regex if the asset is unloaded site-wide
if (isset($data['row']['global_unloaded']) && $data['row']['global_unloaded']) {
    return;
}

if ($assetType === 'scripts') {
    if (isset($data['row']['obj']->tag_output) && strncasecmp($data['row']['obj']->tag_output, '<noscript', 9) === 0) {
        switch ( $data['post_type'] ) {
            case 'product':
                $unloadViaPostTypeTaxText = __( 'Unload NOSCRIPT tag on all WooCommerce "Product" pages if these taxonomies (e.g. Category, Tag) are set', 'wp-asset-clean-up' );
                break;
            case 'download':
                $unloadViaPostTypeTaxText = __( 'Unload NOSCRIPT tag on all Easy Digital Downloads "Download" pages if these taxonomies (e.g. Category, Tag) are set', 'wp-asset-clean-up' );
                break;
            default:
                $unloadViaPostTypeTaxText = sprintf( __( 'Unload NOSCRIPT tag on all pages of "<strong>%s</strong>" post type if these taxonomies (category, tag, etc.) are set', 'wp-asset-clean-up' ), $data['post_type'] );
        }
    } else {
        switch ( $data['post_type'] ) {
            case 'product':
                $unloadViaPostTypeTaxText = __( 'Unload JS on all WooCommerce "Product" pages if these taxonomies (e.g. Category, Tag) are set', 'wp-asset-clean-up' );
                break;
            case 'download':
                $unloadViaPostTypeTaxText = __( 'Unload JS on all Easy Digital Downloads "Download" pages if these taxonomies (e.g. Category, Tag) are set', 'wp-asset-clean-up' );
                break;
            default:
                $unloadViaPostTypeTaxText = sprintf( __( 'Unload JS on all pages of "<strong>%s</strong>" post type if these taxonomies (category, tag, etc.) are set', 'wp-asset-clean-up' ), $data['post_type'] );
        }
    }
} else {
    switch ( $data['post_type'] ) {
        case 'product':
            $unloadViaPostTypeTaxText = __( 'Unload CSS on all WooCommerce "Product" pages if these taxonomies (e.g. Category, Tag) are set', 'wp-asset-clean-up' );
            break;
        case 'download':
            $unloadViaPostTypeTaxText = __( 'Unload CSS on all Easy Digital Downloads "Download" pages if these taxonomies (e.g. Category, Tag) are set', 'wp-asset-clean-up' );
            break;
        default:
            $unloadViaPostTypeTaxText = sprintf( __( 'Unload CSS on all pages of "<strong>%s</strong>" post type if these taxonomies (category, tag, etc.) are set', 'wp-asset-clean-up' ), $data['post_type'] );
    }
}

// Unload it if the post has a certain "Category", "Tag" or other taxonomy associated with it.
// [wpacu_pro]
$handleUnloadViaTax = ( isset( $data['handle_unload_post_type_via_tax'][$assetType][ $data['row']['obj']->handle ] ) && $data['handle_unload_post_type_via_tax'][$assetType][ $data['row']['obj']->handle ] )
    ? $data['handle_unload_post_type_via_tax'][$assetType][ $data['row']['obj']->handle ]
    : array();

$handleUnloadViaTax['enable'] = isset( $handleUnloadViaTax['enable'] ) && $handleUnloadViaTax['enable'];
$handleUnloadViaTax['values'] = isset( $handleUnloadViaTax['values'] ) && $handleUnloadViaTax['values'] ? $handleUnloadViaTax['values'] : '';

$isUnloadViaTaxEnabledWithValues = ($handleUnloadViaTax['enable'] && ! empty($handleUnloadViaTax['values']));

if ($isUnloadViaTaxEnabledWithValues) { $data['row']['at_least_one_rule_set'] = true; }
// [/wpacu_pro]
?>
<!-- [wpacu_pro] -->
<div class="wpacu_asset_options_wrap wpacu_manage_via_tax_area_wrap">
    <ul class="wpacu_asset_options">
        <li>
            <label for="wpacu_unload_it_post_type_via_tax_option_<?php echo $assetTypeS; ?>_<?php echo htmlentities(esc_attr($data['row']['obj']->handle), ENT_QUOTES); ?>"
                <?php
                // [wpacu_pro]
                if ( $isUnloadViaTaxEnabledWithValues ) {
                    echo ' class="wpacu_unload_checked"';
                }
                // [/wpacu_pro]
                ?>>
                <input data-handle="<?php echo htmlentities(esc_attr($data['row']['obj']->handle), ENT_QUOTES); ?>"
                       data-handle-for="<?php echo $assetTypeS; ?>"
                       id="wpacu_unload_it_post_type_via_tax_option_<?php echo $assetTypeS; ?>_<?php echo htmlentities(esc_attr($data['row']['obj']->handle), ENT_QUOTES); ?>"
                       class="wpacu_unload_it_post_type_via_tax_checkbox wpacu_unload_rule_input wpacu_bulk_unload"
                       type="checkbox"
                       name="<?php echo WPACU_FORM_ASSETS_POST_KEY; ?>[<?php echo $assetType; ?>][unload_post_type_via_tax][<?php echo htmlentities(esc_attr($data['row']['obj']->handle), ENT_QUOTES); ?>][enable]"
                    <?php
                    // [wpacu_pro]
                    if ( $isUnloadViaTaxEnabledWithValues ) { ?>
                        checked="checked"
                    <?php
                    }
                    // [/wpacu_pro]
                    ?>
                       value="1"/>&nbsp;
                <span><?php echo wp_kses($unloadViaPostTypeTaxText, array('strong' => array())); ?>:</span>
            </label>
            <a style="text-decoration: none; color: inherit; vertical-align: middle;" target="_blank"
               href="https://www.assetcleanup.com/docs/?p=1415#unload"><span
                        class="dashicons dashicons-editor-help"></span></a>
            <!-- [wpacu_pro] -->
            <div class="wpacu_handle_manage_post_type_via_tax_input_wrap wpacu_handle_unload_post_type_via_tax_input_wrap <?php if ( ! $isUnloadViaTaxEnabledWithValues ) { echo 'wpacu_hide'; } ?>">
                <div class="wpacu_manage_via_tax_rule_area" style="min-width: 300px;">
                    <select name="<?php echo WPACU_FORM_ASSETS_POST_KEY; ?>[<?php echo $assetType; ?>][unload_post_type_via_tax][<?php echo htmlentities(esc_attr($data['row']['obj']->handle), ENT_QUOTES); ?>][values][]"
                            class="wpacu_post_type_via_tax_dd wpacu_unload_post_type_via_tax_dd <?php if ($isUnloadViaTaxEnabledWithValues && $data['plugin_settings']['input_style'] === 'enhanced') { echo ' wpacu_chosen_select '; } echo ($data['plugin_settings']['input_style'] === 'enhanced') ? ' wpacu_chosen_can_be_later_enabled ' : ''; ?>"
                            data-placeholder="<?php esc_attr_e('Select taxonomies added to the post type'); ?>..."
                            multiple="multiple"
                            data-handle="<?php echo htmlentities(esc_attr($data['row']['obj']->handle), ENT_QUOTES); ?>"
                            data-handle-for="<?php echo $assetTypeS; ?>"><?php if ( $isUnloadViaTaxEnabledWithValues ) { echo MainAdminPro::loadDDOptionsForAllSetTermsForPostType($data['post_type'], $assetType, $data['row']['obj']->handle, $handleUnloadViaTax['values']); } ?></select>
                </div>
            </div>
            <div data-wpacu-tax-terms-options-loader="1" style="display: none; margin: 10px 0 10px;">
                <img src="<?php echo WPACU_PLUGIN_URL; ?>/assets/icons/loader-horizontal.svg?x=<?php echo time(); ?>"
                     align="top"
                     width="90"
                     alt="" />
            </div>
            <!-- [/wpacu_pro] -->
        </li>
    </ul>
</div>
<!-- [/wpacu_pro] -->