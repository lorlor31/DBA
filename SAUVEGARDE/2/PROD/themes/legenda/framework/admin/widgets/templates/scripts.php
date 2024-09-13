<?php if ( ! defined( 'ABSPATH' ) ) exit( 'No direct script access allowed' ); ?>
<script type="text/javascript">
    jQuery(document).ready(function ($) {
        $('#wpwrap').prepend('<div class="et_panel-popup"></div>');
        $('.edit-widgets-header__actions').prepend('<button class="et_add-new-sidebar components-button is-primary">Add New Sidebar</button>');

        $('.et_add-new-sidebar').on('click',
            function(e){
                e.preventDefault();
                let data =  {
                    'action':'et_ajax_widgets_form',
                };

                $('body').addClass('et_panel-popup-on');

                $.ajax({
                    url: ajaxurl,
                    data: data,
                    success: function(response){
                        let close = '<span class="et_close-popup et-button-cancel et-button"><span class="dashicons dashicons-no-alt"></span></span>';

                        $('.et_panel-popup').html(close + response);
                        $('.et_panel-popup').addClass('active');

                        let sidebarForm = $(document).find('#etheme_add_sidebar_form');

                        sidebarForm.on('submit', function(e) {
                            e.preventDefault();
                            let data =  {
                                'action':'etheme_add_sidebar',
                                '_wpnonce_etheme_widgets': sidebarForm.find('#_wpnonce_etheme_widgets').val(),
                                'etheme_sidebar_name': sidebarForm.find('#etheme_sidebar_name').val(),
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
                    },
                    error: function(data) {
                        console.log('error');

                    }
                });
            }
        );

        let blockLoaded = false;
        let blockLoadedInterval = setInterval(function() {
            if ($(document).find('[data-type="core/widget-area"]').length) {
                blockLoaded = true;
            }
            if ( blockLoaded ) {
                clearInterval( blockLoadedInterval );
                $.each( $(document).find('[data-type="core/widget-area"]'), function (){
                    let _this = $(this).find('.wp-block-widget-area__inner-blocks.block-editor-inner-blocks.editor-styles-wrapper'),
                        widgetArea = _this.parents('.components-panel__body');
                    if(_this.attr('data-widget-area-id').includes('8theme-sidebar')){
                        widgetArea.find('.components-panel__body-title').append('<span class="delete-sidebar" style="margin-right: 10px;"></span>');
                        widgetArea.find('.delete-sidebar').on("click", function () {
                            if (!confirm('Are you sure?')) return;

                            let data = {
                                'action': 'etheme_delete_sidebar',
                                'etheme_sidebar_name': widgetArea.find('.components-panel__body-title').text()
                            };

                            jQuery.ajax({
                                url: ajaxurl,
                                data: data,
                                success: function (response) {
                                    window.location.reload(true);
                                },
                                error: function (data) {
                                    alert('Error while deleting sidebar');
                                }
                            });
                        });
                    }
                })
            }
        }, 500);

        function remove_widgets(){

        }
    });
</script>
