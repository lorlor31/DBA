<?php

/**
 * displays the shortcode content
 */

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
    die;
}
?>
<div id="ag-s" class="<?php echo $this->settings->get( 'sag_lang' ); ?>">
    <div>
        <div id="agWidgetMain" class="agWidget rad">
            <div class="topBar"><?php _e( 'Reviews about product', 'woo-guaranteed-reviews-company' ); ?></div>
            <div class="inner bgGrey1">
                <div class="logoCont">
                    <img src="<?php echo WC_SAG_PLUGIN_URL; ?>assets/images/steavisgarantis_logo_fr.png" width="150px" height="35px" class="logoAg">
                    <a href="<?php echo esc_url( $this->settings->get( 'certificate_url' ) ); ?>" class="agBt certificateBtn" target="_blank"><?php _e( 'See certificate', 'woo-guaranteed-reviews-company' ); ?></a>
                </div>
                <div class="statCont">
                    <div class="steavisgarantisStats">
                        <div class="item">
                            <span class="stat">
                                <div class="note bar1" style="height:<?php echo $ratings['distribution'][1] / $ratings['count'] * 100; ?>%">
                                    <span class="value"><?php echo $ratings['distribution'][1]; ?></span>
                                </div>
                            </span>
                            <span class="name">1★</span>
                        </div>
                        <div class="item">
                            <span class="stat">
                                <div class="note bar1" style="height:<?php echo $ratings['distribution'][2] / $ratings['count'] * 100; ?>%">
                                    <span class="value"><?php echo $ratings['distribution'][2]; ?></span>
                                </div>
                            </span>
                            <span class="name">2★</span>
                        </div>
                        <div class="item">
                            <span class="stat">
                                <div class="note bar1" style="height:<?php echo $ratings['distribution'][3] / $ratings['count'] * 100; ?>%">
                                    <span class="value"><?php echo $ratings['distribution'][3]; ?></span>
                                </div>
                            </span>
                            <span class="name">3★</span>
                        </div>
                        <div class="item">
                            <span class="stat">
                                <div class="note bar1" style="height:<?php echo $ratings['distribution'][4] / $ratings['count'] * 100; ?>%">
                                    <span class="value"><?php echo $ratings['distribution'][4]; ?></span>
                                </div>
                            </span>
                            <span class="name">4★</span>
                        </div>
                        <div class="item">
                            <span class="stat">
                                <div class="note bar1" style="height:<?php echo $ratings['distribution'][5] / $ratings['count'] * 100; ?>%">
                                    <span class="value"><?php echo $ratings['distribution'][5]; ?></span>
                                </div>
                            </span>
                            <span class="name">5★</span>
                        </div>
                    </div>
                </div>
                <div class="reviewCont">
                    <div class="reviewGlobal">
                        <div class="largeNote">
                            <big><?php echo round($ratings['average'] * 2, 1); ?></big>/10
                            <p><br><?php echo sprintf( _n( 'Based on %s review', 'Based on %s reviews', $reviews_query->found_posts, 'woo-guaranteed-reviews-company' ), $reviews_query->found_posts ); ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <ul class="reviewList">
            
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
                                <?php if ( ( $order_date = get_post_meta( get_the_ID(), '_wcsag_order_date', true ) ) && ( false === strpos( $order_date, '1970-01-01' ) ) && ( false === strpos( $order_date, '0000-00-00' ) ) ) : ?>
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

            </ul>

            <?php if ( $reviews_query->found_posts > $reviews_query->post_count ) : ?>
                <img id="chargement" src="<?php echo WC_SAG_PLUGIN_URL; ?>assets/images/page.gif" style="display:none">
                <div class="inner2">
                    <a class="agBt rad4 agBtBig" href="#more-reviews" id="more-reviews" onclick="return showMoreReviews(<?php echo $atts['id']; ?>, <?php echo $reviews_query->found_posts; ?>, 2, '<?php echo admin_url( 'admin-ajax.php?action=wcsag_more_reviews' ); ?>', null);" rel="2"><?php _e( 'Show more reviews', 'woo-guaranteed-reviews-company' ); ?></a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>