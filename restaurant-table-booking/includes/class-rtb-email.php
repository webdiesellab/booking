<?php

class RTB_Email {
    
    public function send_booking_confirmation($booking_data) {
        $confirmation_enabled = RTB_Database::get_setting('confirmation_enabled', '1');
        
        if ($confirmation_enabled !== '1') {
            return;
        }
        
        // Get location name
        $locations = RTB_Database::get_locations();
        $location_name = '';
        foreach ($locations as $location) {
            if ($location['id'] === $booking_data['location']) {
                $location_name = $location['name'];
                break;
            }
        }
        
        // Format date and time
        $formatted_date = date('l, F j, Y', strtotime($booking_data['date']));
        $formatted_time = date('H:i', strtotime($booking_data['time']));
        
        // Email to customer
        $customer_subject = __('Booking Confirmation - Your Table is Reserved!', 'restaurant-table-booking');
        $customer_message = $this->get_customer_email_template($booking_data, $location_name, $formatted_date, $formatted_time);
        
        wp_mail(
            $booking_data['email'],
            $customer_subject,
            $customer_message,
            array('Content-Type: text/html; charset=UTF-8')
        );
        
        // Email to restaurant
        $notification_emails = json_decode(RTB_Database::get_setting('notification_emails', '[]'), true);
        if (!empty($notification_emails)) {
            $admin_subject = __('New Table Booking Received', 'restaurant-table-booking');
            $admin_message = $this->get_admin_email_template($booking_data, $location_name, $formatted_date, $formatted_time);
            
            foreach ($notification_emails as $email) {
                if (is_email($email)) {
                    wp_mail(
                        $email,
                        $admin_subject,
                        $admin_message,
                        array('Content-Type: text/html; charset=UTF-8')
                    );
                }
            }
        }
    }
    
    private function get_customer_email_template($booking_data, $location_name, $formatted_date, $formatted_time) {
        $site_name = get_bloginfo('name');
        $admin_email = RTB_Database::get_setting('admin_email', get_option('admin_email'));
        
        // Plain text details for cancellation email
        $booking_details_plain = "Name: {$booking_data['full_name']}\n";
        $booking_details_plain .= "Date: {$formatted_date}\n";
        $booking_details_plain .= "Time: {$formatted_time}\n";
        $booking_details_plain .= "Guests: {$booking_data['guests']}\n";
        $booking_details_plain .= "Location: {$location_name}\n";
        $booking_details_plain .= "Phone: {$booking_data['phone']}";
        
        $cancel_subject = "Booking Cancellation Request";
        $cancel_body = "Dear {$site_name} Team,\n\n";
        $cancel_body .= "I would like to cancel my booking with the following details:\n\n";
        $cancel_body .= $booking_details_plain;
        $cancel_body .= "\n\nBest regards,\n{$booking_data['full_name']}";
        
        $cancel_link = "mailto:{$admin_email}?" . 
                      "subject=" . rawurlencode($cancel_subject) . 
                      "&body=" . rawurlencode($cancel_body);

        return "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: linear-gradient(135deg, #f59e0b, #ea580c); color: white; padding: 30px; text-align: center; border-radius: 10px 10px 0 0; }
                .content { background: #f9f9f9; padding: 30px; border-radius: 0 0 10px 10px; }
                .booking-details { background: white; padding: 20px; border-radius: 8px; margin: 20px 0; }
                .detail-row { display: flex; justify-content: space-between; margin: 10px 0; padding: 10px 0; border-bottom: 1px solid #eee; }
                .label { font-weight: bold; color: #666; }
                .value { color: #333; }
                .footer { text-align: center; margin-top: 30px; color: #666; font-size: 14px; }
                .cancel-btn { 
                    display: inline-block; 
                    background: #ef4444; 
                    color: white; 
                    padding: 12px 24px; 
                    text-decoration: none; 
                    border-radius: 6px; 
                    font-weight: bold; 
                    margin: 20px 0; 
                    text-align: center;
                }
                .cancel-btn:hover { 
                    background: #dc2626; 
                }
                .cancel-note {
                    font-size: 13px;
                    color: #666;
                    text-align: center;
                    margin-top: 5px;
                }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h1>🍽️ Booking Confirmed!</h1>
                    <p>Thank you for choosing {$site_name}</p>
                </div>
                <div class='content'>
                    <p>Dear {$booking_data['full_name']},</p>
                    <p>We're delighted to confirm your table reservation. Here are your booking details:</p>
                    
                    <div class='booking-details'>
                        <h3>Reservation Details</h3>
                        <div class='detail-row'>
                            <span class='label'>Date:</span>
                            <span class='value'>{$formatted_date}</span>
                        </div>
                        <div class='detail-row'>
                            <span class='label'>Time:</span>
                            <span class='value'>{$formatted_time}</span>
                        </div>
                        <div class='detail-row'>
                            <span class='label'>Party Size:</span>
                            <span class='value'>{$booking_data['guests']} guest(s)</span>
                        </div>
                        <div class='detail-row'>
                            <span class='label'>Location:</span>
                            <span class='value'>{$location_name}</span>
                        </div>
                        <div class='detail-row'>
                            <span class='label'>Contact:</span>
                            <span class='value'>{$booking_data['phone']}</span>
                        </div>
                    </div>
                    
                    <div style='text-align: center;'>
                        <a href='{$cancel_link}' class='cancel-btn'>Cancel Booking</a>
                        <p class='cancel-note'>Clicking this button will open your email client with a pre-filled cancellation request</p>
                    </div>
                    
                    <h4>Important Information:</h4>
                    <ul>
                        <li>Please arrive 15 minutes before your reservation time</li>
                        <li>We'll hold your table for 15 minutes past your reservation time</li>
                        <li>For cancellations, please contact us at least 2 hours in advance</li>
                        <li>Large parties may require a deposit</li>
                    </ul>
                    
                    <p>We look forward to serving you!</p>
                    
                    <div class='footer'>
                        <p>Best regards,<br>The {$site_name} Team</p>
                    </div>
                </div>
            </div>
        </body>
        </html>";
    }
    
    private function get_admin_email_template($booking_data, $location_name, $formatted_date, $formatted_time) {
        $site_name = get_bloginfo('name');
        
        $admin_message = "
        <html>
        <head>
            <style>
                body { font-family: Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; padding: 20px; }
                .header { background: #1f2937; color: white; padding: 20px; text-align: center; }
                .content { background: #f9f9f9; padding: 20px; }
                .booking-details { background: white; padding: 20px; border-radius: 8px; margin: 20px 0; }
                .detail-row { margin: 10px 0; padding: 10px 0; border-bottom: 1px solid #eee; }
                .label { font-weight: bold; color: #666; }
            </style>
        </head>
        <body>
            <div class='container'>
                <div class='header'>
                    <h2>New Table Booking - {$site_name}</h2>
                </div>
                <div class='content'>
                    <p>A new table booking has been received:</p>
                    
                    <div class='booking-details'>
                        <div class='detail-row'>
                            <span class='label'>Customer Name:</span> {$booking_data['full_name']}
                        </div>
                        <div class='detail-row'>
                            <span class='label'>Email:</span> {$booking_data['email']}
                        </div>
                        <div class='detail-row'>
                            <span class='label'>Phone:</span> {$booking_data['phone']}
                        </div>
                        <div class='detail-row'>
                            <span class='label'>Date:</span> {$formatted_date}
                        </div>
                        <div class='detail-row'>
                            <span class='label'>Time:</span> {$formatted_time}
                        </div>
                        <div class='detail-row'>
                            <span class='label'>Party Size:</span> {$booking_data['guests']} guest(s)
                        </div>
                        <div class='detail-row'>
                            <span class='label'>Location:</span> {$location_name}
                        </div>";

        if (!empty($booking_data['special_requests'])) {
            $admin_message .= "
                        <div class='detail-row'>
                            <span class='label'>Special Requests:</span> {$booking_data['special_requests']}
                        </div>";
        }
        
        return $admin_message . "
                    </div>
                    
                    <p>Please prepare for this reservation accordingly.</p>
                </div>
            </div>
        </body>
        </html>";
    }
}