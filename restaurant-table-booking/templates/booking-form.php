<div id="rtb-booking-form" class="rtb-booking-container">
    <div class="rtb-form-wrapper">
        <div class="rtb-form-header">
            <h2><?php _e('Reserve Your Table', 'restaurant-table-booking'); ?></h2>
            <!-- <p><?php _e('Experience culinary excellence in our beautiful dining spaces', 'restaurant-table-booking'); ?></p> -->
        </div>

        <form id="rtb-booking-form-element" class="rtb-form">
            <div class="rtb-form-row">
                <div class="rtb-form-group">
                    <label for="rtb-full-name"><?php _e('Full Name', 'restaurant-table-booking'); ?> *</label>
                    <input type="text" id="rtb-full-name" name="full_name" required>
                </div>

                <div class="rtb-form-group">
                    <label for="rtb-email"><?php _e('Email Address', 'restaurant-table-booking'); ?> *</label>
                    <input type="email" id="rtb-email" name="email" required>
                </div>
            </div>

            <div class="rtb-form-row">
                <div class="rtb-form-group">
                    <label for="rtb-phone"><?php _e('Phone Number', 'restaurant-table-booking'); ?> *</label>
                    <input type="tel" id="rtb-phone" name="phone" required>
                </div>

                <div class="rtb-form-group">
					<label for="rtb-guests"><?php _e('Number of Guests', 'restaurant-table-booking'); ?></label>
					<select id="rtb-guests" name="guests">
						<?php for ($i = 1; $i <= 10; $i++): ?>
							<option value="<?php echo $i; ?>" <?php selected($i, 2); ?>>
								<?php echo $i == 10 ? '10+' : $i; ?> 
								<?php echo $i === 1 ? __('Guest', 'restaurant-table-booking') : __('Guests', 'restaurant-table-booking'); ?>
							</option>
						<?php endfor; ?>
					</select>
				</div>
            </div>

            <div class="rtb-form-row">
                <div class="rtb-form-group">
                    <label for="rtb-date"><?php _e('Date', 'restaurant-table-booking'); ?> *</label>
                    <input type="date" id="rtb-date" name="date" min="<?php echo date('Y-m-d'); ?>" value="<?php echo date('Y-m-d'); ?>" required>
                </div>

                <div class="rtb-form-group">
                    <label for="rtb-time"><?php _e('Time', 'restaurant-table-booking'); ?> *</label>
                    <select id="rtb-time" name="time" required>
                        <option value=""><?php _e('Select a time', 'restaurant-table-booking'); ?></option>
                    </select>
                </div>
            </div>

            <div class="rtb-form-group">
                <label><?php _e('Choose Your Dining Location', 'restaurant-table-booking'); ?> *</label>
                <div class="rtb-locations-grid">
                    <?php 
                    $locations = RTB_Database::get_locations();
                    foreach ($locations as $location):
                        if (!$location['enabled']) continue;
                    ?>
                        <div class="rtb-location-option">
                            <input type="radio" id="location-<?php echo esc_attr($location['id']); ?>" name="location" value="<?php echo esc_attr($location['id']); ?>" required>
                            <label for="location-<?php echo esc_attr($location['id']); ?>" class="rtb-location-card">
                                <img src="<?php echo esc_url($location['image_url']); ?>" alt="<?php echo esc_attr($location['name']); ?>">
                                <div class="rtb-location-overlay">
                                    <h3>
                                        <?php echo esc_html($location['name']); ?>
                                        <?php if (!empty($location['icon_svg'])): ?>
                                            <span class="rtb-location-icon">
                                                <img src="<?php echo esc_url($location['icon_svg']); ?>" alt="Icon" />
                                            </span>
                                        <?php endif; ?>
                                    </h3>
                                </div>
                                <div class="rtb-location-check">✓</div>
                            </label>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="rtb-form-group">
                <label for="rtb-special-requests"><?php _e('Special Requests (Optional)', 'restaurant-table-booking'); ?></label>
                <textarea id="rtb-special-requests" name="special_requests" rows="3" placeholder="<?php _e('Any dietary restrictions, special occasions, or other requests...', 'restaurant-table-booking'); ?>"></textarea>
            </div>

            <button type="submit" class="rtb-submit-btn">
                <span class="rtb-btn-text"><?php _e('Book Now', 'restaurant-table-booking'); ?></span>
                <span class="rtb-btn-loading" style="display: none;"><?php _e('Processing...', 'restaurant-table-booking'); ?></span>
            </button>
        </form>
    </div>
</div>

<div id="rtb-booking-confirmation" class="rtb-confirmation-modal" style="display: none;">
    <div class="rtb-confirmation-content">
        <div class="rtb-confirmation-header">
            <div class="rtb-success-icon">✓</div>
            <h2><?php _e('Reservation Confirmed!', 'restaurant-table-booking'); ?></h2>
            <p><?php _e('We\'ve sent a confirmation email to your address', 'restaurant-table-booking'); ?></p>
        </div>

        <div class="rtb-confirmation-details">
            <h3><?php _e('Booking Details', 'restaurant-table-booking'); ?></h3>
            <div id="rtb-confirmation-info"></div>
        </div>

        <div class="rtb-confirmation-actions">
            <button type="button" class="rtb-btn rtb-btn-primary" onclick="rtbCloseConfirmation()"><?php _e('Make Another Reservation', 'restaurant-table-booking'); ?></button>
        </div>
    </div>
</div>