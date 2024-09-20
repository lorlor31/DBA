jQuery(document).ready(function ($) {
    'use strict';
    /*Set paged to 1 before submitting*/
    let is_current_page_focus = false;
    $('.tablenav-pages').find('.current-page').on('focus', function (e) {
        is_current_page_focus = true;
    }).on('blur', function (e) {
        is_current_page_focus = false;
    });
    $('.search-box').find('input[type="submit"]').on('click', function () {
        let $form = $(this).closest('form');
        if (!is_current_page_focus) {
            $form.find('.current-page').val(1);
        }
    });
    $('input[name="_wbs_dynamic_price"]').on('change', function () {
        let $dynamic_price=$(this),$container=$dynamic_price.closest('.inline-edit-col');
        if ($dynamic_price.prop('checked')) {
            $container.find('.wbs-bundle-dynamic-price-fields').fadeIn(100);
            $container.find('.wbs-bundle-fixed-price-fields').hide();
        } else {
            $container.find('.wbs-bundle-dynamic-price-fields').hide();
            $container.find('.wbs-bundle-fixed-price-fields').fadeIn(100);
        }
    }).trigger('change');
    $(".wbs-use-other-bundle").on('click', function () {
        let td = $(this).parent().parent();
        if ($(this).prop('checked')) {
            td.find('.wbs-product-search-bundle-container').show();
            td.find('.product-search-crs-container').hide();
        } else {
            td.find('.wbs-product-search-bundle-container').hide();
            td.find('.product-search-crs-container').show();
        }
    })
    $(".product-search-bundle").select2({
        width: '100%',
        closeOnSelect: false,
        placeholder: "Please enter product bundle title",
        ajax: {
            url: "admin-ajax.php?action=wbs_search_product_bundle",
            dataType: 'json',
            type: "GET",
            quietMillis: 50,
            delay: 250,
            data: function (params) {
                return {
                    keyword: params.term,
                    p_bundle_id: $(this).closest('td').find('[name="_wbs_cross_sell_of"]').val()
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

    $(".product-search-crs").select2({
        width: '100%',
        closeOnSelect: false,
        placeholder: "Please fill in your product title",
        ajax: {
            url: "admin-ajax.php?action=wbs_search_product_crs",
            dataType: 'json',
            type: "GET",
            quietMillis: 50,
            delay: 250,
            data: function (params) {
                return {
                    keyword: params.term,
                    p_id: $(this).closest('td').data('id')
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

    /*Save Cross sell*/
    $('.button-save').on('click', function () {
        var product_id = $(this).closest('td').data('id'),
            product_bundle_id = $(this).closest('tr').find('input[name="_wbs_cross_sell_of"]').val(),
            other_bundle_id = '';
        if ($(this).closest('tr').find('select[name="_wbs_cross_sell_bundle"]').val() !== null && $(this).closest('tr').find('.wbs-use-other-bundle').prop('checked')) {
            other_bundle_id = $(this).closest('tr').find('select[name="_wbs_cross_sell_bundle"]').val();
        }
        var btn = $(this);
        if (product_id) {
            var c_id = $('select.u-product-' + product_id).val();
            if (c_id == null && !other_bundle_id) {
                $(this).closest('td').find('.button-remove').triggerHandler('click');

            } else {
                //c_id = c_id.toString();
                btn.text('Saving');
                $.ajax({
                    type: 'POST',
                    data: 'action=wbs_c_save_product' + '&id=' + product_id + '&c_id=' + c_id + '&product_bundle_id=' + product_bundle_id + '&other_bundle_id=' + other_bundle_id,
                    url: wbs_crosssell_admin_params.url,
                    success: function (html) {
                        var obj = $.parseJSON(html);
                        if (obj.check == 'done') {
                            btn.text('Save');
                            btn.removeClass('button-primary');
                        } else if (obj.check == 'wrong') {
                            btn.text('Save');
                            btn.removeClass('button-primary');
                            alert('Please click Clear All replace to use save data without anything !');
                        } else {
                            btn.text('Save');
                            btn.removeClass('button-primary');
                        }
                        location.reload();
                    },
                    error: function (html) {
                    }
                })
            }
        } else {
            return false;
        }
    });
    /*Remove all*/
    $('.button-remove').on('click', function () {
        var r = confirm("Your products in cross-sells of selected product will be removed all and Delete bundle product. Are you sure ?");
        if (r == true) {
            var product_id = $(this).closest('td').data('id'),
                product_bundle_id = $(this).closest('tr').find('input[name="_wbs_cross_sell_of"]').val();
            var btn = $(this);
            if (product_id) {
                btn.text('Removing');
                $.ajax({
                    type: 'POST',
                    data: 'action=wbs_c_remove_product' + '&id=' + product_id + '&product_bundle_id=' + product_bundle_id,
                    url: wbs_crosssell_admin_params.url,
                    success: function (html) {
                        var obj = $.parseJSON(html);
                        if (obj.check == 'done') {
                            btn.text('Remove all');
                            $('select.u-product-' + product_id).val('').trigger("change");
                            location.reload();
                        } else {

                        }
                    },
                    error: function (html) {
                    }
                })
            } else {
                return false;
            }
        }
    });

    /*Action after selected product*/
    $('.product-search-crs').on("select2:selecting", function (e) {
        // what you would like to happen
        var p_id = $(this).closest('td').data('id');
        $('.product-action-' + p_id).find('.button-save').addClass('button-primary');
    });
    /*Action after remove product*/
    $('.product-search-crs').on("select2:unselecting", function (e) {
        var p_id = $(this).closest('td').data('id');
        $('.product-action-' + p_id).find('.button-save').addClass('button-primary');
    });

    /*Reload*/
    function reload_cache() {
        $('.product-search-crs').trigger('change');
        location.reload();
    }

    $('input[name="product_bundle_regular_price"]').keypress(function (event) {
        if (event.which < 44 || event.which == 45 || event.which == 47
            || event.which > 57) {
            event.preventDefault();
        } // prevent if not number/dot

        if (event.which == 46
            && $(this).val().indexOf('.') != -1) {
            event.preventDefault();
        }
        if (event.which == 44
            && $(this).val().indexOf(',') != -1) {
            event.preventDefault();
        }
    });

    $('.button-quick-edit').on('click', function () {
        $(this).next().slideToggle();
    });

    $('.button-cancel').on('click', function () {
        $(this).closest('.inline-edit-row').slideUp('400');
    });

    $('.button-update').on('click', function () {
        let $row = $(this).closest('.inline-edit-row'),
            dynamic_price = $row.find('input[name="_wbs_dynamic_price"]').prop('checked') ? 1 : 0,
            discount_type = $row.find('select[name="_wbs_discount_type"]').val(),
            discount_amount = $row.find('input[name="_wbs_discount_amount"]').val(),
            product_bundle_id = $row.data('product_bundle_id'),
            post_bundle_title = $row.find('input[name="post_bundle_title"]').val(),
            product_bundle_regular_price = $row.find('input[name="product_bundle_regular_price"]').val();
        $(this).next().addClass('is-active');
        if (product_bundle_id) {
            $.ajax({
                type: 'POST',
                data: 'action=wbs_update_product' + '&id=' + product_bundle_id + '&title=' + post_bundle_title + '&price=' + product_bundle_regular_price + '&dynamic_price=' + dynamic_price + '&discount_type=' + discount_type + '&discount_amount=' + discount_amount,
                url: wbs_crosssell_admin_params.url,
                success: function (html) {
                    var obj = $.parseJSON(html);
                    if (obj.check === 'done') {
                        $row.find('span.spinner').removeClass('is-active');
                        $row.slideUp('300');
                        location.reload();
                    } else {
                        alert(obj.detail_err);
                    }
                },
                error: function (html) {
                }
            });
        }
    });

    var wbs_different_up = $('#wbs_different_up-cross-sell').data('wbs_up_crosssell');
    $(document).tooltip({
        items: "#wbs_different_up-cross-sell",
        position: {
            my: "right top+10"
        },
        track: true,
        content: '<img class="wbs_img_tooltip_dfc" src="' + wbs_different_up + '" width="700px" style="float: left; margin-left: 180px;" />',
        show: {
            effect: "slideDown",
            delay: 150
        }
    });
    $('.wbs-crosssells-ajax-enable').on('click', function () {
        $.ajax({
            type: 'POST',
            url: wbs_crosssell_admin_params.url,
            data: {
                action: 'wbs_ajax_enable_crosssell',
                nonce: $('#_wsm_nonce').val(),
            },
            success: function (response) {
                $('.wbs-crosssells-ajax-enable').parent().fadeOut(300);
            },
            error: function (err) {
                $('.wbs-crosssells-ajax-enable').parent().fadeOut(300);
            }
        });
    })
    $('.btn-sync-crosssell').on('click', function () {
        if (confirm("This will create a bundle for each product from it's WooCommerce Cross-sells. Products whose bundles were already created will be skipped. Do you want to continue?")) {
            let btn = $(this);
            let oldtext = btn.html();
            btn.text('Syncing...');
            $.ajax({
                type: 'POST',
                data: {
                    action: 'wbs_u_create_bundle_from_crosssells',
                    nonce: $('#_wsm_nonce').val(),
                },
                url: wbs_crosssell_admin_params.url,
                success: function (response) {
                    let obj = $.parseJSON(response);
                    if (obj.check == 'done') {
                        btn.text(oldtext);
                        reload_cache();
                    } else {

                    }
                },
                error: function (html) {
                }
            })
        }
    });
});