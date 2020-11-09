<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class OrderReturn extends Model
{
	protected $guarded = [];
    protected $appends = ['status_label'];

    protected static function boot() {
        parent::boot();

        static::creating(function ($model) {
            if ( ! $model->getKey()) {
                $model->{$model->getKeyName()} = (string) Str::uuid();
            }
        });
    }

    public function getIncrementing()
    {
        return false;
    }

    public function getKeyType()
    {
        return 'string';
    }

    public function getStatusLabelAttribute() {
        if($this->status == 0) {
            return '<span class="badge badge-secondary"> Menunggu Konfirmasi </span>';
        } elseif ($this->status == 2) {
            return '<span class="badge badge-danger"> Ditolak </span>';
        }

        return '<span class="badge badge-success"> Selesai </span>';
    }
}
