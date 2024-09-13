jQuery(function($) {
    function handlePostcodeUpdate() {
        var postcode;
        if ($('#ship-to-different-address-checkbox').is(':checked')) {
            postcode = $('#shipping_postcode').val();
        } else {
            postcode = $('#billing_postcode').val();
        }
        updateShippingDisplay(postcode);
    }
    $('#shipping_postcode, #billing_postcode').change(handlePostcodeUpdate);
    $(document.body).on('updated_checkout', handlePostcodeUpdate);
	// PANIER
	var initialPostcode = $('#calc_shipping_postcode').val();
    if (initialPostcode) {
        updateShippingDisplay(initialPostcode);
    }
    $(document.body).on('updated_cart_totals', function() {
        var postcode = $('#calc_shipping_postcode').val();
        updateShippingDisplay(postcode);
    });
	// END PANIER
	
    function updateShippingDisplay(postcode) {
		if (!postcode) return;
        $.ajax({
            url: wc_params.ajax_url,
            type: 'POST',
            data: {
                action: 'check_exclusion_status',
                postcode: postcode
            },
            success: function(response) {
                if (response.success && response.data.exclude_shipping !== false) {
                    setupExclusionActions();
					$(".custom-shipping-message").fadeIn(); 
                } else {
                    resetShippingMethodsDisplay();
                }
            },
            error: function(xhr, status, error) {
                console.error("Erreur AJAX : ", status, error);
                resetShippingMethodsDisplay();
            }
        });
    }
    function setupExclusionActions() {
        disableCheckoutButton();
        showExclusionMessage();
    }
    function disableCheckoutButton() {
        $("#place_order").prop("disabled", true).hide();
        $(".checkout-button").prop("disabled", true).hide();
        $(".woocommerce-shipping-totals.shipping").hide();
        $(".woocommerce-shipping-totals.shipping.calculator").fadeIn();
        $(".shipping-calculator-form").fadeIn();
        $(".woocommerce-shipping-totals.package").hide();
        $("ul.wc_payment_methods.payment_methods.methods").hide();
    }
    function showExclusionMessage() {
		$(".custom-shipping-message").remove(); 
        var customMessage = `<tr class="custom-shipping-message"><td colspan="2" style="text-align: left !important; padding: 15px !important; margin-top: 20px; background-color: #f8f9fa; border: 1px solid #ececec; border-radius: 5px;">
            <p>Le Code Postal renseigné doit faire l'objet d'une demande de devis. Vous pouvez en faire la demande en ligne en cliquant sur le bouton "DEMANDER UN DEVIS", ou bien contacter le <a href="/contact/">service client</a>.</p>
        </td></tr>`;
        $(".woocommerce-shipping-totals.shipping.calculator").first().after(customMessage);
		$(".custom-shipping-message").fadeIn();
    }
    function resetShippingMethodsDisplay() {
        $(".woocommerce-shipping-totals.shipping, .woocommerce-shipping-totals.package").fadeIn();
        $(".woocommerce-shipping-totals.shipping.calculator").fadeIn();
		$(".shipping-calculator-form").fadeIn();
        $(".custom-shipping-message").remove(); 
        $("#place_order").prop("disabled", false).show();
        $(".checkout-button").prop("disabled", false).show();
        $("ul.wc_payment_methods.payment_methods.methods").show();
    }
});
