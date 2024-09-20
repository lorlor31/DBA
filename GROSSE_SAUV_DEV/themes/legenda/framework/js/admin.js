jQuery(document).ready(function($){

	/* Promo banner in admin panel */

	jQuery('.promo-text-wrapper .close-btn').on('click', function(){

		var confirmIt = confirm('Are you sure?');

		if(!confirmIt) return;

		var widgetBlock = jQuery(this).parent();

		var data =  {
			'action':'et_close_promo',
			'close': widgetBlock.attr('data-etag')
		};

		widgetBlock.hide();

		jQuery.ajax({
			url: ajaxurl,
			data: data,
			success: function(response){
				widgetBlock.remove();
			},
			error: function(data) {
				alert('Error while deleting');
				widgetBlock.show();
			}
		});
	});

    var theme_settings = jQuery('#prima-theme-settings');
    // Only show the background color input when the background color option type is Color (Hex)
    jQuery('.background-option-types').each(function() {
        showHideHexColor(jQuery(this));
        jQuery(this).change( function() {
            showHideHexColor( jQuery(this) )
        });
    });
    // Add color picker to color input boxes.
    jQuery('input.color-picker').each(function (i) {
        jQuery(this).after('<div id="picker-' + i + '" style="z-index: 100; background: #eee; border: 1px solid #ccc; position: absolute; display: block;"></div>');
        jQuery('#picker-' + i).hide().farbtastic(jQuery(this));
    })
    .on('focus', function() {
        jQuery(this).next().show();
        if (jQuery(this).val() == '') {
            jQuery(this).val('#');
        }
    })
    .on( 'blur', function() {
        jQuery(this).next().hide();
        if (jQuery(this).val() == '#') {
            jQuery(this).val('');
        }
    });
    // Show or hide the hex color input.
    function showHideHexColor(selectElement) {
        // Use of hide() and show() look bad, as it makes it display:block before display:none / inline.
        selectElement.next().css('display','none');
        if (selectElement.val() == 'hex') {
            selectElement.next().css('display', 'inline');
        }
    }
    var sidebarCkeck = jQuery('#product_page_sidebar');
    var defaultSidebar = sidebarCkeck.is(':checked');
    var labelText = sidebarCkeck.next().text();

    function checkState(element,defaultSidebar){
        changedVal = element.val();

        if(changedVal == 3){
            sidebarCkeck.css('opacity',0.5).attr('checked',true).removeAttr("disabled");
            sidebarCkeck.next().text('Sidebar is always enabled for 3 products');
        }else if(changedVal == 6){
            sidebarCkeck.attr('checked',false).attr("disabled", true);
            sidebarCkeck.next().text('Sidebar is disabled for 6 products');
        }else{
            sidebarCkeck.css('opacity',1).attr('checked',defaultSidebar).removeAttr("disabled");
            sidebarCkeck.next().text(labelText);
        }
    }

  jQuery(document).on( 'click', '.install-ver, #install_home_pages', function(e){
    e.preventDefault();
    
    var active = jQuery(this);

    if ( jQuery(this).is( '.install-ver' ) ) {
      var version = jQuery(this).data( 'ver' );
      var id = jQuery(this).data( 'home_id' );
    } else {
      var version = jQuery( '#demo_data_style' ).val();
      var id = jQuery( '#demo_data_style option:selected' ).data( 'home_id' );
    }

    if ( ! confirm( 'Are you sure you want to install demo data in "' + version + '" style? (It will change all your theme configuration, menu etc.)' ) ) {
      return false;
    }

    active.addClass('disabled loading').attr('disabled', 'disabled').unbind('click');
  
    jQuery.ajax({
        method: "POST",
        url: ajaxurl,
        data: {
          'action':'etheme_import_ajax',
          'version' : version,
          'nonce': $('.et_nonce').attr('value'),
          'id' : id,
        },
        success: function(data){ 
          if (data != '') {
            jQuery( '#redux-header' ).before( '<div class="et-message et-success">' + data + '</div>' );
          }
        },
        error: function (data) {
          alert( 'Ajax error' );
        },
        complete: function(){
          active.removeClass('disabled loading').addClass('done').removeAttr('disabled').text('Successfully installed!');
          setTimeout(function() {
            window.location.reload(true);
          }, 200);
        }
      });
  });

  jQuery('.etheme-deactivator').on('click',function(e){


    var confirmIt = confirm( 'Are you sure?' );
    if( ! confirmIt ) return;

    var data =  {
      'action':'etheme_deactivate_theme',
    };

    var redirect = window.location.href;

    redirect = redirect.replace( 'LegendaThemeOptions', 'etheme_activation_page');

    jQuery.ajax({
      url: ajaxurl,
      data: data,
      success: function(data){
        console.log(data);
      },
      error: function(xhr, status, errorThrown) {
        window.stop();
        alert('Error while deactivating');
        console.log( "Error: " + errorThrown );
        console.log( "Status: " + status );
        console.dir( xhr );
      },
      complete: function(){
        window.location.href=redirect;
      }
    });

  });

  jQuery( document ).on( 'click', '#bulk_edit', function() {

       var $bulk_row = jQuery( '#bulk-edit' );

       if ( $bulk_row.find( 'select[name="product_new"]' ).length == 0 ) {
          return;
        }

       var $product_new = $bulk_row.find( 'select[name="product_new"]' ).val();
       if ($product_new == '1') {
          return;
       }else if ($product_new == '2') {
          $product_new = 'disable';
       }else if ($product_new == '3') {
          $product_new = 'enable';
       }

       var $post_ids = new Array();
       $bulk_row.find( '#bulk-titles' ).children().each( function() {
          $post_ids.push( jQuery( this ).attr( 'id' ).replace( /^(ttle)/i, '' ) );
       });

       jQuery.ajax({
          url: ajaxurl,
          type: 'POST',
          async: false,
          cache: false,
          data: {
             action: 'etheme_save_bulk_edit',
             post_ids: $post_ids,
             product_new: $product_new
          },
          success: function(data){
            console.log('success');
          },
          error: function(xhr, status, errorThrown) {
            console.log( "Error: " + errorThrown );
            console.log( "Status: " + status );
            console.dir( xhr );
          }
       });

    });

});


/**
 * Upload Option
 * Allows window.send_to_editor to function properly using a private post_id
 * Dependencies: jQuery, Media Upload, Thickbox
 * Credits: OptionTree
 */
(function ($) {
  uploadOption = {
    init: function () {
      var formfield,
          formID,
          btnContent = true;
      // On Click
      $(document).on( "click", '.upload_button', function () {
        formfield = $(this).prev('input').attr('id');
        formID = $(this).attr('rel');
        // Display a custom title for each Thickbox popup.
        var prima_title = '';
        prima_title = $(this).prev().prev('.upload_title').text();
        tb_show( prima_title, 'media-upload.php?post_id='+formID+'&type=image&amp;TB_iframe=1');
        return false;
      });

      window.original_send_to_editor = window.send_to_editor;
      window.send_to_editor = function(html) {
        if (formfield) {
          if ( $(html).html(html).find('img').length > 0 ) {
          	itemurl = $(html).html(html).find('img').attr('src');
          }
		  else {
          	var htmlBits = html.split("'");
          	itemurl = htmlBits[1];
          	var itemtitle = htmlBits[2];
          	itemtitle = itemtitle.replace( '>', '' );
          	itemtitle = itemtitle.replace( '</a>', '' );
          }
          var image = /(^.*\.jpg|jpeg|png|gif|ico*)/gi;
          var document = /(^.*\.pdf|doc|docx|ppt|pptx|odt*)/gi;
          var audio = /(^.*\.mp3|m4a|ogg|wav*)/gi;
          var video = /(^.*\.mp4|m4v|mov|wmv|avi|mpg|ogv|3gp|3g2*)/gi;
          if (itemurl.match(image)) {
            btnContent = '<img src="'+itemurl+'" alt="" /><a href="#" class="remove etheme">Remove Image</a>';
          } else {
            btnContent = '<div class="no_image">'+html+'<a href="#" class="remove etheme">Remove</a></div>';
          }
          $('#' + formfield).val(itemurl);
          $('#' + formfield).next().next('div').slideDown().html(btnContent);
          tb_remove();
        } else {
          window.original_send_to_editor(html);
        }
      }
    }
  };
  $(document).ready(function () {
	  uploadOption.init();
      // Remove Uploaded Image
      $(document).on('click', '.remove', function(event) {
        $(this).hide();
        $(this).parents().prev().prev('.upload').attr('value', '');
        $(this).parents('.screenshot').slideUp();
      });
	  $('.field-column_width input[name*="menu-item-column_width"][value="0"]').val("").attr("value","");
  });
})(jQuery);
jQuery(document).on('change', '#old_widgets_panel_type', function(e) {
    jQuery(document).find('.etheme-options-form').trigger('submit');
});

jQuery(document).on('click', '.et_close-popup', function(e) {
    jQuery('.et_panel-popup').html('').removeClass('active auto-size');
    jQuery('body').removeClass('et_panel-popup-on');
});