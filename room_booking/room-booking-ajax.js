jQuery(document).ready(function ($) {
    $('.details-form').on('submit', function (event) {
        event.preventDefault();
        const formData = $(this).serialize();

        $.ajax({
            url: roomBookingAjax.ajax_url,
            type: 'POST',
            data: {
                action: 'room_booking',
                ...Object.fromEntries(new URLSearchParams(formData))
            },
            success: function (response) {
                if (response.success) {
                    // Redirect to the payment gateway with booking ID
                    window.location.href = response.data.redirect_url;
                } else {
                    $('.ajax-response').html('<p style="color: red;">' + response.data.message + '</p>');
                }
            },
            error: function () {
                $('.ajax-response').html('<p style="color: red;">An error occurred. Please try again.</p>');
            }
        });
    });
});
