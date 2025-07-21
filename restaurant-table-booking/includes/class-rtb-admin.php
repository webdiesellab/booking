<?php

class RTB_Admin {

    public function __construct() {
        add_action('admin_menu', array($this, 'add_admin_menu'));
        add_action('network_admin_menu', array($this, 'add_network_admin_menu'));
        add_action('admin_enqueue_scripts', array($this, 'enqueue_admin_scripts'));
        add_action('wp_ajax_rtb_save_settings', array($this, 'save_settings'));
        add_action('wp_ajax_rtb_save_location', array($this, 'save_location'));
        add_action('wp_ajax_rtb_delete_location', array($this, 'delete_location'));
    }

    public function add_admin_menu() {
        add_menu_page(
            __('Restaurant Bookings', 'restaurant-table-booking'),
            __('Restaurant Bookings', 'restaurant-table-booking'),
            'manage_options',
            'restaurant-bookings',
            array($this, 'bookings_page'),
            'dashicons-calendar-alt',
            30
        );

        add_submenu_page(
            'restaurant-bookings',
            __('Settings', 'restaurant-table-booking'),
            __('Settings', 'restaurant-table-booking'),
            'manage_options',
            'restaurant-bookings-settings',
            array($this, 'settings_page')
        );
    }

    /**
     * Add network admin menu for multisite
     */
    public function add_network_admin_menu() {
        if (!is_multisite()) {
            return;
        }

        add_menu_page(
            __('Restaurant Bookings Network', 'restaurant-table-booking'),
            __('Restaurant Bookings', 'restaurant-table-booking'),
            'manage_network',
            'restaurant-bookings-network',
            array($this, 'network_admin_page'),
            'dashicons-calendar-alt',
            30
        );
    }

    public function enqueue_admin_scripts($hook) {
        if (strpos($hook, 'restaurant-bookings') !== false) {
            // Добавляем текущее время к версии для принудительного обновления кеша
            $version = RTB_VERSION . '.' . time();
            
            // Enqueue WordPress media scripts
            wp_enqueue_media();
            
            wp_enqueue_style('rtb-admin-css', RTB_PLUGIN_URL . 'assets/admin.css', array(), $version);
            wp_enqueue_script('rtb-admin-js', RTB_PLUGIN_URL . 'assets/admin.js', array('jquery'), $version, true);
            wp_localize_script('rtb-admin-js', 'rtb_ajax', array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce' => wp_create_nonce('rtb_admin_nonce')
            ));
        }
    }

    public function bookings_page() {
        $bookings = RTB_Database::get_bookings();
        $locations = RTB_Database::get_locations();

        include RTB_PLUGIN_PATH . 'templates/admin-bookings.php';
    }

    public function settings_page() {
        // Get current settings
        $business_hours = json_decode(RTB_Database::get_setting('business_hours', '{}'), true);
        $time_interval = RTB_Database::get_setting('time_interval', '30');
        $notification_emails = json_decode(RTB_Database::get_setting('notification_emails', '[]'), true);
        $confirmation_enabled = RTB_Database::get_setting('confirmation_enabled', '1');
        $locations = RTB_Database::get_locations();

        include RTB_PLUGIN_PATH . 'templates/admin-settings.php';
    }

    /**
     * Network admin page for multisite
     */
    public function network_admin_page() {
        if (!is_multisite()) {
            return;
        }

        include RTB_PLUGIN_PATH . 'templates/network-admin.php';
    }

    public function save_settings() {
        check_ajax_referer('rtb_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized', 'restaurant-table-booking'));
        }

        $business_hours = $_POST['business_hours'] ?? array();
        $time_interval = sanitize_text_field($_POST['time_interval'] ?? '30');
        $notification_emails = array_map('sanitize_email', $_POST['notification_emails'] ?? array());
        $confirmation_enabled = isset($_POST['confirmation_enabled']) ? '1' : '0';
        $locations_data = $_POST['locations'] ?? array();

        RTB_Database::update_setting('business_hours', json_encode($business_hours));
        RTB_Database::update_setting('time_interval', $time_interval);
        RTB_Database::update_setting('notification_emails', json_encode($notification_emails));
        RTB_Database::update_setting('confirmation_enabled', $confirmation_enabled);

        // Save all locations
        if (!empty($locations_data)) {
            global $wpdb;
            $locations_table = $wpdb->prefix . 'rtb_locations';
            
            foreach ($locations_data as $location_id => $location_data) {
                $name = sanitize_text_field($location_data['name'] ?? '');
                $image_url = esc_url_raw($location_data['image_url'] ?? '');
                $icon_svg = wp_kses($location_data['icon_svg'] ?? '', array(
                    'svg' => array('width' => array(), 'height' => array(), 'viewBox' => array(), 'fill' => array(), 'stroke' => array(), 'stroke-width' => array()),
                    'path' => array('d' => array(), 'fill' => array(), 'stroke' => array()),
                    'circle' => array('cx' => array(), 'cy' => array(), 'r' => array(), 'fill' => array(), 'stroke' => array()),
                    'rect' => array('x' => array(), 'y' => array(), 'width' => array(), 'height' => array(), 'fill' => array(), 'stroke' => array()),
                    'line' => array('x1' => array(), 'y1' => array(), 'x2' => array(), 'y2' => array(), 'stroke' => array()),
                    'polyline' => array('points' => array(), 'fill' => array(), 'stroke' => array()),
                    'polygon' => array('points' => array(), 'fill' => array(), 'stroke' => array())
                ));
                $enabled = isset($location_data['enabled']) ? 1 : 0;
                
                if (!empty($name)) {
                    $wpdb->replace($locations_table, array(
                        'id' => $location_id,
                        'name' => $name,
                        'image_url' => $image_url,
                        'icon_svg' => $icon_svg,
                        'enabled' => $enabled
                    ));
                }
            }
        }
        wp_send_json_success(__('Settings saved successfully!', 'restaurant-table-booking'));
    }

    public function save_location() {
        check_ajax_referer('rtb_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized', 'restaurant-table-booking'));
        }

        global $wpdb;
        $locations_table = $wpdb->prefix . 'rtb_locations';

        $location_id = sanitize_text_field($_POST['location_id']);
        $name = sanitize_text_field($_POST['name']);
        $image_url = esc_url_raw($_POST['image_url']);
        $icon_svg = esc_url_raw($_POST['icon_svg'] ?? ''); // URL файла SVG
        $enabled = isset($_POST['enabled']) ? 1 : 0;

        $result = $wpdb->replace($locations_table, array(
            'id' => $location_id,
            'name' => $name,
            'image_url' => $image_url,
            'icon_svg' => $icon_svg,
            'enabled' => $enabled
        ));

        if ($result !== false) {
            wp_send_json_success(__('Location saved successfully!', 'restaurant-table-booking'));
        } else {
            wp_send_json_error(__('Error saving location.', 'restaurant-table-booking'));
        }
    }

    public function delete_location() {
        check_ajax_referer('rtb_admin_nonce', 'nonce');

        if (!current_user_can('manage_options')) {
            wp_die(__('Unauthorized', 'restaurant-table-booking'));
        }

        global $wpdb;
        $locations_table = $wpdb->prefix . 'rtb_locations';

        $location_id = sanitize_text_field($_POST['location_id']);

        $result = $wpdb->delete($locations_table, array('id' => $location_id));

        if ($result !== false) {
            wp_send_json_success(__('Location deleted successfully!', 'restaurant-table-booking'));
        } else {
            wp_send_json_error(__('Error deleting location.', 'restaurant-table-booking'));
        }
    }
}