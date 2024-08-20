<?php

add_action('woocommerce_after_order_notes', 'add_delivery_date_fields');

function add_delivery_date_fields($checkout) {
    echo '<div id="delivery_date_fields"><h2>' . __('Delivery Dates') . '</h2>';

    woocommerce_form_field('delivery_date_from', array(
        'type'          => 'date',
        'class'         => array('delivery_date_from form-row-wide'),
        'label'         => __('Delivery Date From'),
        'required'      => true,
    ), $checkout->get_value('delivery_date_from'));

    woocommerce_form_field('delivery_date_to', array(
        'type'          => 'date',
        'class'         => array('delivery_date_to form-row-wide'),
        'label'         => __('Delivery Date To'),
        'required'      => true,
    ), $checkout->get_value('delivery_date_to'));

    echo '</div>';
}



add_action('woocommerce_checkout_process', 'validate_delivery_date_fields');

function validate_delivery_date_fields() {
    if (empty($_POST['delivery_date_from']) || empty($_POST['delivery_date_to'])) {
        wc_add_notice(__('Please enter both delivery dates.'), 'error');
    }
}




add_action('wp_ajax_update_cart_fee', 'update_cart_fee');
add_action('wp_ajax_nopriv_update_cart_fee', 'update_cart_fee');

function update_cart_fee() {
    if (defined('DOING_AJAX') && DOING_AJAX && isset($_POST['day_diff'])) {
        $day_diff = intval($_POST['day_diff']);

        // Store day_diff in session
        //WC()->session->set('day_diff', $day_diff);
        
        // Store the value in a cookie or a transient
        setcookie('day_diff', $day_diff, time() + 3600, COOKIEPATH, COOKIE_DOMAIN, false, true);

        // Send response
        wp_send_json(array('status' => 'success', 'message' => WC()->session->set('day_diff', $day_diff) ));
    } else {
        wp_send_json(array('status' => 'error', 'message' => 'day_diff not set or not doing AJAX.'));
    }
}


add_action('woocommerce_cart_calculate_fees', 'calculate_cart_fee');
function calculate_cart_fee() {
    // Retrieve day_diff from cookie
    $day_diff = isset($_COOKIE['day_diff']) ? intval($_COOKIE['day_diff']) : 0;

    if ($day_diff > 0) {
        // If day_diff is greater than 5, consider the amount for 5
        if ($day_diff > 5) {
            $day_diff = 5;
        }

        global $wpdb;
        $table_name = $wpdb->prefix . 'product_price_factor';
        $price_factor = $wpdb->get_var($wpdb->prepare("SELECT price_factor FROM $table_name WHERE rent_days = %d", $day_diff));

        if ($price_factor) {
            // Add the fee to the cart
            $cartsub = WC()->cart->cart_contents_total;
            
            $pricefactorval = $cartsub * $price_factor;
            
            $additionalfeeforpricefactor = $pricefactorval - $cartsub;    

            WC()->cart->add_fee(__('Additional Fee', 'woocommerce'), $additionalfeeforpricefactor);
        }
    }
}
