<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\City;

class District extends Model
{
    protected $guarded = [];

    public function city() {
    	return $this->belongsTo('App\Models\City', 'city_id');
    }
}
