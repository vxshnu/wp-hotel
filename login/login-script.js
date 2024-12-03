jQuery(document).ready(function ($) {
    // Show and hide forms
    $('#show-login').on('click', function () {
        $('#login-form-container').show();
        $('#registration-form-container').hide();
    });

    $('#show-registration').on('click', function () {
        $('#registration-form-container').show();
        $('#login-form-container').hide();
    });

    // Handle Registration
    $('#registration_submit_button').on('click', function () {
        const data = {
            action: 'login_registration_handler',
            email: $('input[name="email"]').val(),
            password: $('input[name="password1"]').val(),
            first_name: $('input[name="first_name"]').val(),
            last_name: $('input[name="last_name"]').val(),
            phone: $('input[name="phone"]').val(),
            address: $('textarea[name="address"]').val(),
            security: login_ajax_object.nonce,
        };
        
        // Clear any existing messages
        $('#registration_message').html('');

        // AJAX POST request for registration
        $.post(login_ajax_object.ajax_url, data, function (response) {
            const message = response.success ? response.data.message : response.data.message;
            $('#registration_message').css('color', response.success ? 'green' : 'red').html(message);
        }).fail(function () {
            $('#registration_message').css('color', 'red').html('An unexpected error occurred. Please try again.');
        });
    });

    // Handle Login
    $('#login_submit_button').on('click', function () {
        const data = {
            action: 'login_login_handler',
            username: $('input[name="username"]').val(),
            password: $('input[name="password"]').val(),
            security: login_ajax_object.nonce,
        };
        console.log(data);
        // Clear any existing messages
        $('#login_message').html('');

        // AJAX POST request for login
        $.post(login_ajax_object.ajax_url, data, function (response) {
            const message = response.success ? response.data.message : response.data.message;
            $('#login_message').css('color', response.success ? 'green' : 'red').html(message);

            // Redirect on successful login
            if (response.success && response.data.redirect_url) {
                window.location.href = response.data.redirect_url;
            }
        }).fail(function () {
            $('#login_message').css('color', 'red').html('An unexpected error occurred. Please try again.');
        });
    });
});
