var WCMarketplaceCart = /** @class */ (function () {
    function WCMarketplaceCart() {
	}
	
	WCMarketplaceCart.prototype.getClickEventName = function()
	{
		var clickEventName = 'click';
		if ('ontouchend' in document.documentElement) {
		  clickEventName = 'touchend';
		}

		return clickEventName;
	};

	WCMarketplaceCart.prototype.clearEmptyProductProperties = function()
	{
		jQuery('.cart .product-price,.cart .product-quantity, .cart .product-subtotal').each(function() {
			var target = jQuery(this);
	
			var originalHtml = target.html();
			var trimmedHtml = jQuery.trim(originalHtml);
	
			if (trimmedHtml.length === 0 && originalHtml.length > trimmedHtml.length) {
				target.html('');
			}
		});
	};

	WCMarketplaceCart.prototype.onChangeCheckoutFieldsClick = function()
	{
		jQuery('.woocommerce-shipping-fields_change, .woocommerce-billing-fields_change, .billing_preview, .shipping_preview').css('display', 'none');
	
		jQuery('.billing_form, .shipping_form').css('display', 'block');

		return false;
	};

	WCMarketplaceCart.prototype.setupNotices = function()
	{
		jQuery('.xwoocommerce-info').removeClass('xwoocommerce-info').addClass('woocommerce-info');

		this.movePrimaryCartNotice();
	};

	WCMarketplaceCart.prototype.movePrimaryCartNotice = function()
	{
		var container = jQuery('.woocoomerce-cart-packages');
		if (container.length == 0) {
			container = jQuery('.woocommerce-checkout-packages');
		}
		var notice = container.children(':first.woocommerce-info');
		if (notice.length > 0) {
			var oldNotice = container.parent().prev('.woocommerce-info:contains("' + notice.text() + '")');
			if (oldNotice.length > 0) {
				oldNotice.remove();
			}

			notice.detach().insertBefore(container.parent());
		}
	};

	WCMarketplaceCart.prototype.onReady = function()
	{
		var _this = this;
		this.clearEmptyProductProperties();

		var clickEventName = this.getClickEventName();

		// show checkout form fields
		jQuery(document).on(clickEventName, '.woocommerce-shipping-fields_change, .woocommerce-billing-fields_change', function() {
			return _this.onChangeCheckoutFieldsClick();
		});

		jQuery(document).on('removed_coupon applied_coupon updated_cart_totals updated_shipping_method wc_fragments_refreshed wc_fragments_loaded updated_wc_div init_checkout update_checkout updated_checkout', document.body, function() {
			_this.setupNotices();
		});

		this.setupNotices();
	};

	WCMarketplaceCart.prototype.register = function()
	{
		var _this = this;

		jQuery(window).on('load', function() { return _this.onReady(); });

	};

	return WCMarketplaceCart;
}());

(new WCMarketplaceCart()).register();