<?php

/**
 * Plugin Name: Payment Gateway
 * Description: Hotel Managment Payment
 * Author: Vishnu Narayanan
 * Version: 1.0.0
 * Text Domain: hotel-payment
 * 
 */

 function hotel_payment_process(){
    global $wpdb;
    $bookingid=1;
    $wpdb -> insert(
        "hotel_booking",
        array(
            'booking_id'=>$bookingid,
            'full_name'=>'Xyz',
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
 }

 function activate_hotel_payment_portal() {
    add_action('init', 'hotel_payment_process', 1);
}

register_activation_hook(__FILE__, 'activate_hotel_payment_portal');

add_action('init', 'hotel_payment_process', 1);