jQuery(document).ready(function(a) {
    var c = a("#woocommerce_mailerlite_group");

    if (0 !== c.length) {
        var d = c.next(".select2-container");
        a('<span id="woo-ml-refresh-groups" class="woo-ml-icon-refresh" data-woo-ml-refresh-groups="true"></span>').insertAfter(d);
    }

    var e = !1;

    a(document).on("click", "[data-woo-ml-refresh-groups]", function(b) {
        if (b.preventDefault(), !e) {
            refreshGroups();
        }
    });

    let i = 0, j = a("#woo-ml-sync-untracked-resources-progress-bar");

    a(document).on("click", "[data-woo-ml-sync-untracked-resources]", function(c) {

        c.preventDefault();

        syncResources(this);
    });

    var syncResources = function(el) {

        var d = a(el);

        orders_tracked = d.data("woo-ml-untracked-resources-count");
        all_untracked_orders_left = d.data("woo-ml-untracked-resources-left");

        i = d.data("woo-ml-untracked-resources-cycle");

        fail = a('#woo-ml-sync-untracked-resources-fail');
        success = a("#woo-ml-sync-untracked-resources-success");

        let r = 0;

        console.log("inside the loop!");

        a.ajax({
            url: woo_ml_post.ajax_url,
            type: "post",
            beforeSend: function() {
                d.prop("disabled", !0);
                j.show();
                r++;
            },
            data: {
                action: "post_woo_ml_sync_untracked_resources"
            },
            async: 1,
            success: function(data) {

                var response = JSON.parse(data);

                if (response.allDone) {

                    console.log("done!");
                    console.log("Response: True");
                    d.hide();
                    j.hide();
                    fail.hide();
                    success.show();
                } else if (response.error) {

                    if (response.message) {
                        fail.html(response.message);
                    }

                    d.prop('disabled', 0);
                    j.hide();
                    fail.show();
                } else {

                    if (response.completed) {
                        let untracked = parseInt(d.data("woo-ml-untracked-resources-left")) - parseInt(response.completed);

                        d.data("woo-ml-untracked-resources-left", untracked);
                        d.html('Synchronize ' + untracked.toString()  + ' untracked resources');
                    }

                    syncResources(el);
                }
            }
        });
    };

    a(document).on('click', '[data-woo-ml-reset-resources-sync]', function(event) {

        event.preventDefault();

        a(this).prop('disabled', true);
        a(this).text("Please wait. Do not close this window until the reset finishes.");

        resetResourcesSync();
    });

    var resetResourcesSync = function() {

        a.ajax({
            url: woo_ml_post.ajax_url,
            type: 'post',
            data: {
                action: 'post_woo_ml_reset_resources_sync'
            },
            async: true,
            success: function (responseStr) {

                var response = responseStr;

                if (typeof responseStr === "string"){
                    response = parseJSON(responseStr);
                }

                if (response.allDone) {

                    window.location.reload();
                } else {

                    resetResourcesSync();
                }
            }
        });
    }
    
    var field = a("#woocommerce_mailerlite_api_key");

    a('<button id="woo-ml-validate-key" class="button-primary">Validate Key</button>').insertAfter(field);

    a(document).on("click", "#woo-ml-validate-key", function(b) {
        if (b.preventDefault(), !e) {
            var key = a("#woocommerce_mailerlite_api_key").val();
            a.ajax({
                url: woo_ml_post.ajax_url,
                type:"post",
                data: {
                    action: "post_woo_ml_validate_key",
                    key:key
                },
                success: function(a) {
                    location.reload()
                }
            })
        }
    });

    if (a('#woocommerce_mailerlite_group').length > 0) {
        a('#woocommerce_mailerlite_group').select2();
    }

    if (a('#woocommerce_mailerlite_ignore_product_list').length > 0) {
        a('#woocommerce_mailerlite_ignore_product_list').select2();
    }
    
    var cs_field = a('#woocommerce_mailerlite_consumer_secret');

    if (0 !== cs_field.length) {
        var field_desc = cs_field.next(".description");
        field_desc.closest('tr').after(
                                    '<h2>Integration Details</h2>\
                                    <p class="section-description">Customize MailerLite integration for WooCommerce</p>');
    }

    var ml_platform = '';

    if (a('#ml_platform').length > 0) {
        ml_platform = a('#ml_platform').val();
    }

    var tracking_field = a('#woocommerce_mailerlite_popups');
    
    tracking_field.closest('tr').before(
                                        '<h2>Popups</h2>\
                                        <p class="section-description">Display pop-up subscribe forms created within MailerLite</p>');

    var button = a('[name="save"]');

    if (field.length !== 0 && (cs_field.length === 0 && ml_platform !== '2')) {
        button.hide();
    } else {
        button.show();
    }
    
    var ignored_p_field = a('#woocommerce_mailerlite_ignore_product_list');

    if (ignored_p_field.length !== 0) {
        ignored_p_field.closest('tr').before(
            '<h2>E-commerce Automations</h2>\
            <p class="section-description">Customize settings for your e-commerce automations created in MailerLite </p>'
        )
    }

    var auto_update_field = a('#woocommerce_mailerlite_auto_update_plugin');

    if (auto_update_field.length !== 0) {
        auto_update_field.closest('tr').before(
            '<h2>Plugin Updates</h2>\
            <p class="section-description">Customize settings for MailerLite plugin </p>'
        )
    }

    var refreshGroups = function() {
        var c = a(this);
        c.removeClass("error"), c.addClass("running");
        a.ajax({
            url: woo_ml_post.ajax_url,
            type: "post",
            dataType: 'JSON',
            data: {
                action: "post_woo_ml_refresh_groups"
            },
            success: function(res) {
                c.removeClass("running");

                let has_group = false;

                if (res.groups) {
                    a('#woocommerce_mailerlite_group').empty();

                    for (const [id, name] of Object.entries(res.groups)) {

                        if (res.current && parseInt(res.current) === parseInt(id)) {
                            has_group = true;
                        }

                        a('#woocommerce_mailerlite_group').append(a('<option>', {
                            value: id,
                            text: name
                        }));
                    }
                }

                if (res.current && has_group) {
                    a('#woocommerce_mailerlite_group').val(res.current);
                }
            },
            error: function(x, status) {
                c.addClass("error");
            }
        });
    }

    var parseJSON = function(jsonStr){
        try {
            var parsed = JSON.parse(jsonStr);

            if (parsed && typeof parsed === "object") {
                return parsed;
            }
        }
        catch (e) { }

        return false;
    }

    if (a('#woocommerce_mailerlite_api_key').length > 0) {
        refreshGroups();
    }
});