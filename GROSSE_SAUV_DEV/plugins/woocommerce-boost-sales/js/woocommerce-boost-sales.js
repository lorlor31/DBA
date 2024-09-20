jQuery(document).ready(function ($) {
    'use strict';

    jQuery(document).on('click', '#wbs-content-discount-bar .wbs-overlay, #wbs-content-discount-bar .wbs-close', function () {
        jQuery('#wbs-content-discount-bar').fadeOut(200);
        clearTimeout(time_redirect);
        woocommerce_boost_sales_params.show_thank_you = true;
    });
    jQuery('body').on('click', '.wbs-button-continue-stay', function (e) {
        e.preventDefault();
        jQuery(this).closest('.woocommerce-boost-sales').find('.wbs-close').click();
    });

    if (typeof woocommerce_boost_sales_params !== 'undefined') {
        var $woocommerce_boost_sales_cross_sells = jQuery('.wbs-crosssells');
        if ($woocommerce_boost_sales_cross_sells.length > 0) {
            var bundle_selects = $woocommerce_boost_sales_cross_sells.find('select');
            $woocommerce_boost_sales_cross_sells.find('.wbs-variations_form').map(function () {
                let $form = $(this), $frequently_item = $form.closest('.wbs-product'),
                    $current_product_image = $frequently_item.find('.product-image img').eq(0);
                $form.wc_variation_form();
                $form.on('found_variation', function (e, variation) {
                    if (variation.attributes && variation.is_in_stock && variation.is_purchasable) {
                        $frequently_item.data('variation_id', variation['variation_id']);
                        if (variation.price_html) {
                            $frequently_item.data('item_price', parseFloat(variation['display_price']));
                            $frequently_item.find('.price:not(wbs-bundle-item-variation-price)').hide();
                            $frequently_item.find('.wbs-bundle-item-variation-price').html($(variation['price_html']).html()).show();
                        }
                        change_product_image(variation['image'], $current_product_image);
                    }
                    var enable_add_to_cart = true;
                    for (var i = 0; i < bundle_selects.length; i++) {
                        if (bundle_selects.eq(i).val() == '') {
                            enable_add_to_cart = false;
                            break;
                        }
                    }
                    if (enable_add_to_cart) {
                        woo_boost_sale.handle_price($woocommerce_boost_sales_cross_sells, true);
                        $woocommerce_boost_sales_cross_sells.find('.wbs-single_add_to_cart_button').removeClass('disabled wc-variation-selection-needed');
                    } else {
                        woo_boost_sale.handle_price($woocommerce_boost_sales_cross_sells);
                        $woocommerce_boost_sales_cross_sells.find('.wbs-single_add_to_cart_button').addClass('disabled wc-variation-selection-needed');
                    }
                });
            });
            if (bundle_selects.length > 0) {
                $woocommerce_boost_sales_cross_sells.find('.wbs-single_add_to_cart_button').addClass('disabled wc-variation-selection-needed');
                bundle_selects.on('change', function () {
                    if (!$(this).val()) {
                        let $current_product = $(this).closest('.wbs-product'),
                            $current_product_image = $current_product.find('.product-image img').eq(0);
                        $current_product.find('.price:not(wbs-bundle-item-variation-price)').show();
                        $current_product.find('.wbs-bundle-item-variation-price').hide();
                        change_product_image($current_product.data('item_image'), $current_product_image);
                        woo_boost_sale.handle_price($woocommerce_boost_sales_cross_sells);
                        $woocommerce_boost_sales_cross_sells.find('.wbs-single_add_to_cart_button').addClass('disabled wc-variation-selection-needed');
                    }
                })
            }
        }

        var side_cart_auto_open = woocommerce_boost_sales_params.side_cart_auto_open;
        if (woocommerce_boost_sales_params.ajax_add_to_cart_for_upsells === 'yes') {
            submit_form_upsell(side_cart_auto_open);
        }
        if (woocommerce_boost_sales_params.ajax_add_to_cart_for_crosssells === 'yes') {
            submit_form_crosssell(side_cart_auto_open);
        }
    }
    woo_boost_sale.init();
    woo_boost_sale.add_to_cart();
    if (woocommerce_boost_sales_params.added_to_cart) {
        if (woocommerce_boost_sales_params.auto_redirect && woocommerce_boost_sales_params.auto_redirect_time) {
            jQuery('.auto-redirect').html(woocommerce_boost_sales_params.auto_redirect_message);
            woo_boost_sale.counter(jQuery('.auto-redirect span'), woocommerce_boost_sales_params.auto_redirect_time);
        }
    }

    function change_product_image(image_data, $image) {
        if (image_data) {
            if (image_data.hasOwnProperty('srcset') && image_data.srcset) {
                $image.attr('srcset', image_data.srcset);
            } else {
                $image.attr('srcset', '');
            }
            if (image_data.hasOwnProperty('thumb_src') && image_data.thumb_src) {
                $image.attr('src', image_data.thumb_src);
            } else if (image_data.hasOwnProperty('url') && image_data.url) {
                $image.attr('src', image_data.url);
            }
        }
    }

    $(document).on('click', function () {
        $('.vi-wbs-show-select').removeClass('vi-wbs-show-select');
    });

    $(document).on('click', '.vi-wbs-item-attributes-select-options', function (e) {
        e.stopPropagation();
        $('.vi-wbs-chosen.wbs-variation.wbs-product').removeClass('wbs-item-active');
        let $button = $(this), $item = $button.closest('.wbs-cart');
        $('.wbs-cart').not($item).removeClass('vi-wbs-show-select');
        $item.toggleClass('vi-wbs-show-select');
        $button.closest('.vi-wbs-chosen.wbs-variation.wbs-product').addClass('wbs-item-active');
        console.log($button.closest('.vi-wbs-chosen.wbs-variation.wbs-product'))
    });

    $(document).on('click', '.vi-wbs-item-attributes-select-modal', function (e) {
        e.stopPropagation();
    });

    if (typeof wbs_frequently_product_params === 'undefined') {
        $(document).on('change', '.vi-wbs-frequently-product-item-attributes-select-item', function () {
            $(this).closest('.vi-wbs-frequently-product-item-attributes-select-container').find('.vi-wbs-fp-variation').map(function () {
                let $item = $(this),
                    $current_select = $item.find('.vi-wbs-frequently-product-item-attributes-select-item'),
                    current_select = $current_select.val();
                $item.find('.vi-wbs-fp-value-option').map(function () {
                    let $button = $(this),
                        $option = $current_select.find(`option[value="${$.escapeSelector($button.data('wbs_fp_option'))}"]`);
                    if ($option.length > 0 && !$option.prop('disabled')) {
                        $button.removeClass('vi-wbs-fp-value-disabled');
                    } else {
                        $button.addClass('vi-wbs-fp-value-disabled');
                    }
                });
                $item.find('.vi-wbs-fp-value-selected').removeClass('vi-wbs-fp-value-selected');
                if (current_select) {
                    $item.find(`.vi-wbs-fp-value-option[data-wbs_fp_option="${$.escapeSelector(current_select)}"]`).addClass('vi-wbs-fp-value-selected');
                }
            });
        });

        $(document).on('click', '.vi-wbs-fp-value-option', function (e) {
            let $button = $(this);
            if (!$button.hasClass('vi-wbs-fp-value-disabled')) {
                let $attribute_container = $button.closest('.vi-wbs-fp-value');
                if ($button.hasClass('vi-wbs-fp-value-selected')) {
                    $attribute_container.find('.vi-wbs-frequently-product-item-attributes-select-item').val('').trigger('change');
                } else {
                    $attribute_container.find('.vi-wbs-frequently-product-item-attributes-select-item').val($button.data('wbs_fp_option')).trigger('change');
                }
            }
        });

        function init_item_form() {
            $('.vi-wbs-frequently-product-item-attributes-select-container:not(.vi-wbs-frequently-product-item-attributes-select-container-init)').map(function () {
                let $form = $(this), $frequently_item = $form.closest('.vi-wbs-frequently-product-item'),
                    $frequently_product = $form.closest('.vi-wbs-frequently-products-container'),
                    $current_product_image = $frequently_item.find('img').eq(0);
                $form.wc_variation_form();
                $form.on('found_variation', function (e, variation) {
                    if (variation.attributes && variation.is_in_stock && variation.is_purchasable) {
                        let attributes = [], variation_attributes = {};
                        $form.find('.vi-wbs-frequently-product-item-attributes-select-item').map(function () {
                            let $select = $(this), $selected = $select.find(':selected');
                            variation_attributes[$select.data('attribute_name')] = $select.val();
                            attributes.push($selected.html());
                        });
                        let selected_attributes = attributes.join(', ');
                        $frequently_item.data('variation_id', variation['variation_id']);
                        $frequently_item.data('variation_attributes', variation_attributes);
                        if (variation.price_html) {
                            $frequently_item.data('item_price', parseFloat(variation['display_price']));
                            $frequently_item.find('.vi-wbs-frequently-product-item-price').html(variation['price_html']);
                            $frequently_item.find('.vi-wbs-frequently-product-item-price .price').removeClass('price');
                        }
                        $frequently_item.find('.vi-wbs-frequently-product-item-attributes-value').html(selected_attributes).attr('title', selected_attributes);
                        let variation_image = variation['image'];
                        if (variation_image.hasOwnProperty('srcset') && variation_image.srcset) {
                            $current_product_image.attr('srcset', variation_image.srcset);
                        }
                        if (variation_image.hasOwnProperty('thumb_src') && variation_image.thumb_src) {
                            $current_product_image.attr('src', variation_image.thumb_src);
                        } else if (variation_image.hasOwnProperty('url') && variation_image.url) {
                            $current_product_image.attr('src', variation_image.url);
                        }
                        // handle_price($frequently_product);
                    }
                });
                $form.addClass('vi-wbs-frequently-product-item-attributes-select-container-init');
            });
        }

        init_item_form();

        $('.vi-wbs-frequently-product-item-attributes-select-item').map(function () {
            $(this).trigger('change');
        });

        $('.single_variation_wrap').on('show_variation', function (event, variation) {
            if (variation.attributes && variation.is_in_stock && variation.is_purchasable) {
                let $form = $(this).closest('.variations_form ');
                $(`.vi-wbs-frequently-product-item[data-product_id="${$form.data('product_id')}"]`).map(function () {
                    let $frequently_product = $(this);
                    if (variation.variation_id && $frequently_product.data('variation_id') != variation.variation_id) {
                        $frequently_product.find('select.vi-wbs-frequently-product-item-attributes-select-item').val('').trigger('change');
                        for (let attr_name in variation.attributes) {
                            $frequently_product.find(`select.vi-wbs-frequently-product-item-attributes-select-item[data-attribute_name="${attr_name}"]`).val(variation.attributes[attr_name]).trigger('change')
                        }
                    }
                });
            }
        });
    }
});


function wbs_sort_object(object) {
    'use strict';
    return Object.keys(object).sort().reduce(function (result, key) {
        result[key] = object[key];
        return result;
    }, {});
}

function submit_form_upsell(side_cart_auto_open) {
    'use strict';
    jQuery('#wbs-content-upsells').unbind().on('submit', '.cart,.variations_form cart,.woocommerce-boost-sales-cart-form', function (e) {
        e.preventDefault();
        var data = jQuery(this).serializeArray();
        var data1 = jQuery(this).data();
        var button = jQuery(this).find('button[type="submit"]');
        var product_id = button.val() ? button.val() : data1['product_id'];
        var container = jQuery(this).parent().parent().parent();
        var container_mobile = jQuery(this).parent().parent().parent().parent().parent();
        var item_height = container_mobile.find('.wbs-upsells-item-main').css('height');
        container_mobile.find('.wbs-upsells-item-main').css({'max-height': item_height});
        button.attr('disabled', 'disabled').addClass('wbs-loading');
        data.push({name: button.attr('name'), value: button.val()});
        jQuery.ajax({
            url: jQuery(this).attr('action'),
            type: jQuery(this).attr('method'),
            data: data,
            success: function (response) {
                container.find('.wbs-upsells-add-items').html('<span class="wbs-icon-added"></span> ' + woocommerce_boost_sales_params.i18n_added_to_cart);
                container_mobile.addClass('wbs-upsells-item-added');
                button.removeAttr('disabled').removeClass('wbs-loading');
                // jQuery('body').trigger("wc_fragment_refresh");
                jQuery(document.body).trigger('updated_wc_div');
                if (1 == side_cart_auto_open && !jQuery('.xoo-wsc-modal').hasClass('xoo-wsc-active')) {
                    jQuery('.xoo-wsc-basket').click();
                }
                jQuery('#nm-menu-cart-btn').click()
            },
            error: function (err) {
                button.removeAttr('disabled');
            }
        });
    });
}

function submit_form_crosssell(side_cart_auto_open) {
    'use strict';
    jQuery('#wbs-content-cross-sells').unbind().on('submit', '.woocommerce-boost-sales-cart-form', function (e) {
        e.preventDefault();
        var data = jQuery(this).serializeArray();
        var button = jQuery(this).find('button[type="submit"]');
        var product_id = button.parent().find('input[name="add-to-cart"]').val();
        button.attr('disabled', 'disabled');
        data.push({name: button.attr('name'), value: button.val()});
        jQuery('.wbs-content-crossell').addClass('wbs-adding-to-cart');
        jQuery.ajax({
            url: jQuery(this).attr('action'),
            type: jQuery(this).attr('method'),
            data: data,
            success: function (response) {
                button.removeAttr('disabled');
                // jQuery('body').trigger("wc_fragment_refresh");
                jQuery(document.body).trigger('updated_wc_div');
                jQuery('.wbs-content-crossell').addClass('wbs-added-to-cart');
                if (1 == side_cart_auto_open && !jQuery('.xoo-wsc-modal').hasClass('xoo-wsc-active')) {
                    jQuery('.xoo-wsc-basket').click();
                }
                jQuery('#nm-menu-cart-btn').click()
                setTimeout(function () {
                    jQuery('#wbs-content-cross-sells').fadeOut(200);
                    jQuery('.gift-button').fadeOut(200);
                    jQuery('html').removeClass('wbs-html-overflow');
                    jQuery('.wbs-content-crossell').removeClass('wbs-adding-to-cart').removeClass('wbs-added-to-cart');
                }, 2000);
            },
            error: function (err) {
                button.removeAttr('disabled');
            }
        });
    });


    jQuery('#wbs-content-cross-sells-product-single').css({'max-height': jQuery('#wbs-content-cross-sells-product-single').css('height')}).unbind().on('submit', '.woocommerce-boost-sales-cart-form', function (e) {
        e.preventDefault();
        var data = jQuery(this).serializeArray();
        var button = jQuery(this).find('button[type="submit"]');
        button.attr('disabled', 'disabled');
        data.push({name: button.attr('name'), value: button.val()});
        jQuery.ajax({
            url: jQuery(this).attr('action'),
            type: jQuery(this).attr('method'),
            data: data,
            success: function (response) {
                button.removeAttr('disabled');
                // jQuery('body').trigger("wc_fragment_refresh");
                jQuery(document.body).trigger('updated_wc_div');
                jQuery('.wbs-content-cross-sells-product-single-container').addClass('wbs-added-to-cart');
                if (1 == side_cart_auto_open && !jQuery('.xoo-wsc-modal').hasClass('xoo-wsc-active')) {
                    jQuery('.xoo-wsc-basket').click();
                }
                jQuery('#nm-menu-cart-btn').click()
            },
            error: function (err) {
                button.removeAttr('disabled');
            }
        });
    });
}

var time_redirect;
var cross_sell_init;
var woo_boost_sale = {
    hide_crosssell_init: 0,
    check_quantity: 0,
    init: function () {
        if (typeof wbs_add_to_cart_params == 'undefined' || parseInt(wbs_add_to_cart_params.ajax_button) != 1) {
            if (typeof viwsatc_sb_params === 'undefined' || viwsatc_sb_params.added_to_cart) {
                this.slider();
            }
        } else if (woocommerce_boost_sales_params.added_to_cart) {

            this.slider();
        }
        this.product_variation();
        woo_boost_sale.hide();
        if (!this.hide_crosssell_init) {
            this.initial_delay_icon();
        }
        jQuery('.gift-button').on('click', function () {
            // jQuery(document).scrollTop(0);
            //woo_boost_sale.hide_upsell();
            woo_boost_sale.show_cross_sell();
            //woo_boost_sale.slider_cross_sell();
            jQuery('.vi-wbs-headline').removeClass('wbs-crosssell-message').addClass('wbs-crosssell-message');

        });
        /*Cross sells below add to cart button*/
        if (jQuery('#wbs-content-cross-sells-product-single .wbs-crosssells').length > 0) {
            this.cross_slider();
        }
        jQuery('.woocommerce-boost-sales.wbs-content-up-sell .single_add_to_cart_button').unbind();
        // if (jQuery('.wbs-msg-congrats').length > 0) {
        //     var time = jQuery('.wbs-msg-congrats').attr('data-time');
        //     if (time) {
        //         woo_boost_sale.counter(jQuery('.auto-redirect span'), time);
        //     }
        // }
        jQuery('#wbs-gift-button-cat').on('click', function () {
            woo_boost_sale.hide_upsell();
            woo_boost_sale.show_cross_sell_archive();
        });
        if (jQuery('.vi-wbs-topbar').hasClass('wbs_top_bar')) {
            var windowsize = jQuery(window).width();
            jQuery('.vi-wbs-headline').css('top', '50px');
            if (windowsize >= 1366) {
                jQuery('.wbs-archive-upsells .wbs-content').css('margin-top', '45px');
            } else {
                jQuery('.wbs-archive-upsells .wbs-content').css('margin-top', '85px');
            }
        }
        if (jQuery('.vi-wbs-topbar').hasClass('wbs_bottom_bar')) {
        } else {
            var windowsize = jQuery(window).width();
            if (windowsize < 1366) {
                // jQuery('.wbs-archive-upsells .wbs-content').css('margin-top', '70px');
            }
            if (windowsize < 640) {
                jQuery('.wbs-archive-upsells .wbs-content').css('margin-top', '0px');
            }
        }
        if (jQuery('.wbs-message-success').length < 1) {
            jQuery('.wbs-content-up-sell').css('height', '100%');
        }
        if (jQuery('.wbs-content').hasClass('wbs-msg-congrats')) {
            setTimeout(function () {
                jQuery('.vi-wbs-headline').show();
            }, 0);
        }
        jQuery(document).on('click', '.vi-wbs_progress_close', function () {
            jQuery('.vi-wbs-topbar').fadeOut('slow');
        });
        if (!jQuery('#flexslider-cross-sell .vi-flex-prev').hasClass('vi-flex-disabled')) {
            jQuery('#flexslider-cross-sell').hover(function () {
                jQuery('#flexslider-cross-sell .vi-flex-prev').css("opacity", "1");
            }, function () {
                jQuery('#flexslider-cross-sell .vi-flex-prev').css("opacity", "0");
            });
        }
        if (!jQuery('#flexslider-cross-sell .vi-flex-next').hasClass('vi-flex-disabled')) {
            jQuery('#flexslider-cross-sell').hover(function () {
                jQuery('#flexslider-cross-sell .vi-flex-next').css("opacity", "1");
            }, function () {
                jQuery('#flexslider-cross-sell .vi-flex-next').css("opacity", "0");
            });
        }
        /*Smooth Archive page*/
        jQuery('.wbs-wrapper').animate({
            opacity: 1
        }, 200);
        woo_boost_sale.chosen_variable_upsell();
        jQuery('.wbs-upsells > .wbs-').find('div.vi-wbs-chosen:first').removeClass('wbs-hidden-variable').addClass('wbs-show-variable');

    },
    product_variation: function () {
        jQuery('#wbs-content-upsells').find('.wbs-variations_form').each(function () {
            // jQuery(this).addClass('variations_form');
            jQuery(this).wc_variation_form();
        });
        jQuery('#wbs-content-upsells').on('check_variations', function () {
            jQuery(this).find('.variations_button').each(function () {
                if (jQuery(this).hasClass('woocommerce-variation-add-to-cart-disabled')) {
                    jQuery(this).find('.wbs-single_add_to_cart_button').addClass('disabled wc-variation-selection-needed');
                } else {
                    jQuery(this).find('.wbs-single_add_to_cart_button').removeClass('disabled wc-variation-selection-needed');
                }
            });
        });

        jQuery('#wbs-content-upsells').on('show_variation', function () {
            jQuery(this).find('.variations_button').each(function () {
                if (jQuery(this).hasClass('woocommerce-variation-add-to-cart-disabled')) {
                    jQuery(this).find('.wbs-single_add_to_cart_button').addClass('disabled wc-variation-selection-needed');
                } else {
                    jQuery(this).find('.wbs-single_add_to_cart_button').removeClass('disabled wc-variation-selection-needed');
                }
            })
        });
        jQuery('.wbs-single_add_to_cart_button').on('click', function (e) {
            if (jQuery(this).is('.disabled')) {
                e.preventDefault();

                if (jQuery(this).hasClass('wc-variation-is-unavailable')) {
                    window.alert(wc_add_to_cart_variation_params.i18n_unavailable_text);
                } else if (jQuery(this).hasClass('wc-variation-selection-needed')) {
                    window.alert(wc_add_to_cart_variation_params.i18n_make_a_selection_text);
                }
            }
        })
    },
    add_to_cart: function () {
        var check_quantity = 0, $upsells = jQuery('.wbs-content-up-sell');
        jQuery(document).ajaxComplete(function (event, jqxhr, settings) {
            if (settings.hasOwnProperty('contentType') && settings.contentType === false) {
                return;
            }
            var ajax_link = settings.url;
            var data_post = settings.data;
            var product_id = 0;
            var variation_id = 0;
            var check_variation = 0;
            if (data_post == '' || data_post == null || jQuery.isEmptyObject(data_post)) {
                return;
            }
            var data_process = data_post.split('&');
            /*Process get Product ID - Require product_id*/
            for (var i = 0; i < data_process.length; i++) {
                if (data_process[i].search(/product_id/i) >= 0) {
                    product_id = data_process[i];
                } else if (data_process[i].search(/add-to-cart/i) >= 0) {
                    product_id = data_process[i];
                }
                if (data_process[i].search(/variation_id/i) >= 0) {
                    variation_id = data_process[i];
                    check_variation = 1;
                }
            }
            /*Reformat Product ID*/
            if (check_variation) {
                if (variation_id) {
                    product_id = variation_id.replace(/^\D+/g, '');
                    product_id = parseInt(product_id);
                } else {
                    return;
                }
            } else {
                if (product_id) {
                    product_id = product_id.replace(/^\D+/g, '');
                    product_id = parseInt(product_id);
                } else {
                    return;
                }
            }
            if (ajax_link.search(/wc-ajax=add_to_cart/i) >= 0 || ajax_link.search(/wc-ajax=xoo_wsc_add_to_cart/i) >= 0 || ajax_link.search(/wc-ajax=viwcaio_add_to_cart/i) >= 0 || ajax_link.search(/wc-ajax=wpvs_add_to_cart/i) >= 0 || data_post.search(/action=wbs_ajax_add_to_cart/i) >= 0 || data_post.search(/action=wacv_ajax_add_to_cart/i) >= 0 || data_post.search(/action=woofc_update_cart/i) >= 0) {
                let added_to_cart = [];
                if (jqxhr !== undefined && jqxhr.hasOwnProperty('responseJSON') && jqxhr.responseJSON) {
                    if (jqxhr.responseJSON.hasOwnProperty('fragments') && jqxhr.responseJSON.fragments) {
                        let fragments = jqxhr.responseJSON.fragments;
                        if (fragments.hasOwnProperty('wbs_added_to_cart') && fragments.wbs_added_to_cart) {
                            if (fragments.wbs_added_to_cart.hasOwnProperty(product_id) && fragments.wbs_added_to_cart[product_id]) {
                                added_to_cart = fragments.wbs_added_to_cart;
                            }
                        }
                        if (fragments.hasOwnProperty('wbs_upsells_html')) {
                            if (fragments['wbs_upsells_html']) {
                                if (fragments['wbs_upsells_html'].search(/wbs-overlay/i) < 1) {
                                    jQuery('html').removeClass('wbs-html-overflow');
                                    jQuery('.vi-wbs-topbar').animate({opacity: 1}, 500);
                                }
                                if ($upsells.length === 0) {
                                    $upsells = jQuery('<div id="wbs-content-upsells" class="woocommerce-boost-sales wbs-content-up-sell wbs-archive-page" style="display: none;"></div>');
                                    jQuery('body').append($upsells);
                                }
                                $upsells.html(fragments['wbs_upsells_html']);
                                $upsells.css({
                                    'opacity': 0,
                                    'display': 'flex',
                                    'visibility': 'visible'
                                }).animate({'opacity': 1}, 300);
                                woo_boost_sale.hide_crosssell_init = 1;
                                woo_boost_sale.init();
                                woo_boost_sale.slider();
                                setTimeout(function () {
                                    jQuery('.wbs-wrapper').animate({
                                        opacity: 1
                                    }, 200);
                                }, 200);
                            }
                        }
                    }
                }
                if (typeof wbs_add_to_cart_params == 'undefined' || parseInt(wbs_add_to_cart_params.ajax_button) != 1) {

                    return;
                    if (added_to_cart) {
                        $upsells.html('<div class="wbs-overlay"><div class="wbs-loading"></div></div>').fadeIn(200);
                        jQuery.ajax({
                            type: 'POST',
                            data: {
                                action: 'wbs_get_product',
                                added_to_cart: added_to_cart,
                                id: product_id,
                            },
                            url: woocommerce_boost_sales_params.url,
                            success: function (response) {
                                if (response.upsells_html) {
                                    if (response.upsells_html.search(/wbs-overlay/i) < 1) {
                                        jQuery('html').removeClass('wbs-html-overflow');
                                        jQuery('.vi-wbs-topbar').animate({opacity: 1}, 500);
                                    }
                                    $upsells.html(response.upsells_html);
                                    $upsells.fadeIn();
                                    woo_boost_sale.hide_crosssell_init = 1;
                                    woo_boost_sale.init();
                                    woo_boost_sale.slider();
                                    setTimeout(function () {
                                        jQuery('.wbs-wrapper').animate({
                                            opacity: 1
                                        }, 200);
                                    }, 200);
                                } else if (response.upsells_html === false || !woocommerce_boost_sales_params.show_if_empty) {
                                    woo_boost_sale.hide();
                                    jQuery('.wbs-overlay').click();
                                }
                                var discount_bar_html = response.discount_bar_html;
                                if (discount_bar_html.hasOwnProperty('code')) {
                                    if (discount_bar_html.code == 200) {
                                        // jQuery('#wbs-content-discount-bar').html(discount_bar_html.html).css({'position': 'fixed'}).fadeIn(200);
                                    } else if (discount_bar_html.code == 201) {
                                        jQuery('#wbs-content-discount-bar').html(discount_bar_html.html).css({'position': ''}).fadeIn(200);
                                    }
                                }
                            },
                            error: function (error) {
                                jQuery('html').removeClass('wbs-html-overflow');
                            }
                        });
                    }
                } else {
                    if (check_quantity == 1) {
                        window.location.reload();
                    } else {
                        // jQuery.ajax({
                        //     type: 'POST',
                        //     data: 'action=wbs_show_bar&language=' + woocommerce_boost_sales_params.language,
                        //     url: woocommerce_boost_sales_params.url,
                        //     success: function (data) {
                        //         if (data !== null) {
                        //             if (data.code == 200) {
                        //                 jQuery('#wbs-content-discount-bar').html('');
                        //                 jQuery('#wbs-content-upsells').html(data.html).css({'visibility': 'visible'}).animate({'opacity': 1}, 300);
                        //                 jQuery('.vi-wbs-headline').css({'visibility': 'visible'}).animate({'opacity': 1}, 300);
                        //                 woo_boost_sale.hide();
                        //             } else if (data.code == 201) {
                        //                 jQuery('#wbs-content-discount-bar').html(data.html).css({
                        //                     'visibility': 'visible',
                        //                     'display': 'flex'
                        //                 }).animate({'opacity': 1}, 300);
                        //                 jQuery('.vi-wbs-headline').css({'visibility': 'visible'}).animate({'opacity': 1}, 300);
                        //                 woo_boost_sale.hide();
                        //             } else {
                        //                 if (jQuery('.wbs-archive-upsells').length < 1) {
                        //                     jQuery('html').removeClass('wbs-html-overflow');
                        //                 }
                        //             }
                        //         }
                        //     },
                        //     error: function (data) {
                        //     }
                        // });

                    }
                }
            }
        });

        if ($upsells.length > 0) {
            // jQuery(document).ajaxSend(function (event, jqxhr, settings) {
            //     if (settings.hasOwnProperty('contentType') && settings.contentType === false) {
            //         return;
            //     }
            //     var ajax_link = settings.url;
            //     var data_post = settings.data;
            //     var product_id = 0;
            //     var variation_id = 0;
            //     var check_variation = 0;
            //
            //     if (data_post == '' || data_post == null || jQuery.isEmptyObject(data_post)) {
            //         return;
            //     }
            //     var data_process = data_post.split('&');
            //
            //     for (var i = 0; i < data_process.length; i++) {
            //         if (data_process[i].search(/product_id/i) >= 0) {
            //             product_id = data_process[i];
            //         }
            //         if (data_process[i].search(/variation_id/i) >= 0) {
            //             variation_id = data_process[i];
            //             check_variation = 1;
            //         }
            //     }
            //     /*Reformat Product ID*/
            //     if (check_variation) {
            //         if (!variation_id) {
            //             return;
            //         }
            //     } else {
            //         if (!product_id) {
            //             return;
            //         }
            //     }
            //     if (ajax_link.search(/wc-ajax=add_to_cart/i) >= 0 || data_post.search(/action=wbs_ajax_add_to_cart/i) >= 0) {
            //         if (typeof wbs_add_to_cart_params == 'undefined' || parseInt(wbs_add_to_cart_params.ajax_button) != 1) {
            //             $upsells.html('<div class="wbs-overlay"><div class="wbs-loading"></div>').fadeIn(200);
            //         } else {
            //
            //         }
            //     }
            // });
        }
    },
    hide: function () {
        jQuery('.wbs-close, .woocommerce-boost-sales .wbs-overlay').unbind();
        jQuery('.wbs-close, .woocommerce-boost-sales .wbs-overlay').on('click', function () {
            jQuery('.woocommerce-boost-sales').not('.woocommerce-boost-sales-active-discount').fadeOut(200);
            jQuery('html').removeClass('wbs-html-overflow');
            clearTimeout(time_redirect);
            woocommerce_boost_sales_params.show_thank_you = true;
        });
    },
    slider: function () {
        var windowsize = jQuery(window).width();
        var item_per_row = jQuery('#flexslider-up-sell').attr('data-item-per-row');
        var item_per_row_mobile = jQuery('#flexslider-up-sell').attr('data-item-per-row-mobile');
        var rtl = jQuery('#flexslider-up-sell').attr('data-rtl');
        if (parseInt(rtl)) {
            rtl = true;
        } else {
            rtl = false;
        }
        if (item_per_row == undefined) {
            item_per_row = 4;
        }
        if (windowsize < 768 && windowsize >= 600) {
            item_per_row = 2;
        }
        if (windowsize < 600) {
            item_per_row = item_per_row_mobile;
        }
        /*Up-sells*/
        if (jQuery('#flexslider-up-sell').length > 0) {
            jQuery('#flexslider-up-sell').vi_flexslider({
                namespace: "woocommerce-boost-sales-",
                selector: '.wbs-vi-slides > .wbs-product',
                animation: "slide",
                animationLoop: false,
                itemWidth: 145,
                itemMargin: 12,
                controlNav: false,
                maxItems: item_per_row,
                reverse: false,
                slideshow: false,
                rtl: rtl
            });
            if (jQuery('#wbs-content-upsells').hasClass('wbs-form-submit') || (typeof wbs_add_to_cart_params != 'undefined' && parseInt(wbs_add_to_cart_params.ajax_button) != 1)) {
                jQuery('html').addClass('wbs-html-overflow');
            }
        }

    },
    cross_slider: function () {
        var rtl = jQuery('.wbs-cross-sells').attr('data-rtl');
        var windowsize = jQuery(window).width(),
            min_item = 3,
            itemMargin = 24,
            max_item = woocommerce_boost_sales_params.crosssells_max_item_desktop,
            cross_sells_single_width = jQuery('#flexslider-cross-sells').width();
        if (windowsize < 768 && windowsize >= 600) {
            min_item = 2;
            max_item = woocommerce_boost_sales_params.crosssells_max_item_tablet;
        }
        if (windowsize < 600) {
            itemMargin = 6;
            min_item = 1;
            max_item = woocommerce_boost_sales_params.crosssells_max_item_mobile;
        }
        if (max_item < 2) {
            max_item = 2;
        }
        if (parseInt(rtl)) {
            rtl = true;
        } else {
            rtl = false;
        }
        var slide_items = jQuery('#flexslider-cross-sells').find('.wbs-product').length;
        if (max_item > slide_items) {
            max_item = slide_items;
        }
        if (jQuery('#wbs-content-cross-sells-product-single #flexslider-cross-sells').length > 0) {
            itemMargin = 6;
            jQuery('#flexslider-cross-sells').vi_flexslider({
                namespace: "woocommerce-boost-sales-",
                selector: '.wbs-cross-sells > .wbs-product',
                animation: "slide",
                animationLoop: false,
                itemWidth: (parseInt(cross_sells_single_width / max_item) - 6),
                itemMargin: itemMargin,
                controlNav: false,
                maxItems: max_item,
                slideshow: false,
                rtl: rtl
            });
        } else {
            var $crs_flexslider = jQuery('#flexslider-cross-sells');
            if ($crs_flexslider.length > 0) {
                var itemWidth = 150;
                if (slide_items < 3) {
                    itemWidth = 175;
                }
                cross_sells_single_width = (itemWidth + 24) * max_item + 30;
                jQuery('.wbs-content-inner.wbs-content-inner-crs').css({'max-width': $crs_flexslider.find('.wbs-cross-sells').hasClass('wbs-products-1') ? 380 : cross_sells_single_width + 'px'});
                $crs_flexslider.vi_flexslider({
                    namespace: "woocommerce-boost-sales-",
                    selector: '.wbs-cross-sells > .wbs-product',
                    animation: "slide",
                    animationLoop: false,
                    itemWidth: itemWidth,
                    itemMargin: itemMargin,
                    controlNav: false,
                    maxItems: max_item,
                    slideshow: false,
                    rtl: rtl
                });
                jQuery('html').addClass('wbs-html-overflow');
            }
        }
    },
    hide_upsell: function () {
        jQuery('.wbs-content').fadeOut(200);
    },
    hide_cross_sell: function () {
        jQuery('#wbs-content-cross-sells').fadeOut(200);
    },
    show_cross_sell: function () {
        jQuery('#wbs-content-cross-sells').fadeIn('slow');
        jQuery('html').addClass('wbs-html-overflow');
        this.cross_slider();
        this.compatibility();
    },
    show_cross_sell_archive: function () {
        jQuery('#wbs-cross-sell-archive').fadeIn('slow');
        this.compatibility();
    },
    counter: function ($el, n) {
        var checkout_url = jQuery('.vi-wbs-btn-redeem').attr('href');
        (function loop() {
            $el.html(n);
            if (n == 0) {
                if (checkout_url) {
                    window.location.href = checkout_url;
                }
            }
            if (n--) {
                time_redirect = setTimeout(loop, 1000);
            }
        })();
    },
    initial_delay_icon: function () {
        if (jQuery('#wbs-content-cross-sells').length > 0) {
            var initial_delay = jQuery('#wbs-content-cross-sells').attr('data-initial_delay');
            var open = jQuery('#wbs-content-cross-sells').attr('data-open');
            cross_sell_init = setTimeout(function () {
                jQuery('.gift-button').fadeIn('medium');
                if (open > 0) {
                    woo_boost_sale.show_cross_sell()
                }
            }, initial_delay * 1000);
        }
    },
    chosen_variable_upsell: function () {
        jQuery('select.wbs-variable').on('change', function () {
            var selected = jQuery(this).val();
            jQuery(this).closest('div.wbs-product').find('.vi-wbs-chosen').removeClass('wbs-show-variable').addClass('wbs-hidden-variable');
            jQuery(this).closest('div.wbs-product').find('.wbs-variation-' + selected).removeClass('wbs-hidden-variable').addClass('wbs-show-variable');
        });
    },
    format_number(number, decimals, decimal_separator, thousand_separator) {
        if (decimals === undefined) {
            decimals = woocommerce_boost_sales_params.decimals;
        }
        if (decimal_separator === undefined) {
            decimal_separator = woocommerce_boost_sales_params.decimal_separator;
        }
        if (thousand_separator === undefined) {
            thousand_separator = woocommerce_boost_sales_params.thousand_separator;
        }
        /*First convert number to en-US format: "," as thousand separator and "." as decimal separator*/
        number = number.toLocaleString("en-US", {
            maximumFractionDigits: decimals,
            minimumFractionDigits: decimals
        });
        /*Split to integer and decimal parts*/
        let arr = number.split('.');
        /*Replace "," with correct thousand separator*/
        number = arr[0].split(',').join(thousand_separator);
        /*Join integer part with decimal part with correct decimal separator if any*/
        if (arr.length === 2) {
            number = number + decimal_separator + arr[1];
        }
        return number;
    },
    handle_price($woocommerce_boost_sales_cross_sells, is_validate = false) {
        let $items = $woocommerce_boost_sales_cross_sells.find('.wbs-product'), total_price = 0,
            $overall = jQuery('.wbs-crosssells-overall-price'),
            $total_origin = jQuery('.wbs-total-price-origin'),
            $total_current = jQuery('.wbs-total-price-current'),
            $save_origin = jQuery('.wbs-save-price-origin'),
            $save_current = jQuery('.wbs-save-price-current'),
            saved_type = parseInt($woocommerce_boost_sales_cross_sells.data('saved_type')),
            fixed_price = parseFloat($woocommerce_boost_sales_cross_sells.data('fixed_price')),
            $atc_price = jQuery('.wbs-crosssells-atc-price');
        $items.map(function () {
            let $loop_item = jQuery(this);
            total_price += parseInt($loop_item.data('item_quantity')) * parseFloat($loop_item.data('item_price'));
        });
        if (is_validate) {
            $overall.hide();
            $total_current.html(woocommerce_boost_sales_params['modal_price'].replace(woo_boost_sale.format_number(1), woo_boost_sale.format_number(total_price))).show();
            let discount_type = $woocommerce_boost_sales_cross_sells.data('discount_type'),
                discount_amount = $woocommerce_boost_sales_cross_sells.data('discount_amount'),
                final_price = total_price, saved_amount = 0;
            if ($woocommerce_boost_sales_cross_sells.data('dynamic_price')) {
                if (discount_amount) {
                    discount_amount = parseFloat($woocommerce_boost_sales_cross_sells.data('discount_amount'));
                } else {
                    discount_amount = 0;
                }
                if (discount_type === 'percent') {
                    final_price = total_price * (1 - discount_amount / 100);
                    if (final_price < 0) {
                        final_price = 0;
                    }
                } else {
                    final_price = total_price - discount_amount;
                    if (final_price < 0) {
                        final_price = 0;
                    }
                }
            } else {
                final_price = fixed_price
            }
            final_price = parseFloat(woo_boost_sale.format_number(final_price, undefined, '.', ''));
            saved_amount = total_price - final_price;
            if (saved_type === 0) {
                $save_origin.hide();
                $save_current.html(woocommerce_boost_sales_params['modal_price'].replace(woo_boost_sale.format_number(1), woo_boost_sale.format_number(saved_amount))).show();
            } else if (saved_type === 1) {
                $save_origin.hide();
                $save_current.html(`${woo_boost_sale.format_number(saved_amount * 100 / total_price, 0)}%`).show();
            }
            $total_origin.hide();
            $atc_price.html(woocommerce_boost_sales_params['modal_price'].replace(woo_boost_sale.format_number(1), woo_boost_sale.format_number(final_price))).show();
        } else {
            $overall.show();
            $total_origin.show();
            $total_current.hide();
            if (saved_type === 0) {
                $save_origin.show();
                $save_current.hide();
            }
            $atc_price.hide();
        }

    },
    compatibility: function () {
        /*Woodmart lazy loading images*/
        if (window.hasOwnProperty('woodmartThemeModule') && typeof window.woodmartThemeModule !== 'undefined') {
            window.woodmartThemeModule.$document.trigger('wood-images-loaded');
        }
    }
};
