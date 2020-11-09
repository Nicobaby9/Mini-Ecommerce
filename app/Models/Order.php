<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Models\{District, OrderDetail, Payment, Customer};

class Order extends Model
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

    public function district() {
        return $this->belongsTo(District::class);
    }

    public function getStatusLabelAttribute() {
        if ($this->status == 0) {
            return '<span class="badge badge-secondary">Baru</span>';
        } elseif ($this->status == 1) {
            return '<span class="badge badge-primary">Dikonfirmasi</span>';
        } elseif ($this->status == 2) {
            return '<span class="badge badge-info">Proses</span>';
        } elseif ($this->status == 3) {
            return '<span class="badge badge-warning">Dikirim</span>';
        }

        return '<span class="badge badge-success">Selesai</span>';
    }

    public function details() {
        return $this->hasMany(OrderDetail::class);
    }

    public function payment() {
        return $this->hasOne(Payment::class);
    }

    public function customer() {
        return $this->belongsTo(Customer::class);
    }

    public function return() {
        return $this->hasOne(OrderReturn::class);
    }
}
