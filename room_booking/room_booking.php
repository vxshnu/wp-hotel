<?php
/**
 * Plugin Name: Room Booking
 * Description: Hotel Room Booking with Payment Integration
 * Author: Nikita Anna Ajith
 * Version: 1.5.0
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Display Room Booking Form
function display_room_booking_form() {
    if (!is_user_logged_in()) {
        return "<p class='error-message'>You must <a href='" . wp_login_url() . "'>log in</a> to book a room.</p>";
    }

    global $wpdb;

    $room_id = isset($_GET['room_id']) ? intval($_GET['room_id']) : null;

    if (!$room_id) {
        return "<p class='error-message'>No room selected for booking.</p>";
    }

    $room_details = $wpdb->get_row($wpdb->prepare("SELECT * FROM Rooms WHERE room_id = %d", $room_id), ARRAY_A);

    if (!$room_details) {
        return "<p class='error-message'>Room not found.</p>";
    }

    $output = "
        <div class='room-booking-container'>
            <h2>Book Your Stay at {$room_details['room_type']}</h2>
            <img src='{$room_details['image_url']}' alt='{$room_details['room_type']}' class='room-image'>
            <p><strong>Description:</strong> {$room_details['description']}</p>
            <p><strong>Beds:</strong> {$room_details['beds']}</p>
            <p><strong>Amenities:</strong> {$room_details['amenities']}</p>
            <p><strong>Price per Night:</strong> \${$room_details['price_per_night']}</p>
            <p><strong>Max Occupancy:</strong> {$room_details['max_occupancy']} guests</p>
            <form class='details-form'>
                <div class='form-group'>
                    <label>Check-in Date:</label>
                    <input type='date' name='check_in' required>
                </div>
                <div class='form-group'>
                    <label>Check-out Date:</label>
                    <input type='date' name='check_out' required>
                </div>
                <div class='form-group'>
                    <label>Number of Guests:</label>
                    <input type='number' name='guests' min='1' max='{$room_details['max_occupancy']}' required>
                </div>
                <div class='form-group'>
                    <label>Special Requests:</label>
                    <textarea name='special_requests' placeholder='Enter any special requirements'></textarea>
                </div>
                <input type='hidden' name='room_id' value='{$room_id}'>
                <input type='hidden' name='price_per_night' value='{$room_details['price_per_night']}'>
                <input type='hidden' name='max_occupancy' value='{$room_details['max_occupancy']}'>
                <button type='submit' class='submit-booking'>Confirm Booking</button>
            </form>
            <div class='ajax-response'></div>
        </div>
    ";

    return $output;
}

// AJAX Handler for Booking
function handle_room_booking_ajax() {
    global $wpdb;

    if (!session_id()) {
        session_start();
    }

    $user_id = isset($_SESSION['user_id']) ? intval($_SESSION['user_id']) : 0;

    if ($user_id === 0) {
        wp_send_json_error(['message' => 'You must log in to make a booking.']);
        return;
    }

    $check_in = sanitize_text_field($_POST['check_in']);
    $check_out = sanitize_text_field($_POST['check_out']);
    $guests = intval($_POST['guests']);
    $special_requests = sanitize_textarea_field($_POST['special_requests']);
    $room_id = intval($_POST['room_id']);
    $price_per_night = floatval($_POST['price_per_night']);
    $max_occupancy = intval($_POST['max_occupancy']);

    if ($guests > $max_occupancy) {
        wp_send_json_error(['message' => 'The number of guests exceeds the maximum occupancy of the room.']);
        return;
    }

    $days = (strtotime($check_out) - strtotime($check_in)) / 86400;
    if ($days <= 0) {
        wp_send_json_error(['message' => 'Invalid check-in and check-out dates.']);
    }

    $room_charges = $price_per_night * $days;
    $tax = $room_charges * 0.1;
    $additional_fees = 50;
    $total_cost = $room_charges + $tax + $additional_fees;

    $wpdb->insert(
        'Bookings',
        [
            'user_id' => $user_id,
            'room_id' => $room_id,
            'check_in_date' => $check_in,
            'check_out_date' => $check_out,
            'total_cost' => $total_cost,
            'status' => 'confirmed',
            'special_requests' => $special_requests,
            'created_at' => current_time('mysql'),
            'updated_at' => current_time('mysql'),
        ],
        ['%d', '%d', '%s', '%s', '%f', '%s', '%s', '%s']
    );

    $booking_id = $wpdb->insert_id;

    $redirect_url = add_query_arg([
        'booking_id' => $booking_id,
    ], home_url('/payment-gateway/'));

    wp_send_json_success(['redirect_url' => $redirect_url]);
}
add_action('wp_ajax_room_booking', 'handle_room_booking_ajax');
add_action('wp_ajax_nopriv_room_booking', 'handle_room_booking_ajax');

// Enqueue Scripts
function enqueue_custom_scripts() {
    wp_enqueue_script('jquery');
    wp_enqueue_script('room-booking-ajax', plugin_dir_url(__FILE__) . 'room-booking-ajax.js', ['jquery'], null, true);
    wp_localize_script('room-booking-ajax', 'roomBookingAjax', [
        'ajax_url' => admin_url('admin-ajax.php'),
    ]);

    wp_enqueue_style('room-booking-style', plugin_dir_url(__FILE__) . 'room-booking-style.css');
}
add_action('wp_enqueue_scripts', 'enqueue_custom_scripts');

// Register Shortcode
function register_room_booking_shortcodes() {
    add_shortcode('room_booking_form', 'display_room_booking_form');
}
add_action('init', 'register_room_booking_shortcodes');
