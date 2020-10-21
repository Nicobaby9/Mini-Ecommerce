<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Models\Product;

class OrderDetail extends Model
{
	protected $guarded = [];

     protected static function boot() {
        parent::boot();

        static::creating(function ($model) {
            if ( ! $model->getKey()) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    public function getIncrementing() {
        return false;
    }

    public function getKeyType() {
        return 'string';
    }

    public function product() {
        return $this->belongsTo(Product::class);
    }
}
