<?php

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.0
 * @package    Pal_Pro
 * @subpackage Premium_Pal_Advanced_Gateway/includes
 * @author     wpremiumdev <wpremiumdev@gmail.com>
 */
class Premium_Pal_Advanced_Gateway extends WC_Payment_Gateway {

    public function __construct() {
        try {

            $this->id = 'pal_advanced';
            $this->icon = apply_filters('woocommerce_pal_advanced_icon', plugins_url('/images/cards.png', plugin_basename(dirname(__FILE__))));
            $this->has_fields = true;
            $this->method_title = __('PayPal Advanced', 'woo-paypal-advanced');
            $this->init_form_fields();
            $this->init_settings();

            $this->enabled = $this->get_option('premium_enabled') === "yes" ? true : false;
            $this->title = $this->get_option('premium_title');
            $this->description = $this->get_option('premium_description');
            $this->testmode = $this->get_option('premium_testmode') === "yes" ? true : false;
            $this->invoice_prefix = $this->get_option('premium_invoice_prefix');
            $this->paymentaction = $this->get_option('premium_action');
            $this->layout_action = $this->get_option('premium_layout');
            $this->mobilemode = ($this->get_option('premium_mobile_mode')) ? $this->get_option('premium_mobile_mode') : 'yes';
            $this->page_collapse_bgcolor = $this->get_option('premium_page_collapse_bgcolor');
            $this->page_collapse_textcolor = $this->get_option('premium_page_collapse_textcolor');
            $this->page_button_bgcolor = $this->get_option('premium_page_button_bgcolor');
            $this->page_button_textcolor = $this->get_option('premium_page_button_textcolor');
            $this->label_textcolor = $this->get_option('premium_label_textcolor');
            $this->debug = $this->get_option('premium_debug_log') === "yes" ? true : false;
            $this->layout = "TEMPLATEA";
            // Define Custom variables  
            $this->ButtonSource = 'mbjtechnolabs_SP';
            $this->secure_token_id = '';
            $this->securetoken = '';
            $this->post_data = array();
            $this->home_URL = is_ssl() ? home_url('/', 'https') : home_url('/');
            $this->return_URL = add_query_arg('wc-api', 'Premium_Pal_Advanced_Gateway', $this->home_URL);
            $this->cancel_URL = add_query_arg('wc-api', 'Premium_Pal_Advanced_Gateway', add_query_arg('cancel_ec_trans', 'true', $this->home_URL));
            $this->error_URL = add_query_arg('wc-api', 'Premium_Pal_Advanced_Gateway', add_query_arg('error', 'true', $this->home_URL));
            $this->silentpost_URL = add_query_arg('wc-api', 'Premium_Pal_Advanced_Gateway', add_query_arg('silent', 'true', $this->home_URL));

            if ($this->testmode) {
                $this->is_mode = 'TEST';
                $this->Paypal_URL = "https://pilot-payflowpro.paypal.com";
                $this->paypal_vendor = ($this->get_option('premium_sandbox_merchant')) ? trim($this->get_option('premium_sandbox_merchant')) : '';
                $this->paypal_password = ($this->get_option('premium_sandbox_password')) ? trim($this->get_option('premium_sandbox_password')) : '';
                $this->paypal_user = ($this->get_option('premium_sandbox_user')) ? trim($this->get_option('premium_sandbox_user')) : '';
                $this->paypal_partner = ($this->get_option('premium_sandbox_partner')) ? trim($this->get_option('premium_sandbox_partner')) : 'PayPal';
            } else {
                $this->is_mode = 'LIVE';
                $this->Paypal_URL = "https://payflowpro.paypal.com";
                $this->paypal_vendor = ($this->get_option('premium_live_merchant')) ? trim($this->get_option('premium_live_merchant')) : '';
                $this->paypal_password = ($this->get_option('premium_live_password')) ? trim($this->get_option('premium_live_password')) : '';
                $this->paypal_user = ($this->get_option('premium_live_user')) ? trim($this->get_option('premium_live_user')) : '';
                $this->paypal_partner = ($this->get_option('premium_live_partner')) ? trim($this->get_option('premium_live_partner')) : 'PayPal';
            }

            switch ($this->layout_action) {
                case 'A':
                    $this->layout = 'TEMPLATEA';                    
                    break;
                case 'B':
                    $this->layout = 'TEMPLATEB';                    
                    break;
                case 'C':
                    $this->layout = 'MINLAYOUT';                        
                    break;
            }
            
            if ( $this->layout == 'MINLAYOUT' && $this->mobilemode == "yes" ) {
                $this->template = wp_is_mobile() ? "MOBILE" : $this->layout;
            } else {
                $this->template = $this->layout;
            }
            // Hooks
            add_action('admin_notices', array($this, 'premium_pal_advanced_checks_field')); //checks for availability of the plugin
            add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
            add_action('woocommerce_receipt_pal_advanced', array($this, 'premium_pal_advanced_receipt_page'));
            add_action('woocommerce_api_premium_pal_advanced_gateway', array($this, 'premium_pal_advanced_relay_response'));
        } catch (Exception $ex) {
            wc_add_notice('<strong>' . __('Payment error', 'woo-paypal-advanced') . '</strong>: ' . $ex->getMessage(), 'error');
            return;
        }
    }

    public function init_form_fields() {
        return $this->form_fields = premium_pal_advanced_setting_field();
    }

    public function is_available() {
        if ($this->enabled) {
            if (!in_array(get_woocommerce_currency(), apply_filters('woocommerce_pal_advanced_allowed_currencies', array('USD', 'CAD')))) {
                return false;
            }
            if (!$this->paypal_user || !$this->paypal_vendor) {
                return false;
            }
            return true;
        } else {
            return false;
        }
    }

    public function admin_options() {
        ?>        
        <table class="form-table">
            <?php
            //if user's currency is USD
            if (!in_array(get_woocommerce_currency(), array('USD', 'CAD'))) {
                ?>
                <div class="inline error"><p><strong><?php _e('Gateway Disabled', 'woo-paypal-advanced'); ?></strong>: <?php _e('PayPal does not support your store currency.', 'woo-paypal-advanced'); ?></p></div>
                <?php
                return;
            } else {
                $this->generate_settings_html();
            }
            ?>
        </table><!--/.form-table-->
        <script type="text/javascript">
            jQuery('.premium_paypal_advanced_color').wpColorPicker();
            jQuery('#woocommerce_pal_advanced_premium_testmode').change(function () {
                var sandbox = jQuery('#woocommerce_pal_advanced_premium_sandbox_merchant, #woocommerce_pal_advanced_premium_sandbox_password, #woocommerce_pal_advanced_premium_sandbox_user, #woocommerce_pal_advanced_premium_sandbox_partner').closest('tr'),
                        production = jQuery('#woocommerce_pal_advanced_premium_live_merchant, #woocommerce_pal_advanced_premium_live_password, #woocommerce_pal_advanced_premium_live_user, #woocommerce_pal_advanced_premium_live_partner').closest('tr');
                if (jQuery(this).is(':checked')) {
                    sandbox.show();
                    production.hide();
                } else {
                    sandbox.hide();
                    production.show();
                }
            }).change();

        </script>
        <?php
    }

    public function payment_fields() {
        if ($this->description)
            echo wpautop(wptexturize($this->description));
    }

    public function process_refund($order_id, $amount = null, $reason = '') {

        $order = wc_get_order($order_id);

        if (!$order || !$order->get_transaction_id()) {
            return false;
        }
        if (!is_null($amount) && $order->get_total() > $amount) {
            return new WP_Error('pal-advanced-error', __('Partial refund is not supported', 'woo-paypal-advanced'));
        }
        //refund transaction, parameters
        $paypal_args = array(
            'USER' => $this->paypal_user,
            'VENDOR' => $this->paypal_vendor,
            'PARTNER' => $this->paypal_partner,
            'PWD[' . strlen($this->paypal_password) . ']' => $this->paypal_password,
            'ORIGID' => $order->get_transaction_id(),
            'TENDER' => 'C',
            'TRXTYPE' => 'C',
            'VERBOSITY' => 'HIGH'
        );
        
        $postData = premium_pal_advanced_request_string($paypal_args);

        // Using Curl post necessary information to the Paypal Site to generate the secured token 
        $response = wp_remote_post($this->Paypal_URL, array(
            'method' => 'POST',
            'body' => $postData,
            'timeout' => 70,
            'user-agent' => 'Woocommerce ' . WC_VERSION,
            'httpversion' => '1.1',
            'headers' => array('host' => 'www.paypal.com')
        ));

        if (is_wp_error($response))
            throw new Exception(__('There was a problem connecting to the payment gateway.', 'wc_paypaladv'));

        if (empty($response['body']))
            throw new Exception(__('Empty response.', 'wc_paypaladv'));
        
        $parsed_response = array();
        parse_str($response['body'], $parsed_response);      
        
        $this->premium_pal_advanced_log_write('Response of the refund transaction: ', print_r($parsed_response, true));
        
        if ($parsed_response['RESULT'] == 0) {
            $order->add_order_note(sprintf(__('Successfully Refunded - Refund Transaction ID: %s', 'woo-paypal-advanced'), $parsed_response['PNREF']));
        } else {
            $order->add_order_note(sprintf(__('Refund Failed - Refund Transaction ID: %s, Error Msg: %s', 'woo-paypal-advanced'), $parsed_response['PNREF'], $parsed_response['RESPMSG']));
            throw new Exception(sprintf(__('Refund Failed - Refund Transaction ID: %s, Error Msg: %s', 'woo-paypal-advanced'), $parsed_response['PNREF'], $parsed_response['RESPMSG']));
            return false;
        }
        return true;
    }

    public function process_payment($order_id) {
        try {
            $order = new WC_Order($order_id);
            $this->securetoken = $this->premium_pal_advanced_token($order);
            if ($this->securetoken != "") {

                update_post_meta($order->id, '_secure_token_id', $this->secure_token_id);
                update_post_meta($order->id, '_secure_token', $this->securetoken);
                $this->premium_pal_advanced_log_write('Secured Token generated successfully for the order ', $order->get_order_number());

                return array(
                    'result' => 'success',
                    'redirect' => $order->get_checkout_payment_url(true)
                );
            }
        } catch (Exception $e) {
            wc_add_notice(__('Error:', 'woo-paypal-advanced') . ' "' . $e->getMessage() . '"', 'error');
            $this->premium_pal_advanced_log_write($text = null, 'Error Occurred while processing the order ' . $order_id);
        }
    }

    public function premium_pal_advanced_token($order) {
        try {
            static $length_error = 0;
            $this->secure_token_id = uniqid(substr($_SERVER['HTTP_HOST'], 0, 9), true);
            $this->post_data['VERBOSITY'] = 'HIGH';
            $this->post_data['USER'] = $this->paypal_user;
            $this->post_data['VENDOR'] = $this->paypal_vendor;
            $this->post_data['PARTNER'] = $this->paypal_partner;
            $this->post_data['PWD['.strlen($this->paypal_password).']'] = $this->paypal_password;            
            $this->post_data['SECURETOKENID'] = $this->secure_token_id;
            $this->post_data['CREATESECURETOKEN'] = 'Y';
            $this->post_data['TRXTYPE'] = $this->paymentaction;
            $this->post_data['CUSTREF'] = $order->get_order_number();
            $this->post_data['USER1'] = $order->id;
            $this->post_data['INVNUM'] = $this->invoice_prefix . ltrim($order->get_order_number(), '#');
            $this->post_data['AMT'] = $order->get_total();
            $this->post_data['FREIGHTAMT'] = number_format($order->get_total_shipping(), 2, '.', '');
            $this->post_data['COMPANYNAME[' . strlen($order->billing_company) . ']'] = $order->billing_company;
            $this->post_data['CURRENCY'] = get_woocommerce_currency();
            $this->post_data['EMAIL'] = $order->billing_email;
            $this->post_data['BILLTOFIRSTNAME[' . strlen($order->billing_first_name) . ']'] = $order->billing_first_name;
            $this->post_data['BILLTOLASTNAME[' . strlen($order->billing_last_name) . ']'] = $order->billing_last_name;
            $this->post_data['BILLTOSTREET[' . strlen($order->billing_address_1 . ' ' . $order->billing_address_2) . ']'] = $order->billing_address_1 . ' ' . $order->billing_address_2;
            $this->post_data['BILLTOCITY[' . strlen($order->billing_city) . ']'] = $order->billing_city;
            $this->post_data['BILLTOSTATE[' . strlen($order->billing_state) . ']'] = $order->billing_state;
            $this->post_data['BILLTOZIP[' . strlen($order->billing_postcode) . ']'] = $order->billing_postcode;
            $this->post_data['BILLTOCOUNTRY[' . strlen($order->billing_country) . ']'] = $order->billing_country;
            $this->post_data['BILLTOEMAIL'] = $order->billing_email;
            $this->post_data['BILLTOPHONENUM'] = $order->billing_phone;
            $this->post_data['SHIPTOFIRSTNAME[' . strlen($order->shipping_first_name) . ']'] = $order->shipping_first_name;
            $this->post_data['SHIPTOLASTNAME[' . strlen($order->shipping_last_name) . ']'] = $order->shipping_last_name;
            $this->post_data['SHIPTOSTREET[' . strlen($order->shipping_address_1 . ' ' . $order->shipping_address_2) . ']'] = $order->shipping_address_1 . ' ' . $order->shipping_address_2;
            $this->post_data['SHIPTOCITY[' . strlen($order->shipping_city) . ']'] = $order->shipping_city;
            $this->post_data['SHIPTOZIP'] = $order->shipping_postcode;
            $this->post_data['SHIPTOCOUNTRY[' . strlen($order->shipping_country) . ']'] = $order->shipping_country;
            $this->post_data['BUTTONSOURCE'] = $this->ButtonSource;
            $this->post_data['RETURNURL[' . strlen($this->return_URL) . ']'] = $this->return_URL;
            $this->post_data['URLMETHOD'] = 'POST';
            $this->post_data['TEMPLATE'] = $this->template;
            $this->post_data['PAGECOLLAPSEBGCOLOR'] = ltrim($this->page_collapse_bgcolor, '#');
            $this->post_data['PAGECOLLAPSETEXTCOLOR'] = ltrim($this->page_collapse_textcolor, '#');
            $this->post_data['PAGEBUTTONBGCOLOR'] = ltrim($this->page_button_bgcolor, '#');
            $this->post_data['PAGEBUTTONTEXTCOLOR'] = ltrim($this->page_button_textcolor, '#');
            $this->post_data['LABELTEXTCOLOR'] = ltrim($this->label_textcolor, '#');
            $shiptostate = empty($order->shipping_state) ? $order->shipping_city : $order->shipping_state;
            $this->post_data['SHIPTOSTATE[' . strlen($shiptostate) . ']'] = $shiptostate;
            $this->post_data['CANCELURL[' . strlen($this->cancel_URL) . ']'] = $this->cancel_URL;
            $this->post_data['ERRORURL[' . strlen($this->error_URL) . ']'] = $this->error_URL;
            $this->post_data['SILENTPOSTURL[' . strlen($this->silentpost_URL) . ']'] = $this->silentpost_URL;

            // If prices include tax or have order discounts.
            if ($order->prices_include_tax == 'yes' || $order->get_total_discount() > 0 || $length_error > 1) {
                $this->premium_pal_advanced_include_tax_or_discount($length_error, $order);
            } else {
                $this->premium_pal_advanced_no_include_tax_or_discount($length_error, $order);
            }

            return $this->premium_pal_advanced_wp_remote_post($length_error, $order);
        } catch (Exception $e) {
            
        }
    }

    public function premium_pal_advanced_wp_remote_post($length_error, $order) {
        try {

            $postData = premium_pal_advanced_request_string($this->post_data);       
            $this->premium_pal_advanced_log_write('POST_REQUEST_PARAMETER: ', $this->post_data);
            $response = wp_remote_post($this->Paypal_URL, array(
                'method' => 'POST',
                'body' => $postData,
                'timeout' => 70,
                'user-agent' => 'Woocommerce ' . WC_VERSION,
                'httpversion' => '1.1',
                'headers' => array('host' => 'www.paypal.com')
            ));
            if (is_wp_error($response)) {
                $this->premium_pal_advanced_log_write('Error ', $response->get_error_message());
                throw new Exception($response->get_error_message());
            }
            if (empty($response['body'])) {
                $this->premium_pal_advanced_log_write('Empty response! ', $response->get_error_message());
                throw new Exception(__('Empty response!', 'woo-paypal-advanced'));
            }
            /* Parse and assign to array */
            parse_str($response['body'], $parsed_response);

            // Handle response
            if ($parsed_response['RESULT'] > 0) {
                $this->premium_pal_advanced_log_write('Error ', $parsed_response['RESPMSG']);
                throw new Exception(__('There was an error processing your order - ' . $parsed_response['RESPMSG'], 'woo-paypal-advanced'));
            } else {
                return $parsed_response['SECURETOKEN'];
            }
        } catch (Exception $e) {
            $this->premium_pal_advanced_log_write('Secured Token generation failed for the order ' . $order->get_order_number(), 'with error: ' . $e->getMessage());
            if ($parsed_response['RESULT'] != 7) {
                wc_add_notice(__('Error:', 'woo-paypal-advanced') . ' "' . $e->getMessage() . '"', 'error');
                $length_error = 0;
                return;
            } else {
                $this->premium_pal_advanced_log_write('Secured Token generation failed for the order ' . $order->get_order_number(), 'with error: ' . $e->getMessage());
                $length_error++;
                return $this->premium_pal_advanced_token($order);
            }
        }
    }

    public function premium_pal_advanced_include_tax_or_discount($length_error, $order) {
        try {

            $item_names = array();
            if (sizeof($order->get_items()) > 0) {

                $this->post_data['FREIGHTAMT'] = number_format($order->get_total_shipping() + $order->get_shipping_tax(), 2, '.', '');

                if ($length_error <= 1) {
                    foreach ($order->get_items() as $item)
                        if ($item['qty'])
                            $item_names[] = $item['name'] . ' x ' . $item['qty'];
                } else {
                    $item_names[] = "All selected items, refer to Woocommerce order details";
                }
                $items_string = sprintf(__('Order %s', 'woo-paypal-advanced'), $order->get_order_number()) . " - " . implode(', ', $item_names);
                $items_names_string = premium_pal_advanced_item_name($items_string);
                $items_desc_string = premium_pal_advanced_item_desc($items_string);
                $this->post_data['L_NAME0[' . strlen($items_names_str) . ']'] = $items_names_string;
                $this->post_data['L_DESC0[' . strlen($items_desc_str) . ']'] = $items_desc_string;
                $this->post_data['L_QTY0'] = 1;
                $this->post_data['L_COST0'] = number_format($order->get_total() - round($order->get_total_shipping() + $order->get_shipping_tax(), 2), 2, '.', '');

                $this->post_data['ITEMAMT'] = $this->post_data['L_COST0'] * $this->post_data['L_QTY0'];
            }
            return TRUE;
        } catch (Exception $e) {
            
        }
    }

    public function premium_pal_advanced_no_include_tax_or_discount($length_error, $order) {
        try {

            $this->post_data['TAXAMT'] = $order->get_total_tax();
            $this->post_data['ITEMAMT'] = 0;
            $item_loop = 0;
            if (sizeof($order->get_items()) > 0) {
                foreach ($order->get_items() as $item) {
                    if ($item['qty']) {
                        $product = $order->get_product_from_item($item);
                        $item_name = $item['name'];

                        $item_meta = new WC_order_item_meta($item['item_meta']);
                        if ($length_error == 0 && $meta = $item_meta->display(true, true)) {
                            $item_name .= ' (' . $meta . ')';
                            $item_name = premium_pal_advanced_item_name($item_name);
                        }

                        $this->post_data['L_NAME' . $item_loop . '[' . strlen($item_name) . ']'] = $item_name;

                        if ($product->get_sku())
                            $this->post_data['L_SKU' . $item_loop] = $product->get_sku();

                        $this->post_data['L_QTY' . $item_loop] = $item['qty'];
                        $this->post_data['L_COST' . $item_loop] = $order->get_item_total($item, false, false);
                        $this->post_data['L_TAXAMT' . $item_loop] = $order->get_item_tax($item, false);
                        $this->post_data['ITEMAMT'] += $order->get_line_total($item, false, false);

                        $item_loop++;
                    }
                }
            }

            return TRUE;
        } catch (Exception $e) {
            
        }
    }

    public function premium_pal_advanced_checks_field() {
        if (!$this->premium_pal_advanced_is_valid_currency() || $this->enabled == false) {
            return;
        }
        if (!$this->paypal_vendor) {
            echo '<div class="inline error"><p>' . sprintf(__('Paypal Advanced error: Please enter your PayPal Advanced Account Merchant Login.', 'woo-paypal-advanced')) . '</p></div>';
        } elseif (!$this->paypal_partner) {
            echo '<div class="inline error"><p>' . sprintf(__('Paypal Advanced error: Please enter your PayPal Advanced Account Partner.', 'woo-paypal-advanced')) . '</p></div>';
        } elseif (!$this->paypal_password) {
            echo '<div class="inline error"><p>' . sprintf(__('Paypal Advanced error: Please enter your PayPal Advanced Account Password.', 'woo-paypal-advanced')) . '</p></div>';
        }
    }

    public function premium_pal_advanced_is_valid_currency() {
        return in_array(get_woocommerce_currency(), apply_filters('woocommerce_pal_advanced_supported_currencies', array('USD', 'CAD')));
    }

    public function premium_pal_advanced_redirect_to($redirect_url) {

        @ob_clean();
        header('HTTP/1.1 200 OK');
        if ($this->layout != 'MINLAYOUT') {
            wp_redirect($redirect_url);
        } else {
            echo "<script>window.parent.location.href='" . $redirect_url . "';</script>";
        }
        exit;
    }

    public function premium_pal_advanced_log_write($text = null, $message) {
        if ($this->debug) {
            if (!isset($this->log) || empty($this->log)) {
                $this->log = new WC_Logger();
            }

            if (is_array($message) && count($message) > 0) {
                $message = $this->premium_pal_advanced_personal_detail_square($message);
            }
            $this->log->add('pal_advanced', $text . ' ' . print_r($message, true));
        }
    }

    public function premium_pal_advanced_personal_detail_square($message) {

        foreach ($message as $key => $value) {
            if ($key == "USER" || $key == "VENDOR" || $key == "PARTNER" || $key == "PWD[".  strlen($this->paypal_password)."]" || $key == "ACCT" || $key == "PROCCVV2" || $key == "ACCT" || $key == "EXPDATE" || $key == "CVV2") {
                $str_length = strlen($value);
                $ponter_data = "";
                for ($i = 0; $i <= $str_length; $i++) {
                    $ponter_data .= '*';
                }
                $message[$key] = $ponter_data;
            }
        }

        return $message;
    }

    public function premium_pal_advanced_receipt_page($order_id) {

        $order = new WC_Order($order_id);

        $this->secure_token_id = get_post_meta($order->id, '_secure_token_id', true);
        $this->securetoken = get_post_meta($order->id, '_secure_token', true);
        $this->premium_pal_advanced_log_write('Browser Info: ', $_SERVER['HTTP_USER_AGENT']);

        //display the form in IFRAME, if it is layout C, otherwise redirect to paypal site
        if ($this->layout == 'MINLAYOUT' || $this->layout == 'C') {
            $location = 'https://payflowlink.paypal.com?mode=' . $this->is_mode . '&amp;SECURETOKEN=' . $this->securetoken . '&amp;SECURETOKENID=' . $this->secure_token_id;
            $this->premium_pal_advanced_log_write('Show payment form(IFRAME) for the order ', $order->get_order_number() . ' as it is configured to use Layout C');
            ?>
            <iframe id="paypal_for_woocommerce_iframe" src="<?php echo $location; ?>" width="550" height="565" scrolling="no" frameborder="0" border="0" allowtransparency="true"></iframe>
            <?php
        } else {
            $location = 'https://payflowlink.paypal.com?mode=' . $this->is_mode . '&SECURETOKEN=' . $this->securetoken . '&SECURETOKENID=' . $this->secure_token_id;
            $this->premium_pal_advanced_log_write('Show payment form redirecting to ', $location . ' for the order ' . $order->get_order_number() . ' as it is not configured to use Layout C');
            wp_redirect($location);
            exit;
        }
    }

    public function premium_pal_advanced_relay_response() {

        //define a variable to indicate whether it is a silent post or return
        if (isset($_REQUEST['silent']) && $_REQUEST['silent'] == 'true')
            $silent_post = true;
        else
            $silent_post = false;

        if ($silent_post === true)
            $this->premium_pal_advanced_log_write('Silent Relay Response Triggered: ', print_r($_REQUEST, true));
        else
            $this->premium_pal_advanced_log_write('Relay Response Triggered: ', print_r($_REQUEST, true));

        if (!isset($_REQUEST['INVOICE'])) {
            if ($silent_post === false)
                wp_redirect(home_url('/'));
            exit;
        }

        $order_id = $_REQUEST['USER1'];
        $order = new WC_Order($order_id);

        $status = isset($order->status) ? $order->status : $order->get_status();
        if ($status == 'processing' || $status == 'completed') {
            $this->premium_pal_advanced_log_write('Redirecting to Thank You Page for order ', $order->get_order_number());
            if ($silent_post === false)
                $this->premium_pal_advanced_redirect_to($this->get_return_url($order));
        }

        if (isset($_REQUEST['cancel_ec_trans']) && $_REQUEST['cancel_ec_trans'] == 'true')
            $_REQUEST['RESULT'] = -1;

        switch ($_REQUEST['RESULT']) {
            case 0 :
                if ($_REQUEST['RESPMSG'] == 'Approved')
                    $this->premium_pal_advanced_success_handler($order, $order_id, $silent_post);
                else if ($_REQUEST['RESPMSG'] == 'Declined')
                    $this->premium_pal_advanced_decline_handler($order, $order_id, $silent_post);
                else
                    $this->premium_pal_advanced_error_handler($order, $order_id, $silent_post);
                break;
            case 12:
                $this->premium_pal_advanced_decline_handler($order, $order_id, $silent_post);
                break;
            case -1:
                $this->premium_pal_advanced_cancel_handler($order, $order_id);
                break;
            default:
                $this->premium_pal_advanced_error_handler($order, $order_id, $silent_post);
                break;
        }
    }

    public function premium_pal_advanced_success_handler($order, $order_id, $silent_post) {

        if (get_post_meta($order_id, '_secure_token', true) == $_REQUEST['SECURETOKEN']) {
            $this->premium_pal_advanced_log_write('', 'Relay Response Tokens Match');
        } else {
            $this->premium_pal_advanced_log_write('', 'Relay Response Tokens Mismatch');
            if ($silent_post === false)
                $this->premium_pal_advanced_redirect_to($order->get_checkout_payment_url(true));
            exit;
        }
        $order->add_order_note(sprintf(__('PayPal Advanced payment completed (Order: %s). Transaction number/ID: %s.', 'woo-paypal-advanced'), $order->get_order_number(), $_POST['PNREF']));
        $result = $this->premium_pal_advanced_inquiry_transaction($order, $order_id);
        if ($result == 'Approved') {
            $order->payment_complete($_POST['PNREF']);
            WC()->cart->empty_cart();
            $order->add_order_note(sprintf(__('Payment completed for the  (Order: %s)', 'woo-paypal-advanced'), $order->get_order_number()));
            $this->premium_pal_advanced_log_write('Payment completed for the   ', '(Order: ' . $order->get_order_number() . ')');
            if ($silent_post === false) {
                $this->premium_pal_advanced_redirect_to($this->get_return_url($order));
            }
        }
    }

    public function premium_pal_advanced_error_handler($order, $order_id, $silent_post) {

        wc_clear_notices();
        wc_add_notice(__('Error:', 'woo-paypal-advanced') . ' "' . urldecode($_POST['RESPMSG']) . '"', 'error');

        if ($silent_post === false)
            $this->premium_pal_advanced_redirect_to($order->get_checkout_payment_url(true));
    }

    public function premium_pal_advanced_cancel_handler($order, $order_id) {
        wp_redirect($order->get_cancel_order_url());
        exit;
    }

    public function premium_pal_advanced_decline_handler($order, $order_id, $silent_post) {


        $order->update_status('failed', __('Payment failed via PayPal Advanced because of.', 'woo-paypal-advanced') . '&nbsp;' . $_POST['RESPMSG']);
        $this->premium_pal_advanced_log_write('Status has been changed to failed for order ', $response->get_order_number());
        $this->premium_pal_advanced_log_write('Error Occurred while processing ', $response->get_order_number() . ' : ' . urldecode($_POST['RESPMSG']) . ', status:' . $_POST['RESULT']);
        $this->premium_pal_advanced_error_handler($order, $order_id, $silent_post);
    }

    public function premium_pal_advanced_inquiry_transaction($order, $order_id) {

        //inquire transaction, whether it is really paid or not
        $paypal_args = array(
            'USER' => $this->paypal_user,
            'VENDOR' => $this->paypal_vendor,
            'PARTNER' => $this->paypal_partner,
            'PWD[' . strlen($this->paypal_password) . ']' => $this->paypal_password,
            'ORIGID' => $_POST['PNREF'],
            'TENDER' => 'C',
            'TRXTYPE' => 'I',
            'BUTTONSOURCE' => $this->ButtonSource
        );

        $postData = premium_pal_advanced_request_string($paypal_args);

        /* Using Curl post necessary information to the Paypal Site to generate the secured token */
        $response = wp_remote_post($this->Paypal_URL, array(
            'method' => 'POST',
            'body' => $postData,
            'timeout' => 70,
            'user-agent' => 'Woocommerce ' . WC_VERSION,
            'httpversion' => '1.1',
            'headers' => array('host' => 'www.paypal.com')
        ));
        if (is_wp_error($response)) {
            $this->premium_pal_advanced_log_write('Error ', $response->get_error_message());
            throw new Exception(__('There was a problem connecting to the payment gateway.', 'woo-paypal-advanced'));
        }
        if (empty($response['body'])) {
            $this->premium_pal_advanced_log_write('Empty response! ', $response->get_error_message());
            throw new Exception(__('Empty response.', 'woo-paypal-advanced'));
        }

        $parsed_response = array(); //stores the response in array format
        parse_str($response['body'], $parsed_response);

        if ($parsed_response['RESULT'] == 0 && $parsed_response['RESPMSG'] == 'Approved') {
            $order->add_order_note(sprintf(__('Received result of Inquiry Transaction for the  (Order: %s) and is successful', 'woo-paypal-advanced'), $order->get_order_number()));
            return 'Approved';
        } else {
            $order->add_order_note(sprintf(__('Received result of Inquiry Transaction for the  (Order: %s) and with error:%s', 'woo-paypal-advanced'), $order->get_order_number(), $parsed_response['RESPMSG']));
            return 'Error';
        }
    }

}
