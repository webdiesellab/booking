<?php

class RTB_Ajax {
    
    public function __construct() {
        add_action('wp_ajax_rtb_get_available_times', array($this, 'get_available_times'));
        add_action('wp_ajax_nopriv_rtb_get_available_times', array($this, 'get_available_times'));
        add_action('wp_ajax_rtb_submit_booking', array($this, 'submit_booking'));
        add_action('wp_ajax_nopriv_rtb_submit_booking', array($this, 'submit_booking'));
    }
    
    public function get_available_times() {
        check_ajax_referer('rtb_frontend_nonce', 'nonce');
        
        $date = sanitize_text_field($_POST['date']);
        
        // НЕ ИСПОЛЬЗУЕМ ЛОКАЦИЮ - временные слоты зависят только от расписания работы
        $frontend = new RTB_Frontend();
        $times = $frontend->get_available_times($date);
        
        wp_send_json_success($times);
    }
    
    public function submit_booking() {
        check_ajax_referer('rtb_frontend_nonce', 'nonce');
        
        // Validate required fields
        $required_fields = array('full_name', 'email', 'phone', 'guests', 'date', 'time', 'location');
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                wp_send_json_error(sprintf(__('%s is required.', 'restaurant-table-booking'), ucfirst(str_replace('_', ' ', $field))));
            }
        }
        
        // Validate email
        if (!is_email($_POST['email'])) {
            wp_send_json_error(__('Please enter a valid email address.', 'restaurant-table-booking'));
        }
        
        // Validate date (not in the past)
        $booking_date = sanitize_text_field($_POST['date']);
        if (strtotime($booking_date) < strtotime(date('Y-m-d'))) {
            wp_send_json_error(__('Booking date cannot be in the past.', 'restaurant-table-booking'));
        }
        
        // Check if time is still available
        $frontend = new RTB_Frontend();
        $available_times = $frontend->get_available_times($booking_date);
        if (!in_array($_POST['time'], $available_times)) {
            wp_send_json_error(__('Selected time is no longer available.', 'restaurant-table-booking'));
        }
        
        // Create booking
        $booking_data = array(
            'full_name' => sanitize_text_field($_POST['full_name']),
            'email' => sanitize_email($_POST['email']),
            'phone' => sanitize_text_field($_POST['phone']),
            'guests' => intval($_POST['guests']),
            'date' => $booking_date,
            'time' => sanitize_text_field($_POST['time']),
            'location' => sanitize_text_field($_POST['location']),
            'special_requests' => sanitize_textarea_field($_POST['special_requests'] ?? '')
        );
        
        $result = RTB_Database::create_booking($booking_data);
        
        if ($result) {
            // Send confirmation email
            $email_handler = new RTB_Email();
            $email_handler->send_booking_confirmation($booking_data);
            
            wp_send_json_success(array(
                'message' => __('Booking confirmed! You will receive a confirmation email shortly.', 'restaurant-table-booking'),
                'booking_data' => $booking_data
            ));
        } else {
            wp_send_json_error(__('Error creating booking. Please try again.', 'restaurant-table-booking'));
        }
    }
}