<?php

namespace App;

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
}
