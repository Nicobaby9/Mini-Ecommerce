<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Province;

class City extends Model
{
    public function province() {
    	return $this->belongsTo(Province::class);
    }
}
