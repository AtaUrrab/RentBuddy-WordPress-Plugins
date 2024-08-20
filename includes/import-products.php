<?php

// Add sync button
add_action('admin_menu', 'wpi_add_sync_button');
function wpi_add_sync_button() {
    add_submenu_page(
        'wpi-product-importer', 
        'Sync Products', 
        'Sync Products', 
        'manage_options', 
        'wpi-sync-products', 
        'wpi_sync_products_page'
    );
}

// Sync products page content
function wpi_sync_products_page() {
    if (isset($_POST['wpi_sync_products'])) {
        wpi_import_products();
    }
    ?>
    <div class="wrap">
        <h1>Import Products From Rent Buddy Api</h1>
        <form method="post">
            <?php submit_button('Sync Products', 'primary', 'wpi_sync_products'); ?>
        </form>
    </div>
    <?php
}

// Import products from API
function wpi_import_products() {
    // Increase script execution time and memory limit
    ini_set('max_execution_time', 300); // 5 minutes
    ini_set('memory_limit', '512M');

    global $wpdb;
    $table_name = $wpdb->prefix . 'wpi_api_keys';
    $site_name = get_bloginfo('name');

    // Get the API key for this site
    $api_key_row = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE site_name = %s", $site_name), ARRAY_A);

    if (!$api_key_row || empty($api_key_row['api_key'])) {
        echo 'API key is not set or empty.';
        return;
    }

    if ($api_key_row['status'] !== 'active') {
        echo 'API key is inactive.';
        return;
    }

    $api_key = $api_key_row['api_key'];

    $apiUrl = 'e';
    $secretKey = 'Ba65nWCaUvX';

    // Fetch categories
    $response = wp_remote_get($apiUrl, array(
        'headers' => array(
            'secretKey' => $secretKey,
        ),
    ));

    if (is_wp_error($response)) {
        wp_die('Failed to fetch categories.');
    }

    $result = json_decode(wp_remote_retrieve_body($response));

    $catresponseData = [];
    if (isset($result->MainGroup) && is_array($result->MainGroup)) {
        $count = 70001;
        foreach ($result->MainGroup as $mainGroup) {
            if (isset($mainGroup->description)) {
                $name = $mainGroup->description;
                $id = $mainGroup->id;
                $imageUrl = 'YOURDOMAIN.COM' . $count . '.jpg';
                $catresponseData[] = ['name' => $name, 'id' => $id, 'imageUrl' => $imageUrl];
            }
            $count++;
        }
    }

    $totalProducts = 0;
    $importedProducts = 0;
    $updatedProducts = 0;

    // Loop through each category
    foreach ($catresponseData as $category) {
        if ($category['id']) {
            $catname = $category['name'];

            // Check if category exists, if not create it
            $term = term_exists($catname, 'product_cat');
            if ($term === 0 || $term === null) {
                $term = wp_insert_term($catname, 'product_cat');
            }

            if (is_wp_error($term)) {
                continue;
            }

            $category_id = $term['term_id'];

            $apiUrl = 'YOUAPI.COM' . $category['id'];
            $response = wp_remote_get($apiUrl, array(
                'headers' => array(
                    'secretKey' => $secretKey,
                ),
            ));

            if (is_wp_error($response)) {
                continue;
            }

            $result = json_decode(wp_remote_retrieve_body($response));

            if (isset($result->Products) && is_array($result->Products)) {
                foreach ($result->Products as $productData) {
                    $totalProducts++;

                    $productId = $productData->Product->id;
                    $productDescription = $productData->Product->description;
                    $productPrice = $productData->Product->price;
                    $imageUrl = 'YOURDOMAIN.COM' . $productId . '.jpg';

                    // Check if product with the same SKU exists
                    $existing_product_id = wc_get_product_id_by_sku((string)$productId);

                    if ($existing_product_id) {
                        // Update existing product
                        $post_id = $existing_product_id;

                        wp_update_post(array(
                            'ID'           => $post_id,
                            'post_title'   => $productDescription,
                            'post_content' => $productDescription,
                            'post_status'  => 'publish',
                            'post_type'    => 'product',
                        ));

                        // Update product meta
                        update_post_meta($post_id, '_regular_price', $productPrice);
                        update_post_meta($post_id, '_price', $productPrice);

                        // Update product image
                        $image_id = wpi_upload_image($imageUrl, $post_id);
                        if ($image_id) {
                            set_post_thumbnail($post_id, $image_id);
                        }

                        $updatedProducts++;
                    } else {
                        // Insert new product
                        $post_id = wp_insert_post(array(
                            'post_title'   => $productDescription,
                            'post_content' => $productDescription,
                            'post_status'  => 'publish',
                            'post_type'    => 'product',
                        ));

                        if ($post_id && !is_wp_error($post_id)) {
                            // Add product meta
                            update_post_meta($post_id, '_regular_price', $productPrice);
                            update_post_meta($post_id, '_price', $productPrice);
                            update_post_meta($post_id, '_sku', (string)$productId);

                            // Add product image
                            $image_id = wpi_upload_image($imageUrl, $post_id);
                            if ($image_id) {
                                set_post_thumbnail($post_id, $image_id);
                            }

                            // Assign category to product
                            wp_set_object_terms($post_id, $category_id, 'product_cat');

                            $importedProducts++;
                        }
                    }

                    sleep(1); // Add a delay to avoid rate limits
                }
            }
        }
    }

}