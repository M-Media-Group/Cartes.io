<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PostView extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'post_id',
        'user_id',
        'ip',
    ];
    /**
     * Scope a query to only include popular users.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeGrouped($query)
    {
        return $query->addSelect('DISTINCT ip')->groupBy('ip', 'user_id');
    }
}
