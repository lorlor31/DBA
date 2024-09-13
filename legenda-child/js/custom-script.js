/* Custom script */
jQuery( function( $ ) {
	 // Sélectionner l'option YITH ADDON si une quantitié est modifiée ou cliquée
    $(document).on('click change keyup', '.wapo-product-qty', function(event) {
        var parentDiv = $(this).closest('.yith-wapo-option');
        var parentInput = parentDiv.find('.yith-proteo-standard-checkbox');
        if (!parentDiv.hasClass('selected')) {
            parentDiv.addClass('selected');
            parentInput.prop("checked", true);
        }
        calculateTotalAddonsPrice();
    });
    // Fonction pour gérer les clics sur les éléments .yith-wapo-option, y compris le :before
    $(document).on('click', '.yith-wapo-option', function(event) {
		        event.stopImmediatePropagation();
        event.preventDefault();
        var parentDiv = $(this);
        var parentInput = parentDiv.find('.yith-proteo-standard-checkbox');
        // Ajoute la classe 'selected' et coche l'input associé si ce n'est pas déjà fait
        if (parentDiv.hasClass('selected')) {
            parentDiv.removeClass('selected');
            parentInput.prop("checked", false);
            console.log('Class "selected" removed from parent div.');
        } else {
            // Sinon, on le sélectionne
            parentDiv.addClass('selected');
            parentInput.prop("checked", true);
            console.log('Class "selected" added to parent div.');
        }
        // Recalculer les totaux
        calculateTotalAddonsPrice();
    });
	// Références VINCO su rpages produit
    const labelUndefined = 'Aucune déclinaison trouvée';
    const labelUnselected = 'Sélectionner une déclinaison';
    const $skuMpn = $('.product-info .sku-mpn');
    const variations = JSON.parse($skuMpn.attr('data-mpn'));
    const originalMpn = $skuMpn.attr('data-parent-mpn');
    $('.variations_form').on('show_variation', function(event, data) {
        const mpn = variations[data.variation_id] || labelUndefined;
        $skuMpn.text(mpn);
    });
    $('.variations_form').on('hide_variation', function() {
        $skuMpn.text(originalMpn);
    });
	
	// YITH Addon => Chassis telescopique qty max=3
    $('.product-name').each(function() {
        var productName = $(this);
        if (productName.text().includes('Châssis télescopique')) {
            var optionContainer = productName.closest('.yith-wapo-option');
            var quantityInput = optionContainer.find('.wapo-product-qty');
            quantityInput.attr('max', '3');
        }
    });



    // Ajoute un attribut "title" pour l'infobulle et une icône
    var infoIcon = $('<i class="fa fa-info-circle" aria-hidden="true"></i>');
    infoIcon.attr('title', 'Indiquer plusieurs adresses mail séparées par une virgule');
    $('#_shipping_email').after(infoIcon);

    // Active les infobulles de jQuery UI si disponible
    if ($.fn.tooltip) {
        infoIcon.tooltip();
    }



    /* Group label and radio in account client */
    if ($('.woocommerce-MyAccount-content .woocommerce-address-fields').length) {
        $('.woocommerce-address-fields input.input-radio').each(function( index ){
            var input = $(this), label = $(this).next();
            $(this).parent().append($('<div class="group-radio">').append(input.clone(), label.clone()));
            input.remove();
            label.remove();
        });
    }
    /* Group label and radio in checkout */
    if ($('.woocommerce-checkout .woocommerce-billing-fields').length) {
        $('.woocommerce-billing-fields input[type="radio"]').each(function( index ){
            var input = $(this), label = $(this).next();
            $(this).parent().append($('<div class="group-radio">').append(input.clone(), label.clone()));
            input.remove();
            label.remove();
        });
    }


    /* Add accordeon in short-description */
    if ($('.product_meta .short-description').length) {
        if ($('.product_meta .short-description > .bloc_audela_standard').length) {
            $('.product_meta').on('mouseover', '.short-description', function () {
                $('.short-description').addClass('open');
                $('.short-description .arguments_audela_standard').css('margin-top', 0).slideDown(250, 'swing').animate({ opacity: 1 },{ queue: false, duration: 250 });
            }).on('mouseleave', function (e) {
                $('.short-description').removeClass('open');
                $('.short-description .arguments_audela_standard').slideUp(250, 'swing').animate({ opacity: 0 },{ queue: false, duration: 250 });
            });
        } else {
            document.head.insertAdjacentHTML( 'beforeEnd', '<style id="hide-pseudo">.short-description::after{display:none!important;}</style>' );
        }
    }

    /* Refresh shipping method in checkout */
    /*$(document.body).on( 'updated_checkout', function(e) {
        var xhr = $.ajax({
            type: 'POST',
            data: {
                action: 'load_shipping_method',
            },
            url: '/wp-admin/admin-ajax.php',
            success: function(response) {
                if (xhr) {
                    xhr.abort();
                }
                /*$('#shipping_method').remove();
                $('.woocommerce-shipping-totals').remove();*!/
                $('#step5 .woocommerce-shipping-fields__field-wrapper').empty();
                $('#step5 .woocommerce-shipping-fields__field-wrapper').append(response);
            },
            complete: function() {
                $('.woocommerce-shipping-fields__field-wrapper').removeClass('processing');
            }
        });
    });
    $(document.body).on( 'update_checkout', function(e) {
        $('.woocommerce-shipping-fields__field-wrapper').addClass('processing');
    });*/

    /* Hide phone number and add tracking */
   /* var phone_selector = $('a[href^=tel]'),
        phone_number = phone_selector.html().toString();
    phone_selector.html('** voir le numéro **').data('href', '#');
    phone_selector.on('click', show_phone);
    function show_phone(e) {
        e.preventDefault();
        $(this).html(phone_number).data('href', 'tel:' + phone_number);
        phone_selector.off('click', show_phone);
    }
*/
    /* Remove focus mobile search */
    $('#searchform input#s').focus(function() {
        setInterval(function(){
            $('#searchform input#s').blur().val('Rechercher');
        }, 250);
    });

    /* Block submit form search */
    $('.et-mega-search > form').on('submit', function(e) {
        e.preventDefault();
        return false;
    });

    /* Switch variation title compatibility */
    var val = '';
    $('.product_meta').on('mouseenter', '.swatch-wrapper.disabled a.swatch-anchor', function(e) {
        val = $(this).attr('title');
        $(this).attr('title', 'Non compatible avec l\'option sélectionnée');
    });
    $('.product_meta').on('mouseout', '.swatch-wrapper.disabled a.swatch-anchor', function(e) {
        $(this).attr('title', val);
        val = '';
    });

    /* PDF viewer in lightbox */
    var pdf_open = false, pdf_lightbox = '<div class="pdf_lightbox"><div class="pdf-close-btn">X</div><div class="pdf_object"></div><span class="pdf_title"></span></div>';
    $('.pdf-viewer-btn').on('click', function(e) {
        var ua = window.navigator.userAgent;
        var msie = ua.indexOf('MSIE ');
        var trident = ua.indexOf('Trident/');
        if (msie > 0 || trident > 0) {
            window.open($(this).data('url'), '_blank').focus();
        } else {
            if (!pdf_open) {
                $('body').append(pdf_lightbox);
                $('body').find('.pdf_lightbox .pdf_title').html($(this).data('title'));
                PDFObject.embed($(this).data('url'), '.pdf_object');
                pdf_open = true;
            }
        }
    });
    $('body').on('click', '.pdf_lightbox, .pdf-close-btn', function(e) {
        if (pdf_open) {
            $('body .pdf_lightbox').remove();
            pdf_open = false;
        }
    });

    /* Open Tawkto viewer */
   /* window.onload = function() {
        var t = document.cookie.split('; ');
        var f = t.find(row => row.startsWith('tawkopen='));
        if (typeof f == 'undefined') {
            Tawk_API.onLoad = function() {
                if (window.innerWidth > 768) {
                    Tawk_API.toggle();
                }
            };
            document.cookie = 'tawkopen=1;max-age=86400;path=/;SameSite=None;Secure';
        }
    }*/
	
	/* YITH RAQ Identifier les demande de devis panier / product */
	$(document).ready(function() {
		var isProductPage = window.location.pathname.includes('/demande-de-devis/');
		var isCartPage = window.location.pathname.includes('/panier/');
		if(isProductPage) {
			$('#quote_source').val('product_page');
		} else if(isCartPage) {
			$('#quote_source').val('cart_page');
		} else {
			$('#quote_source').val('inconnu');
		}
	});


});
