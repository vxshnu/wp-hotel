<?php
/*
Plugin Name: Booking Management with Refund Policy
Description: Manage bookings with view, modify, cancel functionality, and refund policy.
Version: 1.5
Author: Nikita Anna Ajith
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

session_start(); // Start PHP session

// Enqueue Scripts and Styles
function booking_enqueue_assets() {
    wp_enqueue_style('booking-management-style', plugins_url('booking-style.css', __FILE__));
    wp_enqueue_script('booking-management-script', plugins_url('booking-script.js', __FILE__), ['jquery'], null, true);
    wp_localize_script('booking-management-script', 'booking_ajax_object', [
        'ajax_url' => admin_url('admin-ajax.php'),
        'nonce' => wp_create_nonce('booking_nonce'),
    ]);
}
add_action('wp_enqueue_scripts', 'booking_enqueue_assets');

// Shortcode to display booking management dashboard
function booking_management_dashboard() {
    if (!isset($_SESSION['user_id'])) {
        return "<p>You must be logged in to view your bookings. <a href='" . esc_url(home_url('/login')) . "'>Login</a></p>";
    }

    $user_id = $_SESSION['user_id'];
    global $wpdb;

    // Fetch bookings for the logged-in user
    $bookings = $wpdb->get_results($wpdb->prepare(
        "SELECT b.*, u.first_name, u.last_name, u.email
         FROM Bookings b
         JOIN Users u ON b.user_id = u.user_id
         WHERE b.user_id = %d",
        $user_id
    ));

    ob_start();
    ?>
    <div id="booking-dashboard" style="max-width: 90%; margin: 0 auto;">
        
        <div style="position: relative; width: 100%;">
            <button 
                style="position: absolute; top: 10px; right: 10px; padding: 10px; background-color: red; color: #fff; border: none; border-radius: 3px; cursor: pointer;" 
                onclick="window.location.href='<?php echo esc_url(home_url('/?action=logout')); ?>'">
                Logout
            </button>
        </div>
        <h4 id="heading2">Your Bookings</h4>
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr>
                    <th style="border-bottom: 2px solid #ddd;">Booking ID</th>
                    <th style="border-bottom: 2px solid #ddd;">Guest Name</th>
                    <th style="border-bottom: 2px solid #ddd;">Paid Amount</th>
                    <th style="border-bottom: 2px solid #ddd;">Check-in</th>
                    <th style="border-bottom: 2px solid #ddd;">Check-out</th>
                    <th style="border-bottom: 2px solid #ddd;">Status</th>
                    <th style="border-bottom: 2px solid #ddd;">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($bookings): ?>
                    <?php foreach ($bookings as $booking): ?>
                        <tr data-booking-id="<?php echo $booking->booking_id; ?>">
                            <td><?php echo $booking->booking_id; ?></td>
                            <td><?php echo esc_html($booking->first_name . ' ' . $booking->last_name); ?></td>
                            <td>$<?php echo $booking->total_cost; ?></td>
                            <td><?php echo $booking->check_in_date; ?></td>
                            <td><?php echo $booking->check_out_date; ?></td>
                            <td class="booking-status"><?php echo $booking->status; ?></td>
                            <td style="text-align: center;">
                                <button class="modify-booking" style="margin-bottom: 10px; width: 100px;">Modify</button>
                                <button class="cancel-booking" style="width: 100px;" data-user-email="<?php echo esc_html($booking->email); ?>">Cancel</button>
                            </td>
                        </tr>
                        <tr class="modify-row" style="display: none;">
                            <td colspan="7">
                                <form class="modify-form">
                                    <label>Special Requests:</label>
                                    <textarea name="special_requests"><?php echo esc_textarea($booking->special_requests); ?></textarea>
                                    <button type="button" class="save-modification">Save</button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="7" style="text-align: center;">No bookings found.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('booking_management', 'booking_management_dashboard');

// AJAX: Modify Booking
function modify_booking() {
    global $wpdb;
    check_ajax_referer('booking_nonce', 'security');

    $booking_id = intval($_POST['booking_id']);
    $special_requests = sanitize_textarea_field($_POST['special_requests']);

    $updated = $wpdb->update(
        'Bookings',
        ['special_requests' => $special_requests],
        ['booking_id' => $booking_id],
        ['%s'],
        ['%d']
    );

    if ($updated === false) {
        wp_send_json_error(['message' => 'Failed to modify the booking.']);
    }

    wp_send_json_success(['message' => 'Booking modified successfully.']);
}
add_action('wp_ajax_modify_booking', 'modify_booking');

// AJAX: Cancel Booking
function cancel_booking() {
    global $wpdb;
    check_ajax_referer('booking_nonce', 'security');

    $booking_id = intval($_POST['booking_id']);
    $email = sanitize_email($_POST['user_email']);
    $current_date = current_time('Y-m-d H:i:s');

    // Fetch booking and user details
    $booking = $wpdb->get_row(
        $wpdb->prepare("
            SELECT b.created_at, b.total_cost, b.check_in_date, b.check_out_date, u.first_name, u.last_name 
            FROM Bookings b 
            JOIN Users u ON b.user_id = u.user_id 
            WHERE b.booking_id = %d", 
            $booking_id
        )
    );

    if (!$booking) {
        wp_send_json_error(['message' => 'Booking not found.']);
    }

    $booking_date = $booking->created_at;
    $total_cost = $booking->total_cost;

    // Calculate refund policy
    $refund = $total_cost;
    $time_diff = strtotime($current_date) - strtotime($booking_date);

    if ($time_diff > 24 * 60 * 60) { // If booking is older than 24 hours
        $refund *= 0.75; // Deduct 25%
    }

    // Update the booking status
    $updated = $wpdb->update(
        'Bookings',
        ['status' => 'cancelled'],
        ['booking_id' => $booking_id],
        ['%s'],
        ['%d']
    );

    if ($updated === false) {
        wp_send_json_error(['message' => 'Failed to cancel the booking.']);
    }

    // Prepare the email
    $subject = "Booking Cancellation Confirmation - Sunset View Resort";

    $message = "
    <!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Booking Cancellation Confirmation</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                line-height: 1.6;
                background-color: #f4f4f4;
                margin: 0;
                padding: 0;
            }
            .email-container {
                max-width: 600px;
                margin: 20px auto;
                background: #ffffff;
                padding: 20px;
                border: 1px solid #ddd;
                border-radius: 8px;
                box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
            }
            .header {
                text-align: center;
                background-color: #0073aa;
                color: #ffffff;
                padding: 10px 0;
                border-radius: 8px 8px 0 0;
            }
            .header h1 {
                margin: 0;
                font-size: 24px;
            }
            .content {
                margin: 20px 0;
            }
            .content p {
                margin: 5px 0;
            }
            .footer {
                text-align: center;
                margin-top: 20px;
                font-size: 12px;
                color: #666;
            }
        </style>
    </head>
    <body>
        <div class='email-container'>
            <div class='header'>
                <h1>Sunset View Resort</h1>
            </div>
            <div class='content'>
                <h2>Booking Cancellation Confirmation</h2>
                <p>Dear <strong>{$booking->first_name} {$booking->last_name}</strong>,</p>
                <p>Your booking has been successfully cancelled. Below are the details:</p>
                <table style='width: 100%; border-collapse: collapse; margin: 20px 0;'>
                    <tr>
                        <td style='padding: 10px; border: 1px solid #ddd;'>Booking ID:</td>
                        <td style='padding: 10px; border: 1px solid #ddd;'>{$booking_id}</td>
                    </tr>
                    <tr>
                        <td style='padding: 10px; border: 1px solid #ddd;'>Guest Name:</td>
                        <td style='padding: 10px; border: 1px solid #ddd;'>{$booking->first_name} {$booking->last_name}</td>
                    </tr>
                    <tr>
                        <td style='padding: 10px; border: 1px solid #ddd;'>Check-in Date:</td>
                        <td style='padding: 10px; border: 1px solid #ddd;'>{$booking->check_in_date}</td>
                    </tr>
                    <tr>
                        <td style='padding: 10px; border: 1px solid #ddd;'>Check-out Date:</td>
                        <td style='padding: 10px; border: 1px solid #ddd;'>{$booking->check_out_date}</td>
                    </tr>
                    <tr>
                        <td style='padding: 10px; border: 1px solid #ddd;'>Total Paid:</td>
                        <td style='padding: 10px; border: 1px solid #ddd;'>\${$total_cost}</td>
                    </tr>
                    <tr>
                        <td style='padding: 10px; border: 1px solid #ddd;'>Refund Amount:</td>
                        <td style='padding: 10px; border: 1px solid #ddd;'>\${$refund}</td>
                    </tr>
                </table>
                <p>The refund will be processed within one week. If you have any questions, please contact us at <a href='mailto:support@sunsetviewresort.com'>support@sunsetviewresort.com</a>.</p>
            </div>
            <div class='footer'>
                <p>Thank you for choosing Sunset View Resort. We hope to see you again!</p>
            </div>
        </div>
    </body>
    </html>
    ";

    $headers = [
        'Content-Type: text/html; charset=UTF-8',
        'From: Sunset View Resort <no-reply@sunsetviewresort.com>',
    ];

    // Send the email
    wp_mail($email, $subject, $message, $headers);

    wp_send_json_success(['message' => 'Booking cancelled successfully. Refund will be processed.']);
}

add_action('wp_ajax_cancel_booking', 'cancel_booking');
