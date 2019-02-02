<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class QrCode extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'redirect_to',
        'location',
    ];

    public function views()
    {
        return $this->hasMany('App\QrCodeView');
    }
    // public function getLocationAttribute()
    // {
    //     return $this->selectRaw('AsText(location) as location')->get();
    // }
}
