/******/ (() => { // webpackBootstrap
/******/ 	"use strict";
var __webpack_exports__ = {};

;// CONCATENATED MODULE: external ["wp","plugins"]
const external_wp_plugins_namespaceObject = window["wp"]["plugins"];
;// CONCATENATED MODULE: external ["wp","data"]
const external_wp_data_namespaceObject = window["wp"]["data"];
;// CONCATENATED MODULE: external ["wp","element"]
const external_wp_element_namespaceObject = window["wp"]["element"];
;// CONCATENATED MODULE: external ["wc","wcBlocksData"]
const external_wc_wcBlocksData_namespaceObject = window["wc"]["wcBlocksData"];
;// CONCATENATED MODULE: external ["wc","blocksCheckout"]
const external_wc_blocksCheckout_namespaceObject = window["wc"]["blocksCheckout"];
;// CONCATENATED MODULE: ./includes/wc-blocks/assets/js/image-replacement/index.js





var render = function render() {
  var cartItems = (0,external_wp_data_namespaceObject.useSelect)(function (select) {
    return select(external_wc_wcBlocksData_namespaceObject.CART_STORE_KEY).getCartData().items;
  });
  (0,external_wp_element_namespaceObject.useEffect)(function () {
    setTimeout(function () {
      if (cartItems.length > 0) {
        // Change product thumbnail.
        var cartHtml = document.querySelector('.wc-block-cart .wc-block-cart__main') || document.querySelector('.wc-block-cart .wc-block-components-order-summary__content');
        var checkoutHtml = document.querySelector('.wc-block-checkout .wc-block-components-order-summary');
        var cartElements = {
          itemsRow: '.wc-block-cart-items__row',
          imageSelector: '.wc-block-cart-item__image a img'
        };
        var checkoutElements = {
          itemsRow: '.wc-block-components-order-summary-item',
          imageSelector: '.wc-block-components-order-summary-item__image img'
        };
        var elements = null;
        var elementHtml = null;
        if (cartHtml !== null) {
          elementHtml = cartHtml;
          elements = cartElements;
        } else if (checkoutHtml !== null) {
          elementHtml = checkoutHtml;
          elements = checkoutElements;
        }
        if (elements !== null && elementHtml !== null) {
          var itemsRow = elementHtml.querySelectorAll(elements.itemsRow);
          itemsRow.forEach(function (itemRow, indexRow) {
            var _cartItems$indexRow$e;
            var imageToReplace = (_cartItems$indexRow$e = cartItems[indexRow].extensions) === null || _cartItems$indexRow$e === void 0 ? void 0 : _cartItems$indexRow$e.yith_wapo_wc_block_manager.replace_image;
            if (imageToReplace) {
              var cartItemRowImage = itemRow.querySelector(elements.imageSelector);
              cartItemRowImage.src = imageToReplace;
            }
          });
        }
      }
    }, 500);
  }, [cartItems]);
};
(0,external_wp_plugins_namespaceObject.registerPlugin)('yith-wapo-image-replacement', {
  render: render,
  scope: 'woocommerce-checkout'
});
var addEditLink = function addEditLink() {
  var cartItems = (0,external_wp_data_namespaceObject.useSelect)(function (select) {
    return select(external_wc_wcBlocksData_namespaceObject.CART_STORE_KEY).getCartData().items;
  });
  (0,external_wp_element_namespaceObject.useEffect)(function () {
    setTimeout(function () {
      if (cartItems.length > 0) {
        // Change product thumbnail.
        var cartHtml = document.querySelector('.wc-block-cart .wc-block-cart__main') || document.querySelector('.wc-block-cart .wc-block-components-order-summary__content');
        var cartElements = {
          itemsRow: '.wc-block-cart-items__row',
          productMetadata: '.wc-block-components-product-metadata',
          itemLink: '.yith-wapo-edit-addons-link'
        };
        var elements = null;
        var elementHtml = null;
        if (cartHtml !== null) {
          elementHtml = cartHtml;
          elements = cartElements;
        }
        if (elements !== null && elementHtml !== null) {
          var itemsRow = elementHtml.querySelectorAll(elements.itemsRow);
          itemsRow.forEach(function (itemRow, indexRow) {
            var _cartItems$indexRow$e2;
            var editLink = (_cartItems$indexRow$e2 = cartItems[indexRow].extensions) === null || _cartItems$indexRow$e2 === void 0 ? void 0 : _cartItems$indexRow$e2.yith_wapo_wc_block_manager.edit_link;
            var itemLink = itemRow.querySelector(elements.itemLink);
            if (editLink && !itemLink) {
              var productMetadataDiv = itemRow.querySelector(elements.productMetadata);
              productMetadataDiv.innerHTML += editLink;
            }
          });
        }
      }
    }, 500);
  }, [cartItems]);
};
(0,external_wp_plugins_namespaceObject.registerPlugin)('yith-wapo-add-edit-link', {
  render: addEditLink,
  scope: 'woocommerce-checkout'
});
var RemoveNameIndividualAddons = function RemoveNameIndividualAddons(defaultValue, extensions, args) {
  var _extensions$yith_wapo;
  if ((_extensions$yith_wapo = extensions.yith_wapo_wc_block_manager) !== null && _extensions$yith_wapo !== void 0 && _extensions$yith_wapo.is_individual) {
    defaultValue = '';
  }
  return defaultValue;
};
var IndividualAddonClass = function IndividualAddonClass(defaultValue, extensions, args) {
  var _extensions$yith_wapo2;
  if ((_extensions$yith_wapo2 = extensions.yith_wapo_wc_block_manager) !== null && _extensions$yith_wapo2 !== void 0 && _extensions$yith_wapo2.is_individual) {
    defaultValue += 'yith-wapo-is-individual';
  }
  return defaultValue;
};
var RemoveLinkIndividualAddons = function RemoveLinkIndividualAddons(defaultValue, extensions, args) {
  var _extensions$yith_wapo3;
  if ((_extensions$yith_wapo3 = extensions.yith_wapo_wc_block_manager) !== null && _extensions$yith_wapo3 !== void 0 && _extensions$yith_wapo3.is_individual) {
    defaultValue = false;
  }
  return defaultValue;
};
(0,external_wc_blocksCheckout_namespaceObject.registerCheckoutFilters)('yith-wapo-individual-addon', {
  itemName: RemoveNameIndividualAddons,
  cartItemClass: IndividualAddonClass,
  showRemoveItemLink: RemoveLinkIndividualAddons
});
/******/ })()
;
//# sourceMappingURL=index.js.map