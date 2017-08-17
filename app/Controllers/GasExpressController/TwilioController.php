<?php

namespace App\Controllers\GasExpressController;

use App\Controllers\Controller;
use \Twilio\Rest\Client;

class TwilioController extends Controller {
  
  protected $container;
  
  public function __construct($container) {
    $this->$container = $container;    
  }
  
  public function sendMessage($phoneNumber) {
    
    $phoneNumber = ltrim($phoneNumber, '0');
    $phoneNumber = "+254" . $phoneNumber;
    
    $pin = rand(1001, 9999);
    
    $from = "+18577767019";
    $to = "+254700460888";
    $message = "Your verification code is " . $pin;
    $data = array();
    
    $accountId = "AC569b7f92a715275961173f15b2bba3b6";
    $authToken = "54d95a06297a490af00c26a1fd498763";

    $client = new Client($accountId, $authToken);

    $message = $client->messages->create($to, array(
      'from' => $from,
      'body' => $message
    ));

    $data['message'] = $message;
    $data['success'] = true;

    echo json_encode($data);
    
  }
  
  
}


?>
