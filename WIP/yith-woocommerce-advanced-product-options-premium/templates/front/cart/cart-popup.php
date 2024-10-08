<?php
/**
 * Popup bone template
 *
 * @package YITH\YITHAddons\Templates
 */

defined( 'YITH_WAPO' ) || exit; // Exit if accessed directly.
?>
<div class="yith-wapo-popup fade-in">
    <div class="yith-wapo-overlay"></div>
    <div class="yith-wapo-wrapper">
        <div class="yith-wapo-main">
            <div class="yith-wapo-head">
                <div class="edit-options-label">
                    <?php
                    // translators: Title of the edit add-ons modal in the cart page.
                    echo esc_html__( 'Edit options', 'yith-woocommerce-product-add-ons' );
                    ?>
                </div>
                <a href="#" class="yith-wapo-close"></a>
            </div>
            <div class="yith-wapo-content woocommerce single-product"></div>
            <input type="hidden" class="yith-wapo-cart-item-key"></input>
            <div class="yith-wapo-footer">
                <div class="yith-wapo-add-to-cart"></div>
            </div>
        </div>
    </div>
</div>
<?php
