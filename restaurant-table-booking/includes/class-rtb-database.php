<?php

class RTB_Database {
    
    public static function create_tables() {
        global $wpdb;
        
        $charset_collate = $wpdb->get_charset_collate();
        
        // Bookings table
        $bookings_table = $wpdb->prefix . 'rtb_bookings';
        $bookings_sql = "CREATE TABLE $bookings_table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            full_name varchar(255) NOT NULL,
            email varchar(255) NOT NULL,
            phone varchar(50) NOT NULL,
            guests int(11) NOT NULL,
            booking_date date NOT NULL,
            booking_time time NOT NULL,
            location_id varchar(50) NOT NULL,
            status varchar(20) DEFAULT 'confirmed',
            special_requests text,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            updated_at datetime DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";
        
        // Locations table
        $locations_table = $wpdb->prefix . 'rtb_locations';
        $locations_sql = "CREATE TABLE $locations_table (
            id varchar(50) NOT NULL,
            name varchar(255) NOT NULL,
            image_url text,
            enabled tinyint(1) DEFAULT 1,
            sort_order int(11) DEFAULT 0,
            created_at datetime DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (id)
        ) $charset_collate;";
        
        // Settings table
        $settings_table = $wpdb->prefix . 'rtb_settings';
        $settings_sql = "CREATE TABLE $settings_table (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            setting_key varchar(255) NOT NULL,
            setting_value longtext,
            PRIMARY KEY (id),
            UNIQUE KEY setting_key (setting_key)
        ) $charset_collate;";
        
        require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
        dbDelta($bookings_sql);
        dbDelta($locations_sql);
        dbDelta($settings_sql);
    }
    
    public static function insert_default_data() {
        global $wpdb;
        
        // Insert default locations
        $locations_table = $wpdb->prefix . 'rtb_locations';
        $default_locations = array(
            array('main-dining', 'Main Dining Room', 'https://images.pexels.com/photos/941861/pexels-photo-941861.jpeg?auto=compress&cs=tinysrgb&w=800', 1, 1),
            array('private-dining', 'Private Dining', 'https://images.pexels.com/photos/1267320/pexels-photo-1267320.jpeg?auto=compress&cs=tinysrgb&w=800', 1, 2),
            array('outdoor-terrace', 'Outdoor Terrace', 'https://images.pexels.com/photos/1581384/pexels-photo-1581384.jpeg?auto=compress&cs=tinysrgb&w=800', 1, 3),
            array('wine-cellar', 'Wine Cellar', 'https://images.pexels.com/photos/1267320/pexels-photo-1267320.jpeg?auto=compress&cs=tinysrgb&w=800', 1, 4)
        );
        
        foreach ($default_locations as $location) {
            $wpdb->replace($locations_table, array(
                'id' => $location[0],
                'name' => $location[1],
                'image_url' => $location[2],
                'enabled' => $location[3],
                'sort_order' => $location[4]
            ));
        }
        
        // Insert default settings
        $settings_table = $wpdb->prefix . 'rtb_settings';
        $default_settings = array(
            'business_hours' => json_encode(array(
                'monday' => array('isOpen' => true, 'openTime' => '11:00', 'closeTime' => '22:00'),
                'tuesday' => array('isOpen' => true, 'openTime' => '11:00', 'closeTime' => '22:00'),
                'wednesday' => array('isOpen' => true, 'openTime' => '11:00', 'closeTime' => '22:00'),
                'thursday' => array('isOpen' => true, 'openTime' => '11:00', 'closeTime' => '22:00'),
                'friday' => array('isOpen' => true, 'openTime' => '11:00', 'closeTime' => '23:00'),
                'saturday' => array('isOpen' => true, 'openTime' => '10:00', 'closeTime' => '23:00'),
                'sunday' => array('isOpen' => true, 'openTime' => '10:00', 'closeTime' => '21:00')
            )),
            'time_interval' => '30',
            'time_format' => '24',
            'notification_emails' => json_encode(array(get_option('admin_email'))),
            'confirmation_enabled' => '1',
            'max_guests' => '15',
            'advance_booking_days' => '30'
        );
        
        foreach ($default_settings as $key => $value) {
            $wpdb->replace($settings_table, array(
                'setting_key' => $key,
                'setting_value' => $value
            ));
        }
    }
    
    public static function get_setting($key, $default = '') {
        global $wpdb;
        $settings_table = $wpdb->prefix . 'rtb_settings';
        
        $value = $wpdb->get_var($wpdb->prepare(
            "SELECT setting_value FROM $settings_table WHERE setting_key = %s",
            $key
        ));
        
        return $value !== null ? $value : $default;
    }
    
    public static function update_setting($key, $value) {
        global $wpdb;
        $settings_table = $wpdb->prefix . 'rtb_settings';
        
        return $wpdb->replace($settings_table, array(
            'setting_key' => $key,
            'setting_value' => $value
        ));
    }
    
    public static function get_locations() {
        global $wpdb;
        $locations_table = $wpdb->prefix . 'rtb_locations';
        
        return $wpdb->get_results(
            "SELECT * FROM $locations_table ORDER BY sort_order ASC",
            ARRAY_A
        );
    }
    
    public static function get_bookings($date = null, $location = null) {
        global $wpdb;
        $bookings_table = $wpdb->prefix . 'rtb_bookings';
        
        $where_clauses = array();
        $where_values = array();
        
        if ($date) {
            $where_clauses[] = "booking_date = %s";
            $where_values[] = $date;
        }
        
        if ($location) {
            $where_clauses[] = "location_id = %s";
            $where_values[] = $location;
        }
        
        $where_sql = !empty($where_clauses) ? 'WHERE ' . implode(' AND ', $where_clauses) : '';
        
        $sql = "SELECT * FROM $bookings_table $where_sql ORDER BY booking_date DESC, booking_time DESC";
        
        if (!empty($where_values)) {
            $sql = $wpdb->prepare($sql, $where_values);
        }
        
        return $wpdb->get_results($sql, ARRAY_A);
    }
    
    public static function create_booking($data) {
        global $wpdb;
        $bookings_table = $wpdb->prefix . 'rtb_bookings';
        
        return $wpdb->insert($bookings_table, array(
            'full_name' => sanitize_text_field($data['full_name']),
            'email' => sanitize_email($data['email']),
            'phone' => sanitize_text_field($data['phone']),
            'guests' => intval($data['guests']),
            'booking_date' => sanitize_text_field($data['date']),
            'booking_time' => sanitize_text_field($data['time']),
            'location_id' => sanitize_text_field($data['location']),
            'special_requests' => sanitize_textarea_field($data['special_requests'] ?? ''),
            'status' => 'confirmed'
        ));
    }
    
    /**
     * MULTISITE SUPPORT FUNCTIONS
     */
    
    /**
     * Create tables for all sites in multisite network
     */
    public static function create_tables_for_all_sites() {
        if (!is_multisite()) {
            self::create_tables();
            return;
        }
        
        global $wpdb;
        
        // Get all sites in the network
        $sites = get_sites(array('number' => 0));
        
        foreach ($sites as $site) {
            switch_to_blog($site->blog_id);
            self::create_tables();
            restore_current_blog();
        }
    }
    
    /**
     * Insert default data for all sites in multisite network
     */
    public static function insert_default_data_for_all_sites() {
        if (!is_multisite()) {
            self::insert_default_data();
            return;
        }
        
        global $wpdb;
        
        // Get all sites in the network
        $sites = get_sites(array('number' => 0));
        
        foreach ($sites as $site) {
            switch_to_blog($site->blog_id);
            self::insert_default_data();
            restore_current_blog();
        }
    }
    
    /**
     * Drop tables for all sites in multisite network
     */
    public static function drop_tables_for_all_sites() {
        if (!is_multisite()) {
            self::drop_tables();
            return;
        }
        
        global $wpdb;
        
        // Get all sites in the network
        $sites = get_sites(array('number' => 0));
        
        foreach ($sites as $site) {
            switch_to_blog($site->blog_id);
            self::drop_tables();
            restore_current_blog();
        }
    }
    
    /**
     * Drop plugin tables
     */
    public static function drop_tables() {
        global $wpdb;
        
        $tables = array(
            $wpdb->prefix . 'rtb_bookings',
            $wpdb->prefix . 'rtb_locations',
            $wpdb->prefix . 'rtb_settings'
        );
        
        foreach ($tables as $table) {
            $wpdb->query("DROP TABLE IF EXISTS $table");
        }
    }
    
    /**
     * Handle new site creation in multisite
     */
    public static function on_new_site_created($site_id) {
        if (!is_multisite()) {
            return;
        }
        
        switch_to_blog($site_id);
        self::create_tables();
        self::insert_default_data();
        restore_current_blog();
    }
    
    /**
     * Handle site deletion in multisite
     */
    public static function on_site_deleted($site_id) {
        if (!is_multisite()) {
            return;
        }
        
        switch_to_blog($site_id);
        self::drop_tables();
        restore_current_blog();
    }
}