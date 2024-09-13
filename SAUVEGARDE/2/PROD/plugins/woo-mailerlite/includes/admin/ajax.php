<?php

use MailerLite\Includes\Classes\Process\CartProcess;
use MailerLite\Includes\Classes\Settings\ApiSettings;
use MailerLite\Includes\Classes\Settings\MailerLiteSettings;
use MailerLite\Includes\Classes\Settings\ResetSettings;
use MailerLite\Includes\Classes\Settings\SynchronizeSettings;

/**
 * Ajax
 */

// Exit if accessed directly
if ( ! defined('ABSPATH')) {
    exit;
}

/**
 * Refresh groups
 */
function woo_ml_admin_ajax_refresh_groups()
{
    if (defined('DOING_AJAX') && DOING_AJAX) {
        $mailerLiteSettings = MailerLiteSettings::getInstance();
        wp_send_json([
            'groups'  => $mailerLiteSettings->getGroupOptions(),
            'current' => $mailerLiteSettings->getMlOption('group')
        ]);
    }
}

add_action('wp_ajax_nopriv_post_woo_ml_refresh_groups', 'woo_ml_admin_ajax_refresh_groups');
add_action('wp_ajax_post_woo_ml_refresh_groups', 'woo_ml_admin_ajax_refresh_groups');

/**
 * Sync Customers
 */
function woo_ml_admin_ajax_sync_untracked_resources()
{

    if (defined('DOING_AJAX') && DOING_AJAX) {

        $response = false;

        try {
            $shop_synced = SynchronizeSettings::getInstance()->syncUntrackedResources();
            if (is_bool($shop_synced)) {
                $response = true;
            } else {
                $response = $shop_synced;
            }

            echo $response;
        } catch (\Exception $e) {
            return true;
        }

    }
    exit;
}

add_action('wp_ajax_post_woo_ml_sync_untracked_resources', 'woo_ml_admin_ajax_sync_untracked_resources');

/**
 * Is called when the user presses the Reset resources sync button in the plugin admin settings
 */
function woo_ml_reset_resources_sync()
{

    ResetSettings::getInstance()->resetTrackedResources();
}

add_action('wp_ajax_post_woo_ml_reset_resources_sync', 'woo_ml_reset_resources_sync');

function woo_ml_email_cookie()
{

    if (defined('DOING_AJAX') && DOING_AJAX) {
        try {
            $email     = $_POST['email'] ?? null;
            $subscribe = isset($_POST['signup']) ? ('true' == $_POST['signup']) : null;
            $language  = $_POST['language'] ?? '';

            $subscriber_fields = [];

            $first_name = $_POST['first_name'] ?? '';
            $last_name  = $_POST['last_name'] ?? '';

            if ( ! empty($first_name)) {
                $subscriber_fields['name'] = $first_name;
            }

            if ( ! empty($last_name)) {
                $subscriber_fields['last_name'] = $last_name;
            }

            if (filter_var($email, FILTER_VALIDATE_EMAIL)) {

                //setting email cookie and cart token for two days
                @setcookie('mailerlite_checkout_email', $email, time() + 172800, '/');
                if ( ! isset($_COOKIE['mailerlite_checkout_token'])) {
                    @setcookie('mailerlite_checkout_token', md5(uniqid(rand(), true)), time() + 172800, '/');
                }

                if (get_option('mailerlite_disable_checkout_sync') == false) {
                    CartProcess::getInstance()->sendCart($email, $subscribe, $language, $subscriber_fields);
                }
            }
        } catch (\Exception $e) {
            return true;
        }
    }
    exit;
}

add_action('wp_ajax_nopriv_post_woo_ml_email_cookie', 'woo_ml_email_cookie');
add_action('wp_ajax_post_woo_ml_email_cookie', 'woo_ml_email_cookie');

function woo_ml_validate_key()
{
    if (defined('DOING_AJAX') && DOING_AJAX) {
        if ( ! empty($_POST['key'])) {
            ApiSettings::getInstance()->validateApiKey($_POST['key']);
        }
    }
    exit;
}

add_action('wp_ajax_nopriv_post_woo_ml_validate_key', 'woo_ml_validate_key');
add_action('wp_ajax_post_woo_ml_validate_key', 'woo_ml_validate_key');

/**
 * Update hide checkbox setting
 */
function woo_ml_admin_ajax_update_hide_checkbox()
{

    if ( ! is_admin()) {
        die();
    }

    if (defined('DOING_AJAX') && DOING_AJAX) {

        if (isset($_REQUEST['_wpnonce']) && wp_verify_nonce($_REQUEST['_wpnonce'], 'ml-block-settings-update')) {

            if (isset($_POST['hidden'])) {

                $isHidden = filter_var($_POST['hidden'], FILTER_VALIDATE_BOOLEAN);

                MailerLiteSettings::getInstance()->setOption('checkout_hide', $isHidden ? 'yes' : 'no');

                wp_send_json_success([
                    'hidden' => $isHidden,
                ], 200);
            }
        }

        die();
    }
}

add_action('wp_ajax_woo_ml_admin_ajax_update_hide_checkbox', 'woo_ml_admin_ajax_update_hide_checkbox');

/**
 * Update preselect checkbox setting
 */
function woo_ml_admin_ajax_update_preselect_checkbox()
{

    if ( ! is_admin()) {
        die();
    }

    if (defined('DOING_AJAX') && DOING_AJAX) {

        if (isset($_REQUEST['_wpnonce']) && wp_verify_nonce($_REQUEST['_wpnonce'], 'ml-block-settings-update')) {

            if (isset($_POST['preselect'])) {

                $isPreselect = filter_var($_POST['preselect'], FILTER_VALIDATE_BOOLEAN);

                MailerLiteSettings::getInstance()->setOption('checkout_preselect', $isPreselect ? 'yes' : 'no');

                wp_send_json_success([
                    'preselect' => $isPreselect,
                ], 200);
            }
        }

        die();
    }
}

add_action('wp_ajax_woo_ml_admin_ajax_update_preselect_checkbox', 'woo_ml_admin_ajax_update_preselect_checkbox');