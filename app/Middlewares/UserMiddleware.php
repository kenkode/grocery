<?php

namespace App\Middlewares;

use App\Models\Token;

class UserMiddleware {

  public function __invoke($request, $response, $next) {
      $token = $request->getParam('token');
      if(Token::where('id', $token)->exists()) {
          $response = $next($request, $response);
      }else {
        echo "Wrong/Bad gateway.";
      }
      return $response;
  }


}



 ?>
