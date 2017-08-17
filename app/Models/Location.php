<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Location extends Model {

    protected $fillable = [
      "type",
      "address",
      "lng",
      "lat",
      "id",
      "description",
      "active"
    ];
}

 ?>
