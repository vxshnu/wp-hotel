<?php
/**
 * Plugin Name: Payment Gateway
 * Description: Hotel Managment Payment
 * Author: Vishnu Narayanan
 * Version: 1.0.0
 * Text Domain: hotel-payment
 */

function payment()
{
   $html_code="
    <!DOCTYPE html>
    <html lang='en'>
    <head>
       <meta charset='UTF-8'>
       <meta name='viewport' content='width=device-width, initial-scale=1.0'>
       <title>Payment Processing</title>
       <script>
          setTimeout(function() {
             window.location.href = '/receipt'; // Replace with your desired URL
          }, 4000);
       </script>
    </head>
    <body style='font-family: Arial, sans-serif; background-color: #f4f4f4; margin: 0; padding: 0; display: flex; justify-content: center; align-items: center; height: 100vh;'>
       <div style='text-align: center; background-color: #fff; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);'>
          <h1 style='font-size: 24px; margin-bottom: 20px;'>Processing Your Payment</h1>
          <div style='border: 8px solid #f3f3f3; border-top: 8px solid #3498db; border-radius: 50%; width: 50px; height: 50px; animation: spin 1s linear infinite; margin: 0 auto 20px auto;'></div>
          <p style='font-size: 16px; color: #666;'>Please do not close this window.</p>
       </div>
 
       <style>
          @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
          }
       </style>
    </body>
    </html>
    ";
   echo $html_code;
 }

function after_successful_payment(){
   $name = "Vishnu";
   $email = "creativenarayanan8690@gmail.com";
   $phno = "0987654321";
   $amount = 1000;
   $rooms = 4;
   $from = "2024-10-27";
   $till = "2024-10-28";
   $roomids = array(101, 102, 103, 201);
   hotel_payment_process($name, $email, $phno, $amount, $rooms, $from, $till, $roomids);
}

function hotel_payment_process( $name, $email, $phno, $amount, $rooms, $from, $till, $roomids){
   global $wpdb;
   $fromdate = (new DateTime($from))->format("Y-m-d");
   $tilldate = (new DateTime($till))->format("Y-m-d");
   $wpdb -> insert(
         "hotel_booking",
         array(
            'full_name'=>$name,
            'email'=>$email,
            'phno'=>$phno,
            'amount'=>$amount,
            'total_rooms'=>$rooms,
            'from_date'=>$fromdate,
            'till_date'=>$tilldate
         ),
         array(
            '%s',
            '%s',
            '%d',
            '%d',
            '%s',
            '%s'
         )
         );
   $bookingid = $wpdb->get_row( 
      "SELECT booking_id FROM hotel_booking ORDER BY booking_id DESC LIMIT 1;" 
   );
   $bookingid= $bookingid->booking_id;
   foreach($roomids as $roomid){
      $wpdb -> insert(
         "reserved_rooms",
         array(
            'bookingid'=>$bookingid,
            'roomid'=>$roomid,
            'from_date'=>$from,
            'till_date'=>$till

         ),
         array(
            '%d',
            '%d',
            '%s',
            '%s'
         )
      );
   }
   
   do_action('after_payment',$bookingid);
}

add_shortcode('payment','payment');

// after_successful_payment();
// add_action('init', 'after_successful_payment', 1);