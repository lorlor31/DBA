<?php
/**
 * WAPO Template
 *
 * @author  YITH <plugins@yithemes.com>
 * @package YITH\ProductAddOns
 * @version 4.11.0
 *
 * @var int $product_id
 * @var int $variation_id
 * @var string $addons
 * @var array $cart_item The product in the cart.
 * @var string $cart_item_key Key for the product in the cart.
 *
 */

if ( apply_filters( 'yith_wapo_show_edit_product_link', true, $cart_item ) && yith_wapo_product_has_blocks( $product_id, $variation_id ) && ! isset( $cart_item['yith_wapo_individual_addons'] ) ) {
?>
<div class="yith-wapo-edit-addons-link">
    <a class="yith-wapo-edit-product-cart"
       data-product_id="<?php echo esc_attr( $product_id )?>"
       data-variation_id="<?php echo esc_attr( $variation_id ) ?>"
       data-addons="<?php echo esc_attr( $addons ) ?>"
       data-cart-item-key="<?php echo esc_attr( $cart_item_key ); ?>"
    >
        <small><?php
            // translators: Link of the product in the cart page to open the modal if it has add-ons.
            echo esc_html__( 'Edit options', 'yith-woocommerce-product-add-ons' );
            ?></small>
    </a>
</div>

<?php

} else if ( isset( $cart_item['yith_wapo_options'] ) && isset( $cart_item['yith_wapo_individual_addons'] ) ) {

    $parent_key = $cart_item['yith_wapo_addons_parent_key'] ?? '';

    ?>
    <input type="hidden"
           class="yith-wapo-individual-addons parent-key-<?php echo esc_attr( $parent_key ); ?>"
           data-addons="<?php echo esc_attr( $addons ); ?>"
           data-cart-item-key="<?php echo esc_attr( $cart_item_key );?>"
           data-addons-parent-key="<?php echo esc_attr( $parent_key ); ?>"
    >
    <?php
}