<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class VI_WBOOSTSALES_Upsells {
	private $settings;
	private $quantity;
	private $product;
	private $upsells;
	private $variation_id;
	private $cart_item_key;
	protected $language;

	/**
	 * VI_WBOOSTSALES_Upsells constructor.
	 * Init setting
	 */
	public function __construct( $product_id, $quantity, $upsells, $variation_id = false, $cart_item_key = '' ) {
		$this->product       = $product_id;
		$this->quantity      = $quantity;
		$this->upsells       = $upsells;
		$this->variation_id  = $variation_id;
		$this->cart_item_key = $cart_item_key;
		$this->language      = '';
		$this->settings      = VI_WBOOSTSALES_Data::get_instance();
	}

	/**
	 * Use this function to not get affected by filter of function $product->get_image()
	 *
	 * @param $product WC_Product
	 * @param string $size
	 * @param array $attr
	 * @param bool $placeholder
	 *
	 * @return string
	 */
	public static function get_product_image( $product, $size = 'woocommerce_thumbnail', $attr = array(), $placeholder = true ) {
		$image = '';
		if ( $product->get_image_id() ) {
			$image = wp_get_attachment_image( $product->get_image_id(), $size, false, $attr );
		} elseif ( $product->get_parent_id() ) {
			$parent_product = wc_get_product( $product->get_parent_id() );
			if ( $parent_product ) {
				$image = self::get_product_image( $parent_product, $size, $attr, $placeholder );
			}
		}

		if ( ! $image && $placeholder ) {
			$image = wc_placeholder_img( $size, $attr );
		}

		return $image;
	}

	/**
	 * @return false|string
	 */
	public function show_html() {
		global $wbs_upsell_count;
		/*Check Coupon*/
		$min_amount    = $this->settings->get_coupon( 'min' );
		$show_if_empty = $this->settings->get_option( 'show_if_empty' );
		$cart          = WC()->cart;
		$total_cart    = $cart->get_subtotal();
		if ( $cart->display_prices_including_tax() ) {
			$total_cart += $cart->get_subtotal_tax();
		}
//		if ( wc_tax_enabled() ) {
//			$tax        = $cart->get_cart_contents_tax();
//			$total_cart += $tax;
//		}
		$discount_bar_return_html = '';
		$show_headline            = false;
		if ( $this->settings->get_option( 'enable_discount' ) ) {
			$show_headline = true;
			$coupon_id     = $this->settings->get_option( 'coupon' );
			if ( $coupon_id ) {
				$coupon         = new WC_Coupon( $coupon_id );
				$coupon_code    = $coupon->get_code();
				$total_discount = WC()->cart->get_coupon_discount_amount( $coupon_code );
				$total_cart     += $total_discount;
				if ( $total_cart >= $min_amount ) {
					$show_headline = false;
				}
			}
		}
		/*Hide on single page*/
		if ( is_product() && $this->settings->get_option( 'hide_on_single_product_page' ) ) {
			return $discount_bar_return_html;
		}

		if ( ( ! is_array( $this->upsells ) || ! count( $this->upsells ) ) && ! $show_if_empty ) {
			return $discount_bar_return_html;
		}

		$select_template = $this->settings->get_option( 'select_template' );

		ob_start();
		echo $discount_bar_return_html;
		wbs_get_template( 'upsells/upsells-list.php', array( 'upsells' => $this->upsells ), '', VI_WBOOSTSALES_TEMPLATES );

		$upsells_list = ob_get_clean();

		if ( $wbs_upsell_count < 1 && ! $show_if_empty ) {
			return '';
		}

		ob_start();
		if ( $show_headline ) {
			$discount_bar  = new VI_WBOOSTSALES_Discount_Bar();
			$discount_type = $discount_bar->get_discount_type();
			$this->head_line( $discount_type );
		}
		?>
        <div class="wbs-overlay"></div>
		<?php do_action( 'wbs_before_upsells' ) ?>
        <div class="wbs-wrapper wbs-archive-upsells wbs-upsell-template-<?php echo $select_template ?> wbs-upsell-items-count-<?php echo $wbs_upsell_count ?>"
             style="opacity: 0">
            <div class="wbs-content">
				<?php wbs_get_template( 'upsells/popup-main.php', array(
					'upsells_list'  => $upsells_list,
					'upsells'       => $this->upsells,
					'wbs_data'      => $this->settings,
					'product_id'    => $this->product,
					'variation_id'  => $this->variation_id,
					'quantity'      => $this->quantity,
					'cart_item_key' => $this->cart_item_key,
				), '', VI_WBOOSTSALES_TEMPLATES ); ?>
            </div>
        </div>
		<?php
		do_action( 'wbs_after_upsells' );

		return ob_get_clean();
	}

	protected static function item_attributes_html( $product, $product_type, $item_variation_attributes, $select_type ) {
		$variation_attributes = implode( ', ', $item_variation_attributes );
		$title                = $product_type === 'variation' ? $variation_attributes : '';
		?>
        <div class="vi-wbs-frequently-product-item-attributes">
            <div class="vi-wbs-frequently-product-item-attributes-value"
                 title="<?php echo esc_attr( $title ) ?>"><?php echo $variation_attributes ?></div>
			<?php
			if ( $product_type === 'variable' ) {
				?>
                <div class="vi-wbs-frequently-product-item-attributes-arrow">
                    <div class="vi-wbs-frequently-product-arrow-down"></div>
                </div>
				<?php
			}
			?>
        </div>
        <div class="vi-wbs-frequently-product-arrow-left">
            <div class="vi-wbs-frequently-product-arrow-left-inner"></div>
        </div>
        <div class="vi-wbs-frequently-product-arrow-right">
            <div class="vi-wbs-frequently-product-arrow-right-inner"></div>
        </div>
		<?php
		if ( $product_type === 'variable' ) {
			do_action( 'woocommerce_boost_sales_frequently_product_select', $product, $item_variation_attributes, $select_type );
		}
	}

	/**
	 * @param $discount_type
	 */
	protected function head_line( $discount_type ) {
		global $wbs_language;
		$head_line   = $this->settings->get_option( 'coupon_desc', $wbs_language );
		$description = str_replace( '{discount_amount}', $discount_type, strip_tags( $head_line ) );
		if ( $description ) {
			?>
            <div class="vi-wbs-headline">
                <div class="vi-wbs-typo-container">
					<?php echo $description ?>
                </div>
            </div>
			<?php
		}
	}
}