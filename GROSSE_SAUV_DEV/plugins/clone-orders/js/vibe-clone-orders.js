let vibe_clone_orders = (function( $ ) {

	/**
	 * The line items div
	 */
	let $postbox;

	let _init = function() {
		console.log("_init called");
		$postbox = $( '#woocommerce-order-items' );

		if ( $postbox.length > 0 ) {
			$postbox.on( 'click', 'button.clone-order', _begin_clone_single );
		}

		$( document.body ).on( 'wc_backbone_modal_response', _popup_confirmed );
	};

	let _begin_clone_single = function( e ) {
		e.preventDefault();

		let order_id = $( 'button.clone-order' ).data('id');

		_show_popup( order_id );
	};

	let _block_el = function( $el ) {
		$el.block( {
			message: null, overlayCSS: {
				background: '#fff', opacity: 0.6
			}
		} );
	};

	let _unblock_el = function( $el ) {
		$el.unblock();
	};

	let _show_popup = function( order_id ) {
		$( this ).WCBackboneModal( {
			template: 'wc-modal-clone-order'
		} );

		$( '#vibe-clone-orders-source-id' ).val( order_id );
	};

	let _popup_confirmed = function( event, target ) {
		if ( 'wc-modal-clone-order' === target ) {
			let order_id = $( '#vibe-clone-orders-source-id' ).val();
			let clone_items = $( '#vibe-clone-orders-clone-items' ).is(':checked');

			_clone_order( order_id, clone_items );
		}
	};

	let _clone_order = function( post_id, clone_items ) {
		_block_el( $postbox );

		let data = {
			action:   'vibe_clone_orders_clone_order',
			_wpnonce: vibe_clone_orders_data.cloning_nonce,
			items:    clone_items ? 1 : 0,
			order_id: post_id,
			response: 'json'
		};

		$.post( vibe_clone_orders_data.ajaxurl, data, _clone_order_response );
	};

	let _clone_order_response = function( response ) {
		_unblock_el( $postbox );

		if ( response.notices ) {
			var $headerEnd = $( '.wp-header-end' ).first();

			$headerEnd.after( response.notices );
			$([document.documentElement, document.body]).animate({
				scrollTop: 0
			}, 500);
		}
	};



	$( _init );

	return {};

})( jQuery );