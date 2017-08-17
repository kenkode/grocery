<?php

namespace App\Middlewares;

use App\Models\Staff;
use App\Controllers\Controller;

class AuthMiddleware extends Controller {
  
  public function __invoke($request, $response, $next) {
      if(isset($_SESSION['user'])) {
          $response = $next($request, $response);
      }else {
        return $response->withRedirect($this->container->router->pathFor('login'));
      }
      return $response;
  }


}



 ?>
