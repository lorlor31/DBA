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
    $(".wbs-category-search").select2({
        width: '100%',
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

    $(".product-search").select2({
        width: '100%',
        closeOnSelect: false,
        placeholder: "Please enter product title",
        ajax: {
            url: "admin-ajax.php?action=wbs_search_product",
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
    let upsells_products = {};
    $('.product-search').next(".select2-container").find('ul.select2-selection__rendered').sortable({
        containment: 'parent',
        stop: function (event, ui) {
            var product_id = $(this).closest('tr').find('.column-action').data('id');
            // event target would be the <ul> which also contains a list item for searching (which has to be excluded)
            var arr = Array.from($(event.target).find('li:not(.select2-search)').map(function () {
                return $(this).data('data').id;
            }));
            upsells_products[product_id] = arr;
        }
    });
    let upsells_categories = {};
    $('.wbs-category-search').next(".select2-container").find('ul.select2-selection__rendered').sortable({
        containment: 'parent',
        stop: function (event, ui) {
            var product_id = $(this).closest('tr').find('.column-action').data('id');
            // event target would be the <ul> which also contains a list item for searching (which has to be excluded)
            var arr = Array.from($(event.target).find('li:not(.select2-search)').map(function () {
                return $(this).data('data').id;
            }));
            upsells_categories[product_id] = arr;
        }
    });
    /*Save Up sell*/
    $('.button-save').on('click', function () {
        var product_id = $(this).closest('td').data('id');
        var btn = $(this);
        if (product_id) {
            var u_id;
            var u_cate_ids;
            if (upsells_products.hasOwnProperty(product_id)) {
                u_id = upsells_products[product_id];
            } else {
                u_id = $('select.u-product-' + product_id).val();
            }
            if (upsells_categories.hasOwnProperty(product_id)) {
                u_cate_ids = upsells_categories[product_id];
            } else {
                u_cate_ids = $('select.u-categories-' + product_id).val();
            }
            btn.text('Saving');
            $.ajax({
                type: 'POST',
                data: {
                    action: 'wbs_u_save_product',
                    id: product_id,
                    u_id: u_id,
                    u_cate_ids: u_cate_ids,
                },
                url: wbs_upsell_admin_params.url,
                success: function (obj) {
                    if (obj.check == 'done') {
                        btn.text('Save');
                        btn.removeClass('button-primary');
                    } else {

                    }
                },
                error: function (html) {
                }
            })
        } else {
            return false;
        }
    });
    /*Remove all*/
    $('.button-remove').on('click', function () {
        var r = confirm("Your products in up-sells of selected product will be removed all. Are you sure ?");
        if (r == true) {
            var product_id = $(this).closest('td').data('id');

            var btn = $(this);
            if (product_id) {
                btn.text('Removing');
                $.ajax({
                    type: 'POST',
                    data: 'action=wbs_u_remove_product' + '&id=' + product_id,
                    url: wbs_upsell_admin_params.url,
                    success: function (html) {
                        var obj = $.parseJSON(html);
                        if (obj.check == 'done') {
                            btn.text('Remove all');
                            $('select.u-product-' + product_id).val('null').trigger("change");
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
    $('.product-search').on("select2:selecting", function (e) {
        // what you would like to happen
        var p_id = $(this).closest('td').data('id');
        $('.product-action-' + p_id).find('.button-save').addClass('button-primary');
    });
    /*Action after remove product*/
    $('.product-search').on("select2:unselecting", function (e) {
        var p_id = $(this).closest('td').data('id');
        $('.product-action-' + p_id).find('.button-save').addClass('button-primary');
    });
    /*Click Bulk Adds*/
    $('.btn-bulk-adds').on('click', function () {
        $('.bulk-adds').slideToggle('400');
        $('.list-products').fadeToggle('400');
    });
    /*Bulk Add products Upsell*/
    $('.ba-button-save').on('click', function () {
        var p_id = $('select.ba-product').val();
        var u_id = $('select.ba-u-product').val();
        var btn = $(this);
        u_id = u_id.toString();
        p_id = p_id.toString();
        if (p_id && u_id) {
            btn.text('Adding');
            $.ajax({
                type: 'POST',
                data: 'action=wbs_ba_save_product' + '&p_id=' + p_id + '&u_id=' + u_id,
                url: wbs_upsell_admin_params.url,
                success: function (html) {
                    var obj = $.parseJSON(html);
                    if (obj.check == 'done') {
                        reload_cache();
                    } else {

                    }
                },
                error: function (html) {
                }
            })
        } else if (u_id && $('input#vi_chk_selectall').is(':checked')) {
            u_id = u_id.toString();
            btn.text('Adding');
            $.ajax({
                type: 'POST',
                data: 'action=wbs_ba_save_all_product' + '&u_id=' + u_id,
                url: wbs_upsell_admin_params.url,
                success: function (html) {
                    var obj = $.parseJSON(html);
                    if (obj.check == 'done') {
                        reload_cache();
                    } else {

                    }
                },
                error: function (html) {
                }
            })
        } else {
            return false
        }
    });

    /*checkbox select all*/
    $('input#vi_chk_selectall').on('change', function () {
        if ($('input#vi_chk_selectall').is(':checked')) {
            $(this).closest('td').find('span.select2-container').css('display', 'none');
        } else {
            $(this).closest('td').find('span.select2-container').css('display', 'block');
        }
    });

    $('.btn-sync-crossells').on('click', function () {
        if (confirm('Create Up-sells to use with WooCommerce Boost Sales plugin from Cross-sells data in WooCommerce single product settings. Continue?')) {
            get_product_upsells($(this), 'crossells');
        }
    });
    $('.btn-sync-upsell').on('click', function () {
        if (confirm('Create Up-sells to use with WooCommerce Boost Sales plugin from Up-sells data in WooCommerce single product settings. Continue?')) {
            get_product_upsells($(this));
        }
    });

    function get_product_upsells(btn, src = 'upsells') {
        var original_text = btn.html();
        btn.text('Syncing...');
        $.ajax({
            type: 'POST',
            data: {
                action: 'wbs_u_sync_product',
                src: src,
            },
            url: wbs_upsell_admin_params.url,
            success: function (html) {
                var obj = $.parseJSON(html);
                if (obj.check == 'done') {
                    btn.text(original_text);
                    reload_cache();
                } else {

                }
            },
            error: function (html) {
            }
        })
    }

    $('.btn-sync-upsell-revert').on('click', function () {
        if (confirm('Up-sells data in single product settings will be OVERRIDDEN by Up-sells data managed by WooCommerce Boost Sales plugin. Continue?')) {
            sync_upsells_to_woo($(this));
        }
    });
    $('.btn-sync-upsell-revert-single').on('click', function () {
        if (confirm('Up-sells data in single product settings will be OVERRIDDEN by Up-sells data managed by WooCommerce Boost Sales plugin. Continue?')) {
            var btn = $(this);
            sync_upsells_to_woo(btn, [btn.closest('td').data('id')])
        }
    });

    /*Reload*/
    function sync_upsells_to_woo(btn, product_ids = []) {
        let btnText = btn.html();
        btn.text('Syncing...');
        $.ajax({
            type: 'POST',
            data: {
                action: 'wbs_u_sync_product_revert',
                product_ids: product_ids,
            },
            url: wbs_upsell_admin_params.url,
            success: function (response) {
                alert('Sync completed.');
            },
            error: function (html) {
            },
            complete: function () {
                btn.text(btnText);
            }
        })
    }

    $('.btn-delete-upsells').on('click', function () {
        if (confirm('This will delete upsells(managed by this plugin, not by WooCommerce) of all products. Continue?')) {
            let btn = $(this);
            let btnText = btn.html();
            btn.text('Processing...');
            $.ajax({
                type: 'POST',
                data: {
                    action: 'wbs_u_delete_upsells',
                    _ajax_nonce: $(this).data('wbs_nonce'),
                },
                url: wbs_upsell_admin_params.url,
                success: function (response) {
                    window.location.reload();
                },
                error: function (html) {
                },
                complete: function () {
                    btn.text(btnText);
                }
            })
        }
    });
    $('.wbs-sync-upsells-from-default-language').on('click', function () {
        if (confirm('This will look up for upsells of every product in default language and override your upsells configuration for current language, continue?')) {
            let btn = $(this);
            let btnText = btn.html();
            btn.text('Syncing...');
            $.ajax({
                type: 'POST',
                data: {
                    action: 'wbs_sync_upsells_wpml',
                },
                url: wbs_upsell_admin_params.url,
                success: function (response) {
                    if (response.success === true) {
                        window.location.reload();
                    } else {
                        alert('Error');
                    }
                },
                error: function (html) {
                },
                complete: function () {
                    btn.text(btnText);
                }
            })
        }
    });

    function reload_cache() {
        $('.product-search').trigger('change');
        location.reload();
    }

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
    $('.wbs-upsells-ajax-enable').on('click', function () {
        $.ajax({
            type: 'POST',
            url: wbs_upsell_admin_params.url,
            data: {
                action: 'wbs_ajax_enable_upsell',
                nonce: $('#_wsm_nonce').val(),
            },
            success: function (response) {
                $('.wbs-upsells-ajax-enable').parent().fadeOut(300);
            },
            error: function (err) {
                $('.wbs-upsells-ajax-enable').parent().fadeOut(300);
            }
        });
    })
});