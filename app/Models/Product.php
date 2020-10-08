<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use App\Models\Category;

class Product extends Model
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
            return '<span class="badge badge-secondary"> Draft </span>';
        }

        return '<span class="badge badge-success"> Active </span>';
    }

    public function setSlugAttribute($value) {
        $this->attributes['slug'] = Str::slug($value);
    }

    public function category() {
        return $this->belongsTo(Category::class);
    }
}
