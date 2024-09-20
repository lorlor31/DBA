jQuery(document).ready(function ($) {
    'use strict';

    let currentProductTitle = $('.wbs-p-title').html();

    jQuery('button[name="add-to-cart"]:not(.wbs-ajax-add-to-cart)').each(function () {
        if (jQuery(this).closest('.woocommerce-boost-sales').length > 0) {

        } else if (jQuery(this).hasClass('wbs-ajax-add-to-cart')) {

        } else if (!jQuery(this).hasClass('pisol_buy_now_button')) {
            jQuery(this).remove();
        }
    });

    jQuery('button.single_add_to_cart_button:not(.wbs-ajax-add-to-cart)').each(function () {
        if (jQuery(this).closest('.woocommerce-boost-sales').length > 0) {

        } else if (!jQuery(this).hasClass('pisol_buy_now_button')) {
            jQuery(this).remove();
        }
    });

    if (jQuery('[name="variation_id"]').length > 0) {
        jQuery('[name="variation_id"]').each(function () {
            var variation_el = jQuery(this);
            var variation_val = variation_el.val();
            if (parseInt(variation_val) > 0 && variation_val) {
                variation_el.closest('form.cart').find('.wbs-ajax-add-to-cart').removeClass('disabled');
            } else {
                variation_el.closest('form.cart').find('.wbs-ajax-add-to-cart').addClass('disabled');
            }
            variation_el.on('change', function () {
                var variation_val = jQuery(this).val();
                if (parseInt(variation_val) > 0 && variation_val) {
                    jQuery(this).closest('form.cart').find('.wbs-ajax-add-to-cart').removeClass('disabled');
                } else {
                    jQuery(this).closest('form.cart').find('.wbs-ajax-add-to-cart').addClass('disabled');
                }
            })
        })
    }

    if (typeof wbs_wacv !== 'undefined') {
        if (wbs_wacv.compatible && document.cookie.search(/wacv_get_email/i) > 0) {
            ajaxATCBtn();
        }
    } else {
        ajaxATCBtn();
    }
    let wbs_auto_redirect = false;
    jQuery(document.body).on('wc_fragments_refreshed', function (event) {
        if (woocommerce_boost_sales_params.auto_redirect && woocommerce_boost_sales_params.auto_redirect_time) {
            if (wbs_auto_redirect) {
                jQuery('.auto-redirect').html(woocommerce_boost_sales_params.auto_redirect_message);
                woo_boost_sale.counter(jQuery('.auto-redirect span'), woocommerce_boost_sales_params.auto_redirect_time);
                wbs_auto_redirect = false;
            }
        }
    });

    function ajaxATCBtn() {
        jQuery('.wbs-ajax-add-to-cart').on('click', function (e) {
            wbs_auto_redirect = false;
            e.preventDefault();

            var button = jQuery(this),
                $upsells = jQuery('#wbs-content-upsells'),
                $total = $upsells.find('.wbs-price-total');

            if (button.hasClass('disabled')) return;

            var form = button.closest('.cart');
            var product_id = form.find('input[name="product_id"]').val() || form.find('input[name="add-to-cart"]').val();
            var form_data = form.serialize();

            button.addClass('loading');
            $upsells.addClass('wbs-adding-to-cart');
            button.closest('form').find('.added_to_cart').remove();
            let isGrouped = button.hasClass('wbs-is-grouped');

            jQuery.ajax({
                type: 'POST',
                data: 'action=wbs_ajax_add_to_cart&' + form_data,
                url: woocommerce_boost_sales_params.url,
                success: function (response) {
                    if (response) {
                        jQuery(document.body).trigger('updated_wc_div');

                        if (response.html) {
                            var wbs_notices = jQuery('.wbs-add-to-cart-notices-ajax').html();
                            jQuery('.wbs-add-to-cart-notices-ajax').html(wbs_notices + response.html);
                        }

                        if (response.hasOwnProperty('variation_image_url') && response.variation_image_url) {
                            $upsells.find('.wbs-p-image').find('img').attr('src', response.variation_image_url);
                        }

                        if (response.hasOwnProperty('total') && response.total) {
                            $upsells.find('.wbs-current_total_cart').html(response.total);
                        }
                        if (response.hasOwnProperty('added_to_cart') && Object.keys(response.added_to_cart).length) {
                            jQuery('.vi-wbs-headline').css({'visibility': 'hidden', 'opacity': 0});
                            $upsells.css({'opacity': 0, 'display': 'flex', 'visibility': 'visible'}).animate({'opacity': 1}, 300);

                            if (wbs_add_to_cart_params.submit == 0) {
                                woo_boost_sale.slider();
                            }

                            if (jQuery('.wbs-archive-upsells').length > 0) {
                                jQuery('html').addClass('wbs-html-overflow');
                            }

                            clearTimeout(cross_sell_init);
                            woo_boost_sale.hide_cross_sell();

                            if (isGrouped) {
                                jQuery('.wbs-p-quantity').find('.wbs-p-quantity-number').text(response.grouped_quantity);
                                $total.find('.wbs-money').html(response.grouped_total);
                            } else {
                                let item = Object.values(response.added_to_cart)[0];
                                jQuery('.wbs-p-quantity').find('.wbs-p-quantity-number').text(item.quantity);
                                $total.find('.wbs-money').html(item.formatted_price);

                                if (response.added_to_cart[product_id].variation) {
                                    let added_variation = Object.values(response.added_to_cart[product_id].variation);
                                    if (added_variation.length > 0) {
                                        let title_ext = '<span class="wbs-added-attributes">' + added_variation.join(', ') + '</span>';
                                        jQuery('.wbs-p-title').find('.wbs-p-url').html(wbs_add_to_cart_params.product_title + ' - ' + title_ext);
                                        $upsells.find('.upsell-title').html(wbs_add_to_cart_params.message_bought + ' - ' + title_ext);
                                        if (response.added_to_cart[product_id].variation_image) {
                                            $upsells.find('.wbs-p-image img').eq(0).replaceWith(response.added_to_cart[product_id].variation_image);
                                        }
                                    }
                                }
                            }

                            if (wbs_add_to_cart_params.hasOwnProperty('auto_open_cart') && wbs_add_to_cart_params.auto_open_cart) {
                                if (!jQuery('.xoo-wsc-modal').hasClass('xoo-wsc-active')) {
                                    jQuery('.xoo-wsc-basket').click();
                                }
                            }

                            jQuery('#nm-menu-cart-btn').click();
                            button.after(' <a href="' + wbs_add_to_cart_params.cart_url + '" class="added_to_cart wc-forward" title="' +
                                wbs_add_to_cart_params.i18n_view_cart + '">' + wbs_add_to_cart_params.i18n_view_cart + '</a>');
                            button.addClass('added');
                        }

                        if (response.hasOwnProperty('discount_bar_html')) {
                            let discount_bar_html = response.discount_bar_html;
                            if (discount_bar_html.hasOwnProperty('code')) {
                                if (discount_bar_html.code == 200) {
                                    woocommerce_boost_sales_params.show_thank_you = false;
                                    wbs_auto_redirect = true;
                                    // jQuery('.vi-wbs-headline').css({'visibility': 'visible'}).animate({'opacity': 1}, 300);
                                    // jQuery('#wbs-content-discount-bar').html(discount_bar_html.html).css({'position': 'fixed'}).fadeIn(200);
                                } else if (discount_bar_html.code == 201) {
                                    // jQuery('.vi-wbs-headline').css({'visibility': 'visible'}).animate({'opacity': 1}, 300);
                                    // jQuery('#wbs-content-discount-bar').html(discount_bar_html.html).css({'position': ''}).fadeIn(200);
                                } else if (discount_bar_html.code == 400) {
                                    // jQuery('.vi-wbs-headline').css({'visibility': 'hidden'}).animate({'opacity': 0}, 300);
                                    // jQuery('#wbs-content-discount-bar').html('').css({'position': ''}).fadeOut(200);
                                }
                            }
                        }
                    }
                    jQuery(document).trigger('wbs_after_successful_ajax_add_to_cart', [response]);
                },
                error: function (err) {
                    console.log(err)
                },
                complete: function (html) {
                    button.removeClass('loading');
                    $upsells.removeClass('wbs-adding-to-cart');
                }
            });
        });
    }
});
