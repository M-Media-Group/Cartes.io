<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\Pivot;

class MapUser extends Pivot
{
    use HasFactory;

    public $incrementing = true;

    protected $table = 'map_users';

    protected $fillable = [
        'map_id',
        'user_id',
        'can_create_markers',
        'added_by_user_id',
    ];

    protected $hidden = ['added_by_user_id', 'map_id', 'user_id'];

    protected $casts = [
        'can_create_markers' => 'boolean',
    ];

    // On create, by default we set the created_by_user_id to the user_id
    protected static function booted()
    {
        static::creating(function ($mapUser) {
            $mapUser->added_by_user_id = $mapUser->added_by_user_id ?? optional(request()->user())->id;
        });
    }

    // Define the relationship with the Map model
    public function map()
    {
        return $this->belongsTo(Map::class);
    }

    // Define the relationship with the User model
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Define the relationship with the User model for the user who added this map user
    public function addedByUser()
    {
        return $this->belongsTo(User::class, 'added_by_user_id');
    }
}
