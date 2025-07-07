<?php
/**
 * Plugin Name: Restaurant Table Booking
 * Plugin URI: https://example.com/restaurant-table-booking
 * Description: A comprehensive restaurant table booking system with admin panel, email notifications, and responsive frontend. Supports WordPress Multisite.
 * Version: 1.0.0
 * Author: Web Diesel Laboratory
 * License: GPL v2 or later
 * Text Domain: restaurant-table-booking
 * Network: true
 */

// Prevent direct access
if (!defined('ABSPATH')) {
    exit;
}

// Define plugin constants
define('RTB_PLUGIN_URL', plugin_dir_url(__FILE__));
define('RTB_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('RTB_VERSION', '1.0.0');

// Include required files
require_once RTB_PLUGIN_PATH . 'includes/class-rtb-database.php';
require_once RTB_PLUGIN_PATH . 'includes/class-rtb-admin.php';
require_once RTB_PLUGIN_PATH . 'includes/class-rtb-frontend.php';
require_once RTB_PLUGIN_PATH . 'includes/class-rtb-ajax.php';
require_once RTB_PLUGIN_PATH . 'includes/class-rtb-email.php';

class RestaurantTableBooking {
    
    public function __construct() {
        add_action('init', array($this, 'init'));
        
        // Activation/Deactivation hooks
        register_activation_hook(__FILE__, array($this, 'activate'));
        register_deactivation_hook(__FILE__, array($this, 'deactivate'));
        
        // Multisite hooks
        if (is_multisite()) {
            add_action('wp_initialize_site', array($this, 'on_new_site_created'));
            add_action('wp_delete_site', array($this, 'on_site_deleted'));
        }
    }
    
    public function init() {
        // Initialize classes
        new RTB_Database();
        new RTB_Admin();
        new RTB_Frontend();
        new RTB_Ajax();
        new RTB_Email();
        
        // Load text domain
        load_plugin_textdomain('restaurant-table-booking', false, dirname(plugin_basename(__FILE__)) . '/languages');
    }
    
    public function activate() {
        if (is_multisite()) {
            // Network activation - create tables for all sites
            RTB_Database::create_tables_for_all_sites();
            RTB_Database::insert_default_data_for_all_sites();
        } else {
            // Single site activation
            RTB_Database::create_tables();
            RTB_Database::insert_default_data();
        }
        
        flush_rewrite_rules();
    }
    
    public function deactivate() {
        flush_rewrite_rules();
    }
    
    /**
     * Handle new site creation in multisite
     */
    public function on_new_site_created($site) {
        RTB_Database::on_new_site_created($site->blog_id);
    }
    
    /**
     * Handle site deletion in multisite
     */
    public function on_site_deleted($site) {
        RTB_Database::on_site_deleted($site->blog_id);
    }
}

// Initialize the plugin
new RestaurantTableBooking();

/**
 * Uninstall hook - only runs when plugin is deleted
 */
register_uninstall_hook(__FILE__, 'rtb_uninstall');

function rtb_uninstall() {
    if (is_multisite()) {
        RTB_Database::drop_tables_for_all_sites();
    } else {
        RTB_Database::drop_tables();
    }
}