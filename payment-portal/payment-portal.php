<?php
/**
 * Plugin Name: Payment Gateway
 * Description: Hotel Management Payment Integration
 * Author: Vishnu Narayanan
 * Version: 1.3.2
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Payment processing function
function payment_gateway_shortcode() {
    // Retrieve booking ID from URL
    $booking_id = isset($_GET['booking_id']) ? intval($_GET['booking_id']) : null;

    if (!$booking_id) {
        echo "<p style='color: red;'>Booking ID is missing. Please contact support.</p>";
        return;
    }

    // Display payment processing screen
    $html_code = "
    <!DOCTYPE html>
    <html lang='en'>
    <head>
       <meta charset='UTF-8'>
       <meta name='viewport' content='width=device-width, initial-scale=1.0'>
       <title>Payment Processing</title>
       <style>
          @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
          }
          body {
                font-family: Arial, sans-serif;
                background-color: #f4f4f4;
                margin: 0;
                padding: 0;
                display: flex;
                justify-content: center;
                align-items: center;
                height: 100vh;
          }
          .payment-container {
                text-align: center;
                background-color: #fff;
                padding: 20px;
                border-radius: 8px;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
                max-width : 400px;
                max-height : 300px;
          }
          .loader {
                border: 8px solid #f3f3f3;
                border-top: 8px solid #3498db;
                border-radius: 50%;
                width: 50px;
                height: 50px;
                animation: spin 1s linear infinite;
                margin: 20px auto;
          }
          .message {
                font-size: 14px;
                color: #666;
          }
       </style>
       <script>
           // Automatically redirect after 3 seconds
           setTimeout(function() {
               window.location.href = '" . esc_url(add_query_arg('booking_id', $booking_id, home_url('/booking-confirmation/'))) . "';
           }, 3000);
       </script>
    </head>
    <body>
       <div class='payment-container'>
          <h4>Processing Your Payment</h4>
          <div class='loader'></div>
          <p class='message'>Please do not close this window.</p>
       </div>
    </body>
    </html>
    ";

    echo $html_code;
}
add_shortcode('payment', 'payment_gateway_shortcode');
