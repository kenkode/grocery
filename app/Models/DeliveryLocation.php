<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeliveryLocation extends Model {

    protected $fillable = [
      "order_id",
      "location_id"
    ];
}

 ?>
