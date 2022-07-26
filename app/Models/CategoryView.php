<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryView extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'category_id',
        'user_id',
        'ip',
    ];

    /**
     *  Setup model event hooks.
     */
    public static function boot()
    {
        parent::boot();

        self::creating(function ($model) {
            $model->user_id = $model->user_id ?? (request()->user() ? request()->user()->id : null);
            $model->ip = $model->ip ?? request()->ip() ?? null;
        });
    }
}
