jQuery(document).ready(function ($) {
    $('#login_submit_button').on('click', function () {
        const data = {
            action: 'login_user_login_handler',
            username: $('input[name="username"]').val(),
            password: $('input[name="password"]').val(),
            login_user_login_nonce: $('input[name="login_user_login_nonce"]').val()
        };
        
        $.post(login_ajax_object.ajax_url, data, function (response) {
            $('#login_message').html(response);
        });
    });
});
