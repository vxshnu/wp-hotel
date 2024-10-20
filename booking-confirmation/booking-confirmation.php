<?php
/**
 * Plugin Name: Booking Confirmation
 * Description: Hotel Booking Confirmation
 * Author: Vishnu Narayanan
 * Version: 1.0.0
 * Text Domain: hotel-booking-confirmation
 */

if( !defined('ABSPATH')) {
    exit;
}

add_action('after_payment','generate_receipt',1,3);

function generate_receipt($bookingid)
{
    add_shortcode('receipt','receipt_display');
    echo 'booking id is:'.$bookingid;
    global $wpdb;
    $query = $wpdb->prepare("SELECT full_name,email,phno,roomid FROM hotel_booking WHERE booking_id = %d", $bookingid);
    $option_value = $wpdb->get_var($query);
    $result = $wpdb->get_row($query, ARRAY_A);
    
    // Check if a result was found
    if ($result) {
        // Display all values
        echo 'Full Name: ' . esc_html($result['full_name']) . '<br>';
        echo 'Email: ' . esc_html($result['email']) . '<br>';
        echo 'Phone Number: ' . esc_html($result['phno']) . '<br>';
        echo 'Room ID: ' . esc_html($result['roomid']) . '<br>';
    } else {
        echo 'No booking found for this ID.';
    }
}