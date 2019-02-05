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

    protected $appends = ['rank'];

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

    public function getRankAttribute()
    {
        $views = (int) abs($this->views()->count());
        //return (log10(strtotime($this->published_at) + 1));
        //return floor(log($views + 1)) / 400000;
        return floor(log($views + 1)) / 400000 + log10(strtotime($this->published_at) + 1);
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

    public function scopeSearch($q)
    {
        return empty(request()->search) ? $q : $q->where('title', 'like', '%' . request()->search . '%')->orWhere('excerpt', 'like', '%' . request()->search . '%');
    }

}
