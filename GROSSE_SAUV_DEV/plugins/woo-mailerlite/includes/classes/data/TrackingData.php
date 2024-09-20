<?php

namespace MailerLite\Includes\Classes\Data;

use MailerLite\Includes\Classes\Settings\MailerLiteSettings;
use MailerLite\Includes\Classes\Settings\ShopSettings;
use MailerLite\Includes\Classes\Singleton;

class TrackingData extends Singleton
{
    /**
     * Get untracked product categories
     * woo_ml_get_untracked_categories
     * @return array
     */
    public function getUntrackedCategories()
    {

        $term_args = array(
            'taxonomy' => 'product_cat',
            'hide_empty' => false,
            'orderby' => 'none',
            'meta_key' => '_woo_ml_category_tracked',
            'meta_compare' => 'NOT EXISTS'
        );

        return get_terms($term_args);
    }

    /**
     * Get untracked products
     * woo_ml_get_untracked_products
     *
     * @param array $args
     *
     * @return array
     */
    public function getUntrackedProducts($args = array())
    {

        $defaults = array(
            'post_type' => 'product',
            'posts_per_page' => 100,
            'meta_key' => '_woo_ml_product_tracked',
            'meta_compare' => 'NOT EXISTS'
        );

        $args = wp_parse_args($args, $defaults);
        $product_posts_query = new \WP_Query($args);

        $products = [];

        if ($product_posts_query->have_posts()) {
            $products = $product_posts_query->posts;
        }

        return $products;
    }

    /**
     * Get tracked product categories count
     * woo_ml_get_tracked_categories_count
     * @return int
     */
    public function getTrackedCategoriesCount()
    {

        $term_args = array(
            'taxonomy' => 'product_cat',
            'hide_empty' => false,
            'orderby' => 'none',
            'meta_key' => '_woo_ml_category_tracked',
            'meta_compare' => 'EXISTS'
        );

        $categories = get_terms($term_args);

        return count($categories);
    }

    /**
     *
     * woo_ml_count_untracked_products_count
     * @return mixed
     */
    public function getUntrackedProductsCount()
    {
        $defaults = array(
            'post_type' => 'product',
            'posts_per_page' => 1,
            'meta_key' => '_woo_ml_product_tracked',
            'meta_compare' => 'NOT EXISTS'
        );

        $args = wp_parse_args($defaults);
        $products_query = new \WP_Query($args);

        return $products_query->found_posts;
    }

    /**
     *
     * woo_ml_get_untracked_customers_count
     * @return mixed
     */
    public function getUntrackedCustomersCount()
    {
        $customer_query = new \WP_User_Query(
            [
                'fields' => 'ID',
                'role__in' => ['customer', 'administrator'],
                'meta_query' => [
                    'relation' => 'AND',
                    [
                        'relation' => 'OR',
                        [
                            'key' => 'last_order_date',
                            'compare' => 'EXISTS',
                        ],
                        [
                            'key' => 'billing_last_name',
                            'compare' => 'EXISTS',
                        ],
                    ],
                    [
                        'key' => '_woo_ml_customer_tracked',
                        'compare' => 'NOT EXISTS'
                    ]
                ],
                'number' => 1,
            ]
        );

        return $customer_query->get_total();
    }

    /**
     *
     * woo_ml_get_tracked_products_count
     * @return int
     */
    public function getTrackedProductCount()
    {
        $defaults = array(
            'post_type' => 'product',
            'posts_per_page' => 100,
            'meta_key' => '_woo_ml_product_tracked',
            'meta_compare' => 'EXISTS'
        );

        $args = wp_parse_args($defaults);
        $product_posts_query = new \WP_Query($args);

        $product_posts = [];

        if ($product_posts_query->have_posts()) {
            $product_posts = $product_posts_query->get_posts();
        }

        return count($product_posts);
    }

    /**
     *
     * woo_ml_count_untracked_categories_count
     * @return int
     */
    public function getUntrackedCategoriesCount()
    {
        return count($this->getUntrackedCategories());
    }

    /**
     *
     * woo_ml_get_tracked_customers_count
     * @return mixed
     */
    public function getTrackedCustomersCount()
    {
        $customer_query = new \WP_User_Query(
            [
                'fields' => 'ID',
                'role__in' => ['customer', 'administrator'],
                'meta_query' => [
                    'relation' => 'AND',
                    [
                        'relation' => 'OR',
                        [
                            'key' => 'last_order_date',
                            'compare' => 'EXISTS',
                        ],
                        [
                            'key' => 'billing_last_name',
                            'compare' => 'EXISTS',
                        ],
                    ],
                    [
                        'key' => '_woo_ml_customer_tracked',
                        'compare' => 'EXISTS'
                    ]
                ],
                'number' => 1,
            ]
        );

        return $customer_query->get_total();
    }

    /**
     *
     * woo_ml_get_untracked_customers
     * @return array|array[]
     */
    public function getUntrackedCustomers()
    {

        $customer_query = new \WP_User_Query(
            [
                'fields' => 'ID',
                'role__in' => ['customer', 'administrator'],
                'meta_query' => [
                    'relation' => 'AND',
                    [
                        'relation' => 'OR',
                        [
                            'key' => 'last_order_date',
                            'compare' => 'EXISTS',
                        ],
                        [
                            'key' => 'billing_last_name',
                            'compare' => 'EXISTS',
                        ],
                    ],
                    [
                        'key' => '_woo_ml_customer_tracked',
                        'compare' => 'NOT EXISTS'
                    ]
                ],
                'number' => 100,
            ]
        );

        return array_map(function ($customer) {
            return [
                'id' => $customer
            ];
        }, $customer_query->get_results());
    }

    /**
     * Get settings page url
     * woo_ml_get_settings_page_url
     * @return string
     */
    public function getSettingsPageUrl()
    {
        return admin_url('admin.php?page=wc-settings&tab=integration&section=mailerlite');
    }

    /**
     * Get complete integration setup url
     * woo_ml_get_complete_integration_setup_url
     * @return string
     */
    public function getCompleteIntegrationSetupUrl()
    {
        return add_query_arg('woo_ml_action', 'setup_integration', $this->getSettingsPageUrl());
    }

    /**
     * Update ignore product list in ml_data table
     * woo_ml_update_data
     * @return mixed
     */
    public function updateData($products)
    {

        global $wpdb;

        $table = $wpdb->prefix . 'ml_data';

        $tableCreated = get_option('ml_data_table');

        if ($tableCreated != 1) {

            MailerLiteSettings::getInstance()->createMailerDataTable();
        }

        $updateQuery = $wpdb->prepare("
                INSERT INTO $table (data_name, data_value) VALUES ('products', %s) ON DUPLICATE KEY UPDATE data_value = %s
                ", json_encode($products), json_encode($products));

        return $wpdb->query($updateQuery);
    }

    /**
     * Save ignored products to WooCommerce Integration and ml_data table
     * woo_ml_save_local_ignore_products
     */
    public function saveLocalIgnoreProducts($products)
    {

        $ignore_map = MailerLiteSettings::getInstance()->remapList($products);

        if (ShopSettings::getInstance()->updateIgnoreProductList($ignore_map) === true) {

            // save updated ignore product list to WooCommerce Integration
            $settings = get_option('woocommerce_mailerlite_settings');

            if (!isset($settings['ignore_product_list'])) {
                $settings['ignore_product_list'] = array();
            }

            $settings['ignore_product_list'] = $ignore_map;

            update_option('woocommerce_mailerlite_settings', $settings);

            //save product ignore list to ml_data
            $this->updateData($products);
        }
    }

    /**
     * Remove product from product ignore list for ml_data
     * woo_ml_remove_product_from_list
     * @return array
     */
    public function removeProductFromList($products, $remove_list)
    {

        return array_filter($products, function ($k) use ($remove_list) {
            return !in_array($k, $remove_list);
        }, ARRAY_FILTER_USE_KEY);
    }

    /**
     *
     * woo_ml_get_customers_count
     * @return mixed
     */
    public function getCustomersCount()
    {
        $customer_query = new \WP_User_Query(
            [
                'fields' => 'ID',
                'role__in' => ['customer', 'administrator'],
                'meta_query' => [
                    'relation' => 'OR',
                    [
                        'key' => 'last_order_date',
                        'compare' => 'EXISTS',
                    ],
                    [
                        'key' => 'billing_last_name',
                        'compare' => 'EXISTS',
                    ],
                ],
                'number' => 1,
            ]
        );

        return $customer_query->get_total();
    }

    /**
     *
     * woo_ml_get_all_customers_count
     * @return int
     * @throws \Exception
     */
    public function getAllCustomersCount()
    {
        $data_store = \WC_Data_Store::load('report-customers');

        $customers = $data_store->get_data([
            'per_page' => 1,
            'page' => 1,
            'order_before' => null,
            'order_after' => null,
        ]);

        return $customers->total ?? 0;
    }

    /**
     *
     * woo_ml_get_all_customers
     *
     * @param $page
     *
     * @return array
     * @throws \Exception
     */
    public function getAllCustomers($page = 1)
    {
        $data_store = \WC_Data_Store::load('report-customers');

        $customers = $data_store->get_data([
            'per_page' => 100,
            'page' => $page,
            'order_before' => null,
            'order_after' => null,
        ]);

        if (isset($customers->pages)) {

            if ($customers->pages >= $page) {

                WC()->session->set('untracked_customer_page', $page + 1);
            } else {

                return [];
            }
        }

        return $customers->data ?? [];
    }
}