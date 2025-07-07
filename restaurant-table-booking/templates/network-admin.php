<div class="wrap">
    <h1><?php _e('Restaurant Bookings - Network Administration', 'restaurant-table-booking'); ?></h1>
    
    <div class="notice notice-info">
        <p><?php _e('This plugin is network-activated and each site in your network has its own independent booking system with separate settings, locations, and bookings.', 'restaurant-table-booking'); ?></p>
    </div>
    
    <div class="rtb-network-stats">
        <h2><?php _e('Network Overview', 'restaurant-table-booking'); ?></h2>
        
        <?php
        $sites = get_sites(array('number' => 0));
        $total_sites = count($sites);
        $total_bookings = 0;
        $active_sites = 0;
        
        foreach ($sites as $site) {
            switch_to_blog($site->blog_id);
            
            // Check if plugin tables exist
            global $wpdb;
            $bookings_table = $wpdb->prefix . 'rtb_bookings';
            $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$bookings_table'") == $bookings_table;
            
            if ($table_exists) {
                $active_sites++;
                $site_bookings = $wpdb->get_var("SELECT COUNT(*) FROM $bookings_table");
                $total_bookings += intval($site_bookings);
            }
            
            restore_current_blog();
        }
        ?>
        
        <div class="rtb-admin-stats">
            <div class="rtb-stat-card">
                <h3><?php _e('Total Sites', 'restaurant-table-booking'); ?></h3>
                <div class="rtb-stat-number"><?php echo $total_sites; ?></div>
            </div>
            
            <div class="rtb-stat-card">
                <h3><?php _e('Active Sites', 'restaurant-table-booking'); ?></h3>
                <div class="rtb-stat-number"><?php echo $active_sites; ?></div>
            </div>
            
            <div class="rtb-stat-card">
                <h3><?php _e('Total Bookings', 'restaurant-table-booking'); ?></h3>
                <div class="rtb-stat-number"><?php echo $total_bookings; ?></div>
            </div>
        </div>
    </div>
    
    <div class="rtb-network-sites">
        <h2><?php _e('Sites with Restaurant Booking System', 'restaurant-table-booking'); ?></h2>
        
        <table class="wp-list-table widefat fixed striped">
            <thead>
                <tr>
                    <th><?php _e('Site', 'restaurant-table-booking'); ?></th>
                    <th><?php _e('URL', 'restaurant-table-booking'); ?></th>
                    <th><?php _e('Bookings', 'restaurant-table-booking'); ?></th>
                    <th><?php _e('Status', 'restaurant-table-booking'); ?></th>
                    <th><?php _e('Actions', 'restaurant-table-booking'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($sites as $site): ?>
                    <?php
                    switch_to_blog($site->blog_id);
                    
                    $site_details = get_blog_details($site->blog_id);
                    $site_name = $site_details->blogname;
                    $site_url = $site_details->siteurl;
                    
                    // Check if plugin tables exist
                    global $wpdb;
                    $bookings_table = $wpdb->prefix . 'rtb_bookings';
                    $table_exists = $wpdb->get_var("SHOW TABLES LIKE '$bookings_table'") == $bookings_table;
                    
                    $site_bookings = 0;
                    $status = __('Inactive', 'restaurant-table-booking');
                    
                    if ($table_exists) {
                        $site_bookings = $wpdb->get_var("SELECT COUNT(*) FROM $bookings_table");
                        $status = __('Active', 'restaurant-table-booking');
                    }
                    
                    restore_current_blog();
                    ?>
                    <tr>
                        <td><strong><?php echo esc_html($site_name); ?></strong></td>
                        <td><a href="<?php echo esc_url($site_url); ?>" target="_blank"><?php echo esc_html($site_url); ?></a></td>
                        <td><?php echo $site_bookings; ?></td>
                        <td>
                            <span class="rtb-status rtb-status-<?php echo $table_exists ? 'confirmed' : 'cancelled'; ?>">
                                <?php echo $status; ?>
                            </span>
                        </td>
                        <td>
                            <?php if ($table_exists): ?>
                                <a href="<?php echo esc_url($site_url . '/wp-admin/admin.php?page=restaurant-bookings'); ?>" target="_blank" class="button">
                                    <?php _e('Manage Bookings', 'restaurant-table-booking'); ?>
                                </a>
                                <a href="<?php echo esc_url($site_url . '/wp-admin/admin.php?page=restaurant-bookings-settings'); ?>" target="_blank" class="button">
                                    <?php _e('Settings', 'restaurant-table-booking'); ?>
                                </a>
                            <?php else: ?>
                                <span class="description"><?php _e('Plugin not activated on this site', 'restaurant-table-booking'); ?></span>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    
    <div class="rtb-network-info">
        <h2><?php _e('Multisite Information', 'restaurant-table-booking'); ?></h2>
        
        <div class="notice notice-warning">
            <h4><?php _e('Important Notes:', 'restaurant-table-booking'); ?></h4>
            <ul>
                <li><?php _e('Each site in your network has completely independent booking data', 'restaurant-table-booking'); ?></li>
                <li><?php _e('Settings, locations, and bookings are not shared between sites', 'restaurant-table-booking'); ?></li>
                <li><?php _e('Site administrators can only manage their own site\'s bookings', 'restaurant-table-booking'); ?></li>
                <li><?php _e('When a new site is created, the plugin tables are automatically created', 'restaurant-table-booking'); ?></li>
                <li><?php _e('When a site is deleted, all booking data for that site is permanently removed', 'restaurant-table-booking'); ?></li>
            </ul>
        </div>
        
        <div class="notice notice-info">
            <h4><?php _e('Plugin Features in Multisite:', 'restaurant-table-booking'); ?></h4>
            <ul>
                <li>✅ <?php _e('Independent booking systems per site', 'restaurant-table-booking'); ?></li>
                <li>✅ <?php _e('Automatic table creation for new sites', 'restaurant-table-booking'); ?></li>
                <li>✅ <?php _e('Automatic cleanup when sites are deleted', 'restaurant-table-booking'); ?></li>
                <li>✅ <?php _e('Network admin overview dashboard', 'restaurant-table-booking'); ?></li>
                <li>✅ <?php _e('Site-specific email notifications', 'restaurant-table-booking'); ?></li>
                <li>✅ <?php _e('Individual business hours and locations per site', 'restaurant-table-booking'); ?></li>
            </ul>
        </div>
    </div>
</div>

<style>
.rtb-network-stats {
    margin: 20px 0;
}

.rtb-network-sites {
    margin: 30px 0;
}

.rtb-network-info {
    margin: 30px 0;
}

.rtb-network-info ul {
    margin-left: 20px;
}

.rtb-network-info li {
    margin: 5px 0;
}
</style>