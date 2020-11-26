<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MarkerView extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'marker_id',
        'user_id',
        'ip',
    ];
}
