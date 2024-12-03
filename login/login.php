<?php
/*
Plugin Name: Custom Login and Registration
Description: Provides custom user login, registration, and role-based redirection using the custom Users table.
Version: 1.3
Author: Karol Monsy
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

session_start(); // Start PHP session for custom user management

// Enqueue Scripts and Styles
function login_enqueue_assets() {
    wp_enqueue_style('login-custom-style', plugins_url('login-style.css', __FILE__));
    wp_enqueue_script('login-custom-script', plugins_url('login-script.js', __FILE__), ['jquery'], null, true);
    wp_localize_script('login-custom-script', 'login_ajax_object', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('login_nonce'),
    ]);
}
add_action('wp_enqueue_scripts', 'login_enqueue_assets');

// Combined Login and Registration Form Shortcode
function login_combined_form() {
    if (isset($_SESSION['user_id'])) {
        return "<p>You are already logged in as " . esc_html($_SESSION['username']) . ". <a href='" . esc_url(home_url('?action=logout')) . "'>Logout</a></p>";
    }

    ob_start();
    ?>
    <div id="auth-forms" style="text-align: center; font-family: Arial, sans-serif;">
        <div>
            <button id="show-login" style="margin: 5px; padding: 10px; cursor: pointer;">Login</button>
            <button id="show-registration" style="margin: 5px; padding: 10px; cursor: pointer;">Register</button>
        </div>

        <div id="login-form-container" style="display: none; margin-top: 20px;">
            <form id="login_form">
                <input type="text" name="username" placeholder="Username or Email" required>
                <input type="password" name="password" placeholder="Password" required>
                <button type="button" id="login_submit_button">Login</button>
                <p id="login_message" style="color: red;"></p>
            </form>
        </div>

        <div id="registration-form-container" style="display: none; margin-top: 20px;">
            <form id="registration_form">
                <input type="email" name="email" placeholder="Email" required>
                <input type="password" name="password1" placeholder="Password" required>
                <input type="text" name="first_name" placeholder="First Name" required>
                <input type="text" name="last_name" placeholder="Last Name" required>
                <input type="text" name="phone" placeholder="Phone Number" required>
                <textarea name="address" placeholder="Address"></textarea>
                <button type="button" id="registration_submit_button">Register</button>
                <p id="registration_message" style="color: red;"></p>
            </form>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('login_combined_form', 'login_combined_form');

// AJAX Registration Handler
function login_registration_handler() {
    global $wpdb;
    check_ajax_referer('login_nonce', 'security');

    // Collect and sanitize input
    $email = sanitize_email($_POST['email']);
    $password = $_POST['password']; // Do not sanitize to preserve special characters
    $first_name = sanitize_text_field($_POST['first_name']);
    $last_name = sanitize_text_field($_POST['last_name']);
    $phone = sanitize_text_field($_POST['phone']);
    $address = sanitize_textarea_field($_POST['address']);

    // Check if email already exists
    $exists = $wpdb->get_var($wpdb->prepare(
        "SELECT COUNT(*) FROM Users WHERE email = %s",
        $email
    ));

    if ($exists) {
        wp_send_json_error(['message' => 'Email already exists!']);
    }

    // Insert user into the database
    $result = $wpdb->insert(
        'Users',
        [
            'email' => $email,
            'password_hash' => $password, // Store plaintext password
            'first_name' => $first_name,
            'last_name' => $last_name,
            'phone' => $phone,
            'address' => $address,
            'role' => 'user', // Default role
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql'),
        ],
        [
            '%s', '%s', '%s', '%s', '%s', '%s', '%s', '%s', // Specify formats
        ]
    );

    // Log error if the insert fails
    if ($result === false) {
        error_log('Database Insert Error: ' . $wpdb->last_error); // Log the error
        wp_send_json_error(['message' => 'Registration failed!']);
    }

    // Success response
    wp_send_json_success(['message' => 'Registration successful!']);
}
add_action('wp_ajax_login_registration_handler', 'login_registration_handler');
add_action('wp_ajax_nopriv_login_registration_handler', 'login_registration_handler');



// AJAX Login Handler
function login_login_handler() {
    global $wpdb;
    check_ajax_referer('login_nonce', 'security');

    $username_or_email = sanitize_text_field($_POST['username']);
    $password = sanitize_text_field($_POST['password']);

    // Fetch user from the custom Users table
    $user = $wpdb->get_row($wpdb->prepare(
        "SELECT * FROM Users WHERE email = %s and password_hash = %s",
        $username_or_email,$password
    ));

    if (!$user) {
        wp_send_json_error(['message' => 'Invalid username or password!']);
    }

    // Set session variables
    $_SESSION['user_id'] = $user->user_id;
    $_SESSION['username'] = $user->first_name . ' ' . $user->last_name;
    $_SESSION['role'] = $user->role;

    // Redirect based on role
    if ($user->role === 'admin') {
        $redirect_url = home_url('/admin-dashboard/');
    } else {
        $redirect_url = home_url('/room-listing/');
    }

    wp_send_json_success(['message' => 'Login successful!', 'redirect_url' => $redirect_url]);
}
add_action('wp_ajax_login_login_handler', 'login_login_handler');
add_action('wp_ajax_nopriv_login_login_handler', 'login_login_handler');

// Logout Functionality
function login_logout_handler() {
    session_start();
    session_destroy();
    wp_redirect(home_url());
    exit;
}
add_action('init', function () {
    if (isset($_GET['action']) && $_GET['action'] === 'logout') {
        login_logout_handler();
    }
});

// Disable Admin Bar for Non-Admins
add_filter('show_admin_bar', '__return_false');
