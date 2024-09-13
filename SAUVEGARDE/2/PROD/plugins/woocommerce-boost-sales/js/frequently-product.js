jQuery(document).ready(function ($) {
    'use strict';
    let add_to_cart_text = wbs_frequently_product_params.frequently_product_add_to_cart_text;
    $(document).on('click', function () {
        $('.vi-wbs-show-select').removeClass('vi-wbs-show-select');
    });
    $(document).on('click', '.vi-wbs-frequently-product-item-attributes', function (e) {
        e.stopPropagation();
        let $button = $(this), $item = $button.closest('.vi-wbs-frequently-product-item');
        $('.vi-wbs-frequently-product-item').not($item).removeClass('vi-wbs-show-select');
        $item.toggleClass('vi-wbs-show-select');
    });
    $(document).on('click', '.vi-wbs-frequently-product-item-attributes-select-modal', function (e) {
        e.stopPropagation();
    });
    let frequently_product_checked;
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
                    handle_price($frequently_product);
                }
            });
            $form.addClass('vi-wbs-frequently-product-item-attributes-select-container-init');
        });
    }
    init_item_form();

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
    $('.vi-wbs-frequently-product-item-attributes-select-item').map(function () {
        $(this).trigger('change');
    });
    /*Change variation*/
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

    $('.vi-wbs-frequently-products-container').map(function () {
        let $frequently_product = $(this);
        let wbs_fp_shortcode = $frequently_product.data('wbs_fp_shortcode');
        if (wbs_fp_shortcode.ajax_load) {
            wbs_frequently_product_load($frequently_product);
        }
    });

    function wbs_frequently_product_load($frequently_product) {
        $.ajax({
            url: wbs_frequently_product_params.url,
            type: 'get',
            dataType: 'json',
            data: {
                action: 'vi_wbs_frequently_product_shortcode_ajax_load',
                wbs_fp_shortcode: JSON.stringify($frequently_product.data('wbs_fp_shortcode')),
                is_product: wbs_frequently_product_params.is_product,
                language: wbs_frequently_product_params.language,
            },
            beforeSend: function () {
                $frequently_product.addClass('vi-wbs-frequently-products-ajax-load')
            },
            error: function (err) {
            },
            success: function (response) {
                if (response.status === 'success' && response.data) {
                    let $new_data = $(response.data);
                    $frequently_product.replaceWith($new_data);
                    init_item_form();
                    $new_data.find('.vi-wbs-frequently-product-item-attributes-select-item').map(function () {
                        $(this).trigger('change');
                    });
                } else {
                    $frequently_product.remove();
                }
            },
            complete: function () {
                $frequently_product.removeClass('vi-wbs-frequently-products-ajax-load');
            }
        });
    }

    $(document).on('click', '.vi-wbs-frequently-product-item-check', function () {
        let $button = $(this);
        let $frequently_product = $button.closest('.vi-wbs-frequently-products-container');

        if ($frequently_product.find('.vi-wbs-frequently-product-item-check:checked').length === 0) {
            return false;
        }
        let $add_to_cart = $frequently_product.find('.vi-wbs-frequently-add-to-cart-button');
        handle_price($frequently_product);
        if (frequently_product_checked > 0) {
            $add_to_cart.html(add_to_cart_text.replace('{number_of_items}', frequently_product_checked));
            $add_to_cart.removeClass('disabled');
        } else {
            return false;
        }
    });

    function handle_price($frequently_product) {
        frequently_product_checked = 0;
        let $items = $frequently_product.find('.vi-wbs-frequently-product-item-check');
        let $total_price = $frequently_product.find('.vi-wbs-frequently-sum-price');
        let $total_price_discount = $frequently_product.find('.vi-wbs-frequently-sum-price-show-discount');
        let $saved = $frequently_product.find('.vi-wbs-frequently-sum-price-discount-value');
        let discounted_amount = parseFloat($total_price.data('discounted_amount')),
            discounted_price = parseFloat($total_price.data('discounted_price'));
        let dynamic_price = $total_price.data('dynamic_price'),
            saved_type = parseInt($total_price.data('saved_type')),
            discount_type = $total_price.data('discount_type'),
            discount_amount = parseFloat($total_price.data('discount_amount'));
        let $total_price_html = $total_price.find('.vi-wbs-frequently-sum-price-value');
        let total_price = frequently_product_calculate_total($items), original_price = total_price;
        if ($frequently_product.hasClass('vi-wbs-frequently-products-add-bundle') && woocommerce_boost_sales_params['crosssell_enable'] && frequently_product_checked === $frequently_product.find('.vi-wbs-frequently-product-item-check').length) {
            frequently_product_checked = 0;
            total_price = frequently_product_calculate_total($items, true);
            original_price = total_price;
            if (dynamic_price) {
                if (discount_amount > 0) {
                    if (discount_type === 'percent') {
                        discount_amount = discount_amount / 100;
                        total_price = original_price * (1 - discount_amount);
                    } else {
                        total_price = original_price - discount_amount;
                    }
                    if (total_price < 0) {
                        total_price = 0;
                    }
                    total_price = parseFloat(wbs_format_number(total_price, undefined, '.', ''));
                    discounted_amount = original_price - total_price;
                }
            } else {
                if (discounted_price < original_price) {
                    discounted_amount = original_price - discounted_price;
                }
            }
        }
        $total_price.data('total_price', total_price);
        let total_price_html = woocommerce_boost_sales_params['modal_price'].replace(wbs_format_number(1), wbs_format_number(total_price));
        $total_price_html.html(total_price_html);
        if (frequently_product_checked === $frequently_product.find('.vi-wbs-frequently-product-item-check').length) {
            if (discounted_amount > 0) {
                if (saved_type === 0) {
                    $saved.html(woocommerce_boost_sales_params['modal_price'].replace(wbs_format_number(1), wbs_format_number(discounted_amount)));
                } else if (saved_type === 1) {
                    $saved.html(`${wbs_format_number(discounted_amount * 100 / original_price, 0)}%`)
                }
                $('.vi-wbs-frequently-sum-price-show-discount-origin').html(woocommerce_boost_sales_params['modal_price'].replace(wbs_format_number(1), wbs_format_number(original_price)));
                if (dynamic_price) {
                    $('.vi-wbs-frequently-sum-price-show-discount-current').html(total_price_html);
                }
                $total_price_html.hide();
                $total_price_discount.fadeIn(200);
                $frequently_product.addClass('vi-wbs-frequently-products-bundle-offer');
            } else {
                $total_price_discount.hide();
                $total_price_html.fadeIn(200);
                $frequently_product.removeClass('vi-wbs-frequently-products-bundle-offer');
            }
        } else {
            $total_price_discount.hide();
            $total_price_html.fadeIn(200);
            $frequently_product.removeClass('vi-wbs-frequently-products-bundle-offer');
        }
    }

    function frequently_product_calculate_total($items, count_quantity = false) {
        let total_price = 0;
        $items.map(function () {
            let $loop_item = $(this);
            let $loop_item_container = $loop_item.closest('.vi-wbs-frequently-product-item');
            if ($loop_item.prop('checked')) {
                frequently_product_checked++;
                let quantity = count_quantity ? parseInt($loop_item_container.data('item_quantity')) : 1;
                total_price += quantity * parseFloat($loop_item_container.data('item_price'));
            }
        });
        return total_price;
    }

    let frequently_product_adding = false;
    $(document).on('click', '.vi-wbs-frequently-add-to-cart-button', function (e) {
        e.preventDefault();
        if (!frequently_product_adding) {
            frequently_product_adding = true;
            let $button = $(this);
            let $frequently_product = $button.closest('.vi-wbs-frequently-products-container');
            let $message = $frequently_product.find('.vi-wbs-frequently-add-to-cart-message');
            let wbs_fp_shortcode = $frequently_product.data('wbs_fp_shortcode');
            let atc_data = [];
            $frequently_product.find('.vi-wbs-frequently-product-item').map(function () {
                let $item = $(this);
                if ($item.find('.vi-wbs-frequently-product-item-check').prop('checked')) {
                    atc_data.push({
                        product_id: $item.data('product_id'),
                        variation_id: $item.data('variation_id'),
                        variation_attributes: $item.data('variation_attributes'),
                    });
                }
            });
            if (atc_data.length > 0) {
                let data = {
                    action: 'vi_wbs_frequently_product_add_to_cart',
                    wbs_fp_shortcode: JSON.stringify(wbs_fp_shortcode),
                    data: atc_data,
                    quantity: 1,
                    is_cart: wbs_frequently_product_params.is_cart,
                    is_checkout: wbs_frequently_product_params.is_checkout,
                    is_bundle: $frequently_product.hasClass('vi-wbs-frequently-products-add-bundle') ? 1 : 0,
                };
                let variable = $frequently_product.find('.vi-wbs-frequently-product-item-attributes-select-item').serializeArray();
                if (variable.length > 0) {
                    variable.forEach(function (item) {
                        data[item.name] = item.value;
                    });
                }
                $.ajax({
                    url: wbs_frequently_product_params.url,
                    type: 'post',
                    dataType: 'json',
                    data: data,
                    beforeSend: function () {
                        $button.addClass('loading');
                        $message.html('');
                        $frequently_product.addClass('vi-wbs-frequently-products-ajax-load')
                    },
                    error: function (err) {
                        $message.html(`<div class="vi-wbs-frequently-add-to-cart-message-error">An error occurs. Please try again later.</div>`)
                    },
                    success: function (response) {
                        if (response.status === 'success') {
                            if (response.after_atc) {
                                window.location.href = response.after_atc;
                            } else {
                                if (wbs_frequently_product_params.is_cart || wbs_frequently_product_params.is_checkout) {
                                    window.location.href = window.location.href;
                                } else {
                                    /*Refresh fragment if success*/
                                    // $('body').trigger("wc_fragment_refresh");
                                    jQuery(document.body).trigger('updated_wc_div');
                                    let $crossells = $frequently_product.closest('.woocommerce-boost-sales');
                                    if ($crossells.length > 0) {
                                        setTimeout(function () {
                                            $crossells.fadeOut(200);
                                            jQuery('html').removeClass('wbs-html-overflow');
                                        }, 3000);
                                    } else {
                                        if (wbs_fp_shortcode.after_atc === 'hide') {
                                            $frequently_product.fadeOut(5000);
                                        }
                                    }
                                }
                            }
                            if (1 == woocommerce_boost_sales_params.side_cart_auto_open && !jQuery('.xoo-wsc-modal').hasClass('xoo-wsc-active')) {
                                jQuery('.xoo-wsc-basket').click();
                            }
                            jQuery('#nm-menu-cart-btn').click();
                        }
                        $message.html(`<div class="vi-wbs-frequently-add-to-cart-message-${response.status}">${response.message}</div>`)
                    },
                    complete: function () {
                        $button.removeClass('loading');
                        frequently_product_adding = false;
                        $frequently_product.removeClass('vi-wbs-frequently-products-ajax-load');
                    }
                });
            } else {
                alert(wbs_frequently_product_params.i18n_no_item_selected);
            }
        }
    });

    function wbs_format_number(number, decimals, decimal_separator, thousand_separator) {
        if (decimals === undefined) {
            decimals = wbs_frequently_product_params.decimals;
        }
        if (decimal_separator === undefined) {
            decimal_separator = wbs_frequently_product_params.decimal_separator;
        }
        if (thousand_separator === undefined) {
            thousand_separator = wbs_frequently_product_params.thousand_separator;
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
    }

    function htmlEntities(str) {
        return String(str).replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
    }
});