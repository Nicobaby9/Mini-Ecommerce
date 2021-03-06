<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Payment extends Model
{
	protected $guarded = [];
    protected $append = ['status_label'];

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
        if ($this->status == 0) {
            return '<span class="badge badge-secondary">Menunggu Konfirmasi </span>';
        }

        return '<span class="badge badge=success"> Diterima </span>';
    }
}
