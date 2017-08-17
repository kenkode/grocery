<?php

namespace App\Controllers\AfricasTalking;

use App\Controllers\AfricasTalking\AfricasTalkingGateway;

class AfricasTalkingController {
  
  public function sendMessage($type, $recipients, $data = null, $payment = null) {
    $username   = "gasexpress";
    $apikey     = "d5d6061542e1d16d43491d84316b382b7ad117eab1c4de70d590a50c66d7a6c3";
    
//    $array = array(); // if(!is_array($recipients)) { // array_push($array, $recipients); // $recipients = $array; // }
    
    $recipients = implode($recipients, ',');
    
    if($type == 1) {
      $pin = mt_rand(100000, 999999);    
      $message    = "Confirmation pin " . $pin;
    }else if($type == 2) {
      $message = "New ". $data ." order. Payment : " . $payment;
    }else if($type == 3) {
      $message = "Order number " . $data . " confirmed. Delivery time is a maximum of 30 minutes";      
    }else if($type == 5) {
      $message = $data;
    }else if($type == 6) {
      $message = $data;      
    }
    
    $from = "GAS_EXPRESS";
    
    $gateway = new AfricasTalkingGateway($username, $apikey);
    
    try { 
        $results = $gateway->sendMessage($recipients, $message, $from);
    } catch ( AfricasTalkingGatewayException $e ) {
        echo "Encountered an error while sending: ".$e->getMessage();    
    }
    
    return $pin;
  }
  
}
