jQuery(document).ready(function ($) {
    'use strict';
    let $discount_bar = $('#wbs-content-discount-bar');
    if (!woocommerce_boost_sales_params.added_to_cart) {
        woocommerce_boost_sales_params.show_thank_you = $discount_bar.find('.wbs-msg-congrats').length > 0;
    }
    $(document).ajaxComplete(function (event, jqxhr, settings) {
        let ajax_link = settings.url;
        let data_opts = settings.data;
        if (ajax_link && ajax_link != 'undefined' &&
            (ajax_link.search(/wc-ajax=add_to_cart/i) >= 0
                || ajax_link.search(/wc-ajax=xoo_wsc_add_to_cart/i) >= 0
                || ajax_link.search(/wc-ajax=viwcaio_add_to_cart/i) >= 0
                || ajax_link.search(/wc-ajax=wpvs_add_to_cart/i) >= 0
                || ajax_link.search(/wc-ajax=remove_from_cart/i) >= 0
                || ajax_link.search(/wc-ajax=get_refreshed_fragments/i) >= 0
                || ajax_link.search(/admin-ajax\.php/i) >= 0
                || ajax_link.search(/wc-ajax=xt_woofc_update_cart/i) >= 0
                || data_opts && data_opts != 'undefined' && data_opts.search(/action=basel_ajax_add_to_cart/i) >= 0
            )) {
            if (jqxhr.statusText === 'timeout') return;

            try {
                let responseData = JSON.parse(jqxhr.responseText);
                if (responseData.hasOwnProperty('fragments')) {
                    let fragment = responseData.fragments;
                    if (fragment.hasOwnProperty('wbs_discount_bar') && fragment['wbs_discount_bar'] && fragment['wbs_discount_bar']['code'] == 200) {
                        if (!woocommerce_boost_sales_params.show_thank_you) {
                            woocommerce_boost_sales_params.show_thank_you = true;
                            if (fragment.hasOwnProperty('#wbs-content-discount-bar') && fragment['#wbs-content-discount-bar']) {
                                $discount_bar = $('#wbs-content-discount-bar');
                                if (wbs_discount_bar_params.is_checkout) {
                                    $discount_bar.find('.vi-wbs-btn-redeem').remove();
                                }
                                $discount_bar.show()
                            }
                        }
                    } else {
                        woocommerce_boost_sales_params.show_thank_you = false;
                        if (fragment.hasOwnProperty('#wbs-content-discount-bar') && fragment['#wbs-content-discount-bar']) {
                            $discount_bar = $('#wbs-content-discount-bar');
                            if (wbs_discount_bar_params.is_checkout) {
                                $discount_bar.find('.vi-wbs-btn-redeem').remove();
                            }
                            $discount_bar.show()
                        }
                    }

                }
            } catch (e) {
            }
        }
    });
});