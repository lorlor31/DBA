<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
$product_id = $product->get_id();
?>
<div class="product vi-wbs-item-attributes-select-button-type">
    <form class="vi-wbs-frequently-product-item-attributes-select-container"
          data-product_id="<?php echo absint( $product_id ); ?>"
          data-product_variations="<?php echo esc_attr( json_encode( $available_variations, JSON_UNESCAPED_UNICODE ) ) ?>">
        <div class="vi-wbs-frequently-product-arrow-up">
            <div class="vi-wbs-frequently-product-arrow-up-inner"></div>
        </div>
        <div class="vi-wbs-item-attributes-select-modal">
            <div class="vi-wbs-fp-variations variations">
				<?php
				foreach ( $attributes as $attribute_name => $options ) {
					$select_id = "vi-wbs-fp-{$product_id}-$attribute_name";
					$select_id = function_exists( 'mb_strtolower' ) ? mb_strtolower( $select_id ) : strtolower( $select_id );
					?>
                    <div class="vi-wbs-fp-variation">
                        <div class="vi-wbs-fp-label">
                            <label for="<?php echo esc_attr( sanitize_title( $select_id ) ); ?>"><?php echo wc_attribute_label( $attribute_name ); ?> :</label>
                        </div>
                        <div class="vi-wbs-fp-value">
							<?php
							$selected = '';
							if ( ! empty( $selected_attributes ) ) {
								$selected = function_exists( 'mb_strtolower' )
									? $selected_attributes[ mb_strtolower( html_entity_decode( wc_sanitize_taxonomy_name( $attribute_name ), ENT_QUOTES, 'UTF-8' ) ) ]
									: $selected_attributes[ strtolower( html_entity_decode( wc_sanitize_taxonomy_name( $attribute_name ), ENT_QUOTES, 'UTF-8' ) ) ];
							}
							if ( $product && taxonomy_exists( $attribute_name ) ) {
								$terms = wc_get_product_terms( $product_id, $attribute_name, array(
									'fields' => 'all',
								) );
								foreach ( $terms as $term ) {
									if ( in_array( $term->slug, $options, true ) ) {
										$item_class = array( 'vi-wbs-fp-value-option' );
										if ( $term->slug === $selected ) {
											$item_class[] = 'vi-wbs-fp-value-selected';
										}
										?>
                                        <div class="<?php echo esc_attr( implode( ' ', $item_class ) ) ?>"
                                             data-wbs_fp_option="<?php echo esc_attr( $term->slug ) ?>">
                                            <span><?php echo esc_html( $term->name ) ?></span>
                                        </div>
										<?php
									}
								}
							} else {
								foreach ( $options as $key => $value ) {
									$item_class = array( 'vi-wbs-fp-value-option' );
									if ( $value === $selected ) {
										$item_class[] = 'vi-wbs-fp-value-selected';
									}
									?>
                                    <div class="<?php echo esc_attr( implode( ' ', $item_class ) ) ?>"
                                         data-wbs_fp_option="<?php echo esc_attr( $value ) ?>">
                                        <span><?php echo esc_html( $value ) ?></span>
                                    </div>
									<?php
								}
							}

							VI_WBOOSTSALES_Frontend_Bundles::wc_dropdown_variation_attribute_options( array(
								'options'          => $options,
								'attribute'        => $attribute_name,
								'product'          => $product,
								'id'               => $select_id,
								'name'             => 'vi_chosen_product_variable[' . $product_id . '][' . VI_WBOOSTSALES_Data::sanitize_taxonomy_name( "attribute_{$attribute_name}" ) . ']',
								'show_option_none' => sprintf( esc_html__( 'Choose %s', 'woocommerce-boost-sales' ), wc_attribute_label( $attribute_name ) ),
								'class'            => 'vi-wbs-frequently-product-item-attributes-select-item',
								'selected'         => $selected
							) );
							?>
                        </div>
                    </div>
					<?php
				}
				?>
            </div>
        </div>
        <div class="vi-wbs-item-attributes-select-options button">
			<?php esc_html_e( 'Select options', 'woocommerce-boost-sales' ) ?>
        </div>
        <div class="single_variation_wrap">
			<?php do_action( 'woocommerce_boost_sales_single_variation', $product ); ?>
        </div>
    </form>
</div>
