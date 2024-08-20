<?php

// Settings page content
function wpi_settings_page() {
    if (isset($_POST['wpi_save_api_key'])) {
        wpi_save_api_key();
    }

    if (isset($_GET['action'])) {
        $action = $_GET['action'];
        $id = intval($_GET['id']);
        if ($action === 'delete') {
            wpi_delete_api_key($id);
        } elseif ($action === 'toggle') {
            wpi_toggle_api_key_status($id);
        }
    }

    $api_keys = wpi_get_all_api_keys();
    $current_key = wpi_get_current_site_api_key();

    ?>
    <div class="wrap">
        <h1>Rent Buddy Api Settings:</h1>
        
        <?php if (isset($_GET['saved']) && $_GET['saved'] == 'true'): ?>
            <div class="updated notice is-dismissible">
                <p>API key saved successfully.</p>
            </div>
        <?php elseif ($current_key): ?>
            <div class="updated notice is-dismissible">
                <p>API key is already saved and activated.</p>
            </div>
        <?php endif; ?>
        
        <form method="post">
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">API Key</th>
                    <td><input type="text" name="wpi_api_key" value="<?php echo esc_attr($current_key); ?>" /></td>
                </tr>
            </table>
            <?php submit_button('Save API Key', 'primary', 'wpi_save_api_key'); ?>
        </form>

        <h2>Stored API Keys</h2>
        <table class="widefat">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Site Name</th>
                    <th>API Key</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($api_keys as $key) : ?>
                    <tr>
                        <td><?php echo esc_html($key['id']); ?></td>
                        <td><?php echo esc_html($key['site_name']); ?></td>
                        <td><?php echo esc_html($key['status']); ?></td>
                        <td>

                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php
}

function wpi_save_api_key() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'wpi_api_keys';

    $site_name = get_bloginfo('name');
    $api_key = sanitize_text_field($_POST['wpi_api_key']);

    // Check if the API key for this site already exists
    $existing_key = $wpdb->get_row($wpdb->prepare("SELECT * FROM $table_name WHERE site_name = %s", $site_name));
    
    if ($existing_key) {
        // Update the existing API key
        $wpdb->update(
            $table_name,
            array('api_key' => $api_key),
            array('site_name' => $site_name)
        );
    } else {
        // Insert the new API key
        $wpdb->insert(
            $table_name,
            array(
                'site_name' => $site_name,
                'api_key' => $api_key,
            )
        );
    }

    // Redirect to settings page with saved message
    wp_redirect(admin_url('admin.php?page=wpi-product-importer&saved=true'));
    exit;
}

function wpi_get_all_api_keys() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'wpi_api_keys';
    return $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);
}

?>
