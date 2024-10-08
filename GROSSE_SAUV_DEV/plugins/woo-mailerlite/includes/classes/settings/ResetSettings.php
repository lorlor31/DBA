<?php

namespace MailerLite\Includes\Classes\Settings;

use MailerLite\Includes\Classes\Singleton;

class ResetSettings extends Singleton
{
    /**
     * Class instance
     * @var $instance
     */
    protected static $instance;

    /**
     * Resets the tracked resources so that they can be re-synced
     * woo_ml_reset_tracked_resources
     */
    public function resetTrackedResources()
    {

        $finished = $this->processResetTrackedResources();
        delete_option('woo_ml_guests_sync_count');
        echo json_encode([
            'allDone' => $finished
        ]);
        // issue !!
        exit;
    }

    /**
     * Actual process to reset tracked resources and recreate shop if needed
     * woo_ml_reset_tracked_resources_process
     */
    public function processResetTrackedResources()
    {

        set_time_limit(1800);

        if ( ! $this->resetTrackedCategories()) {

            return false;
        }

        if ( ! $this->resetTrackedProducts()) {

            return false;
        }

        $customer_query = new \WP_User_Query(
            [
                'fields'     => 'ID',
                'role__in'   => ['customer', 'administrator'],
                'meta_query' => [
                    'relation' => 'AND',
                    [
                        'relation' => 'OR',
                        [
                            'key'     => 'last_order_date',
                            'compare' => 'EXISTS',
                        ],
                        [
                            'key'     => 'billing_last_name',
                            'compare' => 'EXISTS',
                        ],
                    ],
                    [
                        'key'     => '_woo_ml_customer_tracked',
                        'compare' => 'EXISTS'
                    ]
                ],
                'number'     => 100,
            ]
        );

        $customers = $customer_query->get_results();

        if ($customers > 0) {

            foreach ($customers as $customer) {

                $wc_customer = new \WC_Customer($customer);

                if ( ! empty($wc_customer->get_email())) {

                    $wc_customer->delete_meta_data('_woo_ml_customer_tracked');
                    $wc_customer->save_meta_data();
                }
            }
        }

        return (count($customers) == 0);
    }

    /**
     * Resets the tracked products so that they can be re-synced
     * woo_ml_reset_tracked_products
     */
    public function resetTrackedProducts()
    {
        set_time_limit(1800);

        $defaults = array(
            'post_type'      => 'product',
            'posts_per_page' => 100,
            'meta_key'       => '_woo_ml_product_tracked',
            'meta_compare'   => 'EXISTS'
        );

        $args                = wp_parse_args($defaults);
        $product_posts_query = new \WP_Query($args);

        $product_posts = [];

        if ($product_posts_query->have_posts()) {
            $product_posts = $product_posts_query->get_posts();
        }

        $finished = count($product_posts) == 0;

        foreach ($product_posts as $post) {

            if ( ! isset($post->ID)) {

                continue;
            }

            delete_post_meta($post->ID, '_woo_ml_product_tracked');
        }

        return $finished;
    }

    /**
     * Resets the tracked categories so that they can be re-synced
     * woo_ml_reset_tracked_categories
     */
    public function resetTrackedCategories()
    {
        set_time_limit(1800);

        $term_args = array(
            'taxonomy'     => 'product_cat',
            'hide_empty'   => false,
            'orderby'      => 'none',
            'meta_key'     => '_woo_ml_category_tracked',
            'meta_compare' => 'EXISTS'
        );

        $categories = get_terms($term_args);

        $finished = count($categories) == 0;

        foreach ($categories as $category) {

            if ( ! isset($category->term_id)) {

                continue;
            }

            delete_term_meta($category->term_id, '_woo_ml_category_tracked');
        }

        return $finished;
    }

    /**
     * Reset shop sync on platform change
     * mailerlite_reset_shop
     * @return bool|void
     */
    public function resetShop()
    {
        $this->processResetTrackedResources();
    }

}