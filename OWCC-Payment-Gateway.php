<?php
/*
 * Plugin Name: Official Wakanda Coin Payment Gateway for WooCommerce
 * Plugin URI: https://lapstacks.com
 * Description: Accept OWCC / BTC Payment.
 * Author: OWC Universal Pay, Inc.
 * Author URI: https://www.officialwakandacoin.com/
 * Version: 1.0.1
 */



defined('ABSPATH') or exit;

include_once('include/CurrencyConvert.php');

// Include our Gateway Class and register Payment Gateway with WooCommerce
add_action('plugins_loaded', 'pwan_OWCC_init', 0);

function pwan_OWCC_init() {
    // If the parent WC_Payment_Gateway class doesn't exist
    // it means WooCommerce is not installed on the site
    // so do nothing
    if (!class_exists('WC_Payment_Gateway'))
        return;

    // If we made it this far, then include our Gateway Class
    include_once( 'PWN_Wakanda.php' );

    // Now that we have successfully included our class,
    // Lets add it too WooCommerce
    add_filter('woocommerce_payment_gateways', 'pwn_OWCC_gateway');

    function pwn_OWCC_gateway($methods) {
        $methods[] = 'PWN_OWCC_gateway';
        return $methods;
    }

}

// Add custom action links
add_filter('plugin_action_links_' . plugin_basename(__FILE__), 'pwn_OWCC_action_links');

function pwn_OWCC_action_links($links) {
    $plugin_links = array(
        '<a href="' . admin_url('admin.php?page=wc-settings&tab=checkout') . '">' . __('Settings', 'pwn-OWCC-gateway') . '</a>',
    );

    // Merge our new link with the default ones
    return array_merge($plugin_links, $links);
}

if (!function_exists('owccpg_my_scripts_method')) {

    function owccpg_my_scripts_method() {
        wp_register_script('owcc_script', plugin_dir_url(__FILE__) . 'js/owccjs.js', array('jquery'), '1.0');
        wp_enqueue_script('owcc_script');
    }

}
add_action('wp_enqueue_scripts', 'owccpg_my_scripts_method');


// BACS payement gateway description: Append custom select field
add_filter('woocommerce_gateway_description', 'gateway_OWCC_custom_fields', 20, 2);

function gateway_OWCC_custom_fields($description, $payment_id) {
    $my_fields = get_option('woocommerce_pwn_OWCC_gateway_settings');


    if ('pwn_OWCC_gateway' === $payment_id) {

        __('<p style="padding:5px;border:1px solid #f2f2f2;">' . $my_fields['description'] . '</p>', 'pwn-OWCC-gateway');
    }
}

// add currency in woocommerce
add_filter('woocommerce_currencies', 'add_cw_currency');

function add_cw_currency($cw_currency) {
    $cw_currency['OWCC'] = esc_html(__('OWCC', 'woocommerce'));
    return $cw_currency;
}

add_filter('woocommerce_currency_symbol', 'add_cw_currency_symbol', 10, 2);

function add_cw_currency_symbol($custom_currency_symbol, $custom_currency) {
    switch ($custom_currency) {
        case 'OWCC': $custom_currency_symbol = 'OWCC &nbsp;';
            break;
    }
    return $custom_currency_symbol;
}

add_filter('woocommerce_endpoint_order-received_title', 'owccpg_webroom_change_thankyou_title');

if (!function_exists('owccpg_webroom_change_thankyou_title')) {

    function owccpg_webroom_change_thankyou_title($old_title) {

        return esc_html('We received your order! Thank you! Please make a payment and click on <b> "Verify Payment"</b>.');
    }

}

if (!function_exists('owccpg_my_enqueue')) {

    function owccpg_my_enqueue() {
        wp_enqueue_script('ajax-script', plugin_dir_url(__FILE__) . 'js/my-ajax-script.js', array('jquery'));
        wp_localize_script('ajax-script', 'my_ajax_object', array('ajax_url' => admin_url('admin-ajax.php')));
    }

}

add_action('wp_enqueue_scripts', 'owccpg_my_enqueue');

if (!function_exists('owccpg_getTransction')) {

    function owccpg_getTransction() {

        $hash = isset($_REQUEST['hash']) ? sanitize_key($_REQUEST['hash']) : '';
        $order_id = isset($_REQUEST['orderid']) ? sanitize_text_field($_REQUEST['orderid']) : 0;
        $curr = isset($_REQUEST['currency']) ? sanitize_text_field($_REQUEST['currency']) : '';
        $account = isset($_REQUEST['act']) ? sanitize_text_field($_REQUEST['act']) : '';

        $response = wp_remote_retrieve_body(wp_remote_get('https://horizon.stellar.org/accounts/' . $account . '/transactions?limit=3'));

        $rsp = json_decode($response);
        $i = 0;
        foreach ($rsp as $trans) {

            if ($trans->records[$i]->hash == $hash && $trans->records[$i]->successful == TRUE) {

                $order = new WC_Order($order_id);
                $order->update_status('Processing', 'Payment by' . $curr);
                $status = add_post_meta($order_id, 'Tracking transection ID', $hash);
                $arr = array(
                    'status' => 'success',
                    'message' => 'Transection id successful verify!.'
                );
                break;
            } else {

                $arr = array(
                    'status' => 'error',
                    'message' => 'Send transection id and order id by email'
                );
                continue;
            }
            $i++;
        }
        __(json_encode($arr), 'pwn-OWCC-gateway');

        die();
    }

}

add_action('wp_ajax_owccpg_getTransction', 'owccpg_getTransction');
add_action('wp_ajax_nopriv_owccpg_getTransction', 'owccpg_getTransction');





add_filter('woocommerce_thankyou_order_received_text', 'webroom_change_thankyou_sub_title', 20, 2);

function webroom_change_thankyou_sub_title() {
    $order_id = isset($_GET['sorted']) ? sanitize_text_field($_GET['sorted']) : 0;

    global $woocommerce;
    $order = new WC_Order($order_id);
    $total = $order->get_total();
    $obj = new CurrencyConvert();
    $cr = $obj->owccpg_stellarxlm();
    $json = json_decode($cr, TRUE);
    // <option value='owcc'  data='".$total."'>OWCC</option>

    $my_fields = get_option('woocommerce_pwn_OWCC_gateway_settings');
    $msg = '<p style="color:green">You have to pay exect payment from your wallet to verify payment. We are waiting your payment. Once you make a payment please click to "Verify Payment" </p><br /> <input class="linkToCopy"  type="text" value="' . $my_fields['apiLogin'] . '" style="width:80%;" readonly /> ';
    $msg .="<button class='copyButton'>Copy</button><div class='copied'></div><br /><br />";
    $msg .= '<img src="https://chart.googleapis.com/chart?chs=300x300&cht=qr&chl=' . $my_fields["apiLogin"] . '" title="Stellar vwallet" />';
    $msg .='<input type="hidden" value="' . $my_fields["apiLogin"] . '" class="act" id="act" />';
    $msg .="<b>Please select the coin you want to pay</b><br /><Select id='currency' class='currency '>
	            <option value='owc'> Wakanda Coin (OWCC)</option>
	            <option value='btc' data='" . $obj->owccpg_bitcon($total) . "'>Bitcoin</option>
	          
	            <option value='xlm'  data='" . $total * $json['XLM'] . "'>XLM</option>
	       </select>";
    $msg .= "<div class='owc'><h3>You have to pay </h3>
	        
	        <table>
	        <tr><th>Coin</th><td>Total</td></tr>
	        <tr><th>1 Wakanda Coin(OWCC) = $1 (USD)<br /> Wakanda Coin(OWCC) * $total </th><td>" . get_woocommerce_currency_symbol() . $total . "</td></tr>
	        <tr><th>Subtotal</th><td>" . $total . " Wakanda Coin (OWCC)</td></tr>
	        <tr><th>Net payable amount<br /><i>(Transfer fee not Included)</i></th><td>" . $total . " Wakanda Coin (OWCC)</td></tr></table>
	        </div>";

    $msg .= "<div class='btc'><h3>You have to pay </h3>
	        
	        <table>
	        <tr><th>Coin</th><td>Total</td></tr>
	        <tr><th>" . $obj->owccpg_bitrate() . "<br /></th><td>" . get_woocommerce_currency_symbol() . $total . "</td></tr>
	        <tr><th>Subtotal</th><td>" . $obj->owccpg_bitcon($total) . " Bitcoin</td></tr>
	        <tr><th>Net payable amount<br /><i>(Transfer fee not Included)</i></th><td>" . $obj->owccpg_bitcon($total) . " Bitcoin</td></tr></table>
	        </div>";

    $msg .= "<div class='owcc'><h3>You have to pay </h3>
	        
	        <table>
	        <tr><th>Coin</th><td>Total</td></tr>
	        <tr><th>1 OWCC = $1 (USD)<br /> OWCC * $total </th><td>" . get_woocommerce_currency_symbol() . $total . "</td></tr>
	        <tr><th>Subtotal</th><td>" . $total . " OWCC</td></tr>
	        <tr><th>Net payable amount<br /><i>(Transfer fee not Included)</i></th><td>" . $total . " OWCC</td></tr></table>
	        </div>";

    $msg .= "<div class='xlm'><h3>You have to pay </h3>
	        
	        <table>
	        <tr><th>Coin</th><td>Total</td></tr>
	        <tr><th>" . $json['XLM'] . " Stellar (XLM) = $1 (USD) </th><td>" . get_woocommerce_currency_symbol() . $total . "</td></tr>
	        <tr><th>Subtotal</th><td>" . $total * $json['XLM'] . " XLM</td></tr>
	        <tr><th>Net payable amount<br /><i>(Transfer fee not Included)</i></th><td>" . $total * $json['XLM'] . " XLM</td></tr></table>
	        </div>";
    ?>
    <img id="image" src="<?php __(plugin_dir_url(__FILE__) . 'images/loader.gif', 'pwn-OWCC-gateway'); ?>" style="display:none;">

    <?php
    $msg .= "<br /><br /><input type='text' id ='hash' data='" . $order_id . "' class='hash' name='transctionid' placeholder='Please enter here your transection ID' size='60' required /> <input type='submit' name='verify' class='verify'  value='Track my Payment' style='background:green;color:#fff;' />";
    $msg .="<div id='message'></div>";
    return $msg;
}

add_action('wp_enqueue_scripts', 'child_enqueue_styles');

function child_enqueue_styles() {

    wp_enqueue_style('owcc-style', plugin_dir_url(__FILE__) . 'css/owcc.css', array());
}
