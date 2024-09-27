<?php
/**
 * Plausible Analytics | Integrations | WooCommerce.
 *
 * @since      2.1.0
 * @package    WordPress
 * @subpackage Plausible Analytics
 */

namespace Plausible\Analytics\WP\Integrations;

use Plausible\Analytics\WP\Integrations;
use Plausible\Analytics\WP\Proxy;
use WC_Cart;
use WC_Product;

class WooCommerce {
	const PURCHASE_TRACKED_META_KEY = '_plausible_analytics_purchase_tracked';

	const CUSTOM_PROPERTIES         = [
		'cart_total',
		'cart_total_items',
		'id',
		'name',
		'price',
		'product_id',
		'product_name',
		'quantity',
		'shipping',
		'subtotal',
		'subtotal_tax',
		'tax_class',
		'total',
		'total_tax',
		'variation_id',
	];

	/**
	 * @var array Custom Event Goals used to track Events in WooCommerce.
	 */
	public $event_goals = [];

	/**
	 * Build class.
	 *
	 * @codeCoverageIgnore
	 */
	public function __construct( $init = true ) {
		$this->event_goals = [
			'view-product'     => __( 'Visit /product*', 'plausible-analytics' ),
			'add-to-cart'      => __( 'Woo Add to Cart', 'plausible-analytics' ),
			'remove-from-cart' => __( 'Woo Remove from Cart', 'plausible-analytics' ),
			'checkout'         => __( 'Woo Start Checkout', 'plausible-analytics' ),
			'purchase'         => __( 'Woo Complete Purchase', 'plausible-analytics' ),
		];

		$this->init( $init );
	}

	/**
	 * Filter and action hooks.
	 *
	 * @return void
	 *
	 * @codeCoverageIgnore
	 */
	private function init( $init ) {
		if ( ! $init ) {
			return;
		}

		/**
		 * Adds required JS and classes.
		 */
		add_action( 'wp_enqueue_scripts', [ $this, 'add_js' ], 1 );
		add_filter( 'woocommerce_store_api_add_to_cart_data', [ $this, 'add_http_referer' ], 10, 2 );

		/**
		 * Trigger tracking events.
		 */
		add_action( 'woocommerce_before_add_to_cart_quantity', [ $this, 'add_cart_form_hidden_input' ] );
		add_action( 'woocommerce_after_add_to_cart_form', [ $this, 'track_add_to_cart_on_product_page' ] );
		add_filter( 'woocommerce_store_api_validate_add_to_cart', [ $this, 'track_add_to_cart' ], 10, 2 );
		add_filter( 'woocommerce_ajax_added_to_cart', [ $this, 'track_ajax_add_to_cart' ] );
		add_action( 'woocommerce_remove_cart_item', [ $this, 'track_remove_cart_item' ], 10, 2 );
		add_action( 'wp_head', [ $this, 'track_entered_checkout' ] );
		add_action( 'woocommerce_thankyou', [ $this, 'track_purchase' ] );
	}

	/**
	 * Enqueue required JS in frontend.
	 *
	 * @return void
	 *
	 * @codeCoverageIgnore Because there's nothing to test here.
	 */
	public function add_js() {
		// Causes errors in checkout and isn't needed either way.
		if ( is_checkout() ) {
			return; // @codeCoverageIgnore
		}

		wp_enqueue_script(
			'plausible-woocommerce-integration',
			PLAUSIBLE_ANALYTICS_PLUGIN_URL . 'assets/dist/js/plausible-woocommerce-integration.js',
			[],
			filemtime( PLAUSIBLE_ANALYTICS_PLUGIN_DIR . 'assets/dist/js/plausible-woocommerce-integration.js' )
		);
	}

	/**
	 * A bit of a hacky approach to ensure the _wp_http_referer header is available to us when hitting the Proxy in @see self::track_add_to_cart()
	 * and @see self::track_remove_cart_item().
	 *
	 * @param $add_to_cart_data
	 * @param $request
	 *
	 * @return mixed
	 *
	 * @codeCoverageIgnore Because there's nothing to test here.
	 */
	public function add_http_referer( $add_to_cart_data, $request ) {
		$http_referer = $request->get_param( '_wp_http_referer' );

		if ( ! empty( $http_referer ) ) {
			$_REQUEST[ '_wp_http_referer' ] = sanitize_url( $http_referer );
		}

		return $add_to_cart_data;
	}

	/**
	 * Adds a hidden input with the same name and value as the add-to-cart button.
	 *
	 * TODO: This hack can be removed when the JS library uses sendBeacon to send the event.
	 *
	 * @return void
	 *
	 * @codeCoverageIgnore Because we can't test JS here.
	 */
	public function add_cart_form_hidden_input() {
		$product = wc_get_product();

		if ( ! $product ) {
			return;
		}
		?>
		<input type="hidden" name="add-to-cart" value="<?php echo $product->get_id(); ?>"/>
		<?php
	}

	/**
	 * A hacky approach (with lack of a proper solution) to make sure Add To Cart events are tracked on simple product pages.
	 *
	 * TODO: Once our JS library uses sendBeacon we might be able to refactor this into a less hacky approach.
	 *
	 * @return void
	 *
	 * @codeCoverageIgnore Because we're not testing JS here.
	 */
	public function track_add_to_cart_on_product_page() {
		$product = wc_get_product();

		if ( ! $product ) {
			return;
		}
		?>
		<script>
			let addToCartForm = document.querySelector('form.cart');
			let quantity = document.querySelector('input[name="quantity"]');

			addToCartForm.classList.add('plausible-event-name=<?php echo str_replace( ' ', '+', $this->event_goals[ 'add-to-cart' ] ); ?>');
			addToCartForm.classList.add('plausible-event-quantity=' + quantity.value);
			addToCartForm.classList.add('plausible-event-product_id=<?php echo $product->get_id(); ?>');
			addToCartForm.classList.add('plausible-event-product_name=<?php echo str_replace( ' ', '+', $product->get_name( null ) ); ?>');
			addToCartForm.classList.add('plausible-event-price=<?php echo $product->get_price( null ); ?>');

			quantity.addEventListener('change', function (e) {
				let target = e.target;
				addToCartForm.className = addToCartForm.className.replace(/(plausible-event-quantity=).+?/, "\$1" + target.value);
			});
		</script>
		<?php
	}

	/**
	 * Track (non-Interactivity API, i.e. AJAX) add to cart events.
	 *
	 * @param string|int $product_id ID of the product added to the cart.
	 *
	 * @return void
	 *
	 * @codeCoverageIgnore Because we can't test XHR requests here.
	 */
	public function track_ajax_add_to_cart( $product_id ) {
		$product          = wc_get_product( $product_id );
		$add_to_cart_data = [
			'id'       => $product_id,
			'quantity' => $_POST[ 'quantity' ] ?? 1,
		];

		$this->track_add_to_cart( $product, $add_to_cart_data );
	}

	/**
	 * Track regular (i.e. interactivity API) add to cart events.
	 *
	 * @param WC_Product $product          General information about the product added to cart.
	 * @param array      $add_to_cart_data Cart data for the product added to the cart, e.g. quantity, variation ID, etc.
	 *
	 * @return void
	 *
	 * @codeCoverageIgnore Because we can't test XHR requests here.
	 */
	public function track_add_to_cart( $product, $add_to_cart_data ) {
		$product_data  = $this->clean_data( $product->get_data() );
		$added_to_cart = $this->clean_data( $add_to_cart_data );
		$cart          = WC()->cart;
		$props         = apply_filters(
			'plausible_analytics_woocommerce_add_to_cart_custom_properties',
			[
				'product_name'     => $product_data[ 'name' ],
				'product_id'       => $added_to_cart[ 'id' ],
				'quantity'         => $added_to_cart[ 'quantity' ],
				'price'            => $product_data[ 'price' ],
				'tax_class'        => $product_data[ 'tax_class' ],
				'cart_total_items' => count( $cart->get_cart_contents() ),
				'cart_total'       => $cart->get_total( null ),
			]
		);
		$proxy         = new Proxy( false );

		$proxy->do_request( $this->event_goals[ 'add-to-cart' ], null, null, $props );
	}

	/**
	 * Removes unneeded elements from the array.
	 *
	 * @param array $product Product Data.
	 *
	 * @return mixed
	 *
	 * @codeCoverageIgnore Because it can't be tested.
	 */
	private function clean_data( $product ) {
		foreach ( $product as $key => $value ) {
			if ( ! in_array( $key, self::CUSTOM_PROPERTIES ) ) {
				unset( $product[ $key ] );
			}
		}

		return $product;
	}

	/**
	 * Track Remove from cart events.
	 *
	 * @param string  $cart_item_key Key of item being removed from cart.
	 * @param WC_Cart $cart          Instance of the current cart.
	 *
	 * @return void
	 *
	 * @codeCoverageIgnore because we can't test XHR requests here.
	 */
	public function track_remove_cart_item( $cart_item_key, $cart ) {
		$cart_contents          = $cart->get_cart_contents();
		$item_removed_from_cart = $this->clean_data( $cart_contents[ $cart_item_key ] ?? [] );
		$product                = null;

		if ( isset( $item_removed_from_cart[ 'product_id' ] ) ) {
			$product = wc_get_product( $item_removed_from_cart[ 'product_id' ] );
		}

		if ( ! $product ) {
			return;
		}

		$props = apply_filters(
			'plausible_analytics_woocommerce_remove_cart_item_custom_properties',
			[
				'product_name'     => $product->get_name(),
				'product_id'       => $item_removed_from_cart[ 'product_id' ],
				'variation_id'     => $item_removed_from_cart[ 'variation_id' ],
				'quantity'         => $item_removed_from_cart[ 'quantity' ],
				'cart_total_items' => count( $cart_contents ),
				'cart_total'       => $cart->get_total( null ),
			]
		);
		$proxy = new Proxy( false );

		$proxy->do_request( $this->event_goals[ 'remove-from-cart' ], null, null, $props );
	}

	/**
	 * @return void
	 */
	public function track_entered_checkout() {
		if ( ! is_checkout() ) {
			return; // @codeCoverageIgnore
		}

		$cart = WC()->cart;

		$props = apply_filters(
			'plausible_analytics_woocommerce_entered_checkout_custom_properties',
			[
				'props' => [
					'subtotal' => $cart->get_subtotal(),
					'shipping' => $cart->get_shipping_total(),
					'tax'      => $cart->get_total_tax(),
					'total'    => $cart->get_total( null ),
				],
			]
		);
		$props = wp_json_encode( $props );
		$label = $this->event_goals[ 'checkout' ];

		echo sprintf( Integrations::SCRIPT_WRAPPER, "window.plausible( '$label', $props )" );
	}

	/**
	 * Track WooCommerce purchase on thank you page.
	 *
	 * @param $order_id
	 *
	 * @return void
	 */
	public function track_purchase( $order_id ) {
		$order      = wc_get_order( $order_id );
		$is_tracked = $order->get_meta( self::PURCHASE_TRACKED_META_KEY );

		if ( $is_tracked ) {
			return; // @codeCoverageIgnore
		}

		$props = wp_json_encode(
			[
				'revenue' => [ 'amount' => (string) $order->get_total(), 'currency' => $order->get_currency() ],
			]
		);
		$label = $this->event_goals[ 'purchase' ];

		echo sprintf( Integrations::SCRIPT_WRAPPER, "window.plausible( '$label', $props )" );

		$order->add_meta_data( self::PURCHASE_TRACKED_META_KEY, true );
		$order->save();
	}
}
