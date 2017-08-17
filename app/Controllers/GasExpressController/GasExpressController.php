<?php

// Location types [""]
// 0 -
// 1 - My Location
// 2 - Order Location

//Order Type
//0 - gas
//1 - acc
//2 - service
//3  - bulk

namespace App\Controllers\GasExpressController;

use App\Controllers\Controller;

use App\Models\Size;
use App\Models\Gas;
use App\Models\Location;
use App\Models\Order;
use App\Models\UserOrder;
use App\Models\DeliveryLocation;
use App\Models\User;
use App\Models\Service;
use App\Models\UserLocation;
use App\Models\Token;
use App\Models\BulkGas;
use App\Models\Staff;
use App\Models\Accessory;
use App\Models\GCMUser;
use App\Controllers\GCMController\GCMController;
use App\Controllers\AfricasTalking\AfricasTalkingController;

class GasExpressController extends Controller {

  public function getUpdate($request, $response) {
    $previous = $request->getParam('previous');
    $data = array();
    date_default_timezone_set("Africa/Nairobi");
    $orders = UserOrder::join('delivery_locations', 'delivery_locations.order_id', 'user_orders.order_id')
      ->join('locations', 'locations.id', 'delivery_locations.location_id')
      ->orderby('user_orders.created_at', 'desc')
      ->get();

    $current = count($orders);

    $data['total'] = $current;
    if($current > $previous) {
      $limit = $current - $previous;
      $new = UserOrder::join('delivery_locations', 'delivery_locations.order_id', 'user_orders.order_id')
        ->join('locations', 'locations.id', 'delivery_locations.location_id')
        ->orderby('user_orders.created_at', 'desc')
        ->limit($limit)
        ->get();
        $data['orders'] = $this->indexPre($new);
    }
    echo json_encode($data);

  }

  public function login($request, $response) {
    if(isset($_SESSION['user'])) {
      return $response->withRedirect($this->container->router->pathFor('dashboard'));
    }
    return $this->container->view->render($response, "login.twig");
  }

  public function logout($request, $response) {
    if(isset($_SESSION['user'])) {
      unset($_SESSION['user']);
    }
    return $this->container->view->render($response, "login.twig");
  }

  public function loginAuth($request, $response) {
    $username = $request->getParam('username');
    $password = $request->getParam('password');
    $staff = Staff::where('username', $username);
    if($staff->exists()) {
      if(password_verify($password, $staff->first()['password'])){
        $_SESSION['user'] = $username;
        return $response->withRedirect($this->container->router->pathFor('dashboard'));
      }
    }
    $this->login($request, $response);
  }

  public function users($request, $response) {
    $users = User::all();
    $total = count($users);
//    usort($users, function($a, $b) { // return $a['id'] - $b['id']; // });
    return $this->container->view->render($response, "dashboard.twig", ['content'=>'users.twig', 'title'=>'Customers', 'users'=>$users, 'total'=>$total]);
  }

  public function indexPre($orders) {

    $data = array();

      foreach($orders as $o) {
        $type;
        $order;
        $totalPrice = 0;
        $total = Order::where('order_id', $o->order_id)->get();
        $userOrder = UserOrder::where('order_id', $o->order_id)->first();
        $user = User::where('id', $userOrder->user_id)->first();
        if(count($total) >= 2) {
          $type = "Multiple";
        }else {
          $type = "Single";
        }

        foreach($total as $t) {
          $price = 0;
          switch($t->type) {
            case 0:
              $gas = Size::where('id', $t->item_id)->first();
              $price = $gas->price * $t->qty;
              break;
            case 1:
              $acc = Accessory::where('id', $t->item_id)->first();
              $price = $acc->price * $t->qty;
              break;
            case 3:
              $gas = BulkGas::first();
              $price = $gas->price * $t->qty;
              break;
          }
          $totalPrice += $price;
        }

        $order['fname'] = $user->fname;
        $order['lname'] = $user->lname;
        $order['phone'] = $user->phone;

        $order['order_id'] = $o->order_id;
        $order['viewed'] = $o->viewed;
        $order['type'] = $type;
        $order['location'] = $o->address;
        $order['price'] = $totalPrice;
        $order['type'] = $type;
        $order['status'] = $o->status;
        $order['created_at'] = date('y-M-d', strtotime($o->created_at));

        array_push($data, $order);
      }

      return $data;
  }

  public function index($request, $response) {
    $orders = UserOrder::join('delivery_locations', 'delivery_locations.order_id', 'user_orders.order_id')
      ->join('locations', 'locations.id', 'delivery_locations.location_id')
      ->orderby('user_orders.created_at', 'desc')
      ->get();
    $data = $this->indexPre($orders);

//    $africas = new AfricasTalkingController();
//    $africas->sendMessage(1, array("+254700460888", "+254733639791", "+254700417987"));
//    die;
//    var_dump($data);die;
      return $this->container->view->render($response, "dashboard.twig", ['content'=>'index.twig', 'title'=>'Dashboard', 'orders'=>$data]);
  }

  public function order($request, $response, $args) {
    $order_id = $args['order_id'];
    $user_id = UserOrder::where('order_id', $order_id)->first();
    $location = DeliveryLocation::join('locations', 'locations.id', 'delivery_locations.location_id')->where('order_id', $order_id)->first();
    $user = User::where('id', $user_id->user_id)->first();
    $items = array();
    $orders = Order::join('user_orders', 'user_orders.order_id', 'orders.order_id')
      ->where('orders.order_id', $order_id)
      ->get();
        foreach($orders as $order) {
          $price;
          $name;
          switch($order->type) {
            case 0:
              $gas = Size::join('gas', 'gas_id', 'gas.id')->where('sizes.id', $order->item_id)->first();
              $price = $gas->price;
              $name = $gas['name'];
              $id = $gas['id'];
              $orderDetails['name'] = $name;
              $orderDetails['price'] = $price;
              $orderDetails['id'] = $id;
              break;
            case 1:
              $acc = Accessory::where('id', $order->item_id)->first();
              $price = $acc->price;
              $name = $acc['name'];
              $id = $acc['id'];
              $orderDetails['name'] = $name;
              $orderDetails['price'] = $price;
              $orderDetails['id'] = $id;
              break;
            case 2:
              $service = Service::where('id', $order->item_id)->first();
              $price = "0";
              $metric;
              $id = $service['id'];
              $name = $service['name'];
              $orderDetails['name'] = $name;
              $orderDetails['price'] = $price;
              $orderDetails['id'] = $id;
              break;
            case 3:
              $gas = BulkGas::first();
              $price = $gas->price;
              $id = $gas['id'];
              $name = "Bulk Gas";
              $orderDetails['name'] = $name;
              $orderDetails['price'] = $price;
              $orderDetails['id'] = $id;
              break;
          }
          $orderDetails['quantity'] = $order->qty;

          array_push($items, $orderDetails);
        }

    $order = array();
    $totalPrice = 0;

    $total = Order::where('order_id', $order_id)->get();
        if(count($total) >= 2) {
          $type = "Multiple";
        }else {
          $type = "Single";
        }

        foreach($total as $t) {
          $price = 0;
          switch($t->type) {
            case 0:
              $gas = Size::where('id', $t->item_id)->first();
              $price = $gas->price * $t->qty;
              break;
            case 1:
              $acc = Accessory::where('id', $t->item_id)->first();
              $price = $acc->price * $t->qty;
              break;
            case 3:
              $gas = BulkGas::first();
              $price = $gas->price * $t->qty;
              break;
          }
          $totalPrice += $price;
          $order['created_at'] = $t->created_at;
        }

        $order['id'] = $order_id;
        $order['type'] = $type;
        $order['price'] = $totalPrice;
        $order['status'] = $user_id->status;

    $results = array('user'=>$user, 'location'=>$location, 'items'=>$items, 'order'=>$order);

    return $this->container->view->render($response, "dashboard.twig", ['content'=>'order.twig', 'title'=>'Order No. ' . $order_id, 'results'=>$results]);
  }

  public function updateStatus($request, $response) {
    $gcmController = new GCMController($this->container);
    $id = $request->getParam('id');
    $status = $request->getParam('status');
    $userOrder = UserOrder::where('order_id', $id);
    $userId = $userOrder->first()['user_id'];
    $user = User::where('id', $userId)->first();
    $africas = new AfricasTalkingController();
    $message;
    $updated;
    $code;
    $title;
    $token = GCMUser::where('user', $userId)->first()['gcm_id'];

    if($status == 0) {
      $updated = 1;
      $title = "Order Completed";
      $message = "Order number $id has been delivered";
      $code = 5;
    }else {
      $updated = 0;
      $title = "Order Pending";
      $message = "Order number $id is pending";
      $code = 6;
    }

    $fbData = array('title'=>"'$title'", 'message'=>"'$message'", 'user'=>"'$userId'");

    $gcmController->send($token, $fbData);

    $userOrder->update([
          'status' => $updated
    ]);

    echo $updated;

  }

  public function user($request, $response) {
      return $this->container->view->render($response, "dashboard.twig", ['content'=>'user.twig', 'title'=>'user']);
  }

  public function gases($request, $response) {
    $gases = Gas::orderby('name', 'asc')->get();
    $sizes = Size::join('gas', 'gas_id', 'gas.id')->select('sizes.id', 'name', 'size', 'price', 'gas_id')->get();
    return $this->container->view->render($response, "dashboard.twig", ['content'=>'gases.twig', 'title'=>'Gases', "gases" => $gases, 'sizes'=>$sizes]);
  }

  public function products($request, $response) {
    $accessories = Accessory::where('active', 1)->get();
    $bulk = BulkGas::all();
    $services = Service::where('active', 1)->get();
    $products = array(
      'accessories' => $accessories,
      'gases' => $bulk,
      'services' => $services
    );
    return $this->container->view->render($response, "dashboard.twig", ['content'=>'products.twig', 'title'=>'Products', "products" => $products]);
  }

  public function updateItem($request, $response) {
    $id = $request->getParam('id');
    $gid = $request->getParam('gid');
    $name = $request->getParam('name');
    $price = $request->getParam('price');
    $type = $request->getParam('type');

    switch($type) {
      case 0:
        Size::where('id', $id)
        ->update([
          'price' => $price
        ]);
        Gas::where('id', $gid)
        ->update([
          'name' => $name
        ]);
        break;
      case 1:
        Accessory::where('id', $id)
        ->update([
          "name" => $name,
          "price" => $price
        ]);
        break;
      case 2:
        Service::where('id', $id)
        ->update([
          "name" => $name
        ]);
        break;
    }
  }

  public function addGasType($request, $response) {
    $name = $request->getParam('type');
    $id = 1;

    while(Gas::where('id', $id)->exists()) {
      $id++;
    }

    Gas::create([
      'id' => $id,
      'name' => $name
    ]);

    echo $id;

  }

  public function addGas($request, $response) {
    $id = $request->getParam('type');
    $size = $request->getParam('size');
    $price = $request->getParam('price');

    if(!Size::where('gas_id', $id)->where('size', $size)->exists()) {
      Size::create([
        'gas_id' => $id,
        'size' => $size,
        'price' => $price
      ]);
    }else {
      echo "E";
    }
  }

  public function removeGas($request, $response) {
      $id = $request->getParam('id');
      Gas::where('id', $id)
        ->update([
          'active' => 0
        ]);
  }

  public function removeSize($request, $response) {
      $id = $request->getParam('id');
      $size = $request->getParam('size');
      $data = array(
        'id' => $id,
        'size' => $size
      );
      Size::where($data)
        ->delete();
  }

  public function removeProduct($request, $response) {
      $id = $request->getParam('id');
      $type = $request->getParam('type');

    switch($type) {
      case 1:
        Accessory::where('id', $id)->delete();
        break;
      case 2:
        Service::where('id', $id)->delete();
      case 3:
        BulkGas::where('id', $id)->delete();
        break;
    }
  }

  public function addAccessory($request, $response) {
      $id = 10099;
      $name = $request->getParam('name');
      $price = $request->getParam('price');

      while(Accessory::where('id', $id)->exists()) {
          $id++;
      }

      Accessory::create([
        "id" => $id,
        "name" => $name,
        "price" => $price
      ]);

      echo $id;

  }

  public function addBulkGas($request, $response) {
    $id = 10099;
    $price = $request->getParam('price');
    $bulk = BulkGas::where('id', $id);

    if($bulk->exists()) {
        $bulk->update([
          'price' => $price
        ]);
    }else {
      BulkGas::create([
      "id" => $id,
      "price" => $price
    ]);
    }

    echo $id;
  }

  public function addService($request, $response) {
    $name = $request->getParam('name');

    Service::create([
      "name" => $name
    ]);
  }

  public function removeAccessory($request, $response) {
    $id = $request->getParam('id');
    Accessory::where('id', $id)
      ->update([
        'active' => 0
      ]);
  }

  public function removeBulkGas($request, $response) {
    $id = $request->getParam('id');
    BulkGas::where('id', $id)
      ->update([
        'active' => 0
      ]);
  }

  public function removeService($request, $response) {
    $id = $request->getParam('id');
    Service::where('id', $id)
      ->update([
        'active' => 0
      ]);
  }

  public function getAccessories($request, $response) {
    $accessories = Accessory::select("id", "name", "price")->get();
    echo json_encode($accessories);
  }

  public function getServices($request, $response) {
    $services = Service::select("id", "name")->get();
    echo json_encode($services);
  }

  public function getBulkGas($request, $response) {
    $bulk = BulkGas::select("id", "price")->first()['price'];
    echo json_encode($bulk);
  }

  public function getSizes($request, $response, $args) {
//    $spec = $args['size'];
//    $sizes = Size::
//
//    $column = array();
//
//    if($spec != 0) {
//      $sizes = $sizes->join('gas', 'gas.id', 'sizes.id')->where('gas.id', $spec);
//      array_push($column, "name");
//    }
//
//    $columns = array_merge(array("sizes.id", "size", "price"), $column);
    $sizes = Size::join('gas', 'gas.id', 'sizes.gas_id')->groupby('size')->orderby('size', 'asc')->pluck('size');

    echo json_encode($sizes);
  }

  public function getGases($request, $response, $args) {
    $size = $request->getParam('size');

    $gases = Gas::join("sizes", "sizes.gas_id", "gas.id")->where('size', $size)
      ->select("name", "sizes.id", "size", "price")
      ->get();

    echo json_encode($gases);
  }

  public function populateOrder($request, $response) {
    $user = $request->getParam('user');
    $distributors = Gas::join('sizes', 'gas.id', 'sizes.id')->select('gas.id', 'name')->where('active', 1)->orderby('name', 'asc')->distinct()->get();
    $locations = Location::where('id', $user)->where('type', 1)->where('active', '!=', 2)->get();

    echo json_encode(array('distributors'=>$distributors, 'locations'=>$locations));

  }

  public function placeOrder($request, $response) {
    $orderJson = $request->getParam('json');
    $user = $request->getParam('user');
    $location = $request->getParam('location');
    $payment = $request->getParam('payment');

    $orders = json_decode($orderJson);

    $orderId = 11001;

    while(UserOrder::where('order_id', $orderId)->exists()) {
      $orderId++;
    }

    foreach($orders as $order) {
       $orderDetails = array(
          "item_id" => $order->id,
          "order_id" => $orderId,
          "type" => $order->type,
          "qty" => $order->quantity
        );
      Order::create($orderDetails);
    }

    UserOrder::create([
      "user_id" => $user,
      "order_id" => $orderId,
      "payment_method" => $payment
    ]);

    DeliveryLocation::create([
      "location_id" => $location,
      "order_id" => $orderId
    ]);

    $orderType;

    if(count($orders) > 1) {
      $orderType = "Multiple";
    }else {
      $orderType = "Single";
    }

    $phone = User::where('id', $user)->first()['phone'];

    $africasTalking = new AfricasTalkingController();
    $africasTalking->sendMessage(2, array("+254717890470"), $orderType, $payment);
    $africasTalking->sendMessage(3, array("+" . $phone), $orderId,  $orderType);

    return json_encode($orderId);

  }

  public function authOrderKey($request, $response) {
    $order_id = $request->getParam('order');

    if(UserOrder::where('order_id', $order_id)->exists() || Order::where('order_id', $order_id)->exists() || DeliveryLocation::where('order_id', $order_id)->exists()) {
      echo "E";
    }else {
      echo "DNE";
    }
  }

  public function getHistory($request, $response) {
    $user = $request->getParam('user');

    $history = UserOrder::join('delivery_locations', 'delivery_locations.order_id', 'user_orders.order_id')
      ->where('user_orders.user_id', $user)
      ->orderby('user_orders.created_at', 'desc')
      ->get();

    $data = array();

      foreach($history as $h) {
        $type;
        $order;
        $totalPrice = 0;
        $total = Order::where('order_id', $h->order_id)->get();
        if(count($total) >= 2) {
          $type = "Multiple";
        }else {
          $type = "Single";
        }

        foreach($total as $t) {
          $price = 0;
          switch($t->type) {
            case 0:
              $gas = Size::where('id', $t->item_id)->first();
              $price = $gas->price * $t->qty;
              break;
            case 1:
              $acc = Accessory::where('id', $t->item_id)->first();
              $price = $acc->price * $t->qty;
              break;
            case 3:
              $gas = BulkGas::first();
              $price = $gas->price * $t->qty;
              break;
          }
          $totalPrice += $price;
        }

        $order['type'] = $type;
        $order['order_id'] = $h->order_id;
        $order['status'] = $h->status;
        $order['type'] = $type;
        $order['price'] = $totalPrice;
        $order['created_at'] = date('m.d.y', strtotime($h->created_at));

        array_push($data, $order);
      }

    echo json_encode($data);
  }

  public function getOrderItems($request, $response) {
    $id = $request->getParam('id');
    $user = $request->getParam('user');
    $orderDetails = array();
    $data = array();

    $orders = Order::join('user_orders', 'user_orders.order_id', 'orders.order_id')
      ->where('orders.order_id', $id)
      ->where('user_id', $user)
      ->get();
        foreach($orders as $order) {
          $price;
          $name;
          switch($order->type) {
            case 0:
              $gas = Size::join('gas', 'gas_id', 'gas.id')->where('sizes.id', $order->item_id)->first();
              $price = $gas->price;
              $name = $gas['name'];
              $id = $gas['id'];
              $orderDetails['name'] = $name;
              $orderDetails['price'] = $price;
              $orderDetails['id'] = $id;
              break;
            case 1:
              $acc = Accessory::where('id', $order->item_id)->first();
              $price = $acc->price;
              $name = $acc['name'];
              $id = $acc['id'];
              $orderDetails['name'] = $name;
              $orderDetails['price'] = $price;
              $orderDetails['id'] = $id;
              break;
            case 2:
              $service = Service::where('id', $order->item_id)->first();
              $price = "0";
              $metric;
              $id = $service['id'];
              $name = $service['name'];
              $orderDetails['name'] = $name;
              $orderDetails['price'] = $price;
              $orderDetails['id'] = $id;
              break;
            case 3:
              $gas = BulkGas::first();
              $price = $gas->price;
              $id = $gas['id'];
              $name = "Bulk Gas";
              $orderDetails['name'] = $name;
              $orderDetails['price'] = $price;
              $orderDetails['id'] = $id;
              break;
          }
          $orderDetails['quantity'] = $order->qty;

          array_push($data, $orderDetails);
        }

    echo json_encode($data);
  }

  public function getOrders($request, $response) {
    $user = $request->getParam('user');
    $orders = Order::join("user_orders", 'user_orders.order_id', 'orders.order_id')
      ->where('user_id', $user)->get();

    echo json_encode($orders);
  }

  public function getLocations($request, $response) {
    $user = $request->getParam('user');
    $locations = Location::join("user_locations", 'user_locations.location_id', 'locations.id')
      ->where('user_locations.id', $user)
      ->where('type', 1)
      ->where('active', '!=', 2)
      ->get();

    echo json_encode($locations);
  }

  public function addLocation($request, $response) {
    $location = json_decode($request->getParam('location'));
    $user = $request->getParam('user');

    $type = $location->type;
    $address = $location->address;
    $lng = $location->lng;
    $lat = $location->lat;
    $desc = $location->description;
    $location_id = uniqid();

    Location::create([
      "type" => $type,
      "address" => $address,
      "lng" => $lng,
      "lat" => $lat,
      "description" => $desc,
      "id" => $location_id
    ]);

    UserLocation::create([
      'id' => $user,
      'location_id' => $location_id
    ]);

    echo json_encode($location_id);

  }

  public function disableLocation($request, $response) {
    $id = $request->getParam('location');
    $user = $request->getParam('user');

    Location::where('id', $id)
      ->update([
        'active' => 2
      ]);

      echo json_encode("Location removed successfully");
  }

  public function generateUniqueId($table, $column, $start_value) {
    while ($table::where($column, $start_value)->exists()) {
      $start_value++;
    }
    return $start_value;
  }

  public function authenticateUser($request, $response) {
    $user = array();
    $phone = $request->getParam('phone');

    $details = array(
      'phone' => $phone
    );

    $auth = User::where($details);

    if($auth->exists()) {
      $africas = new AfricasTalkingController();
      $u = $auth->first();
      $user['status'] = 'E';
      $user['user'] = $u;
      $user['token'] = $this->generateToken($u->id);
      $user['pin'] = $africas->sendMessage(1, array("+" . $phone));
    }else {
      $user['status'] = 'DNE';
    }

    echo json_encode($user);
  }

  public function validateUser($request, $response) {
    $user = $request->getParam('user');

    if(User::where('id', $user)->exists()) {
      echo "E";
    }else {
      "DNE";
    }

  }

  public function updateUser($request, $response) {
    $userDetails = json_decode($request->getParam('update'));
    $userId = $request->getParam('user');
    $message;

    $user = User::whereNotIn("id", [$userId]);

    if($user->where("phone", $userDetails->phone)->exists()) {
      $message = "Phone number exists";
    }else if($user->where("email", $userDetails->email)->exists()) {
      $message = "Email exists";
    }else {
      User::where("id", $userId)
        ->update([
          "fname" => $userDetails->fname,
          "lname" => $userDetails->lname,
          "phone" => $userDetails->phone,
          "email" => $userDetails->email
      ]);
      $message = "Successfully updated";
    }
      echo json_encode($message);
  }

  public function addUser($request, $response) {
    $user = array();
    $userDetails = json_decode($request->getParam('user'));
    $locationDetails = json_decode($request->getParam('location'));

    while (User::where('email', $userDetails->email)->exists()) {
      $user['status'] = "EE";
      echo json_encode($user);
      exit;
    }

    $location = uniqid();

    $userId = 10000001;

    while (User::where('id', $userId)->exists()) {
      $userId++;
    }

    User::create([
      "id" => $userId,
      "fname" => $userDetails->fname,
      "lname" => $userDetails->lname,
      "phone" => $userDetails->phone,
      "email" => $userDetails->email,
      "birthday" => $userDetails->birthday
    ]);

    Location::create([
      "type" => 1,
      "address" => $locationDetails->address,
      "lng" => $locationDetails->lng,
      "lat" => $locationDetails->lat,
      "description" => $locationDetails->description,
      "id" => $location
    ]);

    UserLocation::create([
      'id' => $userId,
      'location_id' => $location
    ]);

    $user['status'] = 'E';
    $user['user'] = User::where('id', $userId)->first();
    $user['token'] = $this->generateToken($userId);

    $africas = new AfricasTalkingController();
    $user['pin'] = $africas->sendMessage(1, array("+" . $userDetails->phone));

    echo json_encode($user);

  }

  public function generateToken($user) {
    $gen = strtotime(date("D M j G:i:s T Y"));
    $prevToken = Token::where('user', $user)->where('status', 0);
    if($prevToken->exists()) {
      $prevToken->update([
        'status' => 1
      ]);
    }

    $token = crypt(sha1($gen));
    Token::create([
      "id" => $token,
      "user" => $user,
      "status" => 0
    ]);
    return $token;
  }

}



 ?>
