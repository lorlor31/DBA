'use strict';
jQuery(document).ready(function () {

    jQuery('.vi-ui.tabular.menu .item').vi_tab({
        history: true,
        historyType: 'hash'
    });

    jQuery('.vi-ui.checkbox').checkbox();
    jQuery('.vi-ui.radio').checkbox();
    jQuery('select.vi-ui.dropdown').dropdown();

    // set Positive number
    jQuery('input[type="number"]').attr({
        'min': 0
    });

    // Color picker
    jQuery('.color-picker').iris({
        change: function (event, ui) {
            jQuery(this).parent().find('.color-picker').css({backgroundColor: ui.color.toString()});
        },
        hide: true,
        border: true
    }).click(function () {
        jQuery('.iris-picker').hide();
        jQuery(this).closest('td').find('.iris-picker').show();
    });

    jQuery('body').click(function () {
        jQuery('.iris-picker').hide();
    });
    jQuery('.color-picker').click(function (event) {
        event.stopPropagation();
    });

    jQuery('.vi-wbs-dcoupon.dropdown').dropdown({
        onChange: function () {
            var value_cp = jQuery(this).children('.text').text();
            jQuery.ajax({
                type: 'POST',
                data: 'action=wbs_select_coupon' + '&value=' + value_cp,
                url: wbs_admin_fields_params.url,
                success: function (response) {
                    jQuery('.vi-ui-expired').remove();
                    jQuery('.vi-wbs-dcoupon.dropdown').before(response);
                },
                error: function (response) {

                }
            });
        }
    });

    jQuery('.wbs-message_congrats').dependsOn({
        'input[name="_woocommerce_boost_sales[enable_thankyou]"]': {
            checked: true
        }
    });

    jQuery('.wbs-enable_checkout').dependsOn({
        'input[name="_woocommerce_boost_sales[enable_checkout]"]': {
            checked: true
        }
    });

    jQuery(".product-search select").select2({
        closeOnSelect: false,
        placeholder: "Please fill in your  product title",
        ajax: {
            url: "admin-ajax.php?action=wbs_search_product_excl",
            dataType: 'json',
            type: "GET",
            quietMillis: 50,
            delay: 250,
            data: function (params) {
                return {
                    keyword: params.term
                };
            },
            processResults: function (data) {
                return {
                    results: data
                };
            }
        },
        escapeMarkup: function (markup) {
            return markup;
        }, // let our custom formatter work
        minimumInputLength: 1
    });
    jQuery(".select-coupon select").select2({
        ajax: {
            url: "admin-ajax.php?action=wbs_select_coupon",
            dataType: 'json',
            type: "GET",
            quietMillis: 50,
            delay: 250,
            data: function (params) {
                return {
                    keyword: params.term
                };
            },
            processResults: function (data) {
                return {
                    results: data
                };
            }
        },
        escapeMarkup: function (markup) {
            return markup;
        }, // let our custom formatter work
        minimumInputLength: 1
    });
    jQuery(".wbs-category-search select").select2({
        closeOnSelect: false,
        placeholder: "Please fill in your category title",
        ajax: {
            url: "admin-ajax.php?action=wbs_search_category_excl",
            dataType: 'json',
            type: "GET",
            quietMillis: 50,
            delay: 250,
            data: function (params) {
                return {
                    keyword: params.term
                };
            },
            processResults: function (data) {
                return {
                    results: data
                };
            }
        },
        escapeMarkup: function (markup) {
            return markup;
        }, // let our custom formatter work
        minimumInputLength: 1
    });
    jQuery('input:text, .vi-ui.button', '.vi-ui.action.input').on('click', function (e) {
        jQuery('input:file', jQuery(e.target).parents()).click();
    });

    jQuery('input:file', '.vi-ui.action.input').on('change', function (e) {
        var name = e.target.files[0].name;
        jQuery('input:text', jQuery(e.target).parent()).val(name);
    });
    jQuery('.wbs-crosssell-bundle-price-discount-type').dropdown({
        onChange: function (val) {
            let container=jQuery(this).closest('tr');
            if (val === 'percent') {
				container.find('.wbs-crosssell-bundle-price-discount-value').attr('max',100);
            } else {
                container.find('.wbs-crosssell-bundle-price-discount-value').removeAttr('max');
            }
        }
    })
});