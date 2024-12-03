<?php
/**
 * Plugin Name: Booking Confirmation
 * Description: Hotel Booking Confirmation
 * Author: Vishnu Narayanan
 * Version: 1.3.2
 * Text Domain: hotel-booking-confirmation
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Shortcode to generate the receipt
function receipt_shortcode() {
    if (isset($_GET['booking_id']) && intval($_GET['booking_id'])) {
        $booking_id = intval($_GET['booking_id']);
        generate_receipt($booking_id);
    } else {
        echo "<p>No booking details found. Please contact support.</p>";
    }
}
add_shortcode('receipt', 'receipt_shortcode');

// Generate receipt and send email
function generate_receipt($booking_id) {
    global $wpdb;

    // Fetch booking details
    $query = $wpdb->prepare("
        SELECT 
            B.booking_id,
            CONCAT(U.first_name, ' ', U.last_name) AS full_name,
            U.phone,
            U.email, -- Fetch email address
            B.room_id,
            B.total_cost,
            B.check_in_date,
            B.check_out_date,
            B.special_requests
        FROM Bookings AS B
        JOIN Users AS U ON B.user_id = U.user_id
        WHERE B.booking_id = %d
    ", $booking_id);

    $result = $wpdb->get_row($query, ARRAY_A);

    if ($result) {
        // Display the receipt
        display_receipt(
            $result['booking_id'],
            $result['full_name'],
            $result['phone'],
            $result['total_cost'],
            $result['room_id'],
            $result['check_in_date'],
            $result['check_out_date']
        );

        // Send the receipt via email
        send_mail(
            $result['booking_id'],
            $result['full_name'],
            $result['phone'],
            $result['total_cost'],
            $result['room_id'],
            $result['check_in_date'],
            $result['check_out_date'],
            $result['email'] // Pass email address to the mail function
        );
    } else {
        echo "<p>Error retrieving booking details. Please contact support.</p>";
    }
}

function send_mail($booking_id, $name, $phone, $total_cost, $rooms, $check_in_date, $check_out_date, $email) {
    $to = $email; // Use retrieved email address
    $subject = "Booking Confirmation - Booking ID #$booking_id";

    // Inline styles and HTML for the email
    $message = "<!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <title>Booking Confirmation</title>
    </head>
    <body style='margin: 0; padding: 0; font-family: Arial, sans-serif; background-color: #f9f9f9;'>
        <div class='receipt' style='max-width: 900px; margin: 20px auto; padding: 20px; background-color: #ffffff; border-radius: 12px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); font-family: Arial, sans-serif;'>
            <!-- Logo and Hotel Information -->
            <div class='hotel-header' style='text-align: center; margin-bottom: 20px;'>
                <img src='https://example.com/path/to/your/logo.png' alt='Hotel Logo' style='width: 80px; margin-bottom: 10px;'/>
                <h1 style='font-size: 28px; margin: 5px 0;'>Luxury Stay Hotel</h1>
                <p>123 Hotel Street, City, State, Zip Code</p>
                <p>Phone: (123) 456-7890 | Email: info@hotelname.com</p>
            </div>
            
            <!-- Separator -->
            <hr style='border: none; border-top: 1px solid #333; margin: 20px 0;'/>
            
            <!-- Receipt Details -->
            <div style='padding: 20px;'>
                <p><strong>Booking ID:</strong> $booking_id</p>
                <p><strong>Name:</strong> $name</p>
                <p><strong>Phone:</strong> $phone</p>
                <p><strong>Total Cost:</strong> $$total_cost</p>
                <p><strong>Check-In Date:</strong> $check_in_date</p>
                <p><strong>Check-Out Date:</strong> $check_out_date</p>
                <p><strong>Room Numbers:</strong> $rooms</p>
            </div>
            
            <!-- Separator -->
            <hr style='border: none; border-top: 1px solid #333; margin: 20px 0;'/>
            
            <!-- Footer -->
            <div class='page-end' style='margin-top: 20px; text-align: center;'>
                <p>Note: A cleaning fee may apply if additional cleaning is required after check-out.</p>
                <p><strong>Thank you for choosing us for your upcoming stay! We look forward to welcoming you soon!</strong></p>
            </div>
        </div>
    </body>
    </html>";

    // Email headers
    $headers = [
        'Content-Type: text/html; charset=UTF-8',
        'From: Luxury Stay Hotel <no-reply@hotelname.com>'
    ];

    // Send the email
    wp_mail($to, $subject, $message, $headers);
}



function display_receipt($booking_id, $name, $phone, $total_cost, $rooms, $check_in_date, $check_out_date) {
    $css_url = plugins_url('styles/style.css', __FILE__);
    echo '<link rel="stylesheet" type="text/css" href="' . esc_url($css_url) . '">';

    $image_url = plugins_url('styles/hotel-png-11554023271eafhegd6i5.png', __FILE__);
    echo "<div class='receipt' style='position:relative ;top:7% ; max-width: 900px; margin: 20px auto; padding: 20px; background-color: #ffffff; border-radius: 12px; box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1); font-family: Arial, sans-serif;'>";

    // Logo and Hotel Information
    echo "<div class='hotel-header' style='text-align: center; margin-bottom: 20px;'>";
    echo '<img src="' . esc_url($image_url) . '" alt="Hotel Logo" style="width: 80px; margin-bottom: 10px;"/>';
    echo "<h1 style='font-size: 28px; margin: 5px 0;'>Luxury Stay Hotel</h1>";
    echo "<p>123 Hotel Street, City, State, Zip Code</p>";
    echo "<p>Phone: (123) 456-7890 | Email: info@hotelname.com</p>";
    echo "</div>";

    // Separator after hotel details
    echo "<hr style='border: none; border-top: 1px solid #333; margin: 20px 0;'>";

    // Receipt Details
    echo "<div style='padding: 20px;'>";
    echo "<p><strong>Booking ID:</strong> $booking_id</p>";
    echo "<p><strong>Name:</strong> $name</p>";
    echo "<p><strong>Phone:</strong> $phone</p>";
    echo "<p><strong>Total Cost:</strong> $$total_cost</p>";
    echo "<p><strong>Check-In Date:</strong> $check_in_date</p>";
    echo "<p><strong>Check-Out Date:</strong> $check_out_date</p>";
    echo "<p><strong>Room Numbers:</strong> $rooms </p>";
    echo "</div>";

    // Separator after room booked details
    echo "<hr style='border: none; border-top: 1px solid #333; margin: 20px 0;'>";

    // Footer with Home Button
    echo "<div class='page-end' style='margin-top: 20px; text-align: center;'>";
    echo "<p>Note: A cleaning fee may apply if additional cleaning is required after check-out.</p>";
    echo "<p><strong>Thank you for choosing us for your upcoming stay! We look forward to welcoming you soon!</strong></p>";
    echo "<a href='" . home_url() . "' style='display: inline-block; margin-top: 20px; margin-bottom: 20px; padding: 10px 20px; background-color: #007BFF; color: #fff; text-decoration: none; border-radius: 5px;'>Go Back to Home</a>";
    echo "</div>";

    echo "</div>";
}

?>
