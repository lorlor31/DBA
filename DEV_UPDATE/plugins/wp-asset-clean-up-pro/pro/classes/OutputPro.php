<?php
namespace WpAssetCleanUpPro;

use WpAssetCleanUp\Main;
use WpAssetCleanUp\Misc;

/**
 * Class OutputPro
 * @package WpAssetCleanUpPro
 */
class OutputPro
{
	/**
	 * Output constructor.
	 */
	public function __construct()
	{
		add_action('wpacu_pro_frontend_before_asset_list', array($this, 'frontendBeforeAssetList'));

        // e.g. "Unload on All Pages of category taxonomy type * bulk unload" / "Unload on All Author Pages * bulk unload"
		add_action('wpacu_pro_bulk_unload_output',         array($this, 'bulkUnloadOutput'), 10, 3);

        // e.g. Make an exception (from an already bulk unloading rule), and "Load on All Pages of category taxonomy type * bulk unload" / "Load on All Author Pages * bulk unload"
        add_action('wpacu_pro_bulk_load_output',           array($this, 'bulkLoadOutput'), 10, 3);
	}

	/**
	 *
	 */
	public function frontendBeforeAssetList()
	{
	    global $wp_query;

		$object = $wp_query->get_queried_object();

		if (is_404()) {
			?>
			<p><strong><span style="color: #0f6cab;" class="dashicons dashicons-warning"></span> This is a <u>404 (Not Found)</u> page. Any changes made here will be applied to any URL that returns a 404 response.</strong></p>
			<?php
		}

		elseif (Main::isWpDefaultSearchPage()) {
			?>
			<p><strong><span style="color: #0f6cab;" class="dashicons dashicons-search"></span> This is a default WordPress <u>Search</u> page. Any changes made here will be applied to any search request made on this page.</strong></p>
			<?php
		}

		elseif (is_author()) {
			$authorName = '';

			if (isset($object->data->ID)) {
				$authorName = ($object->data->display_name) ?: $object->data->user_login;
            } elseif (function_exists('get_the_author_meta')) {
                $authorName = get_the_author_meta('display_name') ?: get_the_author_meta('user_login');
			}
			?>
			<p><strong><span style="color: #0f6cab;" class="dashicons dashicons-admin-users"></span> This is a WordPress "<?php echo esc_html($authorName); ?>" <u>Author</u> archive page. The changes will also be applied on its pagination pages too.</strong></p>
			<?php
		}

		elseif (is_date()) {
			?>
			<p><strong><span style="color: #0f6cab;" class="dashicons dashicons-calendar-alt"></span> This is a WordPress <u>Date</u> archive page. The changes will also be applied on its pagination pages too.</strong></p>
			<?php
		}

		elseif (is_tag()) {
		    $tagName = $object->name;
		    ?>
            <p><strong><span style="color: #0f6cab;" class="dashicons dashicons-tag"></span> This is a WordPress "<?php echo esc_html($tagName); ?>" <u>Tag</u> archive page. The changes will also be applied on its pagination pages too.</strong></p>
            <?php
        }

        elseif (isset($object->taxonomy)) {
		    $taxonomySlug = $object->taxonomy;

	        $isWooPage = false;

	        if (function_exists('is_woocommerce') && function_exists('is_product_category')
                && (is_woocommerce() || is_product_category())) {
		        $isWooPage = true;
	        }
		    ?>
            <p>
                <strong>
                <?php if ($isWooPage) { ?>
                    <img src="<?php echo esc_url(WPACU_PLUGIN_URL . '/assets/icons/woocommerce-icon-logo.svg'); ?>" alt="" style="height: 40px !important; margin-top: -6px; margin-right: 5px;" align="middle" />
                <?php } else { ?>
                    <span style="color: #0f6cab;" class="dashicons dashicons-category"></span><?php } ?> This is a WordPress "<?php echo esc_html($taxonomySlug); ?>" <u>Taxonomy</u> page. The changes will also be applied on its pagination pages too.
                </strong>
            </p>
			<?php
        }
	}

    /**
     * @return array|false
     */
    public static function showOutputForSpecificBulkRules()
    {
        $keyString = $checkBoxArrayKeyValue = $objectTax = false;

        // [START Front-end View]
        if (isset($_POST['action']) && $_POST['action'] === WPACU_PLUGIN_ID . '_print_loaded_hardcoded_assets') {
            // AJAX call; Check the parameters passed; The plugin is instructed if the call is from a taxonomy / author page
            $pageType = isset($_POST['page_type']) ? $_POST['page_type'] : '';

            if ( ! in_array($pageType, array('tax', 'author')) ) {
                return false;
            }

            if ($pageType === 'tax' && isset($_POST['tax_name']) && $_POST['tax_name']) {
                $taxName = sanitize_text_field($_POST['tax_name']);
                $keyString = 'taxonomy';
                $objectTax = (object)array('taxonomy' => $taxName);
                $checkBoxArrayKeyValue = $taxName;
            } elseif ($pageType === 'author') {
                $keyString = 'author';
                $checkBoxArrayKeyValue = 'all';
            }
        } elseif ( Main::instance()->isFrontendEditView ) {
            // Regular view
            global $wp_query;
            $objectTax = $wp_query->get_queried_object();

            if ( isset($objectTax->taxonomy) && ($taxName = $objectTax->taxonomy) ) {
                $keyString = 'taxonomy';
                $checkBoxArrayKeyValue = $taxName;
            } elseif (is_author()) {
                $keyString = 'author';
                $checkBoxArrayKeyValue = 'all';
            } else {
                return false;
            }
        }
        // [END Front-end View]

        // [START Dashboard View]
        elseif (Main::instance()->settings['dashboard_show']) {
            if (Misc::getVar('request', 'wpacu_taxonomy') && Misc::getVar('request', 'tag_id')) {
                $keyString = 'taxonomy';
                $objectTax = (object)array('taxonomy' => Misc::getVar('request', 'wpacu_taxonomy'));
                $checkBoxArrayKeyValue = $objectTax->taxonomy;
            } else {
                return false;
            }
        }
        // [END Dashboard View]

        return array('key_string' => $keyString, 'object_tax' => $objectTax, 'check_box_array_key_value' => $checkBoxArrayKeyValue);
    }

	/**
	 * @param array $data
	 * @param object $obj
	 * @param string $assetTypeS ('style' or 'script')
	 */
	public function bulkUnloadOutput($data, $obj, $assetTypeS)
    {
	    // Taxonomy or Author page
        // Post Type is already added in the Lite version
        // 404, Search and Date pages are considered as "single" pages and do not belong to this group
        // e.g. Search page will have the same assets unloaded disregarding the keyword used for the search
        // Same thing for the 404 page (does not matter the requested not found URL)
        $outputCheck = self::showOutputForSpecificBulkRules();

        if ( ! (isset($outputCheck['key_string']) && $outputCheck['key_string']) ) {
	        return;
        }

        $keyString = $outputCheck['key_string'];
        $objectTax = $outputCheck['object_tax'];
        $checkBoxArrayKeyValue = $outputCheck['check_box_array_key_value'];

        /*
		 * STYLES (.css)
		 */
        if ($assetTypeS === 'style') {
	        $bulkUnloadedStyles = ( ! empty($data['bulk_unloaded'][$keyString]['styles']) );
	        $isBulkUnloadedAsset = $bulkUnloadedStyles && in_array( $obj->handle, $data['bulk_unloaded'][ $keyString ]['styles'] );
            ?>
            <div class="wpacu_asset_options_wrap">
            <?php
            if ( $isBulkUnloadedAsset ) {
	            // Unloaded On Taxonomy Pages for the Selected Taxonomy (e.g. 'category', 'product_cat', 'post_tag' etc.)
	            if ( $keyString === 'taxonomy' ) {
		            ?>
                    <p><strong style="color: #d54e21;">This stylesheet is unloaded on all <u><?php echo esc_html($objectTax->taxonomy); ?></u> taxonomy pages.</strong></p>
                    <div style="height: 0; margin-top: -5px;" class="wpacu_clearfix"></div>
		            <?php
	            } elseif ( $keyString === 'author' ) {
		            ?>
                    <p><strong style="color: #d54e21;">This stylesheet is unloaded on all <u>author</u> pages.</strong></p>
                    <div style="height: 0; margin-top: -5px;" class="wpacu_clearfix"></div>
		            <?php
	            }
            }
	        ?>

            <ul class="wpacu_asset_options">
		        <?php
		        if ( $isBulkUnloadedAsset ) {
                ?>
                    <li>
                        <label><input data-handle="<?php echo esc_attr($obj->handle); ?>"
                                      class="wpacu_bulk_option wpacu_style wpacu_keep_bulk_rule"
                                      type="radio"
                                      name="wpacu_options_<?php echo esc_attr($keyString); ?>_styles[<?php echo esc_attr($obj->handle); ?>]"
                                      checked="checked"
                                      value="default"/> Keep bulk rule</label>
                    </li>

                    <li>
                        <label><input data-handle="<?php echo esc_attr($obj->handle); ?>"
                                      class="wpacu_bulk_option wpacu_style wpacu_remove_bulk_rule"
                                      type="radio"
                                      name="wpacu_options_<?php echo esc_attr($keyString); ?>_styles[<?php echo esc_attr($obj->handle); ?>]"
                                      value="remove"/> Remove bulk rule</label>
                    </li>
			        <?php
		        } else {
                ?>
                    <li>
                        <label for="wpacu_bulk_unload_<?php echo esc_attr($keyString); ?>_style_<?php echo esc_attr($obj->handle); ?>">
                            <input data-handle="<?php echo esc_attr($obj->handle); ?>"
                                      data-handle-for="style"
                                      class="wpacu_bulk_unload wpacu_unload_rule_input wpacu_<?php echo esc_attr($keyString); ?>_unload wpacu_<?php echo esc_attr($keyString); ?>_style"
                                      id="wpacu_bulk_unload_<?php echo esc_attr($keyString); ?>_style_<?php echo esc_attr($obj->handle); ?>"
                                      type="checkbox"
                                      name="wpacu_bulk_unload_styles[<?php echo esc_attr($keyString); ?>][<?php echo esc_attr($checkBoxArrayKeyValue); ?>][]"
                                      value="<?php echo esc_attr($obj->handle); ?>"/>

                            <?php if ($keyString === 'taxonomy') { ?>
                                Unload on All Pages of <strong><?php echo esc_attr($objectTax->taxonomy); ?></strong> taxonomy type
                            <?php } elseif ($keyString === 'author') { ?>
                                Unload on All <strong>Author</strong> Archive Pages
                            <?php } ?>
                            <small>* bulk unload</small>
                        </label>
                    </li>
                <?php
		        }
		        ?>
            </ul>
        </div>
            <?php
            /*
             * SCRIPTS (.js)
             */
        } elseif ($assetTypeS === 'script') {
	        $bulkUnloadedScripts = ( ! empty($data['bulk_unloaded'][$keyString]['scripts']) );
	        $isBulkUnloadedAsset = $bulkUnloadedScripts && in_array( $obj->handle, $data['bulk_unloaded'][ $keyString ]['scripts'] );
	        ?>
            <div class="wpacu_asset_options_wrap">
		        <?php
                if ( $isBulkUnloadedAsset ) {
	                // Unloaded On Taxonomy Pages for the Selected Taxonomy (e.g. 'category', 'product_cat', 'post_tag' etc.)
	                if ( $keyString === 'taxonomy' ) {
		                ?>
                        <p><strong style="color: #d54e21;">This JavaScript file is unloaded on all <u><?php echo esc_html($objectTax->taxonomy); ?></u> taxonomy pages.</strong></p>
                        <div class="wpacu_clearfix" style="margin-top: -5px; height: 0;"></div>
		                <?php
	                } elseif ( $keyString === 'author' ) {
		                ?>
                        <p><strong style="color: #d54e21;">This JavaScript file is unloaded on all <u>author</u> pages.</strong></p>
                        <div class="wpacu_clearfix"></div>
		                <?php
	                }
                }
		        ?>

                <ul class="wpacu_asset_options">
			        <?php
			        if ( $isBulkUnloadedAsset ) {
				        ?>
                        <li>
                            <label><input data-handle="<?php echo esc_attr($obj->handle); ?>"
                                          class="wpacu_bulk_option wpacu_script wpacu_keep_bulk_rule"
                                          type="radio"
                                          name="wpacu_options_<?php echo esc_attr($keyString); ?>_scripts[<?php echo esc_attr($obj->handle); ?>]"
                                          checked="checked"
                                          value="default"/>
                                Keep rule</label>
                        </li>

                        <li>
                            <label><input data-handle="<?php echo esc_attr($obj->handle); ?>"
                                          class="wpacu_bulk_option wpacu_script wpacu_remove_bulk_rule"
                                          type="radio"
                                          name="wpacu_options_<?php echo esc_attr($keyString); ?>_scripts[<?php echo esc_attr($obj->handle); ?>]"
                                          value="remove"/>
                                Remove bulk rule</label>
                        </li>
				        <?php
			        } else {
				        ?>
                        <li>
                            <label for="wpacu_bulk_unload_<?php echo esc_attr($keyString); ?>_script_<?php echo esc_attr($obj->handle); ?>">
                                <input data-handle="<?php echo esc_attr($obj->handle); ?>"
                                          data-handle-for="script"
                                          class="wpacu_bulk_unload wpacu_unload_rule_input wpacu_<?php echo esc_attr($keyString); ?>_unload wpacu_<?php echo esc_attr($keyString); ?>_script"
                                          id="wpacu_bulk_unload_<?php echo esc_attr($keyString); ?>_script_<?php echo esc_attr($obj->handle); ?>"
                                          type="checkbox"
                                          name="wpacu_bulk_unload_scripts[<?php echo esc_attr($keyString); ?>][<?php echo esc_attr($checkBoxArrayKeyValue); ?>][]"
                                          value="<?php echo esc_attr($obj->handle); ?>"/>

	                            <?php if ($keyString === 'taxonomy') { ?>
                                    Unload on All Pages of <strong><?php echo esc_html($objectTax->taxonomy); ?></strong> taxonomy type
	                            <?php } elseif ($keyString === 'author') { ?>
                                    Unload on All <strong>Author</strong> Pages
	                            <?php } ?>
                                <small>* bulk unload</small>
                            </label>
                        </li>
				        <?php
			        }
			        ?>
                </ul>
            </div>
            <?php
        }
    }

    /**
     * @param $data
     * @param $obj
     * @param $assetTypeS ("style" or "script")
     *
     * @return void
     */
    public function bulkLoadOutput($data, $obj, $assetTypeS)
    {
        // Taxonomy or Author page
        // Post Type is already added in the Lite version
        // 404, Search and Date pages are considered as "single" pages and do not belong to this group
        // e.g. Search page will have the same assets unloaded disregarding the keyword used for the search
        // Same thing for the 404 page (does not matter the requested not found URL)

        $outputCheck = self::showOutputForSpecificBulkRules();

        if ( ! (isset($outputCheck['key_string']) && $outputCheck['key_string']) ) {
            return;
        }

        $assetType = ($assetTypeS === 'style') ? 'styles' : 'scripts';

        if ($data['bulk_unloaded_type'] === 'taxonomy') {
            $loadBulkText = sprintf(__('On All Pages of "<strong>%s</strong>" taxonomy type', 'wp-asset-clean-up'),  $data['tax_name']);
            ?>
            <li>
                <label for="wpacu_load_it_option_tax_type_<?php echo $assetTypeS; ?>_<?php echo htmlentities(esc_attr($obj->handle), ENT_QUOTES); ?>">
                    <input data-handle="<?php echo htmlentities(esc_attr($obj->handle), ENT_QUOTES); ?>"
                           data-handle-for="<?php echo $assetTypeS; ?>"
                              id="wpacu_load_it_option_tax_type_<?php echo $assetTypeS; ?>_<?php echo htmlentities(esc_attr($obj->handle), ENT_QUOTES); ?>"
                              class="wpacu_load_it_option_tax_type wpacu_<?php echo $assetTypeS; ?> wpacu_load_exception"
                              type="checkbox"
                        <?php if ($data['row']['is_load_exception_via_tax_type']) { ?> checked="checked" <?php } ?>
                              name="<?php echo WPACU_FORM_ASSETS_POST_KEY; ?>[load_it_tax_type][<?php echo $assetType; ?>][]"
                              value="<?php echo htmlentities(esc_attr($obj->handle), ENT_QUOTES); ?>"/>
                <span><?php echo wp_kses($loadBulkText, array('strong' => array())); ?></span></label>
            </li>
            <?php
        } elseif ($data['bulk_unloaded_type'] === 'author') {
            $loadBulkText = __('On All Author Archive Pages', 'wp-asset-clean-up');
            ?>
            <li>
                <label for="wpacu_load_it_option_author_type_<?php echo $assetTypeS; ?>_<?php echo htmlentities(esc_attr($obj->handle), ENT_QUOTES); ?>">
                    <input data-handle="<?php echo htmlentities(esc_attr($obj->handle), ENT_QUOTES); ?>"
                           data-handle-for="<?php echo $assetTypeS; ?>"
                           id="wpacu_load_it_option_author_type_<?php echo $assetTypeS; ?>_<?php echo htmlentities(esc_attr($obj->handle), ENT_QUOTES); ?>"
                           class="wpacu_load_it_option_author_type wpacu_<?php echo $assetTypeS; ?> wpacu_load_exception"
                           type="checkbox"
                        <?php if ($data['row']['is_load_exception_via_author_type']) { ?> checked="checked" <?php } ?>
                           name="<?php echo WPACU_FORM_ASSETS_POST_KEY; ?>[load_it_author_type][<?php echo $assetType; ?>][]"
                           value="<?php echo htmlentities(esc_attr($obj->handle), ENT_QUOTES); ?>"/>
                    <span><?php echo wp_kses($loadBulkText, array('strong' => array())); ?></span></label>
            </li>
            <?php
        }
    }
}
