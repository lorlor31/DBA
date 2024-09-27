/*
 * NOTICE OF LICENSE
 *
 * This file is licenced under the Software License Agreement.
 * With the purchase or the installation of the software in your application
 * you accept the licence agreement.
 *
 * You must not modify, adapt or create derivative works of this source code
 *
 *  @author    vosfactures.fr
 *  @copyright 2020 vosfactures.fr
 *  @license   LICENSE.txt
 */
/*
 * NOTICE OF LICENSE
 *
 * This file is licenced under the Software License Agreement.
 * With the purchase or the installation of the software in your application
 * you accept the licence agreement.
 *
 * You must not modify, adapt or create derivative works of this source code
 *
 *  @author    vosfactures.fr
 *  @copyright 2020 vosfactures.fr
 *  @license   LICENSE.txt
 */
(function ($) {
	'use strict';

	var $woocommerceFirmletForceVat = $('#woocommerce_firmlet_force_vat').parent().parent().parent().parent();
	var $woocommerceFirmletIdentifyOss = $('#woocommerce_firmlet_identify_oss');

	if (!$woocommerceFirmletIdentifyOss.prop('checked')) {
		$woocommerceFirmletForceVat.hide();
	}

	$woocommerceFirmletIdentifyOss.on('change', function() {
		if($(this).prop('checked')) {
			$woocommerceFirmletForceVat.show();
		} else {
			$woocommerceFirmletForceVat.hide();
		}
	})

})( jQuery );
