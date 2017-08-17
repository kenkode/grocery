<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model {

    protected $fillable = [
      "item_id",
      "order_id",
      "type",
      "qty"
    ];
}

 ?>
