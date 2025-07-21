
jQuery(document).ready(function($) {
    'use strict';

    // Initialize admin functionality
    initAdminTabs();
    initSettingsForm();
    initLocationManagement();
    initEmailManagement();
    initMediaSelector();

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

        // Update hidden inputs when location fields change
        $(document).on('input change', '.rtb-location-name, .rtb-location-image-url, .rtb-location-enabled', function() {
            updateLocationHiddenInputs();
        });
        
        // Update hidden inputs when SVG changes
        $(document).on('change', '.rtb-location-icon-svg', function() {
            updateLocationHiddenInputs();
        });
        
        // Обновляем скрытые поля при загрузке страницы
        updateLocationHiddenInputs();

        // Принудительно показываем все поля времени
        $(document).ready(function() {
            $('.rtb-time-inputs').css({
                'display': 'flex !important',
                'visibility': 'visible !important',
                'opacity': '1 !important'
            });
            $('.rtb-time-inputs').show();
            $('.rtb-time-inputs').addClass('rtb-time-inputs-visible');
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

        // Collect business hours
        const businessHours = {};
        const days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        
        console.log('Collecting business hours data...');
        
        days.forEach(function(day) {
            const $openTime = $(`input[name="business_hours[${day}][openTime]"]`);
            const $closeTime = $(`input[name="business_hours[${day}][closeTime]"]`);
            
            const openTime = $openTime.val() || '09:00';
            const closeTime = $closeTime.val() || '17:00';

            businessHours[day] = {
                isOpen: true,
                openTime: openTime,
                closeTime: closeTime
            };

            console.log(`${day}: openTime=${openTime}, closeTime=${closeTime}`);
        });

        formData.business_hours = businessHours;
        formData.time_interval = $('select[name="time_interval"]').val() || '30';
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

        // Collect locations data - ИСПРАВЛЕНО
        const locationsData = {};
        $('.rtb-location-item').each(function() {
            const $item = $(this);
            const locationId = $item.data('location-id');
            const name = $item.find('.rtb-location-name').val();
            const iconSvg = $item.find('.rtb-location-icon-svg').val();
            const imageUrl = $item.find('.rtb-location-image-url').val();
            const enabled = $item.find('.rtb-location-enabled').is(':checked');
            
            console.log(`Location ${locationId}:`, {
                name: name,
                iconSvg: iconSvg,
                imageUrl: imageUrl,
                enabled: enabled
            });
            
            if (name && name.trim()) {
                locationsData[locationId] = {
                    name: name,
                    icon_svg: iconSvg,
                    image_url: imageUrl,
                    enabled: enabled
                };
            }
        });
        
        formData.locations = locationsData;
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
            const iconSvg = $item.find('.rtb-location-icon-svg').val();
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
                    icon_svg: iconSvg,
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
                        // Update hidden inputs
                        updateLocationHiddenInputs();
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
                        <div class="rtb-svg-selector">
                            <input type="hidden" class="rtb-location-icon-svg" value="">
                            <div class="rtb-svg-preview">
                                <span class="rtb-no-icon">Нет иконки</span>
                            </div>
                            <button type="button" class="button rtb-select-svg">Выбрать SVG иконку</button>
                            <button type="button" class="button rtb-remove-svg" style="display:none;">Удалить</button>
                        </div>
                        <input type="url" class="rtb-location-image-url" value="" placeholder="Image URL">
                        <label class="rtb-checkbox">
                            <input type="checkbox" class="rtb-location-enabled" checked>
                            Enabled
                        </label>
                        <input type="hidden" name="locations[${newLocationId}][name]" value="">
                        <input type="hidden" name="locations[${newLocationId}][icon_svg]" value="">
                        <input type="hidden" name="locations[${newLocationId}][image_url]" value="">
                        <input type="hidden" name="locations[${newLocationId}][enabled]" value="1">
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

    function initMediaSelector() {
        // Initialize WordPress Media Library
        $(document).on('click', '.rtb-select-svg', function(e) {
            e.preventDefault();
            
            const $button = $(this);
            const $container = $button.closest('.rtb-svg-selector');
            const $hiddenInput = $container.find('.rtb-location-icon-svg');
            const $preview = $container.find('.rtb-svg-preview');
            const $removeBtn = $container.find('.rtb-remove-svg');
            
            // Create media frame
            const mediaFrame = wp.media({
                title: 'Выберите SVG иконку',
                button: {
                    text: 'Выбрать'
                },
                multiple: false,
                library: {
                    type: 'image/svg+xml'
                }
            });
            
            // When an image is selected
            mediaFrame.on('select', function() {
                const attachment = mediaFrame.state().get('selection').first().toJSON();
                
                // Update hidden input
                $hiddenInput.val(attachment.url).trigger('change');
                
                // Update preview
                $preview.html('<img src="' + attachment.url + '" alt="SVG Icon" style="width: 24px; height: 24px;">');
                
                // Show remove button
                $removeBtn.show();
            });
            
            // Open media frame
            mediaFrame.open();
        });
        
        // Remove SVG icon
        $(document).on('click', '.rtb-remove-svg', function(e) {
            e.preventDefault();
            
            const $button = $(this);
            const $container = $button.closest('.rtb-svg-selector');
            const $hiddenInput = $container.find('.rtb-location-icon-svg');
            const $preview = $container.find('.rtb-svg-preview');
            
            // Clear hidden input
            $hiddenInput.val('').trigger('change');
            
            // Update preview
            $preview.html('<span class="rtb-no-icon">Нет иконки</span>');
            
            // Hide remove button
            $button.hide();
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

    function updateLocationHiddenInputs() {
        $('.rtb-location-item').each(function() {
            const $item = $(this);
            const locationId = $item.data('location-id');
            
            // Найти или создать скрытые поля
            let $nameInput = $item.find(`input[name="locations[${locationId}][name]"]`);
            let $iconInput = $item.find(`input[name="locations[${locationId}][icon_svg]"]`);
            let $imageInput = $item.find(`input[name="locations[${locationId}][image_url]"]`);
            let $enabledInput = $item.find(`input[name="locations[${locationId}][enabled]"]`);
            
            // Создать скрытые поля если их нет
            if ($nameInput.length === 0) {
                $nameInput = $('<input type="hidden" name="locations[' + locationId + '][name]">');
                $item.append($nameInput);
            }
            if ($iconInput.length === 0) {
                $iconInput = $('<input type="hidden" name="locations[' + locationId + '][icon_svg]">');
                $item.append($iconInput);
            }
            if ($imageInput.length === 0) {
                $imageInput = $('<input type="hidden" name="locations[' + locationId + '][image_url]">');
                $item.append($imageInput);
            }
            if ($enabledInput.length === 0) {
                $enabledInput = $('<input type="hidden" name="locations[' + locationId + '][enabled]">');
                $item.append($enabledInput);
            }
            
            // Обновить значения
            $nameInput.val($item.find('.rtb-location-name').val());
            $iconInput.val($item.find('.rtb-location-icon-svg').val());
            $imageInput.val($item.find('.rtb-location-image-url').val());
            $enabledInput.val($item.find('.rtb-location-enabled').is(':checked') ? '1' : '0');
        });
    }
});
