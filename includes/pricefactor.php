<?php

// Hook into admin_menu to add a submenu page under WooCommerce menu
add_action('admin_menu', 'woocommerce_custom_settings_page');

function woocommerce_custom_settings_page() {
    // Add submenu page under WooCommerce menu
    add_submenu_page(
        'wpi-product-importer',                      // Parent slug (WooCommerce menu)
        'Product Price Factor',             // Page title
        'Product Price Factor',             // Menu title
        'manage_options',                   // Capability
        'wpi-product-pricefactor',          // Menu slug
        'woocommerce_custom_settings_callback' // Callback function
    );
}
 


// Callback function for the settings page
function woocommerce_custom_settings_callback() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'product_price_factor';

    // Handle form submission
    if (isset($_POST['submit']) && check_admin_referer('save_price_factors')) {
        // Get the site name
        $site_name = get_bloginfo('name');

        // Prepare to insert or update records
        for ($i = 1; $i <= 5; $i++) {
            $rent_days = sanitize_text_field($_POST["rent_days_$i"]);
            $price_factor = sanitize_text_field($_POST["price_factor_$i"]);

            // Check if a record with the same site_name and rent_days already exists
            $existing_record = $wpdb->get_row($wpdb->prepare(
                "SELECT id FROM $table_name WHERE site_name = %s AND rent_days = %s",
                $site_name,
                $rent_days
            ));

            if ($existing_record) {
                // Update existing record
                $wpdb->update(
                    $table_name,
                    array('price_factor' => $price_factor),
                    array('site_name' => $site_name, 'rent_days' => $rent_days),
                    array('%s'),
                    array('%s', '%s')
                );
            } else {
                // Insert new record
                $wpdb->insert(
                    $table_name,
                    array('site_name' => $site_name, 'rent_days' => $rent_days, 'price_factor' => $price_factor),
                    array('%s', '%s', '%s')
                );
            }
        }

        // Show success message
        echo '<div class="updated"><p>Settings saved.</p></div>';
    }

    // Retrieve saved values
    $saved_values = $wpdb->get_results($wpdb->prepare(
        "SELECT rent_days, price_factor FROM $table_name WHERE site_name = %s",
        get_bloginfo('name')
    ), ARRAY_A);

    // Display the form
    ?>
    <div class="wrap">
        <h1><?php echo esc_html(get_admin_page_title()); ?></h1>
        <form method="post">
            <?php wp_nonce_field('save_price_factors'); ?>
            <table class="form-table">
                <tbody>
                    <?php
                    for ($i = 1; $i <= 5; $i++) {
                        $rent_days_value = '';
                        $price_factor_value = '';

                        // Find saved values for the current index
                        foreach ($saved_values as $record) {
                            if ($record['rent_days'] == $i) {
                                $rent_days_value = esc_attr($record['rent_days']);
                                $price_factor_value = esc_attr($record['price_factor']);
                                break;
                            }
                        }
                    ?>
                        <tr valign="top">
                            <th scope="row">Rent Days <?php echo $i; ?></th>
                            <td><input type="text" name="rent_days_<?php echo $i; ?>" value="<?php echo $rent_days_value; ?>" /></td>
                        </tr>
                        <tr valign="top">
                            <th scope="row">Price Factor <?php echo $i; ?></th>
                            <td><input type="text" name="price_factor_<?php echo $i; ?>" value="<?php echo $price_factor_value; ?>" /></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
            <?php submit_button('Save Settings'); ?>
        </form>
    </div>
    <?php
}
