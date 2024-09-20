<?php
/**
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

// check user capabilities
if ( ! current_user_can( 'manage_options' ) ) {
	return;
}
	settings_errors( 'firmlet_messages' );
?>
<div class="wrap">
	<form action="options.php" method="post">
		<?php
			settings_fields( 'firmlet' );
			do_settings_sections( 'firmlet' );
			submit_button();
		?>
	</form>
</div>
