jQuery(document).ready(function($) {
    'use strict';
    
    // Initialize booking form
    initBookingForm();
    
    function initBookingForm() {
        const $form = $('#rtb-booking-form-element');
        const $dateInput = $('#rtb-date');
        const $timeSelect = $('#rtb-time');
        const $locationInputs = $('input[name="location"]');
        
        // Load initial time slots for today (СРАЗУ при загрузке)
        if ($dateInput.val()) {
            console.log('Loading initial time slots for:', $dateInput.val());
            loadTimeSlots($dateInput.val());
        }
        
        // Date change handler - УБИРАЕМ ЗАВИСИМОСТЬ ОТ ЛОКАЦИИ
        $dateInput.on('change', function() {
            const date = $(this).val();
            console.log('Date changed to:', date);
            
            if (date) {
                loadTimeSlots(date);
            } else {
                $timeSelect.html('<option value="">' + rtb_translations.select_time + '</option>');
            }
        });
        
        // Location change handler - НЕ ПЕРЕЗАГРУЖАЕМ СЛОТЫ
        $locationInputs.on('change', function() {
            console.log('Location changed to:', getSelectedLocation());
            // ВАЖНО: Никакой загрузки временных слотов здесь быть не должно!
        });
        
        // Form submission
        $form.on('submit', function(e) {
            e.preventDefault();
            submitBooking();
        });
        
        // Business hours checkbox handlers for admin
        $('.rtb-day-hours input[type="checkbox"]').on('change', function() {
            const $timeInputs = $(this).closest('.rtb-day-hours').find('.rtb-time-inputs');
            if ($(this).is(':checked')) {
                $timeInputs.show();
            } else {
                $timeInputs.hide();
            }
        });
    }
    
    function getSelectedLocation() {
        return $('input[name="location"]:checked').val() || '';
    }
    
    function loadTimeSlots(date) {
        const $timeSelect = $('#rtb-time');
        
        console.log('Loading time slots for date:', date);
        
        // Show loading state
        $timeSelect.html('<option value="">' + rtb_translations.loading + '</option>');
        $timeSelect.prop('disabled', true);
        
        $.ajax({
            url: rtb_ajax.ajax_url,
            type: 'POST',
            data: {
                action: 'rtb_get_available_times',
                nonce: rtb_ajax.nonce,
                date: date
                // НЕ ПЕРЕДАЕМ ЛОКАЦИЮ - слоты зависят только от расписания
            },
            success: function(response) {
                console.log('Time slots response:', response);
                $timeSelect.prop('disabled', false);
                
                if (response.success && response.data.length > 0) {
                    let options = '<option value="">' + rtb_translations.select_time + '</option>';
                    
                    response.data.forEach(function(time) {
                        console.log('Processing time slot:', time);
                        const timeFormatted = formatTime(time);
                        console.log('Final formatted time for option:', timeFormatted);
                        options += '<option value="' + time + '">' + timeFormatted + '</option>';
                    });
                    
                    $timeSelect.html(options);
                } else {
                    // No available times - show "Reservation unavailable" message
                    $timeSelect.html('<option value="">' + rtb_translations.reservation_unavailable + '</option>');
                    $timeSelect.prop('disabled', true);
                }
            },
            error: function(xhr, status, error) {
                console.error('Time slots error:', status, error);
                $timeSelect.prop('disabled', false);
                $timeSelect.html('<option value="">' + rtb_translations.error_loading + '</option>');
            }
        });
    }
    
    function formatTime(time) {
        // ПРИНУДИТЕЛЬНО 24-часовой формат - никаких исключений!
        console.log('Formatting time:', time);
        const [hours, minutes] = time.split(':').map(Number);
        const formatted = `${hours.toString().padStart(2, '0')}:${minutes.toString().padStart(2, '0')}`;
        console.log('Formatted time result:', formatted);
        return formatted;
    }
    
    function submitBooking() {
        const $form = $('#rtb-booking-form-element');
        const $submitBtn = $form.find('.rtb-submit-btn');
        const $btnText = $submitBtn.find('.rtb-btn-text');
        const $btnLoading = $submitBtn.find('.rtb-btn-loading');
        
        // Clear previous errors
        $('.rtb-form-group').removeClass('rtb-error');
        $('.rtb-error-message').remove();
        
        // Show loading state
        $form.addClass('rtb-loading');
        $submitBtn.prop('disabled', true);
        $btnText.hide();
        $btnLoading.show();
        
        const formData = {
            action: 'rtb_submit_booking',
            nonce: rtb_ajax.nonce,
            full_name: $('#rtb-full-name').val(),
            email: $('#rtb-email').val(),
            phone: $('#rtb-phone').val(),
            guests: $('#rtb-guests').val(),
            date: $('#rtb-date').val(),
            time: $('#rtb-time').val(),
            location: getSelectedLocation(),
            special_requests: $('#rtb-special-requests').val()
        };
        
        $.ajax({
            url: rtb_ajax.ajax_url,
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    showBookingConfirmation(response.data.booking_data);
                    $form[0].reset();
                } else {
                    showError(response.data || rtb_translations.error_occurred);
                }
            },
            error: function() {
                showError(rtb_translations.network_error);
            },
            complete: function() {
                // Hide loading state
                $form.removeClass('rtb-loading');
                $submitBtn.prop('disabled', false);
                $btnText.show();
                $btnLoading.hide();
            }
        });
    }
    
    function showError(message) {
        // You can customize this to show errors in a better way
        alert(message);
    }
    
    function showBookingConfirmation(bookingData) {
        const $modal = $('#rtb-booking-confirmation');
        const $info = $('#rtb-confirmation-info');
        
        // Get location name
        const locationName = $('input[name="location"]:checked').closest('.rtb-location-option').find('h3').text();
        
        // Format date and time
        const date = new Date(bookingData.date);
        const formattedDate = date.toLocaleDateString('ru-RU', {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        });
        const formattedTime = formatTime(bookingData.time);
        
        // Build confirmation details
        const confirmationHTML = `
            <div class="rtb-confirmation-info">
                <div class="rtb-detail-row">
                    <span class="rtb-detail-label">${rtb_translations.date}:</span>
                    <span class="rtb-detail-value">${formattedDate}</span>
                </div>
                <div class="rtb-detail-row">
                    <span class="rtb-detail-label">${rtb_translations.time}:</span>
                    <span class="rtb-detail-value">${formattedTime}</span>
                </div>
                <div class="rtb-detail-row">
                    <span class="rtb-detail-label">${rtb_translations.party_size}:</span>
                    <span class="rtb-detail-value">${bookingData.guests} ${bookingData.guests !== '1' ? rtb_translations.guests : rtb_translations.guest}</span>
                </div>
                <div class="rtb-detail-row">
                    <span class="rtb-detail-label">${rtb_translations.location}:</span>
                    <span class="rtb-detail-value">${locationName}</span>
                </div>
                <div class="rtb-detail-row">
                    <span class="rtb-detail-label">Email:</span>
                    <span class="rtb-detail-value">${bookingData.email}</span>
                </div>
                <div class="rtb-detail-row">
                    <span class="rtb-detail-label">${rtb_translations.phone}:</span>
                    <span class="rtb-detail-value">${bookingData.phone}</span>
                </div>
            </div>
        `;
        
        $info.html(confirmationHTML);
        $modal.show();
        
        // Prevent body scroll
        $('body').css('overflow', 'hidden');
    }
    
    // Global function to close confirmation modal
    window.rtbCloseConfirmation = function() {
        $('#rtb-booking-confirmation').hide();
        $('body').css('overflow', '');
        
        // Reload time slots for current date
        const date = $('#rtb-date').val();
        if (date) {
            loadTimeSlots(date);
        }
    };
    
    // Close modal when clicking outside
    $(document).on('click', '.rtb-confirmation-modal', function(e) {
        if (e.target === this) {
            rtbCloseConfirmation();
        }
    });
    
    // Close modal with Escape key
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape' && $('#rtb-booking-confirmation').is(':visible')) {
            rtbCloseConfirmation();
        }
    });
});