<?php

/**
 * displays the shortcode content
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}
?>
<?php while( $reviews_query->have_posts() ) : $reviews_query->the_post(); ?>
    <li class="bgGrey0">
        <div class="author">
            <img width="24px" height="24px" src="<?php echo WC_SAG_PLUGIN_URL; ?>assets/images/ico_user.png">
            <span><?php echo esc_html( get_post_meta( get_the_ID(), '_wcsag_firstname', true ) ); ?> <?php echo esc_html( substr( get_post_meta( get_the_ID(), '_wcsag_lastname', true ), 0, 1 ) . '.' ); ?></span>
            <br>
            <span class="time">
                <span class="published">
                    <?php printf( __( 'Published on %1$s at %2$s', 'woo-guaranteed-reviews-company' ), date_i18n( wc_date_format(), get_post_time() ), date_i18n( wc_time_format(), get_post_time() ) ); ?>
                </span>
                <?php if ( ( $order_date = get_post_meta( get_the_ID(), '_wcsag_order_date', true ) ) && ( false === strpos( $order_date, '1970-01-01' ) )  && ( false === strpos( $order_date, '0000-00-00' ) )) : ?>
                    (<?php printf( __( 'Order date: %1$s at %2$s', 'woo-guaranteed-reviews-company' ), date_i18n( wc_date_format(), strtotime( $order_date ) ), date_i18n( wc_time_format(), strtotime( $order_date ) ) ); ?>)
                <?php endif; ?>
            </span>
        </div>
        <div class="reviewTxt">
            <div class="steavisgarantisStar">
                <span></span>
                <span class="note" style="width:<?php echo get_post_meta( get_the_ID(), '_wcsag_rating', true ) / 5 * 100; ?>%"></span>
            </div>
            <span class="metaHide"><?php echo get_post_meta( get_the_ID(), '_wcsag_rating', true ); ?></span>
            <?php the_content(); ?>
            <?php if ( $answer = get_post_meta( get_the_ID(), '_wcsag_answer_text', true ) ) : ?>
                <div class="reponse">
                    <span><img src="<?php echo WC_SAG_PLUGIN_URL; ?>assets/images/ico_pen.png" height="12"><?php _e('Merchant\'s answer', 'woo-guaranteed-reviews-company'); ?></span>
                    <p><?php echo esc_html($answer); ?></p>
                </div>
            <?php endif; ?>
        </div>
    </li>
<?php endwhile; ?>
<?php wp_reset_postdata(); ?>