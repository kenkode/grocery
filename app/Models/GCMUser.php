<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GCMUser extends Model {

    protected $fillable = [
      "user",
      "gcm_id"
    ];
}

 ?>
