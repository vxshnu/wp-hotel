<?php
/**
 * Plugin Name: Room Booking
 * Description: Hotel Room Booking
 * Author: Nikita Anna Ajith
 * Version: 1.0.0
 * Text Domain: hotel-room-booking
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Function to display the room booking form
function display_room_booking_form() {
    // Room details - hardcoded for display purposes
    $room_details = [
        'name' => 'Deluxe Room',
        'description' => 'A spacious room with sea view, king-size bed, and ensuite bathroom.',
        'price_per_night' => 150,
        'nights' => 3,
        'tax_rate' => 0.1, // 10% tax
        'additional_fees' => 50 // Additional fees (e.g., service fee)
    ];

    // Calculate total cost
    $room_charges = $room_details['price_per_night'] * $room_details['nights'];
    $tax = $room_charges * $room_details['tax_rate'];
    $total_cost = $room_charges + $tax + $room_details['additional_fees'];

    // Display room details and cost breakdown
    $output = "
        <div class='room-booking-container'>
            <h2>Room Details</h2>
            <p><strong>Room Name:</strong> {$room_details['name']}</p>
            <p><strong>Description:</strong> {$room_details['description']}</p>
            <p><strong>Price per Night:</strong> \${$room_details['price_per_night']}</p>
            <p><strong>Number of Nights:</strong> {$room_details['nights']}</p>
            <h3>Cost Breakdown</h3>
            <p><strong>Room Charges:</strong> \${$room_charges}</p>
            <p><strong>Tax (10%):</strong> \${$tax}</p>
            <p><strong>Additional Fees:</strong> \${$room_details['additional_fees']}</p>
            <p class='total-cost'><strong>Total Cost:</strong> \${$total_cost}</p>
            <h3>Booking Form</h3>
            <form method='post' action=''>
                <p>
                    <label for='name'>Full Name:</label><br>
                    <input type='text' id='name' name='name' required>
                </p>
                <p>
                    <label for='contact'>Contact Details:</label><br>
                    <input type='text' id='contact' name='contact' required>
                </p>
                <p>
                    <label for='guests'>Number of Guests:</label><br>
                    <input type='number' id='guests' name='guests' min='1' required>
                </p>
                <p>
                    <label for='special_requirements'>Special Requirements:</label><br>
                    <textarea id='special_requirements' name='special_requirements'></textarea>
                </p>
                <p><input type='submit' name='submit_booking' value='Book Now'></p>
            </form>
        </div>
    ";

    // Handle form submission
    if (isset($_POST['submit_booking'])) {
        $name = sanitize_text_field($_POST['name']);
        $contact = sanitize_text_field($_POST['contact']);
        $guests = intval($_POST['guests']);
        $special_requirements = sanitize_textarea_field($_POST['special_requirements']);

        // Process booking data (e.g., save to database or send an email)
        $output .= "<p class='success-message'>Thank you, $name! Your booking for {$room_details['name']} has been received.</p>";
    }

    // CSS for styling
    $output .= '
    <style>
        .room-booking-container {
            max-width: 600px;
            margin: 20px auto;
            padding: 20px;
            border: 1px solid #ddd;
            border-radius: 8px;
            background-color: #f9f9f9;
            font-family: Arial, sans-serif;
        }
        .room-booking-container h2,
        .room-booking-container h3 {
            color: #333;
            border-bottom: 2px solid #ddd;
            padding-bottom: 5px;
            margin-bottom: 15px;
        }
        .room-booking-container p {
            margin: 10px 0;
        }
        .room-booking-container p strong {
            color: #333;
        }
        .total-cost {
            font-size: 1.2em;
            color: #d9534f;
            margin-top: 10px;
        }
        .booking-form input[type="text"],
        .booking-form input[type="number"],
        .booking-form textarea {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .booking-form input[type="submit"] {
            background-color: #5cb85c;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 1em;
        }
        .booking-form input[type="submit"]:hover {
            background-color: #4cae4c;
        }
        .success-message {
            color: #5cb85c;
            font-weight: bold;
            margin-top: 20px;
            padding: 10px;
            border: 1px solid #5cb85c;
            background-color: #e6ffe6;
            border-radius: 4px;
        }
    </style>';

    return $output;
}

// Register the shortcode to display the booking form
function register_room_booking_shortcode() {
    add_shortcode('room_booking_form', 'display_room_booking_form');
}
add_action('init', 'register_room_booking_shortcode');
