<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Post extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'title',
        'body_markdown',
        'excerpt',
        'slug',
        'user_id',
        'header_image',
        'published_at',
    ];

    protected $dates = [
        'published_at',
    ];

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function categories()
    {
        return $this->belongsToMany('App\Category', 'post_categories');
    }

    public function views()
    {
        return $this->hasMany('App\PostView');
    }

    /**
     * Scope a query to only include popular users.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopePublished($query)
    {
        return $query->where('published_at', '!=', null);
    }

}
