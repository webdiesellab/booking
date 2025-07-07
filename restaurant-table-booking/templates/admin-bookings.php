<div class="wrap">
    <h1><?php _e('Restaurant Bookings', 'restaurant-table-booking'); ?></h1>
    
    <div class="rtb-admin-stats">
        <div class="rtb-stat-card">
            <h3><?php _e('Today\'s Bookings', 'restaurant-table-booking'); ?></h3>
            <div class="rtb-stat-number">
                <?php 
                $today_bookings = RTB_Database::get_bookings(date('Y-m-d'));
                echo count($today_bookings);
                ?>
            </div>
        </div>
        
        <div class="rtb-stat-card">
            <h3><?php _e('This Week', 'restaurant-table-booking'); ?></h3>
            <div class="rtb-stat-number">
                <?php 
                $week_start = date('Y-m-d', strtotime('monday this week'));
                $week_bookings = array_filter($bookings, function($booking) use ($week_start) {
                    return $booking['booking_date'] >= $week_start;
                });
                echo count($week_bookings);
                ?>
            </div>
        </div>
        
        <div class="rtb-stat-card">
            <h3><?php _e('Total Bookings', 'restaurant-table-booking'); ?></h3>
            <div class="rtb-stat-number"><?php echo count($bookings); ?></div>
        </div>
    </div>
    
    <div class="rtb-bookings-table-wrapper">
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php _e('Customer', 'restaurant-table-booking'); ?></th>
                    <th><?php _e('Contact', 'restaurant-table-booking'); ?></th>
                    <th><?php _e('Date & Time', 'restaurant-table-booking'); ?></th>
                    <th><?php _e('Guests', 'restaurant-table-booking'); ?></th>
                    <th><?php _e('Location', 'restaurant-table-booking'); ?></th>
                    <th><?php _e('Status', 'restaurant-table-booking'); ?></th>
                    <th><?php _e('Created', 'restaurant-table-booking'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($bookings)): ?>
                    <tr>
                        <td colspan="7" style="text-align: center; padding: 40px;">
                            <?php _e('No bookings found.', 'restaurant-table-booking'); ?>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($bookings as $booking): ?>
                        <?php 
                        $location_name = '';
                        foreach ($locations as $location) {
                            if ($location['id'] === $booking['location_id']) {
                                $location_name = $location['name'];
                                break;
                            }
                        }
                        ?>
                        <tr>
                            <td>
                                <strong><?php echo esc_html($booking['full_name']); ?></strong>
                            </td>
                            <td>
                                <div><?php echo esc_html($booking['email']); ?></div>
                                <div><?php echo esc_html($booking['phone']); ?></div>
                            </td>
                            <td>
                                <div><strong><?php echo date('M j, Y', strtotime($booking['booking_date'])); ?></strong></div>
                                <div><?php echo date('g:i A', strtotime($booking['booking_time'])); ?></div>
                            </td>
                            <td><?php echo $booking['guests']; ?></td>
                            <td><?php echo esc_html($location_name); ?></td>
                            <td>
                                <span class="rtb-status rtb-status-<?php echo esc_attr($booking['status']); ?>">
                                    <?php echo ucfirst($booking['status']); ?>
                                </span>
                            </td>
                            <td><?php echo date('M j, Y g:i A', strtotime($booking['created_at'])); ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>