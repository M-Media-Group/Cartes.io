<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class QrCodeView extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'qr_code_id',
        'user_id',
        'ip',
    ];
}
