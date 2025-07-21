<div class="wrap">
    <h1><?php _e('Restaurant Booking Settings', 'restaurant-table-booking'); ?></h1>
    
    <div class="rtb-admin-tabs">
        <nav class="nav-tab-wrapper">
            <a href="#business-hours" class="nav-tab nav-tab-active"><?php _e('Business Hours', 'restaurant-table-booking'); ?></a>
            <a href="#locations" class="nav-tab"><?php _e('Locations', 'restaurant-table-booking'); ?></a>
            <a href="#notifications" class="nav-tab"><?php _e('Notifications', 'restaurant-table-booking'); ?></a>
        </nav>
        
        <form id="rtb-settings-form">
            <!-- Business Hours Tab -->
            <div id="business-hours" class="rtb-tab-content rtb-tab-active">
                <h2><?php _e('Operating Hours', 'restaurant-table-booking'); ?></h2>
                
                <?php 
                $days = array(
                    'monday' => __('Monday', 'restaurant-table-booking'),
                    'tuesday' => __('Tuesday', 'restaurant-table-booking'),
                    'wednesday' => __('Wednesday', 'restaurant-table-booking'),
                    'thursday' => __('Thursday', 'restaurant-table-booking'),
                    'friday' => __('Friday', 'restaurant-table-booking'),
                    'saturday' => __('Saturday', 'restaurant-table-booking'),
                    'sunday' => __('Sunday', 'restaurant-table-booking')
                );
                
                foreach ($days as $day => $label):
                    $day_hours = $business_hours[$day] ?? array('isOpen' => false, 'openTime' => '09:00', 'closeTime' => '17:00');
                ?>
                    <div class="rtb-day-hours">
                        <div class="rtb-day-label">
                            <label><?php echo $label; ?></label>
                        </div>
                        
                        <div class="rtb-day-controls">
                            <div class="rtb-time-inputs rtb-time-inputs-visible" style="display: flex !important; visibility: visible !important; opacity: 1 !important;">
                                <label><?php _e('From:', 'restaurant-table-booking'); ?></label>
                                <input type="time" name="business_hours[<?php echo $day; ?>][openTime]" value="<?php echo esc_attr($day_hours['openTime']); ?>">
                                
                                <label><?php _e('To:', 'restaurant-table-booking'); ?></label>
                                <input type="time" name="business_hours[<?php echo $day; ?>][closeTime]" value="<?php echo esc_attr($day_hours['closeTime']); ?>">
                                
                                <input type="hidden" name="business_hours[<?php echo $day; ?>][isOpen]" value="1">
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                
                <div class="rtb-time-interval">
                    <h3><?php _e('Time Interval', 'restaurant-table-booking'); ?></h3>
                    <select name="time_interval">
                        <option value="15" <?php selected($time_interval, '15'); ?>><?php _e('15 minutes', 'restaurant-table-booking'); ?></option>
                        <option value="30" <?php selected($time_interval, '30'); ?>><?php _e('30 minutes', 'restaurant-table-booking'); ?></option>
                        <option value="60" <?php selected($time_interval, '60'); ?>><?php _e('1 hour', 'restaurant-table-booking'); ?></option>
                    </select>
                </div>
                
                
            </div>
            
            <!-- Locations Tab -->
            <div id="locations" class="rtb-tab-content">
                <h2><?php _e('Dining Locations', 'restaurant-table-booking'); ?></h2>
                
                <div id="rtb-locations-list">
                    <?php foreach ($locations as $location): ?>
                        <div class="rtb-location-item" data-location-id="<?php echo esc_attr($location['id']); ?>">
                            <div class="rtb-location-image">
                                <img src="<?php echo esc_url($location['image_url']); ?>" alt="<?php echo esc_attr($location['name']); ?>">
                            </div>
                            
                            <div class="rtb-location-details">
                                <input type="text" class="rtb-location-name" value="<?php echo esc_attr($location['name']); ?>" placeholder="<?php _e('Location Name', 'restaurant-table-booking'); ?>">
                                <div class="rtb-svg-selector">
                                    <input type="hidden" class="rtb-location-icon-svg" value="<?php echo esc_attr($location['icon_svg'] ?? ''); ?>">
                                    <div class="rtb-svg-preview">
                                        <?php if (!empty($location['icon_svg'])): ?>
                                            <img src="<?php echo esc_url($location['icon_svg']); ?>" alt="SVG Icon" style="width: 24px; height: 24px;">
                                        <?php else: ?>
                                            <span class="rtb-no-icon">Нет иконки</span>
                                        <?php endif; ?>
                                    </div>
                                    <button type="button" class="button rtb-select-svg">Выбрать SVG иконку</button>
                                    <button type="button" class="button rtb-remove-svg" style="<?php echo empty($location['icon_svg']) ? 'display:none;' : ''; ?>">Удалить</button>
                                </div>
                                <input type="url" class="rtb-location-image-url" value="<?php echo esc_url($location['image_url']); ?>" placeholder="<?php _e('Image URL', 'restaurant-table-booking'); ?>">
                                
                                <label class="rtb-checkbox">
                                    <input type="checkbox" class="rtb-location-enabled" <?php checked($location['enabled'], 1); ?>>
                                    <?php _e('Enabled', 'restaurant-table-booking'); ?>
                                </label>
                            </div>
                            
                            <div class="rtb-location-actions">
                                <button type="button" class="button rtb-save-location"><?php _e('Save', 'restaurant-table-booking'); ?></button>
                                <button type="button" class="button rtb-delete-location"><?php _e('Delete', 'restaurant-table-booking'); ?></button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <button type="button" id="rtb-add-location" class="button button-primary"><?php _e('Add New Location', 'restaurant-table-booking'); ?></button>
            </div>
            
            <!-- Notifications Tab -->
            <div id="notifications" class="rtb-tab-content">
                <h2><?php _e('Notification Settings', 'restaurant-table-booking'); ?></h2>
                
                <div class="rtb-form-group">
                    <label><?php _e('Notification Email Addresses', 'restaurant-table-booking'); ?></label>
                    <div id="rtb-notification-emails">
                        <?php foreach ($notification_emails as $index => $email): ?>
                            <div class="rtb-email-input">
                                <input type="email" name="notification_emails[]" value="<?php echo esc_attr($email); ?>">
                                <button type="button" class="button rtb-remove-email"><?php _e('Remove', 'restaurant-table-booking'); ?></button>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <button type="button" id="rtb-add-email" class="button"><?php _e('Add Email', 'restaurant-table-booking'); ?></button>
                </div>
                
                <div class="rtb-form-group">
                    <label class="rtb-checkbox">
                        <input type="checkbox" name="confirmation_enabled" value="1" <?php checked($confirmation_enabled, '1'); ?>>
                        <?php _e('Send confirmation emails to customers', 'restaurant-table-booking'); ?>
                    </label>
                </div>
            </div>
            
            <div class="rtb-settings-footer">
                <button type="submit" class="button button-primary button-large">
                    <?php _e('Save Settings', 'restaurant-table-booking'); ?>
                </button>
            </div>
        </form>
    </div>
</div>