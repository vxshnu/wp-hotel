<?php

/**
 * Plugin Name: Payment Gateway
 * Description: Hotel Managment Payment
 * Author: Vishnu Narayanan
 * Version: 1.0.0
 * Text Domain: hotel-payment
 * 
 */

global $wpdb;
$bookingid=123;
$wpdp -> insert(
    "{wp->prefix}hotel_booking",
    array(
        'booking_id'=>$bookingid,
        'name'=>'Xyz',
        'email'=>'vishnunarayanan8690@gmail.com',
        'phno'=>1234567890,
        'roomid'=>2
    ),
    array(
        '%d',
        '%s',
        '%s',
        '%d',
        '%d'

    )
    );
do_action('after_payment',$bookingid);