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

//hook to capture the booking ID after payment
add_action('after_payment', function($bookingid) use (&$last_booking_id) {
    $last_booking_id = $bookingid;
});

//shortcode to generate the receipt
function receipt_shortcode() {
    global $last_booking_id;
    //if page is receipt page then call the generate_receipt function
    if (is_page('receipt')) {
        generate_receipt($last_booking_id);
    } 
}

add_shortcode('receipt', 'receipt_shortcode');

//function to call the email generation and receipt displaying
function generate_receipt($bookingid)
{
    global $wpdb;
    if ($bookingid) {
        $query = $wpdb->prepare("SELECT full_name, email, phno, amount, total_rooms, from_date, till_date FROM hotel_booking WHERE booking_id = %d", $bookingid);
        $result = $wpdb->get_row($query, ARRAY_A);
        if ($result) {
            display_receipt($bookingid, $result['full_name'], $result['phno'], $result['amount'], $result['total_rooms'], $result['from_date'], $result['till_date']);
            send_mail($bookingid, $result['full_name'],$result['email'] , $result['phno'], $result['amount'], $result['total_rooms'], $result['from_date'], $result['till_date']);
        }
    }
}

//fetches the rooms that user booked
function fetch_reserved_rooms($bookingid) {
    global $wpdb;
    $query = $wpdb->prepare("SELECT roomid FROM reserved_rooms WHERE bookingid = %d", $bookingid);
    $result = $wpdb->get_col($query); 
    return $result; 
}

//sends mail to the customer with the receipt details
function send_mail($bookingid, $name, $email, $phno, $amount, $rooms, $from, $till){
    $to = $email;
    $reserved_rooms = fetch_reserved_rooms($bookingid);
    $subject = 'Hotel Booking Confirmation';
    $message = "<!DOCTYPE html>
    <html>
    <head>
        <meta charset='UTF-8'>
        <title>Hotel Receipt</title>
        <style>
            .receipt {
                border: 1px solid #ccc;
                border-radius: 5px;
                padding: 20px;
                margin: 20px auto;
                background-color: #f9f9f9;
                font-family: Arial, sans-serif;
                box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
                width: 90%;
                max-width: 800px;
            }
            .receipt h2 {
                color: #333;
                font-size: 45px;
                margin: 20px 0 15px;
                text-align: center;
                font-family: 'Franklin Gothic Medium', 'Arial Narrow', Arial, sans-serif;
            }
            .receipt p {
                color: #555;
                line-height: 1.5;
            }
            .receipt strong {
                color: #000;
            }
            #hotel-logo {
                width: 10%;
                display: block;
                margin: 0 auto 20px;
            }
            .hotel-info {
                border-bottom: 2px solid black;
                text-align: center; 
                margin-bottom: 20px;
            }
            .reservation-details {
                width: 100%;
                border-bottom: 2px solid black;
            }
            .page-end {
                border-bottom: 2px solid black;
                text-align: left; 
                padding-left: 20px; 
                font-size: 14px;
                font-family: 'Trebuchet MS', 'Lucida Sans Unicode', 'Lucida Grande', 'Lucida Sans', Arial, sans-serif;
            }
            .page-mid {
                text-align: left; 
                padding-left: 20px; 
                font-size: 18px;
            }
        </style>
    </head>
    <body>
        <div class='receipt'> 

            <div class='hotel-info'>
                <h3 style='margin: 0; font-size: 24px;'>Hotel Name</h3>
                <p style='margin: 5px 0;'>123 Hotel Street</p>
                <p style='margin: 5px 0;'>City, State, Zip Code</p>
                <p style='margin: 5px 0;'>Phone: (123) 456-7890</p>
                <p style='margin: 5px 0;'>Email: info@hotelname.com</p>
            </div>
            <div class='reservation-details'>
                <h2 style='text-align: center;'>RECEIPT</h2>
                <div style='text-align: left; padding-left: 20px; font-size: 18px;'>
                    <p><strong>Booking ID:</strong> " . esc_html($bookingid) . "</p>
                    <p style='display:inline; margin-right: 40%;'><strong>Name:</strong> " . esc_html($name) . "</p>
                    <p><strong>Phone Number:</strong> <span>" . esc_html($phno) . "</span></p>
                    <p><strong>Total number of rooms booked:</strong> " . esc_html($rooms) . "</p>
                    <p><strong>From Date:</strong> " . esc_html($from) . "</p>
                    <p><strong>Till Date:</strong> " . esc_html($till) . "</p>
                    <p><strong>Room Number:</strong> " . implode(", ", $reserved_rooms) . "</p>
                </div>
                <p class='page-mid'><strong>Total Amount Paid:</strong> $" . esc_html($amount) . "</p>
                <p class='page-mid'><strong>Payment Method:</strong> Online</p>
            </div>
            <div class='page-end'>
                <p>Note: A cleaning fee may apply if additional cleaning is required after check-out.</p>
                <p><strong>Thank you for choosing us for your upcoming stay! We look forward to welcoming you soon!</strong></p>
            </div>
        </div>
    </body>
    </html>";
    $headers = array('Content-Type: text/html; charset=UTF-8');
    //use an appropriate mail plugin from wordpress
    wp_mail($email, $subject, $message, $headers);

}

//displays the receipt
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
    //get the reserved room ids
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
