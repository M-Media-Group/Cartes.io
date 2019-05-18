<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class IncidentView extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'incident_id',
        'user_id',
        'ip',
    ];
}
