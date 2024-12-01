    <?php
    /**
     * Plugin Name: Room Listings and Search Functionality
     * Description: Provides room listings and search functionality with a detailed view for each room.
     * Version: 1.1.0
     * Author: Nikitha A R
     */

    if (!defined('ABSPATH')) {
        exit; // Exit if accessed directly
    }

    // Enqueue plugin styles and JavaScript
    function enqueue_room_styles_scripts() {
        echo '<style>
        /* Full-Screen Room Listings and Search Plugin Styles */
        .room-listing-search-form {
            padding: 20px;
            background-color: #ffffff;
            border: 1px solid #ddd;
            margin-bottom: 25px;
            border-radius: 5px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            width: 100%;
            box-sizing: border-box;
        }

        .room-listing-search-form label {
            font-weight: bold;
            font-size: 1em;
            color: #333;
            display: block;
            margin-bottom: 5px;
        }

        .room-listing-search-form select,
        .room-listing-search-form input[type="date"],
        .room-listing-search-form button {
            margin: 8px 0;
            padding: 10px;
            border-radius: 5px;
            border: 1px solid #ddd;
            width: 100%;
            font-size: 1em;
        }

        .room-listing-search-form button {
            background-color: #0073aa;
            color: #fff;
            border: none;
            cursor: pointer;
            padding: 10px 20px;
            font-weight: bold;
            width: 100%;
        }

        .room-listings {
            display: flex;
            flex-wrap: wrap;
            gap: 20px;
            margin-top: 20px;
            justify-content: center;
            display: none; /* Initially hidden */
        }

        .room {
            border: 2px solid #000;
            padding: 15px;
            width: calc(33.33% - 20px);
            background-color: #ffffff;
            border-radius: 10px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            text-align: left;
            transition: transform 0.3s ease;
        }

        .room:hover {
            transform: scale(1.02);
            box-shadow: 0 6px 12px rgba(0, 0, 0, 0.15);
        }

        .room h3 {
            font-size: 1.4em;
            margin-bottom: 10px;
            color: #333;
            font-weight: bold;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
        }

        .room p {
            margin: 8px 0;
            font-size: 0.95em;
            color: #555;
            line-height: 1.4;
        }
        </style>';
        
        // JavaScript to handle showing room listings after search
        echo '<script>
        document.addEventListener("DOMContentLoaded", function() {
            const form = document.querySelector(".room-listing-search-form form");
            const listings = document.querySelector(".room-listings");

            form.addEventListener("submit", function(event) {
                event.preventDefault();
                
                // Display the room listings div
                listings.style.display = "flex";
                
                // Clear previous results
                listings.innerHTML = "";

                // Get filter values
                const checkIn = form.querySelector("input[name=\'check_in\']").value;
                const checkOut = form.querySelector("input[name=\'check_out\']").value;
                const roomType = form.querySelector("select[name=\'room_type\']").value;
                const priceRange = form.querySelector("select[name=\'price_range\']").value;

                // Simulate AJAX call to fetch filtered rooms
                fetch(`${window.location.href}&check_in=${checkIn}&check_out=${checkOut}&room_type=${roomType}&price_range=${priceRange}`)
                    .then(response => response.text())
                    .then(data => {
                        listings.innerHTML = data;
                    });
            });
        });
        </script>';
    }
    add_action('wp_head', 'enqueue_room_styles_scripts');

    // Shortcode for Room Listing and Search
    function room_listing_search_shortcode() {
        ob_start();
        global $wpdb;

        // Fetch room listings from the database
        $rooms = $wpdb->get_results("SELECT * FROM wp_rooms", ARRAY_A);
        ?>
        <div class="room-listing-search-form">
            <!-- Search Form -->
            <form method="GET" action="">
                <label>Check-in Date:</label>
                <input type="date" name="check_in" required>
                <label>Check-out Date:</label>
                <input type="date" name="check_out" required>
                <label>Room Type:</label>
                <select name="room_type">
                    <option value="">All</option>
                    <option value="single">Single</option>
                    <option value="double">Double</option>
                    <option value="suite">Suite</option>
                </select>
                <label>Price Range:</label>
                <select name="price_range">
                    <option value="">All</option>
                    <option value="0-100">$0 - $100</option>
                    <option value="100-200">$100 - $200</option>
                    <option value="200-300">$200 - $300</option>
                </select>
                <button type="submit" id="search-button">Search</button>
            </form>
        </div>

        <!-- Room Listings -->
        <div class="room-listings">
            <?php 
            foreach ($rooms as $room) {
                // Apply filters for search criteria
                if (isset($_GET['room_type']) && $_GET['room_type'] && $_GET['room_type'] !== $room['type']) continue;
                if (isset($_GET['price_range']) && $_GET['price_range']) {
                    list($min, $max) = explode('-', $_GET['price_range']);
                    if ($room['price'] < $min || $room['price'] > $max) continue;
                }

                // Display individual room details in a card view
                echo "<div class='room'>";
                echo "<h3>" . ucfirst($room['type']) . " Room</h3>";
                echo "<p>Price: $" . $room['price'] . " per night</p>";
                echo "<p>Beds: " . $room['beds'] . "</p>";
                echo "<p>Amenities: " . $room['amenities'] . "</p>";
                echo "<p>Max Occupancy: " . $room['occupancy'] . "</p>";
                echo "</div>";
            }
            ?>
        </div>
        <?php
        return ob_get_clean();
    }

    // Register shortcode
    add_shortcode('room_listing_search', 'room_listing_search_shortcode');
