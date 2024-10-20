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

add_action('after_booking','generate_receipt',1,1);

function generate_receipt($bookingid)
{
    echo 'booking id is:'.$bookingid;
}