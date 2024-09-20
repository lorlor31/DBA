<div class="notice notice-warning is-dismissible" id="gpf_wc2_deprecation_notice">
	<p>
		<?php _e( 'The <strong>WooCommerce Product Feeds</strong> plugin is ending support for WooCommerce versions older than version 3.0. Future releases past v7.9.x will <strong>not</strong> work with your current version of WooCommerce.<br>For more information see the <a href="https://docs.woocommerce.com/document/frequently-asked-questions/#wcvers">WooCommerce support policy</a>.', 'woocommerce_gpf' ); ?>
	</p>
</div>

<script type="text/javascript">
	jQuery( function() {
	   jQuery( '#gpf_wc2_deprecation_notice' ).on( 'click', function() {
           var data = {
               'action': 'gpf_dismiss_admin_notice',
	           'notice': 'wc2_deprecation_notice',
           };
           jQuery.post( ajaxurl, data );
	   } );
	} );
</script>
