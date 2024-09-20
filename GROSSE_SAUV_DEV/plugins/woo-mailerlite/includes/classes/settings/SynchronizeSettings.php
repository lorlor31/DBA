<?php

namespace MailerLite\Includes\Classes\Settings;

use Automattic\WooCommerce\Utilities\OrderUtil;
use MailerLite\Includes\Classes\Data\TrackingData;
use MailerLite\Includes\Classes\Process\OrderProcess;
use MailerLite\Includes\Classes\Process\ProductProcess;
use MailerLite\Includes\Classes\Singleton;
use MailerLite\Includes\Shared\Api\ApiType;
use MailerLite\Includes\Shared\Api\PlatformAPI;

class SynchronizeSettings extends Singleton
{

    /**
     * Class instance
     * @var $instance
     */
    protected static $instance;


    /**
     * Bulk synchronize untracked products
     * woo_ml_sync_untracked_products
     * @return array
     */
    public function syncUntrackedProducts()
    {

        set_time_limit(600);

        $message = 'Oops, we did not manage to sync all of your products, please try again.';

        try {

            $checkProducts = TrackingData::getInstance()->getUntrackedProducts();

            $mailerliteClient = new PlatformAPI(MAILERLITE_WP_API_KEY);

            if (is_array($checkProducts) && sizeof($checkProducts) > 0) {

                $shop = get_option('woo_ml_shop_id', false);

                if ($shop === false) {

                    return [
                        'error' => true,
                        'message' => 'Shop is not activated.'
                    ];
                }

                $syncProducts = [];

                foreach ($checkProducts as $post) {

                    if (!isset($post->ID)) {
                        continue;
                    }

                    $product = wc_get_product($post->ID);

                    $productID = $product->get_id();
                    $productName = $product->get_name() ?: 'Untitled product';
                    $productPrice = floatval($product->get_price('edit'));
                    $productImage = ProductProcess::getInstance()->productImage($product);

                    $productURL = $product->get_permalink();

                    $categories = get_the_terms($productID, 'product_cat');

                    $productCategories = [];

                    foreach ($categories as $category) {

                        if (isset($category->term_id) && is_numeric($category->term_id)) {
                            if(!in_array((string) $category->term_id, $productCategories)) {
                                $productCategories[] = (string)$category->term_id;
                            }
                        }
                    }

                    $exclude_automation = get_post_meta($productID, '_woo_ml_product_ignored', true) === "1";

                    $syncProduct = [
                        'resource_id' => (string)$productID,
                        'name' => $productName,
                        'price' => $productPrice,
                        'url' => $productURL,
                        'exclude_from_automations' => $exclude_automation,
                        'categories' => $productCategories
                    ];

                    if (!empty($productImage)) {

                        $syncProduct['image'] = (string)$productImage;
                    }


                    $syncProducts[] = $syncProduct;
                }

                $syncCount = 0;

                if (count($syncProducts) > 0) {

                    $result = $mailerliteClient->importProducts($shop, $syncProducts);

                    if (empty($result) || $mailerliteClient->responseCode() == 422 || $mailerliteClient->responseCode() == 500) {

                        $errorMsg = json_decode($mailerliteClient->getResponseBody());
                        $message = 'Oops, we did not manage to sync all of your products, please try again. (' . $mailerliteClient->responseCode() . ')';

                        if ($mailerliteClient->responseCode() == 422 && isset($errorMsg->message)) {

                            $message = $errorMsg->message;
                        }

                        return [
                            'error' => true,
                            'code' => $mailerliteClient->responseCode(),
                            'message' => $message
                        ];
                    }

                    if ($mailerliteClient->responseCode() == 201 || $mailerliteClient->responseCode() == 200) {

                        foreach ($syncProducts as $product) {

                            MailerLiteSettings::getInstance()->completeProductTracking($product['resource_id']);
                            $syncCount++;
                        }
                    }
                }

                return [
                    'completed' => $syncCount,
                    'code' => $mailerliteClient->responseCode()
                ];

            }

            return [];
        } catch (\Exception $e) {
            return [
                'error' => true,
                'message' => $message
            ];
        }
    }

    /**
     * Bulk synchronize untracked resources
     * woo_ml_sync_untracked_resources
     * @return bool
     * @throws Exception
     */
    public function syncUntrackedResources()
    {
        $trackingData = TrackingData::getInstance();
        if ((int)get_option('woo_mailerlite_platform', 1) === ApiType::CURRENT) {
            $untracked_categories_count = $trackingData->getUntrackedCategoriesCount();

            if ($untracked_categories_count > 0) {

                return json_encode(array_merge([
                    'allDone' => false,
                ], $this->syncUntrackedCategories()));
            }

            $untracked_products_count = $trackingData->getUntrackedProductsCount();

            if ($untracked_products_count > 0) {

                return json_encode(array_merge([
                    'allDone' => false,
                ], $this->syncUntrackedProducts()));
            }
        }

        $untracked_customers_count = $trackingData->getUntrackedCustomersCount();

        $guestsSynced = get_option('woo_ml_guests_sync_count', false);

        if (($untracked_customers_count > 0) || !$guestsSynced) {
            return json_encode(array_merge([
                'allDone' => false,
            ], $this->syncUntrackedCustomers()));
        }

        //reset after all customers are synced
        WC()->session->set('untracked_customer_page', null);
        return json_encode([
            'allDone' => true,
            'completed' => 0,
        ]);
    }

    /**
     * Bulk synchronize untracked customers
     * woo_ml_sync_untracked_customers
     * @return array
     * @throws Exception
     */
    public function syncUntrackedCustomers()
    {

        set_time_limit(600);

        $message = 'Oops, we did not manage to sync all of your customers, please try again.';

        try {

            $customers = [];
            $guestsSynced = get_option('woo_ml_guests_sync_count', 0);
            $page = WC()->session->get('untracked_customer_page');
            $trackingData = TrackingData::getInstance();
            if (is_null($page)) {
                $untracked_customers_count = $trackingData->getUntrackedCustomersCount();
                $total_customers_count = $trackingData->getCustomersCount();

                if (($total_customers_count == $untracked_customers_count) || !$guestsSynced || $total_customers_count == 0) {
                    $guestsSynced = 0;
                    // start sync all, including guest to initial setup
                    $page = 1;

                    $customers = $trackingData->getAllCustomers($page);
                } else {

                    // resume/update sync
                    $customers = $trackingData->getUntrackedCustomers();
                }
            } else {

                $customers = $trackingData->getAllCustomers($page);

            }

            $mailerliteClient = new PlatformAPI(MAILERLITE_WP_API_KEY);

            $shop = get_option('woo_ml_shop_id', false);

            if ($shop === false && $mailerliteClient->getApiType() == ApiType::CURRENT) {

                return [
                    'error' => true,
                    'message' => 'Shop is not activated.'
                ];
            }

            if (empty($customers)) {

                WC()->session->set('untracked_customer_page', null);

                return [
                    'allDone' => true,
                    'completed' => 0,
                ];
            }

            $syncCount = 0;

            $syncCustomers = [];

            foreach ($customers as $customer) {

                //initialise empty sync array
                $syncCustomer = [];

                $wc_customer = new \WC_Customer($customer['user_id']);

                //set resource id
                $syncCustomer['resource_id'] = (string) $customer['user_id'];

                // if customer does not contain email key find email by wc_customer
                if (!isset($customer['email']) && !empty($wc_customer->get_billing_email())) {

                    $customerEmail = $wc_customer->get_billing_email();
                } else {
                    $customerEmail = $customer['email'];
                }

                // fetch customer by email (to get all customers having same email)
                $customerWithOrders = \WC_Data_Store::load('report-customers')->get_data([
                    'order_before' => null,
                    'order_after'  => null,
                    'searchby'     => 'email',
                    'search'       => $customerEmail,
                ])->data;

                // initialise values
                $syncCustomer['email'] = $customerEmail;
                $syncCustomer['create_subscriber'] = true;
                $syncCustomer['orders_count'] = 0;
                $syncCustomer['total_spent']  = 0;


                // loop through customers and get order details
                foreach ($customerWithOrders as $value) {
                    if (!isset($customer['email'])) {
                        $customer = $value;
                    }

                    // initialise $wc_customer to get details of registered customer (if exists)
                    if(empty($wc_customer->get_id())) {
                        $wc_customer = new \WC_Customer($value['user_id']);
                    }

                    // set last order date
                    if ( ! isset($customerWithOrders['last_order_date'])) {
                        $customerWithOrders['last_order_date'] = $value['date_last_order'];
                    }

                    // update last order date
                    if ($customerWithOrders['last_order_date'] < $value['date_last_order']) {
                        $customerWithOrders['last_order_date'] = $value['date_last_order'];
                    }

                    // set orders count and spend
                    $syncCustomer['orders_count'] += $value['orders_count'] ?? 0;
                    $syncCustomer['total_spent']  += $value['total_spend'] ?? 0;

                }


                // if customer is registered
                if (! empty($wc_customer->get_id())) {
                    // get subscriber fields from wc_customer
                    $syncCustomer['subscriber_fields'] = [
                        'name' => $wc_customer->get_first_name(),
                        'last_name' => $wc_customer->get_last_name(),
                        'company' => $wc_customer->get_billing_company(),
                        'city' => $wc_customer->get_billing_city(),
                        'state' => $wc_customer->get_billing_state(),
                        'country' => $wc_customer->get_billing_country(),
                        'phone' => $wc_customer->get_billing_phone()
                    ];

                } else {
                    $guestsSynced++;
                    // get guest checkout customer details
                    $syncCustomer['subscriber_fields'] = [
                        'name'      => $customer['name'],
                        'last_name' => $customer['last_name'] ?? '',
                        'company'   => $customer['company'] ?? '',
                        'city'      => $customer['city'],
                        'state'     => $customer['state'],
                        'country'   => $customer['country'],
                        'phone'     => $customer['phone'] ?? '',
                    ];
                }

                // classic ZIP field is different
                if ($mailerliteClient->getApiType() == ApiType::CURRENT) {
                    $syncCustomer['subscriber_fields']['z_i_p'] = $wc_customer->get_billing_postcode() ?? $customer['postcode'];
                } else {
                    $syncCustomer['subscriber_fields']['zip'] = $wc_customer->get_billing_postcode() ?? $customer['postcode'];
                }

                // get all orders of customer
                $orders = wc_get_orders([
                    'email' => $customerEmail,
                    'limit' => -1,
                ]);

                // initialise array for last order date and id
                $last_order = [];

                // loop through orders to get last order id and date
                foreach ($orders as $value) {
                    if ( ! isset($last_order['date'])) {
                        $last_order['date'] = $value->get_date_created()->date_i18n('Y-m-d H:i:s');
                        $last_order['id'] = $value->get_id();
                    }

                    if ($last_order['date'] < $value->get_date_created()->date_i18n('Y-m-d H:i:s')) {
                        $last_order['date'] = $value->get_date_created()->date_i18n('Y-m-d H:i:s');
                        $last_order['id'] = $value->get_id();
                    }
                }

                // set last order date and id
                $syncCustomer['last_order_id'] = $last_order['id'] ?? 0;
                $syncCustomer['last_order'] = $last_order['date'] ?? '';

                //update meta fields
                if ($wc_customer->meta_exists('_woo_ml_subscribe')) {
                    $syncCustomer['accepts_marketing'] = $wc_customer->get_meta('_woo_ml_subscribe');
                } else {
                    if ($last_order['id']) {
                        $syncCustomer['accepts_marketing'] = OrderProcess::getInstance()->orderCustomerSubscribe($last_order['id']);
                    }
                }

                // add customer to sync list
                $syncCustomers[] = $syncCustomer;

                if(!empty($wc_customer->get_id())) {
                    $wc_customer->add_meta_data('_woo_ml_customer_tracked', true, true);
                    $wc_customer->save_meta_data();
                }
            }

            if (count($syncCustomers) > 0) {

                if ($mailerliteClient->getApiType() == ApiType::CLASSIC) {

                    foreach ($syncCustomers as $syncCustomer) {

                        $subscriber_fields = $syncCustomer['subscriber_fields'];

                        $subscriber_fields['woo_orders_count'] = $syncCustomer['orders_count'];
                        $subscriber_fields['woo_total_spent'] = $syncCustomer['total_spent'];
                        $subscriber_fields['woo_last_order'] = $syncCustomer['last_order'];
                        $subscriber_fields['woo_last_order_id'] = $syncCustomer['last_order_id'];

                        $store = home_url();

                        $subscriber_updated = $mailerliteClient->syncCustomer($store, $syncCustomer['resource_id'],
                            $syncCustomer['email'], $subscriber_fields);

                        if (isset($subscriber_updated->updated_subscriber)) {
                            // used for updating order meta
                        }
                        if(!$guestsSynced) {
                            $guestsSynced = true;
                        }
                        update_option('woo_ml_guests_sync_count', $guestsSynced);

                        $syncCount++;
                    }
                }

                if ($mailerliteClient->getApiType() == ApiType::CURRENT) {

                    $result = $mailerliteClient->importCustomers($shop, $syncCustomers);

                    if ($mailerliteClient->responseCode() !== 200) {

                        $errorMsg = json_decode($mailerliteClient->getResponseBody());
                        $message = 'Oops, we did not manage to sync all of your customers, please try again. (' . $mailerliteClient->responseCode() . ')';

                        if ($mailerliteClient->responseCode() == 422 && isset($errorMsg->message)) {

                            $message = $errorMsg->message;
                        }

                        return [
                            'error' => true,
                            'code' => $mailerliteClient->responseCode(),
                            'message' => $message
                        ];
                    }

                    $syncCount += count($result);
                }
            }

            return [
                'completed' => $syncCount,
                'data' => $syncCustomers
            ];
        } catch (\Exception $e) {
            return [
                'error' => true,
                'message' => $message
            ];
        }
    }

    /**
     * Bulk synchronize untracked categories
     * woo_ml_sync_untracked_categories
     * @return array
     */
    public function syncUntrackedCategories()
    {

        set_time_limit(600);

        try {

            $syncCount = 0;

            $checkCategories = TrackingData::getInstance()->getUntrackedCategories();

            $mailerliteClient = new PlatformAPI(MAILERLITE_WP_API_KEY);

            if (is_array($checkCategories) && sizeof($checkCategories) > 0) {

                $shop = get_option('woo_ml_shop_id', false);

                if ($shop === false) {

                    return [
                        'error' => true,
                        'message' => 'Shop is not activated.'
                    ];
                }

                $importCategories = [];

                foreach ($checkCategories as $category) {

                    if (!isset($category->term_id)) {
                        continue;
                    }

                    $importCategories[] = [
                        'name' => $category->name,
                        'resource_id' => (string)$category->term_id
                    ];
                }

                if (count($importCategories) > 0) {

                    $result = $mailerliteClient->importCategories($shop, $importCategories);

                    if ($mailerliteClient->responseCode() !== 200) {

                        return [
                            'error' => true,
                        ];
                    }

                    foreach ($result as $category) {

                        MailerLiteSettings::getInstance()->completeCategoryTracking($category->resource_id,
                            $category->id);
                        $syncCount++;
                    }
                }
            }

            return [
                'completed' => $syncCount,
            ];
        } catch (\Exception $e) {

            return [
                'error' => true,
            ];
        }
    }

    /**
     * Call to handle product, order and customer delete event
     * mailerlite_wp_sync_post_delete
     */
    public function syncPostDelete($post_id)
    {

        $mailerliteClient = new PlatformAPI(MAILERLITE_WP_API_KEY);

        if ($mailerliteClient->getApiType() === ApiType::CURRENT) {

            $shop = get_option('woo_ml_shop_id', false);

            if ($shop === false) {

                return false;
            }

            if (get_post_type($post_id) === 'product') {
                $mailerliteClient->deleteProduct($shop, $post_id);
            }

            if (OrderUtil::get_order_type($post_id) === 'shop_order') {
                $mailerliteClient->deleteOrder($shop, $post_id);
            }
        }
    }
}