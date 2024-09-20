<?php

/**
 * displays the star rating
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}
?>
<div class="steavisgarantisStar">
    <span></span>
    <span class="note animate" style="width:<?php echo $ratings['average'] / 5 *100; ?>%;"></span>
</div>