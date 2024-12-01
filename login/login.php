<?php
/*
Plugin Name: Login
Description: A custom plugin for user registration, profile management, and login.
Version: 1.0
Author: Karol Monsy
*/

// Shortcode for the registration form
function login_user_registration_form() {
    ob_start();
    ?>
    <form id="registration_form" method="post">
        <input type="email" name="email" placeholder="Email" required>
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit" name="register">Register</button>
    </form>
    <?php
    return ob_get_clean();
}
add_shortcode('login_registration_form', 'login_user_registration_form');

// Registration handler
function login_user_registration_handler() {
    if (isset($_POST['register'])) {
        ob_start();
        $username = sanitize_text_field($_POST['username']);
        $email = sanitize_email($_POST['email']);
        $password = sanitize_text_field($_POST['password']);
        
        if (username_exists($username) || email_exists($email)) {
            echo "<p>Username or Email already exists!</p>";
            return ob_get_clean();
        }

        $user_id = wp_create_user($username, $password, $email);
        if (is_wp_error($user_id)) {
            echo "<p>Error: " . $user_id->get_error_message() . "</p>";
            return ob_get_clean();
        } else {
            wp_redirect('http://dashboard.local/login/');
            exit;
        }
    }
}
add_action('init', 'login_user_registration_handler');

// Shortcode for the login form
function login_user_login_form() {
    ob_start();
    ?>
    <form id="login_form" method="post">
        <input type="text" name="log" placeholder="Username or Email" required>
        <input type="password" name="pwd" placeholder="Password" required>
        <button type="submit" name="login">Login</button>
    </form>
    <?php
    return ob_get_clean();
}
add_shortcode('login_form', 'login_user_login_form');

// Login handler
function login_user_login_handler() {
    if (isset($_POST['login'])) {
        $username_or_email = sanitize_text_field($_POST['log']);
        $password = sanitize_text_field($_POST['pwd']);

        // Log the user in
        $creds = array(
            'user_login'    => $username_or_email,
            'user_password' => $password,
            'remember'      => true,
        );

        $user = wp_signon($creds, false);

        if (is_wp_error($user)) {
            echo "<p>Error: " . $user->get_error_message() . "</p>";
        } else {
            // Redirect to a specific page after successful login
            wp_redirect(home_url()); // Redirect to homepage or desired page
            exit;
        }
    }
}
add_action('init', 'login_user_login_handler');

// Profile form shortcode with editable username field
function login_user_profile_form() {
    if (is_user_logged_in()) {
        $current_user = wp_get_current_user();
        ob_start();
        ?>
        <div class="login-form-container">
            <form id="login_profile_form" method="post">
                <?php wp_nonce_field('login_user_profile', 'login_user_profile_nonce'); ?>
                
                <label for="username">Username</label>
                <input type="text" name="username" value="<?php echo esc_attr($current_user->user_login); ?>" required>

                <label for="email">Email</label>
                <input type="email" name="email" value="<?php echo esc_attr($current_user->user_email); ?>" required>

                <button type="submit" name="update_profile">Update Profile</button>
            </form>
        </div>
        <?php
        return ob_get_clean();
    } else {
        return "<p>Please log in to view your profile.</p>";
    }
}
add_shortcode('login_profile_form', 'login_user_profile_form');

// Profile update handler
function login_user_profile_update() {
    if (isset($_POST['update_profile']) && is_user_logged_in() && wp_verify_nonce($_POST['login_user_profile_nonce'], 'login_user_profile')) {
        $current_user = wp_get_current_user();
        $email = sanitize_email($_POST['email']);
        $username = sanitize_text_field($_POST['username']);

        // Update user data
        $userdata = array(
            'ID' => $current_user->ID,
            'user_email' => $email,
        );

        // Only allow username change if it's a new, non-existing one
        if ($username && !username_exists($username)) {
            $userdata['user_login'] = $username;
        }

        $user_id = wp_update_user($userdata);

        if (is_wp_error($user_id)) {
            echo "<p>Error: " . $user_id->get_error_message() . "</p>";
        } else {
            echo "<p>Profile updated successfully!</p>";
        }
    }
}
add_action('wp_head', 'login_user_profile_update');

function login_enqueue_styles() {
    // Enqueue the custom CSS file
    wp_enqueue_style('login-custom-style', plugins_url('login-style.css', __FILE__));
}
add_action('wp_enqueue_scripts', 'login_enqueue_styles');


add_filter('show_admin_bar', '__return_false');
