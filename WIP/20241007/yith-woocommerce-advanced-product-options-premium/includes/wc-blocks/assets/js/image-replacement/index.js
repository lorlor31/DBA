import {registerPlugin} from '@wordpress/plugins';
import {useSelect, dispatch} from "@wordpress/data";
import {useEffect} from "@wordpress/element";
import {CART_STORE_KEY as storeKey } from '@woocommerce/block-data';
import { registerCheckoutFilters } from '@woocommerce/blocks-checkout';

const render = () => {
    const cartItems = useSelect( (select) => select( storeKey ).getCartData().items );

    useEffect(() => {
        setTimeout( function () {
            if ( cartItems.length > 0 ) {

                // Change product thumbnail.
                const cartHtml     = document.querySelector('.wc-block-cart .wc-block-cart__main') ||
                    document.querySelector('.wc-block-cart .wc-block-components-order-summary__content');
                const checkoutHtml = document.querySelector('.wc-block-checkout .wc-block-components-order-summary');

                const cartElements = {
                    itemsRow : '.wc-block-cart-items__row',
                    imageSelector : '.wc-block-cart-item__image a img'
                };
                const checkoutElements = {
                    itemsRow : '.wc-block-components-order-summary-item',
                    imageSelector : '.wc-block-components-order-summary-item__image img'
                };

                var elements = null;
                var elementHtml = null;

                if ( cartHtml !== null ) {
                    elementHtml = cartHtml;
                    elements    = cartElements;
                } else if ( checkoutHtml !== null ) {
                    elementHtml = checkoutHtml;
                    elements    = checkoutElements;
                }

                if ( elements !== null && elementHtml !== null ) {

                    const itemsRow = elementHtml.querySelectorAll( elements.itemsRow );
                    itemsRow.forEach( (itemRow, indexRow) => {
                        const imageToReplace = cartItems[indexRow].extensions?.yith_wapo_wc_block_manager.replace_image;
                        if ( imageToReplace ) {
                            const cartItemRowImage = itemRow.querySelector( elements.imageSelector );
                            cartItemRowImage.src   = imageToReplace;
                        }
                    } );
                }
            }
        }, 500 );
    }, [cartItems]);
}

registerPlugin( 'yith-wapo-image-replacement', {
    render,
    scope: 'woocommerce-checkout',
} );

const addEditLink = () => {
    const cartItems = useSelect( (select) => select( storeKey ).getCartData().items );
        useEffect( () => {
            setTimeout( function() {

                if ( cartItems.length > 0 ) {

                    // Change product thumbnail.
                    const cartHtml = document.querySelector('.wc-block-cart .wc-block-cart__main') ||
                        document.querySelector('.wc-block-cart .wc-block-components-order-summary__content');

                    const cartElements = {
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
                        const itemsRow = elementHtml.querySelectorAll( elements.itemsRow );

                        itemsRow.forEach( (itemRow, indexRow) => {

                            const editLink = cartItems[indexRow].extensions?.yith_wapo_wc_block_manager.edit_link;
                            const itemLink = itemRow.querySelector(elements.itemLink);

                            if ( editLink && !itemLink ) {
                                const productMetadataDiv = itemRow.querySelector(elements.productMetadata);

                                productMetadataDiv.innerHTML += editLink;
                            }
                        } );
                    }
                }

            }, 500 )

        }, [cartItems])

}

registerPlugin( 'yith-wapo-add-edit-link', {
    render: addEditLink,
    scope: 'woocommerce-checkout',
} );

const RemoveNameIndividualAddons = ( defaultValue, extensions, args ) => {

    if ( extensions.yith_wapo_wc_block_manager?.is_individual ) {
        defaultValue = '';
    }

    return defaultValue;
};

const IndividualAddonClass = ( defaultValue, extensions, args ) => {

    if ( extensions.yith_wapo_wc_block_manager?.is_individual ) {
        defaultValue += 'yith-wapo-is-individual';
    }

    return defaultValue;
};

const RemoveLinkIndividualAddons = ( defaultValue, extensions, args ) => {
    if ( extensions.yith_wapo_wc_block_manager?.is_individual ) {
        defaultValue = false;
    }

    return defaultValue;
};

registerCheckoutFilters( 'yith-wapo-individual-addon', {
    itemName           : RemoveNameIndividualAddons,
    cartItemClass      : IndividualAddonClass,
    showRemoveItemLink : RemoveLinkIndividualAddons,
} );