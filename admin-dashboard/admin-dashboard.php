<?php
/**
 * Plugin Name: Hotel Admin Dashboard with Collapsible Sidebar
 * Description: A stylish hotel admin dashboard with a sidebar that expands on click and collapses when clicked again.
 * Version: 1.3
 * Author: Aman Nair, Karol Monsy Theruvil
 */

if (!defined('ABSPATH')) {
    exit;
}

// Register shortcode for displaying the dashboard
add_shortcode('hotel_admin_dashboard', 'display_hotel_admin_dashboard');

// Enqueue custom styles and JavaScript for the sidebar dashboard
function hotel_dashboard_sidebar_styles() {
    echo '<style>
        /* Sidebar styling */
        .sidebar {
            width: 60px;
            background: #2c3e50;
            color: #ecf0f1;
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            transition: width 0.3s;
            overflow-x: hidden;
            z-index: 1000;
            padding-top: 20px;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.2);
        }
        .sidebar.expanded { width: 200px; }
        
        .hamburger {
            font-size: 30px;
            cursor: pointer;
            color: #ecf0f1;
            text-align: center;
            padding: 15px;
            transition: color 0.3s;
        }
        
        .menu-item {
            padding: 15px 20px;
            cursor: pointer;
            font-weight: bold;
            display: none;
            color: #ecf0f1;
            text-transform: uppercase;
            transition: background 0.3s;
        }
        
        .sidebar.expanded .menu-item {
            display: block;
        }

        .menu-item:hover, .menu-item.active {
            background: #34495e;
        }

        /* Content styling */
        .content {
            margin-left: 60px;
            padding: 20px;
            transition: margin-left 0.3s;
            font-family: Arial, sans-serif;
            background-color: #f4f6f8;
            min-height: 100vh;
        }
        .sidebar.expanded + .content { margin-left: 200px; width: calc(100% - 200px); }

        h1, h2, h3, h4 {
            color: #2c3e50;
            font-weight: 600;
        }

        p, label {
            color: #7f8c8d;
        }

        /* Form and button styling */
        form {
            margin-top: 15px;
            margin-bottom: 15px;
        }
        input[type="text"], input[type="password"], input[type="number"], select, textarea {
            width: 100%;
            padding: 10px;
            margin-top: 5px;
            margin-bottom: 15px;
            border: 1px solid #bdc3c7;
            border-radius: 5px;
            box-sizing: border-box;
        }

        button {
            background-color: #3498db;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: background 0.3s;
        }
        button:hover {
            background-color: #2980b9;
        }

        /* Table styling */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
            background: #ffffff;
            box-shadow: 0px 4px 8px rgba(0,0,0,0.1);
        }
        th, td {
            padding: 12px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }
        th {
            background-color: #3498db;
            color: #ffffff;
        }
        
        .logout-button, .login-button {
            background-color: #e74c3c;
            color: white;
            padding: 10px 15px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 14px;
            transition: background 0.3s;
            margin-top: 20px;
        }
        .logout-button:hover, .login-button:hover {
            background-color: #c0392b;
        }

        /* Hide all sections except Home initially */
        .section { display: none; }
        #home.section { display: block; }
    </style>';
}
add_action('wp_head', 'hotel_dashboard_sidebar_styles');

// Display the full dashboard with sidebar and login/logout functionality
function display_hotel_admin_dashboard() {
    if (!is_user_logged_in()) {
        echo '<button class="login-button" onclick="window.location.href=\'' . wp_login_url(get_permalink()) . '\'">Login</button>';
        return;
    }

    if (!current_user_can('manage_options')) return '<p>You do not have permission to access this page.</p>';

    ob_start();
    ?>
    <div class="sidebar" id="sidebar" onclick="toggleSidebar()">
        <div class="hamburger">&#9776;</div>
        <div class="menu-item" onclick="showSection('home')">HOME</div>
        <div class="menu-item" onclick="showSection('room_details')">ROOM DETAILS</div>
        <div class="menu-item" onclick="showSection('customer_info')">BOOKING MANAGEMENT</div>
        <div class="menu-item" onclick="showSection('staff_details')">USER DETAILS</div>
        <div class="menu-item" onclick="showSection('reports_analytics')">REPORTS AND ANALYTICS</div>
    </div>

    <div class="content">
        <button class="logout-button" onclick="logout()">Logout</button>
        <h1>Hotel Admin Dashboard</h1>

        <div id="home" class="section">
            <h2>Home</h2>
            <p>Welcome to our luxurious hotel! We offer fine dining, spa, swimming pool, conference rooms, and more. Enjoy premium services tailored to make your stay comfortable and memorable.</p>
        </div>

        <div id="room_details" class="section">
            <h2>Room Management</h2>
            <?php display_room_details_section(); ?>
        </div>

        <div id="customer_info" class="section">
            <h2>Booking Management</h2>
            <?php display_customer_information_section(); ?>
        </div>

        <div id="staff_details" class="section">
            <h2>User Details</h2>
            <?php display_user_management_section(); ?>
        </div>

        <div id="reports_analytics" class="section">
            <h2>Reports and Analytics</h2>
            <?php display_reports_analytics_section(); ?>
        </div>
    </div>

    <script>
        function toggleSidebar() {
            document.getElementById("sidebar").classList.toggle("expanded");
        }

        function showSection(sectionId) {
            document.querySelectorAll('.section').forEach(function(section) {
                section.classList.remove('active');
                section.style.display = 'none'; // Hide all sections
            });

            document.querySelectorAll('.menu-item').forEach(function(item) {
                item.classList.remove('active');
            });

            document.getElementById(sectionId).classList.add('active');
            document.getElementById(sectionId).style.display = 'block'; // Show selected section
            document.querySelector('.menu-item[onclick="showSection(\'' + sectionId + '\')"]').classList.add('active');
        }

        function logout() {
            window.location.href = "<?php echo wp_logout_url(home_url()); ?>";
        }
    </script>
    <?php
    return ob_get_clean();
}   

// Room Management Section
function display_room_details_section() {
    global $wpdb;

    // Add Room Form
    echo '<h3>Add New Room</h3>';
    echo '<form method="post">';
    echo '<label>Room Type:</label> <input type="text" name="room_type" required><br>';
    echo '<label>Description:</label> <textarea name="description" required></textarea><br>';
    echo '<label>Amenities:</label> <input type="text" name="amenities"><br>';
    echo '<label>Price:</label> <input type="number" step="0.01" name="price" required><br>';
    echo '<label>Max Occupancy:</label> <input type="number" name="max_occupancy" required><br>';
    echo '<button type="submit" name="add_room">Add Room</button>';
    echo '</form>';

    if (isset($_POST['add_room'])) {
        $wpdb->insert('hotel_rooms', [
            'room_type' => sanitize_text_field($_POST['room_type']),
            'description' => sanitize_textarea_field($_POST['description']),
            'amenities' => sanitize_text_field($_POST['amenities']),
            'price' => floatval($_POST['price']),
            'max_occupancy' => intval($_POST['max_occupancy']),
        ]);
        echo '<p>New room added successfully!</p>';
    }

    // Room Update/Delete Form
    $rooms = $wpdb->get_results("SELECT * FROM hotel_rooms LIMIT 100");
    echo '<h3>Manage Existing Rooms</h3>';
    echo '<form method="post"><select name="room_id" onchange="this.form.submit()">';
    echo '<option value="">Choose a room...</option>';
    foreach ($rooms as $room) {
        echo "<option value='{$room->id}' " . selected($_POST['room_id'], $room->id, false) . ">Room {$room->id} - {$room->room_type}</option>";
    }
    echo '</select></form>';

    if (!empty($_POST['room_id'])) {
        $room = $wpdb->get_row("SELECT * FROM hotel_rooms WHERE id = " . intval($_POST['room_id']));
        echo '<h3>Edit Room</h3><form method="post">';
        echo "<input type='hidden' name='room_id' value='{$room->id}'>";
        echo '<label>Room Type:</label> <input type="text" name="room_type" value="' . esc_attr($room->room_type) . '" required><br>';
        echo '<label>Description:</label> <textarea name="description" required>' . esc_textarea($room->description) . '</textarea><br>';
        echo '<label>Amenities:</label> <input type="text" name="amenities" value="' . esc_attr($room->amenities) . '"><br>';
        echo '<label>Price:</label> <input type="number" step="0.01" name="price" value="' . esc_attr($room->price) . '" required><br>';
        echo '<label>Max Occupancy:</label> <input type="number" name="max_occupancy" value="' . esc_attr($room->max_occupancy) . '" required><br>';
        echo '<button type="submit" name="update_room">Update Room</button>';
        echo '<button type="submit" name="delete_room" onclick="return confirm(\'Are you sure you want to delete this room?\')">Delete Room</button>';
        echo '</form>';
    }

    if (isset($_POST['update_room'])) {
        $wpdb->update('hotel_rooms', [
            'room_type' => sanitize_text_field($_POST['room_type']),
            'description' => sanitize_textarea_field($_POST['description']),
            'amenities' => sanitize_text_field($_POST['amenities']),
            'price' => floatval($_POST['price']),
            'max_occupancy' => intval($_POST['max_occupancy']),
        ], ['id' => intval($_POST['room_id'])]);
        echo '<p>Room updated successfully!</p>';
    }

    if (isset($_POST['delete_room'])) {
        $wpdb->delete('hotel_rooms', ['id' => intval($_POST['room_id'])]);
        echo '<p>Room deleted successfully!</p>';
    }
}

// Booking Management Section
function display_customer_information_section() {
    global $wpdb;

    echo '<h3>Booking Management</h3>';

    // Search option to find a specific booking by ID
    echo '<form method="post">';
    echo '<label>Enter Booking ID to Search:</label>';
    echo '<input type="number" name="search_booking_id" required>';
    echo '<button type="submit" name="search_booking">Search</button>';
    echo '</form>';

    // If booking ID is entered, display specific booking details
    if (isset($_POST['search_booking'])) {
        $booking_id = intval($_POST['search_booking_id']);
        display_booking_details($booking_id);
    } else {
        // Display a list of all bookings excluding 'cancelled' and 'checked-out' statuses
        $bookings = $wpdb->get_results("SELECT * FROM hotel_bookings WHERE status NOT IN ('cancelled', 'checked-out')");

        echo '<h4>All Active Bookings</h4>';
        if ($bookings) {
            foreach ($bookings as $booking) {
                echo "<div style='border: 1px solid #ccc; padding: 10px; margin-bottom: 10px;'>";
                echo "<p><strong>Booking ID:</strong> {$booking->id}</p>";
                echo "<p><strong>User ID:</strong> {$booking->user_id}</p>";
                echo "<p><strong>Room ID:</strong> {$booking->room_id}</p>";
                echo "<p><strong>Check-in Date:</strong> {$booking->check_in_date}</p>";
                echo "<p><strong>Check-out Date:</strong> {$booking->check_out_date}</p>";
                echo "<p><strong>Total Price:</strong> \${$booking->total_price}</p>";
                echo "<p><strong>Status:</strong> {$booking->status}</p>";
                
                // Form to update booking status
                echo '<form method="post">';
                echo "<input type='hidden' name='booking_id' value='{$booking->id}'>";
                echo '<label>Update Status:</label>';
                echo '<select name="new_status">';
                echo '<option value="confirmed"' . selected($booking->status, 'confirmed', false) . '>Confirmed</option>';
                echo '<option value="checked-in"' . selected($booking->status, 'checked-in', false) . '>Checked-in</option>';
                echo '<option value="checked-out"' . selected($booking->status, 'checked-out', false) . '>Checked-out</option>';
                echo '<option value="cancelled"' . selected($booking->status, 'cancelled', false) . '>Cancelled</option>';
                echo '</select>';
                echo '<button type="submit" name="update_status">Update Status</button>';
                echo '</form>';
                echo "</div>";
            }
        } else {
            echo "<p>No active bookings found.</p>";
        }
    }

    // Handle status update
    if (isset($_POST['update_status'])) {
        $booking_id = intval($_POST['booking_id']);
        $new_status = sanitize_text_field($_POST['new_status']);
        
        // Update booking status in the hotel_bookings table
        $wpdb->update('hotel_bookings', ['status' => $new_status], ['id' => $booking_id]);
        echo "<p>Booking ID {$booking_id} status updated to '{$new_status}' successfully!</p>";
    }
}

// Function to display specific booking details
function display_booking_details($booking_id) {
    global $wpdb;

    $booking = $wpdb->get_row("SELECT * FROM hotel_bookings WHERE id = $booking_id");

    if ($booking) {
        echo "<h4>Booking Details for Booking ID {$booking_id}</h4>";
        echo "<p><strong>User ID:</strong> {$booking->user_id}</p>";
        echo "<p><strong>Room ID:</strong> {$booking->room_id}</p>";
        echo "<p><strong>Check-in Date:</strong> {$booking->check_in_date}</p>";
        echo "<p><strong>Check-out Date:</strong> {$booking->check_out_date}</p>";
        echo "<p><strong>Total Price:</strong> \${$booking->total_price}</p>";
        echo "<p><strong>Status:</strong> {$booking->status}</p>";

        // Update status for specific booking
        echo '<form method="post">';
        echo "<input type='hidden' name='booking_id' value='{$booking->id}'>";
        echo '<label>Update Status:</label>';
        echo '<select name="new_status">';
        echo '<option value="confirmed"' . selected($booking->status, 'confirmed', false) . '>Confirmed</option>';
        echo '<option value="checked-in"' . selected($booking->status, 'checked-in', false) . '>Checked-in</option>';
        echo '<option value="checked-out"' . selected($booking->status, 'checked-out', false) . '>Checked-out</option>';
        echo '<option value="cancelled"' . selected($booking->status, 'cancelled', false) . '>Cancelled</option>';
        echo '</select>';
        echo '<button type="submit" name="update_status">Update Status</button>';
        echo '</form>';
    } else {
        echo "<p>Booking not found. Please enter a valid Booking ID.</p>";
    }
}


function display_user_management_section() {
    global $wpdb;

    echo '<h3>User Management</h3>';

    // Search for a specific user by ID or email
    echo '<form method="post">';
    echo '<label>Enter User ID or Email to Search:</label>';
    echo '<input type="text" name="search_user" required>';
    echo '<button type="submit" name="search_user_btn">Search</button>';
    echo '</form>';

    // Display specific user details if searched
    if (isset($_POST['search_user_btn'])) {
        $search_user = sanitize_text_field($_POST['search_user']);
        $user = $wpdb->get_row("SELECT * FROM hotel_users WHERE id = '$search_user' OR email = '$search_user'");

        if ($user) {
            display_user_details($user);
        } else {
            echo "<p>User not found. Please enter a valid User ID or Email.</p>";
        }
    } else {
        // Display all users with options to manage them
        $users = $wpdb->get_results("SELECT * FROM hotel_users LIMIT 10");

        echo '<h4>All Users</h4>';
        foreach ($users as $user) {
            echo "<div style='border: 1px solid #ccc; padding: 10px; margin-bottom: 10px;'>";
            echo "<p><strong>User ID:</strong> {$user->id}</p>";
            echo "<p><strong>Name:</strong> {$user->first_name} {$user->last_name}</p>";
            echo "<p><strong>Email:</strong> {$user->email}</p>";
            echo "<form method='post'>";
            echo "<input type='hidden' name='user_id' value='{$user->id}'>";
            echo '<label>New Password:</label> <input type="password" name="new_password" required>';
            echo '<button type="submit" name="update_password">Update Password</button>';
            echo '</form>';
            echo "</div>";
        }
    }

    // Handle password update action
    if (isset($_POST['update_password'])) {
        $user_id = intval($_POST['user_id']);
        $new_password = sanitize_text_field($_POST['new_password']);
        update_user_password($user_id, $new_password);
    }
}

// Function to display individual user details
function display_user_details($user) {
    echo "<h4>User Details</h4>";
    echo "<p><strong>User ID:</strong> {$user->id}</p>";
    echo "<p><strong>Name:</strong> {$user->first_name} {$user->last_name}</p>";
    echo "<p><strong>Email:</strong> {$user->email}</p>";
    echo "<p><strong>Created At:</strong> {$user->created_at}</p>";
    echo "<p><strong>Updated At:</strong> {$user->updated_at}</p>";

    // Form to reset password
    echo '<form method="post">';
    echo "<input type='hidden' name='user_id' value='{$user->id}'>";
    echo '<label>New Password:</label> <input type="password" name="new_password" required>';
    echo '<button type="submit" name="update_password">Update Password</button>';
    echo '</form>';
}

// Function to update user password with a manually entered password
function update_user_password($user_id, $new_password) {
    global $wpdb;
    $hashed_password = wp_hash_password($new_password);

    // Update the user's password in the database
    $wpdb->update('hotel_users', ['password' => $hashed_password], ['id' => $user_id]);

    echo "<p>Password updated successfully for User ID {$user_id}.</p>";
}



function display_reports_analytics_section() {
    global $wpdb;

    // Total bookings
    $total_bookings = $wpdb->get_var("SELECT COUNT(*) FROM hotel_bookings");

    // Most booked room
    $most_booked_room = $wpdb->get_var("SELECT room_id FROM hotel_bookings GROUP BY room_id ORDER BY COUNT(*) DESC LIMIT 1");

    // Total revenue from confirmed and checked-in bookings
    $revenue = $wpdb->get_var("SELECT SUM(total_price) FROM hotel_bookings WHERE status IN ('confirmed', 'checked-in')");

    // Monthly booking trends
    $monthly_bookings = $wpdb->get_results("
        SELECT DATE_FORMAT(booking_created_at, '%Y-%m') AS month, COUNT(*) AS count 
        FROM hotel_bookings 
        GROUP BY month 
        ORDER BY month DESC 
        LIMIT 12
    ");

    // Room occupancy rate (percentage of occupied rooms)
    $total_rooms = $wpdb->get_var("SELECT COUNT(*) FROM hotel_rooms");
    $occupied_rooms = $wpdb->get_var("SELECT COUNT(DISTINCT room_id) FROM hotel_bookings WHERE status IN ('confirmed', 'checked-in')");
    $occupancy_rate = $total_rooms > 0 ? round(($occupied_rooms / $total_rooms) * 100, 2) : 0;
    
    // Display analytics
    echo "<h3>Analytics</h3>";
    echo "<p><strong>Total Bookings:</strong> {$total_bookings}</p>";
    echo "<p><strong>Most Booked Room ID:</strong> {$most_booked_room}</p>";
    echo "<p><strong>Total Revenue:</strong> \${$revenue}</p>";
    echo "<p><strong>Room Occupancy Rate:</strong> {$occupancy_rate}%</p>";

    // Display monthly booking trends
    echo "<h4>Monthly Booking Trends (Last 12 Months)</h4>";
    if ($monthly_bookings) {
        echo "<table style='border: 1px solid #ccc; border-collapse: collapse; width: 100%;'>";
        echo "<tr><th style='border: 1px solid #ccc; padding: 8px;'>Month</th><th style='border: 1px solid #ccc; padding: 8px;'>Bookings</th></tr>";
        foreach ($monthly_bookings as $month) {
            echo "<tr><td style='border: 1px solid #ccc; padding: 8px;'>{$month->month}</td><td style='border: 1px solid #ccc; padding: 8px;'>{$month->count}</td></tr>";
        }
        echo "</table>";
    } else {
        echo "<p>No booking data available for the past year.</p>";
    }

}

?>
