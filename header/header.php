<?php
/*
Plugin Name: Full-Width Header for Sunset View Resort
Description: Adds a custom full-width header bar with navigation links for Sunset View Resort using a shortcode. The header is fixed at the top of the page.
Version: 1.7
Author: Your Name
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Start PHP session for user login checks
if (!session_id()) {
    session_start();
}

// Enqueue CSS for the header
function enqueue_full_width_header_styles() {
    wp_enqueue_style('full-width-header-style', plugins_url('full-width-header-style.css', __FILE__));
}
add_action('wp_enqueue_scripts', 'enqueue_full_width_header_styles');

// Header Functionality
function render_full_width_header() {
    ob_start();
    $is_logged_in = isset($_SESSION['user_id']);
    ?>
    <header class="custom-header">
        <div class="header-container">
            <div class="header-logo">
                <h1>Sunset View Resort</h1>
            </div>
            <nav class="header-nav">
                <a href="<?php echo home_url(); ?>">Home</a>
                <a href="<?php echo esc_url(home_url($is_logged_in ? '/booking-management/' : '/login/')); ?>">
                    <?php echo $is_logged_in ? 'Profile' : 'Login'; ?>
                </a>
                <?php if ($is_logged_in): ?>
                    <a href="<?php echo esc_url(home_url('/room-listing/')); ?>">Rooms</a>
                <?php endif; ?>
            </nav>
        </div>
    </header>
    <div style="margin-top: 60px;"></div> <!-- Spacer to prevent content overlap -->
    <?php
    return ob_get_clean();
}

// Shortcode for Custom Header
function full_width_header_shortcode() {
    return render_full_width_header();
}
add_shortcode('custom_header', 'full_width_header_shortcode');

// CSS for the full-width header
function create_full_width_header_css() {
    ?>
    <style>
        .custom-header {
            position: fixed;
            top: 0;
            left: 0;
            min-width: 100%;
            background-color: #000;
            color: #fff;
            padding: 10px 0;
            z-index: 1000;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.2);
        }
        .header-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        .header-logo h1 {
            margin: 0;
            font-size: 24px;
            font-weight: bold;
            color: #f0a500; /* Updated color for the hotel name */
        }
        .header-nav a {
            color: #fff;
            text-decoration: none;
            margin: 0 15px;
            font-size: 18px;
            transition: color 0.3s;
        }
        .header-nav a:hover {
            color: #f0a500;
        }
        body {
            margin: 0;
            font-family: Arial, sans-serif;
        }
    </style>
    <?php
}
add_action('wp_head', 'create_full_width_header_css');
