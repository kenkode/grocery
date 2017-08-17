<?php

namespace App\Controllers\GCMController;

define('FIREBASE_API_KEY', 'AAAAS_v9flE:APA91bEnBZ39mqC_cNwoG8oflHRoVHaF8XloF_n31C5q94HBs9aThTDOSdUHcK0VaamzLGPrrJP8-8X87hIRq1weMlbJOuUCVHx6ZMizKcPSE_eOVSnrPXzDpGCs7F7O5wyotflr93tB');

use App\Models\GCMUser;
use App\Controllers\Controller;

class GCMController extends Controller {

//Register app
//Update apps
//Send notifications

  public function registerApplication($request, $response) {
    $user = $request->getParam('user');
    $gcm_id = $request->getParam('fcm');
    if(GCMUser::where('user', $user)->exists()) {
      GCMUser::where('user', $user)
        ->update([
            'gcm_id' => $gcm_id
        ]);
    }else {
      GCMUser::create([
        'user' => $user,
        'gcm_id' => $gcm_id
      ]);
    }
  }

  public function send($to, $message) {
    $fields = array(
      'to' => $to,
      'data' => $message
    );
    $this->sendNotification($fields);
  }
  public function sendNotification($data) {
    $url = 'https://fcm.googleapis.com/fcm/send';

    $headers = array(
      'Authorization: key='.FIREBASE_API_KEY,
      'Content-Type: application/json'
    );

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));

    $result = curl_exec($ch);
    if($result === false) {
        die("CURL Failed:".curl_error($ch));
    }

    curl_close($ch);

    return $result;

  }






}


?>
