jQuery(document).ready(function ($) {
    // Show Modify Form
    $('.modify-booking').on('click', function () {
        $(this).closest('tr').next('.modify-row').toggle();
    });

    // Save Modification
    $('.save-modification').on('click', function () {
        const row = $(this).closest('tr').prev();
        const bookingId = row.data('booking-id');
        const specialRequests = $(this).closest('form').find('textarea[name="special_requests"]').val();

        $.ajax({
            url: booking_ajax_object.ajax_url,
            method: 'POST',
            data: {
                action: 'modify_booking',
                security: booking_ajax_object.nonce,
                booking_id: bookingId,
                special_requests: specialRequests,
            },
            success(response) {
                alert(response.data.message);
                location.reload();
            },
            error(response) {
                alert(response.responseJSON ? response.responseJSON.data.message : 'An error occurred.');
            },
        });
    });

    // Cancel Booking
    $('.cancel-booking').on('click', function () {
        const row = $(this).closest('tr');
        const bookingId = row.data('booking-id');
        const userEmail = $(this).data('user-email');

        if (confirm('Are you sure you want to cancel this booking?')) {
            $.ajax({
                url: booking_ajax_object.ajax_url,
                method: 'POST',
                data: {
                    action: 'cancel_booking',
                    security: booking_ajax_object.nonce,
                    booking_id: bookingId,
                    user_email: userEmail,
                },
                success(response) {
                    alert(response.data.message);
                    location.reload();
                },
                error(response) {
                    alert(response.responseJSON ? response.responseJSON.data.message : 'An error occurred.');
                },
            });
        }
    });
});
