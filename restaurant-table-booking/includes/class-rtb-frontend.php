<?php

class RTB_Frontend {
    
    public function __construct() {
        add_action('wp_enqueue_scripts', array($this, 'enqueue_scripts'));
        add_shortcode('restaurant_booking_form', array($this, 'booking_form_shortcode'));
        add_action('wp_footer', array($this, 'add_booking_modal'));
    }
    
    public function enqueue_scripts() {
        wp_enqueue_style('rtb-frontend-css', RTB_PLUGIN_URL . 'assets/frontend.css', array(), RTB_VERSION);
        wp_enqueue_script('rtb-frontend-js', RTB_PLUGIN_URL . 'assets/frontend.js', array('jquery'), RTB_VERSION, true);
        
        // Get time format setting
        $time_format = RTB_Database::get_setting('time_format', '24');
        
        // Debug: Log time format setting
        error_log('RTB Frontend: time_format setting = ' . $time_format);
        
        // Add translations for JavaScript
        wp_localize_script('rtb-frontend-js', 'rtb_ajax', array(
            'ajax_url' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('rtb_frontend_nonce')
        ));
        
        wp_localize_script('rtb-frontend-js', 'rtb_translations', array(
            'select_time' => __('Выберите время', 'restaurant-table-booking'),
            'loading' => __('Загрузка...', 'restaurant-table-booking'),
            'reservation_unavailable' => __('Резервация недоступна', 'restaurant-table-booking'),
            'error_loading' => __('Ошибка загрузки времени', 'restaurant-table-booking'),
            'error_occurred' => __('Произошла ошибка. Пожалуйста, попробуйте снова.', 'restaurant-table-booking'),
            'network_error' => __('Ошибка сети. Проверьте подключение и попробуйте снова.', 'restaurant-table-booking'),
            'date' => __('Дата', 'restaurant-table-booking'),
            'time' => __('Время', 'restaurant-table-booking'),
            'party_size' => __('Количество гостей', 'restaurant-table-booking'),
            'location' => __('Локация', 'restaurant-table-booking'),
            'phone' => __('Телефон', 'restaurant-table-booking'),
            'guests' => __('гостей', 'restaurant-table-booking'),
            'guest' => __('гость', 'restaurant-table-booking')
        ));
        
        // Add time format setting
        wp_localize_script('rtb-frontend-js', 'rtb_settings', array(
            'time_format' => $time_format
        ));
    }
    
    public function booking_form_shortcode($atts) {
        $atts = shortcode_atts(array(
            'style' => 'default'
        ), $atts);
        
        ob_start();
        include RTB_PLUGIN_PATH . 'templates/booking-form.php';
        return ob_get_clean();
    }
    
    public function add_booking_modal() {
        // Only add modal once per page
        static $modal_added = false;
        if ($modal_added) {
            return;
        }
        $modal_added = true;
        
        include RTB_PLUGIN_PATH . 'templates/booking-modal.php';
    }
    
    public function get_available_times($date) {
        $business_hours = json_decode(RTB_Database::get_setting('business_hours', '{}'), true);
        $time_interval = intval(RTB_Database::get_setting('time_interval', '30'));
        
        // Get day name in English
        $timestamp = strtotime($date);
        $day_name = strtolower(date('l', $timestamp));
        
        // Check if restaurant is open on this day
        if (!isset($business_hours[$day_name]) || !$business_hours[$day_name]['isOpen']) {
            return array();
        }
        
        $open_time = $business_hours[$day_name]['openTime'];
        $close_time = $business_hours[$day_name]['closeTime'];
        
        $times = array();
        
        // Parse open and close times
        $open_hour = intval(substr($open_time, 0, 2));
        $open_minute = intval(substr($open_time, 3, 2));
        $close_hour = intval(substr($close_time, 0, 2));
        $close_minute = intval(substr($close_time, 3, 2));
        
        // Check if it's overnight hours (e.g., 14:00 - 01:00)
        $is_overnight = ($close_hour < $open_hour) || ($close_hour == $open_hour && $close_minute < $open_minute);
        
        $current_hour = $open_hour;
        $current_minute = $open_minute;
        
        // Generate time slots
        $slot_count = 0;
        while ($slot_count < 100) { // Safety limit
            $time_string = sprintf('%02d:%02d', $current_hour, $current_minute);
            
            // Check if we've reached the end time
            if ($is_overnight) {
                // For overnight hours, we need to handle crossing midnight
                $current_time_minutes = ($current_hour * 60) + $current_minute;
                $close_time_minutes = ($close_hour * 60) + $close_minute;
                
                // If current time is past midnight (< 12:00) and we've reached close time
                if ($current_hour < 12 && $current_time_minutes >= $close_time_minutes) {
                    break;
                }
                
                // Stop 30 minutes before closing
                $stop_time_minutes = $close_time_minutes - 30;
                if ($current_hour < 12 && $current_time_minutes >= $stop_time_minutes) {
                    break;
                }
            } else {
                // For normal hours, stop 30 minutes before closing
                $end_hour = $close_hour;
                $end_minute = $close_minute - 30;
                if ($end_minute < 0) {
                    $end_hour--;
                    $end_minute = 30;
                }
                
                if ($current_hour > $end_hour || ($current_hour == $end_hour && $current_minute >= $end_minute)) {
                    break;
                }
            }
            
            // Check if time is in the past for today
            $is_available = true;
            if ($date === date('Y-m-d')) {
                $now = current_time('timestamp');
                $booking_timestamp = strtotime($date . ' ' . $time_string);
                
                // Add 1 hour buffer for current day
                if ($booking_timestamp <= $now + (60 * 60)) {
                    $is_available = false;
                }
            }
            
            if ($is_available) {
                $times[] = $time_string;
            }
            
            // Increment time by interval
            $current_minute += $time_interval;
            if ($current_minute >= 60) {
                $current_hour += 1;
                $current_minute = 0;
            }
            
            // Handle midnight crossing for overnight hours
            if ($is_overnight && $current_hour >= 24) {
                $current_hour = 0;
            }
            
            $slot_count++;
        }
        
        return $times;
    }
}