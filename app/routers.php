<?php

use App\Middlewares\UserMiddleware;
use App\Middlewares\AuthMiddleware;

$app->get("/login", "GasExpressController:login")->SetName('login');

$app->post("/login", "GasExpressController:loginAuth");

$app->get("/register_gcm", "GCMController:registerApplication");

$app->group("", function() {

$this->get("/gases", "GasExpressController:gases")->SetName('gases');

$this->get("/products", "GasExpressController:products")->SetName('products');

$this->get("/users", "GasExpressController:users")->SetName('users');

$this->get("/get_updates", "GasExpressController:getUpdate");

//$this->get("/user", "GasExpressController:user")->SetName('user');

$this->get("/", "GasExpressController:index")->SetName('dashboard');

$this->get("/order/{order_id}", "GasExpressController:order")->SetName('order');

$this->post("/status", "GasExpressController:updateStatus");

$this->get("/logout", "GasExpressController:logout")->SetName('logout');

$this->post("/add_gas", "GasExpressController:addGas")->SetName('addGas');

$this->post("/add_gas_type", "GasExpressController:addGasType")->SetName('addGasType');

$this->post("/remove_gas", "GasExpressController:removeGas");

$this->post("/remove_size", "GasExpressController:removeSize");

$this->post("/remove_product", "GasExpressController:removeProduct");

$this->post("/add_service", "GasExpressController:addService");

$this->post("/add_bulkgas", "GasExpressController:addBulkGas");

$this->post("/add_accessory", "GasExpressController:addAccessory");

$this->post("/remove_service", "GasExpressController:removeService");

$this->post("/remove_bulkgas", "GasExpressController:removeBulkGas");

$this->post("/remove_accessory", "GasExpressController:removeAccessory");

$this->post("/update_item", "GasExpressController:updateItem");

})->add(new AuthMiddleware($container));

$app->get("/authenticate_user", "GasExpressController:authenticateUser");

$app->get("/add_user", "GasExpressController:addUser");

$app->get("/get_items", "GasExpressController:getOrderItems");

$app->group("", function() {

$this->get("/disable_location", "GasExpressController:disableLocation");

$this->get("/update_user", "GasExpressController:updateUser");

$this->get("/place_order", "GasExpressController:placeOrder");

$this->get("/add_location", "GasExpressController:addLocation");

$this->get("/my_orders", "GasExpressController:getHistory");

//$this->get("/get_items", "GasExpressController:getOrderItems");

$this->get("/get_gases", "GasExpressController:getGases");

$this->get("/get_accessories", "GasExpressController:getAccessories");

$this->get("/get_services", "GasExpressController:getServices");

$this->get("/get_sizes", "GasExpressController:getSizes");

$this->get("/get_bulk_gas", "GasExpressController:getBulkGas");

$this->get("/my_locations", "GasExpressController:getLocations");

})->add(new UserMiddleware());

 ?>
