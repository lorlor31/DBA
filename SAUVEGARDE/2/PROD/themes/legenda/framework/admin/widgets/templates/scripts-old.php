<?php if ( ! defined( 'ABSPATH' ) ) exit( 'No direct script access allowed' ); ?>
<script type="text/javascript">
    jQuery(document).ready(function ($) {
        var sidebarForm = $('#etheme_add_sidebar_form');
        var sidebarFormNew = sidebarForm.clone();
        sidebarForm.remove();
        $('#widgets-right').append('<div style="clear:both;"></div>');
        $('#widgets-right').append(sidebarFormNew);

        sidebarFormNew.submit(function(e){
            e.preventDefault();
            var data =  {
                'action':'etheme_add_sidebar',
                '_wpnonce_etheme_widgets': $('#_wpnonce_etheme_widgets').val(),
                'etheme_sidebar_name': $('#etheme_sidebar_name').val(),
            };
            $.ajax({
                url: ajaxurl,
                data: data,
                success: function(response){
                    window.location.reload(true);

                },
                error: function(data) {
                    console.log('error');

                }
            });
        });
    });

    var delSidebar = '<div class="delete-sidebar">delete</div>';

    jQuery('.sidebar-etheme_custom_sidebar').find('.handlediv').before(delSidebar);

    jQuery('.delete-sidebar').on("click", function () {

        var confirmIt = confirm('Are you sure?');

        if (!confirmIt) return;

        var widgetBlock = jQuery(this).closest('.sidebar-etheme_custom_sidebar');

        var data = {
            'action': 'etheme_delete_sidebar',
            'etheme_sidebar_name': jQuery(this).parent().find('h2').text()
        };

        widgetBlock.hide();

        jQuery.ajax({
            url: ajaxurl,
            data: data,
            success: function (response) {
                widgetBlock.remove();
            },
            error: function (data) {
                alert('Error while deleting sidebar');
                widgetBlock.show();
            }
        });
    });
</script>
