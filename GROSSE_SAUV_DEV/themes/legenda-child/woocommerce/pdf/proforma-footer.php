<?php
/**
 * This file belongs to the YIT Plugin Framework.
 *
 * This source file is subject to the GNU GENERAL PUBLIC LICENSE (GPL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://www.gnu.org/licenses/gpl-3.0.txt
 */

/**
 * @var $footer string
 * @var $pagination string
 * @var $order_id int
 */

if( function_exists('icl_get_languages')   ) {
	global $sitepress;
	$lang = get_post_meta( $order_id, 'wpml_language', true );
	YITH_Request_Quote_Premium()->change_pdf_language( $lang );
}
?>
<div class="footer">
    <div class="footer-content">
        <hr>
        <p>
            Société à responsabilité limitée (SARL) - Capital de 91 000 € - SIRET: 393 708 052 00070<br>
            NAF-APE: 4669 B - RCS/RM: Toulouse 393 708 052 - Numéro TVA: FR 79 393 708 052
        </p>
    </div>
</div>
<?php
if( function_exists('wc_restore_locale')) {
	wc_restore_locale();
}
?>