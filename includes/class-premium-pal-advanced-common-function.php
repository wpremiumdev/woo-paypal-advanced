<?php

function premium_pal_advanced_setting_field() {
    
    return array(
        'premium_enabled' => array(
            'title' => __('Enable/Disable', 'woo-paypal-advanced'),
            'label' => __('Enable PayPal Advanced', 'woo-paypal-advanced'),
            'type' => 'checkbox',
            'description' => '',
            'default' => 'no'
        ),
        'premium_title' => array(
            'title' => __('Title', 'woo-paypal-advanced'),
            'type' => 'text',
            'description' => __('This controls the title which the user sees during checkout.', 'woo-paypal-advanced'),
            'desc_tip' => true,
            'default' => __('PayPal Advanced', 'woo-paypal-advanced')
        ),
        'premium_description' => array(
            'title' => __('Description', 'woo-paypal-advanced'),
            'type' => 'textarea',
            'description' => __('This controls the description which the user sees during checkout.', 'woo-paypal-advanced'),
            'desc_tip' => true,
            'default' => __("Pay with your credit card via PayPal Website Payments Advanced.", 'woo-paypal-advanced')
        ),
        'premium_testmode' => array(
            'title' => __('Test Mode', 'woo-paypal-advanced'),
            'type' => 'checkbox',
            'default' => 'yes',
            'description' => __('Place the payment gateway in development mode.', 'woo-paypal-advanced'),
            'desc_tip' => true,
            'label' => __('The sandbox is PayPal\'s test environment and is only for use with sandbox accounts created within your <a href="http://developer.paypal.com" target="_blank">PayPal developer account</a>', 'woo-paypal-advanced')
        ),
        'premium_sandbox_merchant' => array(
            'title' => __('Merchant Login', 'woo-paypal-advanced'),
            'type' => 'text',
            'description' => __('Get your API credentials from PayPal.', 'woo-paypal-advanced'),
            'desc_tip' => true,
            'default' => ''
        ),
        'premium_sandbox_password' => array(
            'title' => __('Password', 'woo-paypal-advanced'),
            'type' => 'password',
            'description' => __('Get your API credentials from PayPal.', 'woo-paypal-advanced'),
            'desc_tip' => true,
            'default' => ''
        ),
        'premium_sandbox_user' => array(
            'title' => __('User (or Merchant Login if no designated user is set up for the account)', 'woo-paypal-advanced'),
            'type' => 'text',
            'description' => __('Get your API credentials from PayPal.', 'woo-paypal-advanced'),
            'desc_tip' => true,
            'default' => ''
        ),
        'premium_sandbox_partner' => array(
            'title' => __('PayPal Partner', 'woo-paypal-advanced'),
            'type' => 'text',
            'description' => __('Get your API credentials from PayPal.', 'woo-paypal-advanced'),
            'desc_tip' => true,
            'default' => ''
        ),
        'premium_live_merchant' => array(
            'title' => __('Merchant Login', 'woo-paypal-advanced'),
            'type' => 'text',
            'description' => __('Get your API credentials from PayPal.', 'woo-paypal-advanced'),
            'desc_tip' => true,
            'default' => ''
        ),
        'premium_live_password' => array(
            'title' => __('Password', 'woo-paypal-advanced'),
            'type' => 'password',
            'description' => __('Get your API credentials from PayPal.', 'woo-paypal-advanced'),
            'desc_tip' => true,
            'default' => ''
        ),
        'premium_live_user' => array(
            'title' => __('User (or Merchant Login if no designated user is set up for the account)', 'woo-paypal-advanced'),
            'type' => 'text',
            'description' => __('Get your API credentials from PayPal.', 'woo-paypal-advanced'),
            'desc_tip' => true,
            'default' => ''
        ),
        'premium_live_partner' => array(
            'title' => __('PayPal Partner', 'woo-paypal-advanced'),
            'type' => 'text',
            'description' => __('Get your API credentials from PayPal.', 'woo-paypal-advanced'),
            'desc_tip' => true,
            'default' => ''
        ),
        'premium_invoice_prefix' => array(
            'title' => __('Invoice ID Prefix', 'woo-paypal-advanced'),
            'type' => 'text',
            'description' => __('Add a prefix to the invoice ID sent to PayPal. This can resolve duplicate invoice problems when working with multiple websites on the same PayPal account.', 'woo-paypal-advanced'),
            'desc_tip' => true,
            'default' => ''
        ),
        'premium_action' => array(
            'title' => __('Payment Action', 'woo-paypal-advanced'),
            'type' => 'select',
            'description' => __('Choose whether you wish to capture funds immediately or authorize payment only.', 'woo-paypal-advanced'),
            'desc_tip' => true,
            'options' => array(
                'S' => __('Sale', 'woo-paypal-advanced'),
                'A' => __('Authorization', 'woo-paypal-advanced'),
            ),
        ),
        'premium_layout' => array(
            'title' => __('Layout', 'woo-paypal-advanced'),
            'type' => 'select',
            'description' => __('Layouts A and B redirect to PayPal\'s website for the user to pay. Layout C (recommended) is a secure PayPal-hosted page but is embedded on your site using an iFrame.', 'woo-paypal-advanced'),
            'desc_tip' => true,
            'options' => array(
                'A' => __('Layout A', 'woo-paypal-advanced'),
                'B' => __('Layout B', 'woo-paypal-advanced'),
                'C' => __('Layout C', 'woo-paypal-advanced'),
            ),
        ),
        'premium_mobile_mode' => array(
            'title' => __('Enable Mobile Mode', 'woo-paypal-advanced'),
            'type' => 'checkbox',
            'description' => __('Disable this option if your theme is not compatible with Mobile. Otherwise You would get Silent Post Error in Layout C.', 'woo-paypal-advanced'),
            'desc_tip' => true,
            'default' => 'no'
        ),
        'premium_page_collapse_bgcolor' => array(
            'title' => __('Page Collapse Border Color', 'woo-paypal-advanced'),
            'type' => 'text',
            'class' => 'premium_paypal_advanced_color',
            'default' => '#1e73be',
        ),
        'premium_page_collapse_textcolor' => array(
            'title' => __('Page Collapse Text Color', 'woo-paypal-advanced'),
            'type' => 'text',
            'class' => 'premium_paypal_advanced_color',
            'default' => '#8224e3',
        ),
        'premium_page_button_bgcolor' => array(
            'title' => __('Page Button Background Color', 'woo-paypal-advanced'),
            'type' => 'text',
            'class' => 'premium_paypal_advanced_color',
            'default' => '#81d742',
        ),
        'premium_page_button_textcolor' => array(
            'title' => __('Page Button Text Color', 'woo-paypal-advanced'),
            'type' => 'text',
            'class' => 'premium_paypal_advanced_color',
            'default' => '#eeee22',
        ),
        'premium_label_textcolor' => array(
            'title' => __('Label Text Color', 'woo-paypal-advanced'),
            'type' => 'text',
            'class' => 'premium_paypal_advanced_color',
            'default' => '#dd9933',
        ),
        'premium_debug_log' => array(
            'title' => __('Debug Log', 'woo-paypal-advanced'),
            'type' => 'checkbox',
            'description' => __('Enable Log Pal Pro', 'woo-paypal-advanced'),
            'desc_tip' => true,
            'default' => 'no',
            'label' => __('Enable logging <code>/wp-content/uploads/wc-logs/</code>', 'woo-paypal-advanced')
        )
    );
}

function premium_pal_advanced_get_user_ip() {
    
    return (isset($_SERVER['HTTP_X_FORWARD_FOR']) && !empty($_SERVER['HTTP_X_FORWARD_FOR'])) ? $_SERVER['HTTP_X_FORWARD_FOR'] : $_SERVER['REMOTE_ADDR'];
}

function premium_pal_advanced_item_name($item_name) {
    
    if (strlen($item_name) > 36) {
        $item_name = substr($item_name, 0, 33) . '...';
    }
    return html_entity_decode($item_name, ENT_NOQUOTES, 'UTF-8');
}

function premium_pal_advanced_item_desc($item_desc) {
    
    if (strlen($item_desc) > 127) {
        $item_desc = substr($item_desc, 0, 124) . '...';
    }
    return html_entity_decode($item_desc, ENT_NOQUOTES, 'UTF-8');
}

function premium_pal_advanced_request_string($paypal_args) {

    if (!is_array($paypal_args) && count($paypal_args) == 0) {
        return false;
    }
    $postData = "";
    foreach ($paypal_args as $key => $val) {
        $postData .='&' . $key . '=' . $val;
    }
    return trim($postData, '&');
}
