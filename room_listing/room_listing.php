<?php
/**
 * Plugin Name: Room Listings
 * Description: Provides room listings with a link to detailed booking pages.
 * Version: 1.6.0
 * Author: Nikitha A R
 */

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

// Shortcode for Room Listing and Search
function room_listing_search_shortcode() {
    ob_start();
    global $wpdb;

    // Hotel name
    $hotel_name = "Luxury Stay Hotel";

    // Initialize filters
    $check_in = isset($_GET['check_in']) ? $_GET['check_in'] : null;
    $check_out = isset($_GET['check_out']) ? $_GET['check_out'] : null;
    $room_type = isset($_GET['room_type']) ? $_GET['room_type'] : null;
    $price_range = isset($_GET['price_range']) ? $_GET['price_range'] : null;

    // SQL query to fetch filtered rooms
    $query = "
        SELECT r.* 
        FROM Rooms r
        WHERE 1=1
    ";

    // Filter by room type
    if ($room_type) {
        $query .= $wpdb->prepare(" AND r.room_type = %s", $room_type);
    }

    // Filter by price range
    if ($price_range) {
        if (strpos($price_range, '+') !== false) {
            $min_price = intval($price_range);
            $query .= $wpdb->prepare(" AND r.price_per_night >= %d", $min_price);
        } else {
            list($min_price, $max_price) = explode('-', $price_range);
            $query .= $wpdb->prepare(" AND r.price_per_night BETWEEN %d AND %d", $min_price, $max_price);
        }
    }

    // Filter by date availability if provided
    if ($check_in && $check_out) {
        $query .= $wpdb->prepare("
            AND NOT EXISTS (
                SELECT 1 
                FROM Room_Availability ra
                WHERE ra.room_id = r.room_id 
                AND (ra.start_date <= %s AND ra.end_date >= %s)
            )",
            $check_out, $check_in
        );
    }

    // Fetch rooms
    $rooms = $wpdb->get_results($query, ARRAY_A);

    // Display hotel name and search form
    ?>
    <div>
        <h1 style="text-align: center; margin-bottom: 20px;"><?php echo esc_html($hotel_name); ?></h1>
        <form method="GET" style="max-width:520px ; margin-bottom: 20px; text-align: center;">
            <label>Check-in Date:</label>
            <input style="max-width:500px;" type="date" name="check_in" value="<?php echo esc_attr($check_in); ?>">
            
            <label>Check-out Date:</label>
            <input style="max-width:500px;" type="date" name="check_out" value="<?php echo esc_attr($check_out); ?>">
            
            <label>Room Type:</label>
            <select name="room_type">
                <option value="">All</option>
                <option value="single" <?php selected($room_type, 'single'); ?>>Single</option>
                <option value="double" <?php selected($room_type, 'double'); ?>>Double</option>
                <option value="suite" <?php selected($room_type, 'suite'); ?>>Suite</option>
                <option value="deluxe" <?php selected($room_type, 'deluxe'); ?>>Deluxe</option>
                <option value="family" <?php selected($room_type, 'family'); ?>>Family</option>
                <option value="penthouse" <?php selected($room_type, 'penthouse'); ?>>Penthouse</option>
            </select>
            
            <label>Price Range:</label>
            <select name="price_range">
                <option value="">All</option>
                <option value="0-100" <?php selected($price_range, '0-100'); ?>>$0 - $100</option>
                <option value="100-500" <?php selected($price_range, '100-500'); ?>>$100 - $500</option>
                <option value="500-1000" <?php selected($price_range, '500-1000'); ?>>$500 - $1000</option>
                <option value="1000-2000" <?php selected($price_range, '1000-2000'); ?>>$1000 - $2000</option>
                <option value="2000+" <?php selected($price_range, '2000+'); ?>>$2000+</option>
            </select>
            
            <button type="submit">Search</button>
        </form>
    </div>

    <!-- Room Listings -->
    <div>
        <?php if (!empty($rooms)) : ?>
            <?php foreach ($rooms as $room) : ?>
                <div style="max-width:540px ; border: 1px solid #ddd; margin-bottom: 20px; padding: 10px;">
                    <h3><?php echo ucfirst($room['room_type']); ?> Room</h3>
                    <p><strong>Price:</strong> $<?php echo $room['price_per_night']; ?> per night</p>
                    <p><strong>Beds:</strong> <?php echo $room['beds']; ?></p>
                    <p><strong>Amenities:</strong> <?php echo $room['amenities']; ?></p>
                    <p><strong>Max Occupancy:</strong> <?php echo $room['max_occupancy']; ?></p>
                    <a href="<?php echo home_url('/room-booking?room_id=' . $room['room_id']); ?>" style="display: inline-block; padding: 10px 20px; background: #0073aa; color: #fff; text-decoration: none; border-radius: 4px;">View Details</a>
                </div>
            <?php endforeach; ?>
        <?php else : ?>
            <p>No rooms available for the selected criteria.</p>
        <?php endif; ?>
    </div>
    <?php

    return ob_get_clean();
}

// Register shortcode
add_shortcode('room_listing_search', 'room_listing_search_shortcode');
