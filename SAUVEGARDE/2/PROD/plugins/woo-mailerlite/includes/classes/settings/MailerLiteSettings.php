<?php

namespace MailerLite\Includes\Classes\Settings;

use MailerLite\Includes\Classes\Singleton;

class MailerLiteSettings extends Singleton
{
    /**
     * Class instance
     * @var $instance
     */
    protected static $instance;

    /**
     * Check if checkout action is active
     * woo_ml_is_active
     * @return mixed
     */
    public function isActive()
    {
        $api_status = $this->getMlOption('api_status', false);

        return $api_status;
    }

    /**
     * Get settings option
     * woo_ml_get_option
     *
     * @param $key
     * @param null $default
     *
     * @return null
     */
    public function getMlOption($key, $default = null)
    {
        $settings = get_option('woocommerce_mailerlite_settings');

        return (isset($settings[$key])) ? $settings[$key] : $default;
    }

    /**
     *
     * woo_ml_sync_failed
     * @return bool
     */
    public function syncFailed()
    {
        return get_option('woo_ml_resource_sync_failed') || (get_option('woo_ml_sync_active') && ! get_transient('woo_ml_resource_sync_in_progress'));
    }

    /**
     * Get settings group options
     * woo_ml_settings_get_group_options
     * @return array
     */
    public function getGroupOptions()
    {

        if ( ! is_admin()) {
            return [];
        }

        $options = array();

        $groups = mailerlite_wp_get_groups();

        if (is_array($groups) && sizeof($groups) > 0) {
            $options[''] = __('Please select...', 'woo-mailerlite');
            foreach ($groups as $group) {
                if (isset($group['id']) && isset($group['name'])) {
                    $options[$group['id']] = $group['name'];
                }
            }
        } else {
            $options[''] = __('No groups found', 'woo-mailerlite');
        }

        return $options;
    }

    /**
     * Map array in correct structure for WooCommerce Integration
     * woo_ml_remap_list
     * @return array
     */
    public function remapList($products)
    {

        return array_map('strval', array_keys($products));
    }

    /**
     * Creates the ml_data table and sets the ml_data_table option flag
     * woo_create_mailer_data_table
     */
    public function createMailerDataTable()
    {

        global $wpdb;

        $charset_collate = $wpdb->get_charset_collate();

        $table = $wpdb->prefix . 'ml_data';

        $sql = "CREATE TABLE $table (
            data_name varchar(45) NOT NULL,
            data_value text NOT NULL,
            PRIMARY KEY  (data_name)
        ) $charset_collate;";

        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($sql);

        update_option('ml_data_table', 1);

    }

    /**
     * Set WooCommerce MailerLite settings options
     * woo_ml_set_option
     *
     * @param $key
     * @param $value
     *
     * @return bool
     */
    public function setOption($key, $value)
    {
        $settings = get_option('woocommerce_mailerlite_settings');

        if (isset($settings[$key])) {
            $settings[$key] = $value;
        }

        update_option('woocommerce_mailerlite_settings', $settings);

        return true;
    }

    /**
     * Remove product from ignored
     * woo_ml_remove_ignore_product
     *
     * @param $product_id
     */
    public function removeIgnoreProduct($product_id)
    {

        delete_post_meta($product_id, '_woo_ml_product_ignored');
    }

    /**
     * Mark product as ignored for automation
     * woo_ml_ignore_product
     *
     * @param $product_id
     */
    public function ignoreProduct($product_id)
    {

        add_post_meta($product_id, '_woo_ml_product_ignored', true, true);
    }

    /**
     * Mark category as being tracked
     * woo_ml_complete_category_tracking
     *
     * @param $category_id
     * @param $ecommerce_id
     */
    public function completeCategoryTracking($category_id, $ecommerce_id)
    {

        add_term_meta($category_id, '_woo_ml_category_tracked', $ecommerce_id, true);
    }

    /**
     * Mark product as being tracked
     * woo_ml_complete_product_tracking
     *
     * @param $product_id
     */
    public function completeProductTracking($product_id)
    {

        add_post_meta($product_id, '_woo_ml_product_tracked', true, true);
    }

    /**
     * Check whether product was already tracked or not
     * woo_ml_product_tracking_completed
     *
     * @param $product_id
     *
     * @return bool
     */
    public function checkProductTracking($product_id)
    {

        $product_tracked = get_post_meta($product_id, '_woo_ml_product_tracked', true);

        return ('1' == $product_tracked) ? true : false;
    }

    /**
     * Get subscriber fields from customer data
     * woo_ml_get_subscriber_fields_from_customer_data
     *
     * @param $customer_data
     *
     * @return array
     */
    public function getSubscriberFieldsFromCustomerData($customer_data)
    {

        $subscriber_fields = array();

        if ( ! empty($customer_data['first_name'])) {
            $subscriber_fields['name'] = $customer_data['first_name'];
        }

        if ( ! empty($customer_data['last_name'])) {
            $subscriber_fields['last_name'] = $customer_data['last_name'];
        }

        if ( ! empty($customer_data['company'])) {
            $subscriber_fields['company'] = $customer_data['company'];
        }

        if ( ! empty($customer_data['city'])) {
            $subscriber_fields['city'] = $customer_data['city'];
        }

        if ( ! empty($customer_data['postcode'])) {
            $subscriber_fields['zip'] = $customer_data['postcode'];
        }

        if ( ! empty($customer_data['state'])) {
            $subscriber_fields['state'] = $customer_data['state'];
        }

        if ( ! empty($customer_data['country'])) {
            $subscriber_fields['country'] = $customer_data['country'];
        }

        if ( ! empty($customer_data['phone'])) {
            $subscriber_fields['phone'] = $customer_data['phone'];
        }

        return $subscriber_fields;
    }
}