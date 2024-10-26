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

global $last_booking_id;

//Hook to capture the booking ID after payment
add_action('after_payment', function($bookingid) use (&$last_booking_id) {
    $last_booking_id = $bookingid; //Store the booking ID globally
});

//Shortcode to generate the receipt
function receipt_shortcode() {
    global $last_booking_id;
    if (is_page('receipt')) {
        generate_receipt($last_booking_id);
    } 
}

add_shortcode('receipt', 'receipt_shortcode');

function generate_receipt($bookingid)
{
    global $wpdb;
    if ($bookingid) {
        $query = $wpdb->prepare("SELECT full_name,  phno, amount, total_rooms, from_date, till_date FROM hotel_booking WHERE booking_id = %d", $bookingid);
        $result = $wpdb->get_row($query, ARRAY_A);
        if ($result) {
            display_receipt($bookingid, $result['full_name'], $result['phno'], $result['amount'], $result['total_rooms'], $result['from_date'], $result['till_date']);
        }
    }
}


function fetch_reserved_rooms($bookingid) {
    global $wpdb;
    $query = $wpdb->prepare("SELECT roomid FROM reserved_rooms WHERE bookingid = %d", $bookingid);
    $result = $wpdb->get_col($query); 
    return $result; 
}


function display_receipt($bookingid, $name, $phno, $amount, $rooms, $from, $to) {
    $css_url = plugins_url('styles/style.css', __FILE__);

    echo '<link rel="stylesheet" type="text/css" href="' . esc_url($css_url) . '">';
    echo "<div class='receipt'>"; 
    $image_url = plugins_url('styles/hotel-png-11554023271eafhegd6i5.png', __FILE__);
    echo '<img src="' . esc_url($image_url) . '" alt="Your Image" id="hotel-logo" style="margin-bottom: 20px;"/>';
    
    // Hotel Information Section
    echo "<div class='hotel-info'>";
    echo "<h3 style='margin: 0; font-size: 24px;'>Hotel Name</h3>";
    echo "<p style='margin: 5px 0;'>123 Hotel Street</p>";
    echo "<p style='margin: 5px 0;'>City, State, Zip Code</p>";
    echo "<p style='margin: 5px 0;'>Phone: (123) 456-7890</p>";
    echo "<p style='margin: 5px 0;'>Email: info@hotelname.com</p>";
    echo "</div>";
    
    // Receipt information
    echo "<div class='reservation-details'>";
    echo "<h2 style='text-align: center;'>RECEIPT</h2>"; 
    echo "<div style='text-align: left; padding-left: 20px; font-size: 18px;'>";
    echo "<p><strong>Booking ID:</strong> {$bookingid}</p>";
    echo "<p style='display:inline; margin-right: 40%;'><strong>Name:</strong> {$name} </p>";
    echo "<p id='phno'><strong>Phone Number:</strong> <span>{$phno}</span></p>";
    
    
    echo "<p><strong>Total number of rooms booked:</strong> {$rooms}</p>";
    echo "<p><strong>From Date:</strong> {$from}</p>"; 
    echo "<p><strong>Till Date:</strong> {$to}</p>";
    $reserved_rooms = fetch_reserved_rooms($bookingid);
    echo "<p><strong>Room Number:</strong> " . implode(", ", $reserved_rooms) . "</p>";
    echo "</div>";
    echo "<p class='page-mid'><strong>Total Amount Paid:</strong> \${$amount}</p>";
    echo "<p class='page-mid'><strong>Payment Method:</strong> Online</p>";
    echo "</div>";
    echo "<div class='page-end'>";
    
    echo "<p>Note: A cleaning fee may apply if additional cleaning is required after check-out.</p>";
    echo "<p><strong>Thank you for choosing us for your upcoming stay! We look forward to welcoming you soon!</strong></p>";
    echo "</div>";
    echo "</div>";
}
?>
