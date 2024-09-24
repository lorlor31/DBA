<?php

/**
 * displays the shortcode content
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}
?>
<div id="agWidgetH" class="agWidget rad <?php echo $this->settings->get( 'sag_lang' ); ?>">
    <div class="inner rad">
    <img src="<?php echo WC_SAG_PLUGIN_URL; ?>assets/images/steavisgarantis_logo_badge_<?php echo $this->settings->get( 'sag_lang' ); ?>.png" class="logoAg">
        <div class="reviewGlobal">
        <div class="steavisgarantisStar">
            <span></span>
            <span class="note animate" style="width:<?php echo $ratings['average'] / 5 *100; ?>%;"></span>
        </div>
        <p>(<?php if (isset($ratings)) {echo round($ratings['average'], 1);}?>/5) <?php if ($reviews_query->found_posts > 1) {echo sprintf( _n( 'Based on %s review', 'Based on %s reviews', $reviews_query->found_posts, 'woo-guaranteed-reviews-company' ), $reviews_query->found_posts );} ?></p>
        <a class="agBt rad4" onclick="showReviews(); return false;" href="#ag-s"><?php _e( 'See reviews', 'woo-guaranteed-reviews-company' ); ?></a>
        </div>
    </div>
</div>