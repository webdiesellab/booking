jQuery(document).ready(function($) {
    'use strict';
    
    // Initialize admin functionality
    initAdminTabs();
    initSettingsForm();
    initLocationManagement();
    initEmailManagement();
    
    function initAdminTabs() {
        $('.nav-tab').on('click', function(e) {
            e.preventDefault();
            
            const target = $(this).attr('href');
            
            // Update active tab
            $('.nav-tab').removeClass('nav-tab-active');
            $(this).addClass('nav-tab-active');
            
            // Show corresponding content
            $('.rtb-tab-content').removeClass('rtb-tab-active');
            $(target).addClass('rtb-tab-active');
        });
    }
    
    function initSettingsForm() {
        $('#rtb-settings-form').on('submit', function(e) {
            e.preventDefault();
            saveSettings();
        });
        
        // Business hours checkbox handlers
        $('input[name*="[isOpen]"]').on('change', function() {
            const $timeInputs = $(this).closest('.rtb-day-hours').find('.rtb-time-inputs');
            if ($(this).is(':checked')) {
                $timeInputs.show();
            } else {
                $timeInputs.hide();
            }
        });
        
        // Initialize checkbox states on page load - ПРАВИЛЬНАЯ ИНИЦИАЛИЗАЦИЯ
        $('input[name*="[isOpen]"]').each(function() {
            const $checkbox = $(this);
            const $timeInputs = $checkbox.closest('.rtb-day-hours').find('.rtb-time-inputs');
            
            // Показываем/скрываем поля времени в зависимости от состояния чекбокса
            if ($checkbox.is(':checked')) {
                $timeInputs.show();
            } else {
                $timeInputs.hide();
            }
        });
    }
    
    function saveSettings() {
        const $form = $('#rtb-settings-form');
        const $submitBtn = $form.find('button[type="submit"]');
        
        // Show loading state
        $submitBtn.prop('disabled', true);
        $submitBtn.html('<span class="rtb-spinner"></span>Saving...');
        
        const formData = {
            action: 'rtb_save_settings',
            nonce: rtb_ajax.nonce
        };
        
        // Collect business hours - ИСПРАВЛЕННАЯ ЛОГИКА
        const businessHours = {};
        const days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        
        days.forEach(function(day) {
            const $dayContainer = $(`input[name="business_hours[${day}][isOpen]"]`).closest('.rtb-day-hours');
            const isOpen = $(`input[name="business_hours[${day}][isOpen]"]`).is(':checked');
            const openTime = $(`input[name="business_hours[${day}][openTime]"]`).val();
            const closeTime = $(`input[name="business_hours[${day}][closeTime]"]`).val();
            
            businessHours[day] = {
                isOpen: isOpen,
                openTime: openTime || '09:00',
                closeTime: closeTime || '17:00'
            };
            
            console.log(`${day}: isOpen=${isOpen}, openTime=${openTime}, closeTime=${closeTime}`);
        });
        
        formData.business_hours = businessHours;
        formData.time_interval = $('select[name="time_interval"]').val();
        formData.time_format = $('select[name="time_format"]').val();
        formData.confirmation_enabled = $('input[name="confirmation_enabled"]').is(':checked') ? '1' : '0';
        
        // Collect notification emails
        const emails = [];
        $('input[name="notification_emails[]"]').each(function() {
            const email = $(this).val().trim();
            if (email) {
                emails.push(email);
            }
        });
        formData.notification_emails = emails;
        
        console.log('Saving form data:', formData);
        
        $.ajax({
            url: rtb_ajax.ajax_url,
            type: 'POST',
            data: formData,
            success: function(response) {
                console.log('Save response:', response);
                if (response.success) {
                    showMessage(response.data, 'success');
                } else {
                    showMessage(response.data || 'Error saving settings.', 'error');
                }
            },
            error: function(xhr, status, error) {
                console.error('Save error:', status, error);
                showMessage('Network error. Please try again.', 'error');
            },
            complete: function() {
                $submitBtn.prop('disabled', false);
                $submitBtn.html('Save Settings');
            }
        });
    }
    
    function initLocationManagement() {
        // Save location
        $(document).on('click', '.rtb-save-location', function() {
            const $item = $(this).closest('.rtb-location-item');
            const locationId = $item.data('location-id');
            const name = $item.find('.rtb-location-name').val();
            const imageUrl = $item.find('.rtb-location-image-url').val();
            const enabled = $item.find('.rtb-location-enabled').is(':checked');
            
            if (!name.trim()) {
                alert('Location name is required.');
                return;
            }
            
            const $btn = $(this);
            $btn.prop('disabled', true).html('<span class="rtb-spinner"></span>Saving...');
            
            $.ajax({
                url: rtb_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'rtb_save_location',
                    nonce: rtb_ajax.nonce,
                    location_id: locationId,
                    name: name,
                    image_url: imageUrl,
                    enabled: enabled ? '1' : '0'
                },
                success: function(response) {
                    if (response.success) {
                        showMessage(response.data, 'success');
                        // Update image if URL changed
                        if (imageUrl) {
                            $item.find('.rtb-location-image img').attr('src', imageUrl);
                        }
                    } else {
                        showMessage(response.data || 'Error saving location.', 'error');
                    }
                },
                error: function() {
                    showMessage('Network error. Please try again.', 'error');
                },
                complete: function() {
                    $btn.prop('disabled', false).html('Save');
                }
            });
        });
        
        // Delete location
        $(document).on('click', '.rtb-delete-location', function() {
            if (!confirm('Are you sure you want to delete this location?')) {
                return;
            }
            
            const $item = $(this).closest('.rtb-location-item');
            const locationId = $item.data('location-id');
            const $btn = $(this);
            
            $btn.prop('disabled', true).html('<span class="rtb-spinner"></span>Deleting...');
            
            $.ajax({
                url: rtb_ajax.ajax_url,
                type: 'POST',
                data: {
                    action: 'rtb_delete_location',
                    nonce: rtb_ajax.nonce,
                    location_id: locationId
                },
                success: function(response) {
                    if (response.success) {
                        $item.fadeOut(300, function() {
                            $(this).remove();
                        });
                        showMessage(response.data, 'success');
                    } else {
                        showMessage(response.data || 'Error deleting location.', 'error');
                        $btn.prop('disabled', false).html('Delete');
                    }
                },
                error: function() {
                    showMessage('Network error. Please try again.', 'error');
                    $btn.prop('disabled', false).html('Delete');
                }
            });
        });
        
        // Add new location
        $('#rtb-add-location').on('click', function() {
            const newLocationId = 'location-' + Date.now();
            const locationHTML = `
                <div class="rtb-location-item" data-location-id="${newLocationId}">
                    <div class="rtb-location-image">
                        <img src="https://via.placeholder.com/80x80?text=Image" alt="New Location">
                    </div>
                    <div class="rtb-location-details">
                        <input type="text" class="rtb-location-name" value="" placeholder="Location Name">
                        <input type="url" class="rtb-location-image-url" value="" placeholder="Image URL">
                        <label class="rtb-checkbox">
                            <input type="checkbox" class="rtb-location-enabled" checked>
                            Enabled
                        </label>
                    </div>
                    <div class="rtb-location-actions">
                        <button type="button" class="button rtb-save-location">Save</button>
                        <button type="button" class="button rtb-delete-location">Delete</button>
                    </div>
                </div>
            `;
            
            $('#rtb-locations-list').append(locationHTML);
        });
    }
    
    function initEmailManagement() {
        // Add email
        $('#rtb-add-email').on('click', function() {
            const emailHTML = `
                <div class="rtb-email-input">
                    <input type="email" name="notification_emails[]" value="">
                    <button type="button" class="button rtb-remove-email">Remove</button>
                </div>
            `;
            
            $('#rtb-notification-emails').append(emailHTML);
        });
        
        // Remove email
        $(document).on('click', '.rtb-remove-email', function() {
            $(this).closest('.rtb-email-input').remove();
        });
    }
    
    function showMessage(message, type) {
        const messageClass = type === 'success' ? 'rtb-success' : 'rtb-error';
        const $message = $(`<div class="rtb-message ${messageClass}">${message}</div>`);
        
        // Remove existing messages
        $('.rtb-message').remove();
        
        // Add new message
        $('.wrap h1').after($message);
        
        // Auto-hide after 5 seconds
        setTimeout(function() {
            $message.fadeOut(300, function() {
                $(this).remove();
            });
        }, 5000);
        
        // Scroll to top
        $('html, body').animate({ scrollTop: 0 }, 300);
    }
});